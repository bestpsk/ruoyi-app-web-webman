<?php

namespace app\service;

use app\model\BizPlan;
use app\model\BizPlanItem;
use app\model\BizEnterprise;

class BizPlanService
{
    public function selectEnterpriseList($params = [])
    {
        $query = BizEnterprise::where('status', '0');

        if (!empty($params['enterprise_name'])) {
            $query->where('enterprise_name', 'like', '%' . $params['enterprise_name'] . '%');
        }

        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('enterprise_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectPlanList($params = [])
    {
        $query = BizPlan::with(['enterprise']);

        if (!empty($params['enterprise_id'])) {
            $query->where('enterprise_id', $params['enterprise_id']);
        }
        if (!empty($params['enterprise_name'])) {
            $query->whereHas('enterprise', function ($q) use ($params) {
                $q->where('enterprise_name', 'like', '%' . $params['enterprise_name'] . '%');
            });
        }
        if (!empty($params['plan_name'])) {
            $query->where('plan_name', 'like', '%' . $params['plan_name'] . '%');
        }
        if (isset($params['audit_status']) && $params['audit_status'] !== '') {
            $query->where('audit_status', $params['audit_status']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }

        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        $result = $query->orderBy('plan_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
        
        foreach ($result as $plan) {
            $plan->enterprise_name = $plan->enterprise ? $plan->enterprise->enterprise_name : null;
        }
        
        return $result;
    }

    public function selectPlanById($planId)
    {
        return BizPlan::with(['items', 'enterprise', 'shipments.items'])->find($planId);
    }

    public function generatePlanNo()
    {
        $today = date('Ymd');
        $lastPlan = BizPlan::where('plan_no', 'like', 'PL' . $today . '%')
            ->orderBy('plan_id', 'desc')
            ->first();

        if ($lastPlan) {
            $lastSeq = intval(substr($lastPlan->plan_no, -3));
            $seq = $lastSeq + 1;
        } else {
            $seq = 1;
        }

        return 'PL' . $today . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function insertPlan($data)
    {
        $data['plan_no'] = $this->generatePlanNo();
        $data['remaining_amount'] = $data['gift_amount'] ?? 0;
        $data['shipped_amount'] = 0;
        $data['audit_status'] = '0';
        $data['create_time'] = date('Y-m-d H:i:s');

        $items = $data['items'] ?? [];
        unset($data['items']);

        $plan = BizPlan::create($data);

        if (!empty($items)) {
            $this->syncPlanItems($plan->plan_id, $items);
        }

        return $plan;
    }

    public function updatePlan($data)
    {
        $plan = BizPlan::find($data['plan_id']);
        if (!$plan) {
            return false;
        }

        if (in_array($plan->audit_status, ['2', '3'])) {
            return false;
        }

        $items = $data['items'] ?? [];
        unset($data['items']);

        $data['remaining_amount'] = ($data['gift_amount'] ?? $plan->gift_amount) - $plan->shipped_amount;
        $data['update_time'] = date('Y-m-d H:i:s');

        $fillable = [
            'plan_name', 'commission_rate', 'plan_amount', 'gift_amount',
            'remaining_amount', 'effective_date', 'expiry_date',
            'status', 'remark', 'update_by', 'update_time'
        ];
        $updateData = array_intersect_key($data, array_flip($fillable));

        $result = BizPlan::where('plan_id', $data['plan_id'])->update($updateData);

        if (!empty($items)) {
            $this->syncPlanItems($data['plan_id'], $items);
        }

        return $result;
    }

    private function syncPlanItems($planId, $items)
    {
        BizPlanItem::where('plan_id', $planId)->delete();

        foreach ($items as $item) {
            $item['plan_id'] = $planId;
            $item['remaining_quantity'] = $item['quantity'] ?? 0;
            $item['shipped_quantity'] = 0;
            if (isset($item['amount']) === false) {
                $item['amount'] = bcmul($item['quantity'] ?? 0, $item['sale_price'] ?? 0, 2);
            }
            BizPlanItem::create($item);
        }
    }

    public function deletePlanByIds($planIds)
    {
        foreach ($planIds as $planId) {
            $plan = BizPlan::find($planId);
            if ($plan && !in_array($plan->audit_status, ['0', '4'])) {
                return false;
            }
        }
        BizPlanItem::whereIn('plan_id', $planIds)->delete();
        return BizPlan::whereIn('plan_id', $planIds)->delete();
    }

    public function submitAudit($planId, $submitBy = '')
    {
        $plan = BizPlan::find($planId);
        if (!$plan || !in_array($plan->audit_status, ['0', '4'])) {
            return false;
        }
        return BizPlan::where('plan_id', $planId)->update([
            'audit_status' => '1',
            'submit_by' => $submitBy,
            'submit_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function audit($data)
    {
        $planId = $data['plan_id'];
        $passed = $data['passed'] ?? true;
        $auditRemark = $data['audit_remark'] ?? '';

        $plan = BizPlan::find($planId);
        if (!$plan || $plan->audit_status !== '1') {
            return false;
        }

        $updateData = [
            'audit_by' => $data['audit_by'] ?? '',
            'audit_time' => date('Y-m-d H:i:s'),
            'audit_remark' => $auditRemark,
            'update_time' => date('Y-m-d H:i:s')
        ];

        if ($passed) {
            $updateData['audit_status'] = '2';
        } else {
            $updateData['audit_status'] = '4';
        }

        return BizPlan::where('plan_id', $planId)->update($updateData);
    }

    public function changeStatus($planId, $status, $statusChangeBy = '')
    {
        return BizPlan::where('plan_id', $planId)->update([
            'status' => $status,
            'status_change_by' => $statusChangeBy,
            'status_change_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateShippedAmount($planId, $amount)
    {
        $plan = BizPlan::find($planId);
        if (!$plan) {
            return false;
        }

        $shippedAmount = bcadd($plan->shipped_amount, $amount, 2);
        $remainingAmount = bcsub($plan->gift_amount, $shippedAmount, 2);
        $auditStatus = $plan->audit_status;

        if (bccomp($remainingAmount, 0, 2) <= 0) {
            $remainingAmount = 0;
            $auditStatus = '3';
        }

        return BizPlan::where('plan_id', $planId)->update([
            'shipped_amount' => $shippedAmount,
            'remaining_amount' => $remainingAmount,
            'audit_status' => $auditStatus,
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateItemShippedQuantity($planItemId, $quantity)
    {
        $item = BizPlanItem::find($planItemId);
        if (!$item) {
            return false;
        }

        $shippedQty = $item->shipped_quantity + $quantity;
        $remainingQty = $item->quantity - $shippedQty;

        return BizPlanItem::where('item_id', $planItemId)->update([
            'shipped_quantity' => $shippedQty,
            'remaining_quantity' => max(0, $remainingQty)
        ]);
    }
}
