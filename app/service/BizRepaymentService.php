<?php

namespace app\service;

use app\model\BizRepaymentRecord;
use app\model\BizSalesOrder;
use app\model\BizOrderItem;
use app\model\BizCustomerPackage;
use app\model\BizPackageItem;
use app\service\BizCustomerArchiveService;
use support\Db;

class BizRepaymentService
{
    public function selectRepaymentList($params = [])
    {
        $query = BizRepaymentRecord::query();
        
        if (!empty($params['customer_id'])) {
            $query->where('customer_id', $params['customer_id']);
        }
        if (!empty($params['customer_name'])) {
            $query->where('customer_name', 'like', '%' . $params['customer_name'] . '%');
        }
        if (!empty($params['repayment_no'])) {
            $query->where('repayment_no', 'like', '%' . $params['repayment_no'] . '%');
        }
        if (!empty($params['package_id'])) {
            $query->where('package_id', $params['package_id']);
        }
        if (!empty($params['order_id'])) {
            $query->where('order_id', $params['order_id']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (!empty($params['enterprise_id'])) {
            $query->where('enterprise_id', $params['enterprise_id']);
        }
        if (!empty($params['store_id'])) {
            $query->where('store_id', $params['store_id']);
        }
        if (!empty($params['start_date'])) {
            $query->where('create_time', '>=', $params['start_date'] . ' 00:00:00');
        }
        if (!empty($params['end_date'])) {
            $query->where('create_time', '<=', $params['end_date'] . ' 23:59:59');
        }
        
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        
        return $query->orderBy('repayment_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectOwedPackageList($customerId)
    {
        return BizCustomerPackage::with('items')
            ->where('customer_id', $customerId)
            ->where('owed_amount', '>', 0)
            ->where('status', '1')
            ->orderBy('create_time', 'desc')
            ->get();
    }

    public function selectRepaymentById($repaymentId)
    {
        return BizRepaymentRecord::find($repaymentId);
    }

    public function insertRepayment($data)
    {
        $data['repayment_no'] = $this->generateRepaymentNo();
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['status'] = '0';
        
        Db::beginTransaction();
        try {
            $order = $this->createRepaymentOrder($data);
            
            $data['repayment_order_id'] = $order->order_id;
            $data['repayment_order_no'] = $order->order_no;
            
            $repayment = BizRepaymentRecord::create($data);
            
            Db::commit();

            try {
                $archiveService = new BizCustomerArchiveService();
                $archiveService->insertArchiveFromRepayment($repayment);
            } catch (\Exception $e) {
                \support\Log::error('写入还款档案失败: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            }

            return $repayment;
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }
    }

    private function createRepaymentOrder($data)
    {
        $orderNo = $this->generateRepaymentOrderNo();
        $repaymentAmount = floatval($data['repayment_amount'] ?? 0);
        
        $order = BizSalesOrder::create([
            'order_no' => $orderNo,
            'customer_id' => $data['customer_id'] ?? null,
            'customer_name' => $data['customer_name'] ?? '',
            'enterprise_id' => $data['enterprise_id'] ?? null,
            'enterprise_name' => $data['enterprise_name'] ?? '',
            'store_id' => $data['store_id'] ?? null,
            'store_name' => $data['store_name'] ?? '',
            'total_amount' => $repaymentAmount,
            'deal_amount' => $repaymentAmount,
            'paid_amount' => $repaymentAmount,
            'owed_amount' => 0,
            'order_status' => '3',
            'package_name' => '还款-' . ($data['package_name'] ?? ''),
            'enterprise_audit_status' => '1',
            'finance_audit_status' => '1',
            'enterprise_audit_by' => $data['creator_user_name'] ?? '',
            'enterprise_audit_time' => date('Y-m-d H:i:s'),
            'finance_audit_by' => $data['creator_user_name'] ?? '',
            'finance_audit_time' => date('Y-m-d H:i:s'),
            'creator_user_id' => $data['creator_user_id'] ?? null,
            'creator_user_name' => $data['creator_user_name'] ?? '',
            'customer_feedback' => '',
            'remark' => ($data['remark'] ?? '') . ' [还款订单]',
            'create_by' => $data['create_by'] ?? '',
            'create_time' => date('Y-m-d H:i:s')
        ]);

        BizOrderItem::create([
            'order_id' => $order->order_id,
            'product_name' => '还款-' . ($data['package_name'] ?? '欠款'),
            'quantity' => 1,
            'plan_price' => $repaymentAmount,
            'is_deal' => 1,
            'deal_amount' => $repaymentAmount,
            'paid_amount' => $repaymentAmount,
            'customer_feedback' => '',
            'remark' => '支付方式: ' . ($data['payment_method'] ?? '-'),
            'create_time' => date('Y-m-d H:i:s')
        ]);

        return $order;
    }

    public function updateRepayment($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return BizRepaymentRecord::where('repayment_id', $data['repayment_id'])->update($data);
    }

    public function auditRepayment($repaymentId, $auditBy)
    {
        $repayment = BizRepaymentRecord::find($repaymentId);
        if (!$repayment) {
            return false;
        }
        
        if ($repayment->status !== '0') {
            return false;
        }
        
        Db::beginTransaction();
        try {
            $repayment->status = '1';
            $repayment->audit_by = $auditBy;
            $repayment->audit_time = date('Y-m-d H:i:s');
            $repayment->save();
            
            $this->updatePackageOwedAmount($repayment->package_id, $repayment->repayment_amount);
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }
    }

    public function cancelRepayment($repaymentId)
    {
        $repayment = BizRepaymentRecord::find($repaymentId);
        if (!$repayment) {
            return false;
        }
        
        if ($repayment->status !== '0') {
            return false;
        }
        
        Db::beginTransaction();
        try {
            $repayment->status = '2';
            $repayment->update_time = date('Y-m-d H:i:s');
            $repayment->save();
            
            if ($repayment->repayment_order_id) {
                BizSalesOrder::where('order_id', $repayment->repayment_order_id)->update([
                    'order_status' => '4',
                    'update_time' => date('Y-m-d H:i:s')
                ]);
            }
            
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }
    }

    private function updatePackageOwedAmount($packageId, $repaymentAmount)
    {
        $package = BizCustomerPackage::find($packageId);
        if (!$package) {
            return;
        }
        
        $newOwedAmount = max(0, $package->owed_amount - $repaymentAmount);
        $newPaidAmount = $package->paid_amount + $repaymentAmount;
        
        $package->owed_amount = $newOwedAmount;
        $package->paid_amount = $newPaidAmount;
        $package->save();
    }

    private function generateRepaymentNo()
    {
        $date = date('Ymd');
        $lastRecord = BizRepaymentRecord::where('repayment_no', 'like', 'RP' . $date . '%')
            ->orderBy('repayment_id', 'desc')
            ->first();
        $seq = $lastRecord ? intval(substr($lastRecord->repayment_no, -4)) + 1 : 1;
        return 'RP' . $date . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    private function generateRepaymentOrderNo()
    {
        $date = date('Ymd');
        $lastOrder = BizSalesOrder::where('order_no', 'like', 'SO' . $date . '%')
            ->orderBy('order_id', 'desc')
            ->first();
        $seq = $lastOrder ? intval(substr($lastOrder->order_no, -4)) + 1 : 1;
        return 'SO' . $date . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
