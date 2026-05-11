<?php

namespace app\service;

use app\model\BizShipment;
use app\model\BizShipmentItem;
use app\model\BizPlan;
use app\model\BizPlanItem;
use app\model\BizInventory;
use app\service\BizPlanService;

class BizShipmentService
{
    public function selectShipmentList($params = [])
    {
        $query = BizShipment::with(['plan']);

        if (!empty($params['shipment_no'])) {
            $query->where('shipment_no', 'like', '%' . $params['shipment_no'] . '%');
        }
        if (!empty($params['enterprise_name'])) {
            $query->where('enterprise_name', 'like', '%' . $params['enterprise_name'] . '%');
        }
        if (isset($params['shipment_status']) && $params['shipment_status'] !== '') {
            $query->where('shipment_status', $params['shipment_status']);
        }
        if (!empty($params['plan_id'])) {
            $query->where('plan_id', $params['plan_id']);
        }
        if (!empty($params['begin_date']) && !empty($params['end_date'])) {
            $query->whereBetween('create_time', [$params['begin_date'] . ' 00:00:00', $params['end_date'] . ' 23:59:59']);
        }

        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('shipment_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectShipmentById($shipmentId)
    {
        return BizShipment::with(['items', 'plan', 'enterprise'])->find($shipmentId);
    }

    public function generateShipmentNo()
    {
        $today = date('Ymd');
        $lastShipment = BizShipment::where('shipment_no', 'like', 'SH' . $today . '%')
            ->orderBy('shipment_id', 'desc')
            ->first();

        if ($lastShipment) {
            $lastSeq = intval(substr($lastShipment->shipment_no, -3));
            $seq = $lastSeq + 1;
        } else {
            $seq = 1;
        }

        return 'SH' . $today . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function insertShipment($data)
    {
        $plan = BizPlan::find($data['plan_id']);
        if (!$plan || $plan->audit_status !== '2') {
            return ['error' => '方案未审核通过，无法创建出货单'];
        }

        $items = $data['items'] ?? [];
        unset($data['items']);

        $totalQuantity = 0;
        $totalAmount = 0;

        foreach ($items as $item) {
            $amount = bcmul($item['discount_price'] ?? $item['sale_price'] ?? 0, $item['quantity'] ?? 0, 2);
            $item['amount'] = $amount;
            $totalQuantity += intval($item['quantity'] ?? 0);
            $totalAmount = bcadd($totalAmount, $amount, 2);
        }

        if (bccomp($totalAmount, $plan->remaining_amount, 2) > 0) {
            return ['error' => '出货总金额不能大于方案剩余金额'];
        }

        foreach ($items as $item) {
            if (!empty($item['plan_item_id'])) {
                $planItem = BizPlanItem::find($item['plan_item_id']);
                if ($planItem && $planItem->remaining_quantity > 0) {
                    if (intval($item['quantity'] ?? 0) > $planItem->remaining_quantity) {
                        return ['error' => '货品"' . ($item['product_name'] ?? '') . '"出货数量不能大于方案剩余数量'];
                    }
                }
            }
        }

        $data['shipment_no'] = $this->generateShipmentNo();
        $data['total_quantity'] = $totalQuantity;
        $data['total_amount'] = $totalAmount;
        $data['shipment_status'] = '0';
        $data['create_time'] = date('Y-m-d H:i:s');

        $shipment = BizShipment::create($data);

        foreach ($items as $item) {
            $item['shipment_id'] = $shipment->shipment_id;
            if (empty($item['discount_price'])) {
                $item['discount_price'] = $item['sale_price'] ?? 0;
            }
            $item['amount'] = bcmul($item['discount_price'], $item['quantity'] ?? 0, 2);
            BizShipmentItem::create($item);
        }

        return $shipment;
    }

    public function updateShipment($data)
    {
        $shipment = BizShipment::find($data['shipment_id']);
        if (!$shipment || $shipment->shipment_status !== '0') {
            return false;
        }

        $items = $data['items'] ?? [];
        unset($data['items']);

        $totalQuantity = 0;
        $totalAmount = 0;
        foreach ($items as $item) {
            $amount = bcmul($item['discount_price'] ?? $item['sale_price'] ?? 0, $item['quantity'] ?? 0, 2);
            $totalQuantity += intval($item['quantity'] ?? 0);
            $totalAmount = bcadd($totalAmount, $amount, 2);
        }

        $data['total_quantity'] = $totalQuantity;
        $data['total_amount'] = $totalAmount;
        $data['update_time'] = date('Y-m-d H:i:s');

        $fillable = [
            'contact_person', 'contact_phone', 'shipping_address',
            'total_quantity', 'total_amount', 'remark', 'update_by', 'update_time'
        ];
        $updateData = array_intersect_key($data, array_flip($fillable));

        $result = BizShipment::where('shipment_id', $data['shipment_id'])->update($updateData);

        if (!empty($items)) {
            BizShipmentItem::where('shipment_id', $data['shipment_id'])->delete();
            foreach ($items as $item) {
                $item['shipment_id'] = $data['shipment_id'];
                if (empty($item['discount_price'])) {
                    $item['discount_price'] = $item['sale_price'] ?? 0;
                }
                $item['amount'] = bcmul($item['discount_price'], $item['quantity'] ?? 0, 2);
                BizShipmentItem::create($item);
            }
        }

        return $result;
    }

    public function deleteShipmentByIds($shipmentIds)
    {
        foreach ($shipmentIds as $shipmentId) {
            $shipment = BizShipment::find($shipmentId);
            if ($shipment && $shipment->shipment_status !== '0') {
                return false;
            }
        }
        BizShipmentItem::whereIn('shipment_id', $shipmentIds)->delete();
        return BizShipment::whereIn('shipment_id', $shipmentIds)->delete();
    }

    public function audit($data)
    {
        $shipmentId = $data['shipment_id'];
        $passed = $data['passed'] ?? true;

        $shipment = BizShipment::find($shipmentId);
        if (!$shipment || $shipment->shipment_status !== '0') {
            return false;
        }

        return BizShipment::where('shipment_id', $shipmentId)->update([
            'shipment_status' => $passed ? '1' : '4',
            'audit_by' => $data['audit_by'] ?? '',
            'audit_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function ship($data)
    {
        $shipmentId = $data['shipment_id'];
        $shipment = BizShipment::find($shipmentId);
        if (!$shipment || $shipment->shipment_status !== '1') {
            return false;
        }

        return BizShipment::where('shipment_id', $shipmentId)->update([
            'shipment_status' => '2',
            'logistics_company' => $data['logistics_company'] ?? $shipment->logistics_company,
            'logistics_no' => $data['logistics_no'] ?? $shipment->logistics_no,
            'shipment_date' => date('Y-m-d'),
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function confirmReceipt($shipmentId)
    {
        $shipment = BizShipment::with('items')->find($shipmentId);
        if (!$shipment || $shipment->shipment_status !== '2') {
            return false;
        }

        \support\Db::beginTransaction();
        try {
            BizShipment::where('shipment_id', $shipmentId)->update([
                'shipment_status' => '3',
                'receipt_date' => date('Y-m-d'),
                'update_time' => date('Y-m-d H:i:s')
            ]);

            $planService = new BizPlanService();
            $planService->updateShippedAmount($shipment->plan_id, $shipment->total_amount);

            foreach ($shipment->items as $item) {
                if (!empty($item->product_id)) {
                    $inventory = BizInventory::where('product_id', $item->product_id)->first();
                    if ($inventory) {
                        $inventory->quantity = max(0, $inventory->quantity - $item->quantity);
                        $inventory->save();
                    }
                }

                if (!empty($item->plan_item_id)) {
                    $planService->updateItemShippedQuantity($item->plan_item_id, $item->quantity);
                }
            }

            \support\Db::commit();
            return true;
        } catch (\Exception $e) {
            \support\Db::rollBack();
            \support\Log::error('确认收货失败: ' . $e->getMessage());
            return false;
        }
    }

    public function changeStatus($shipmentId, $status)
    {
        return BizShipment::where('shipment_id', $shipmentId)->update([
            'shipment_status' => $status,
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }
}
