<?php

namespace app\service;

use app\model\BizSalesOrder;
use app\model\BizOrderItem;
use app\model\BizCustomerPackage;
use app\model\BizPackageItem;
use app\service\BizCustomerArchiveService;

class BizSalesOrderService
{
    public function selectOrderList($params = [])
    {
        $query = BizSalesOrder::query();
        if (!empty($params['customer_id'])) $query->where('customer_id', $params['customer_id']);
        if (!empty($params['order_no'])) $query->where('order_no', 'like', '%' . $params['order_no'] . '%');
        if (!empty($params['customer_name'])) $query->where('customer_name', 'like', '%' . $params['customer_name'] . '%');
        if (!empty($params['enterprise_id'])) $query->where('enterprise_id', $params['enterprise_id']);
        if (!empty($params['store_id'])) $query->where('store_id', $params['store_id']);
        if (isset($params['order_status']) && $params['order_status'] !== '') $query->where('order_status', $params['order_status']);
        if (!empty($params['creator_user_id'])) $query->where('creator_user_id', $params['creator_user_id']);
        if (isset($params['enterprise_audit_status']) && $params['enterprise_audit_status'] !== '') $query->where('enterprise_audit_status', $params['enterprise_audit_status']);
        if (isset($params['finance_audit_status']) && $params['finance_audit_status'] !== '') $query->where('finance_audit_status', $params['finance_audit_status']);
        if (!empty($params['start_date'])) $query->where('create_time', '>=', $params['start_date'] . ' 00:00:00');
        if (!empty($params['end_date'])) $query->where('create_time', '<=', $params['end_date'] . ' 23:59:59');
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->with('items')->orderBy('order_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectOrderById($orderId)
    {
        return BizSalesOrder::with('items')->find($orderId);
    }

    public function generateOrderNo()
    {
        $date = date('Ymd');
        $lastOrder = BizSalesOrder::where('order_no', 'like', 'SO' . $date . '%')->orderBy('order_id', 'desc')->first();
        $seq = $lastOrder ? intval(substr($lastOrder->order_no, -4)) + 1 : 1;
        return 'SO' . $date . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function insertOrder($data, $items = [])
    {
        $data['order_no'] = $this->generateOrderNo();
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['enterprise_audit_status'] = '0';
        $data['finance_audit_status'] = '0';

        $dealAmount = 0;
        $paidAmount = 0;

        $convertedItems = [];
        foreach ($items as $item) {
            $quantity = intval($item['count'] ?? $item['quantity'] ?? 1);
            $itemDealAmount = floatval($item['price'] ?? $item['deal_amount'] ?? $item['dealPrice'] ?? 0);
            $itemPaidAmount = floatval($item['paid_amount'] ?? $item['paidAmount'] ?? 0);
            $itemOwedAmount = $itemDealAmount - $itemPaidAmount;
            $unitPrice = $quantity > 0 ? round($itemDealAmount / $quantity, 2) : 0;

            $convertedItem = [
                'product_name' => $item['item_name'] ?? $item['product_name'] ?? $item['productName'] ?? '',
                'quantity' => $quantity,
                'deal_amount' => $itemDealAmount,
                'paid_amount' => $itemPaidAmount,
                'unit_price' => $unitPrice,
                'owed_amount' => $itemOwedAmount,
                'remark' => $item['remark'] ?? null,
                'create_time' => date('Y-m-d H:i:s')
            ];
            $convertedItems[] = $convertedItem;

            $dealAmount += $itemDealAmount;
            $paidAmount += $itemPaidAmount;
        }

        $data['deal_amount'] = $dealAmount;
        $data['paid_amount'] = $paidAmount;
        $data['owed_amount'] = $dealAmount - $paidAmount;

        if (!isset($data['order_status'])) {
            $data['order_status'] = '1';
        }

        $order = BizSalesOrder::create($data);

        foreach ($convertedItems as $item) {
            $item['order_id'] = $order->order_id;
            BizOrderItem::create($item);
        }

        if (!empty($convertedItems)) {
            $this->generatePackage($order, $convertedItems);
        }

        try {
            $archiveService = new BizCustomerArchiveService();
            $archiveService->insertArchiveFromOrder($order);
        } catch (\Exception $e) {
            \support\Log::error('写入开单档案失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return $order;
    }

    public function updateOrder($data, $items = [])
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        if (!empty($items)) {
            $dealAmount = 0;
            $paidAmount = 0;
            foreach ($items as $item) {
                $dealAmount += $item['deal_amount'] ?? 0;
                $paidAmount += $item['paid_amount'] ?? 0;
            }
            $data['deal_amount'] = $dealAmount;
            $data['paid_amount'] = $paidAmount;
            $data['owed_amount'] = $dealAmount - $paidAmount;
        }

        $result = BizSalesOrder::where('order_id', $data['order_id'])->update($data);

        if (!empty($items)) {
            BizOrderItem::where('order_id', $data['order_id'])->delete();
            foreach ($items as $item) {
                $item['order_id'] = $data['order_id'];
                $item['create_time'] = date('Y-m-d H:i:s');
                BizOrderItem::create($item);
            }
        }

        return $result;
    }

    public function deleteOrderByIds($orderIds)
    {
        BizOrderItem::whereIn('order_id', $orderIds)->delete();
        return BizSalesOrder::whereIn('order_id', $orderIds)->delete();
    }

    public function enterpriseAudit($orderId, $auditBy)
    {
        return BizSalesOrder::where('order_id', $orderId)->update([
            'enterprise_audit_status' => '1',
            'enterprise_audit_by' => $auditBy,
            'enterprise_audit_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function financeAudit($orderId, $auditBy)
    {
        return BizSalesOrder::where('order_id', $orderId)->update([
            'finance_audit_status' => '1',
            'finance_audit_by' => $auditBy,
            'finance_audit_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function generatePackage($order, $items)
    {
        if (empty($items)) return;

        $packageNo = $this->generatePackageNo();
        $packageName = !empty($order->package_name) ? $order->package_name : ($order->customer_name . ' ' . date('Y-m-d') . ' 持卡记录');

        $paidAmount = 0;
        foreach ($items as $item) {
            $paidAmount += floatval($item['paid_amount'] ?? 0);
        }
        $owedAmount = floatval($order->deal_amount) - $paidAmount;

        $package = BizCustomerPackage::create([
            'package_no' => $packageNo,
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer_name,
            'order_id' => $order->order_id,
            'order_no' => $order->order_no,
            'enterprise_id' => $order->enterprise_id,
            'store_id' => $order->store_id,
            'package_name' => $packageName,
            'total_amount' => $order->deal_amount,
            'paid_amount' => $paidAmount,
            'owed_amount' => $owedAmount,
            'status' => '1',
            'remark' => $order->remark ?? null,
            'create_by' => $order->create_by,
            'create_time' => date('Y-m-d H:i:s')
        ]);

        foreach ($items as $item) {
            $quantity = intval($item['quantity'] ?? 1);
            $dealPrice = floatval($item['deal_amount'] ?? 0);
            $itemPaidAmount = floatval($item['paid_amount'] ?? 0);
            $itemOwedAmount = floatval($item['owed_amount'] ?? 0);
            $unitPrice = floatval($item['unit_price'] ?? ($quantity > 0 ? round($dealPrice / $quantity, 2) : 0));
            BizPackageItem::create([
                'package_id' => $package->package_id,
                'product_name' => $item['product_name'],
                'unit_price' => $unitPrice,
                'deal_price' => $dealPrice,
                'paid_amount' => $itemPaidAmount,
                'owed_amount' => $itemOwedAmount,
                'total_quantity' => $quantity,
                'used_quantity' => 0,
                'remaining_quantity' => $quantity,
                'remark' => $item['remark'] ?? null
            ]);
        }
    }

    private function generatePackageNo()
    {
        $date = date('Ymd');
        $lastPackage = BizCustomerPackage::where('package_no', 'like', 'PK' . $date . '%')->orderBy('package_id', 'desc')->first();
        $seq = $lastPackage ? intval(substr($lastPackage->package_no, -4)) + 1 : 1;
        return 'PK' . $date . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
