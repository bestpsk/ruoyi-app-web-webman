<?php

namespace app\service;

use app\model\BizCustomer;
use app\model\BizSalesOrder;
use app\model\BizCustomerPackage;
use app\model\BizPackageItem;
use app\model\BizOperationRecord;

class BizCustomerService
{
    public function selectCustomerList($params = [])
    {
        $query = BizCustomer::query();
        if (!empty($params['enterprise_id'])) $query->where('enterprise_id', $params['enterprise_id']);
        if (!empty($params['store_id'])) $query->where('store_id', $params['store_id']);
        if (!empty($params['customer_name'])) $query->where('customer_name', 'like', '%' . $params['customer_name'] . '%');
        if (!empty($params['phone'])) $query->where('phone', 'like', '%' . $params['phone'] . '%');
        if (!empty($params['tag'])) $query->where('tag', $params['tag']);
        if (!empty($params['status'])) $query->where('status', $params['status']);
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('customer_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectCustomerById($customerId)
    {
        return BizCustomer::find($customerId);
    }

    public function searchCustomer($keyword, $enterpriseId, $storeId = null, $hasDeal = null, $satisfaction = null)
    {
        $query = BizCustomer::query();
        $query->where('enterprise_id', $enterpriseId);
        if ($storeId) $query->where('store_id', $storeId);
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('customer_name', 'like', '%' . $keyword . '%')
                  ->orWhere('phone', 'like', '%' . $keyword . '%');
            });
        }
        $customers = $query->where('status', '0')->orderBy('customer_name', 'asc')->limit(100)->get();

        $result = [];
        foreach ($customers as $customer) {
            $customerId = $customer->customer_id;

            $customerHasDeal = BizSalesOrder::where('customer_id', $customerId)
                ->where('order_status', '1')->exists();
            $customer->has_deal = $customerHasDeal;

            $dealAmount = BizSalesOrder::where('customer_id', $customerId)
                ->where('order_status', '1')
                ->sum('deal_amount');
            $customer->deal_amount = round(floatval($dealAmount), 2);

            $dealPackageIds = BizCustomerPackage::where('customer_id', $customerId)
                ->whereIn('status', ['1', '2'])
                ->pluck('package_id')
                ->toArray();

            if (!empty($dealPackageIds)) {
                $allUsedUp = BizPackageItem::whereIn('package_id', $dealPackageIds)
                    ->where('remaining_quantity', '>', 0)
                    ->doesntExist();
                $customer->all_exhausted = $allUsedUp;
            } else {
                $customer->all_exhausted = false;
            }

            $totalConsumed = BizOperationRecord::where('customer_id', $customerId)
                ->sum('consume_amount');
            $customer->total_consumed = round(floatval($totalConsumed), 2);

            $avgSatisfaction = BizOperationRecord::where('customer_id', $customerId)
                ->whereNotNull('satisfaction')
                ->avg('satisfaction');
            $customer->avg_satisfaction = $avgSatisfaction ? round(floatval($avgSatisfaction), 1) : null;

            if ($hasDeal !== null && $hasDeal !== '') {
                $dealFilter = $hasDeal === '1' ? true : false;
                if ($customerHasDeal !== $dealFilter) continue;
            }

            if ($satisfaction !== null && $satisfaction !== '') {
                $satFilter = floatval($satisfaction);
                if ($customer->avg_satisfaction === null || $customer->avg_satisfaction < $satFilter) continue;
            }

            $result[] = $customer;
        }

        return collect($result);
    }

    public function insertCustomer($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return BizCustomer::create($data);
    }

    public function updateCustomer($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return BizCustomer::where('customer_id', $data['customer_id'])->update($data);
    }

    public function deleteCustomerByIds($customerIds)
    {
        return BizCustomer::whereIn('customer_id', $customerIds)->delete();
    }
}
