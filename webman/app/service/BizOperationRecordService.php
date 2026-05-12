<?php

namespace app\service;

use app\model\BizOperationRecord;
use app\model\BizPackageItem;
use app\model\BizCustomerPackage;
use app\service\BizCustomerArchiveService;

class BizOperationRecordService
{
    public function selectRecordList($params = [])
    {
        $query = BizOperationRecord::query();
        if (!empty($params['customer_id'])) $query->where('customer_id', $params['customer_id']);
        if (!empty($params['enterprise_id'])) $query->where('enterprise_id', $params['enterprise_id']);
        if (!empty($params['store_id'])) $query->where('store_id', $params['store_id']);
        if (!empty($params['package_id'])) $query->where('package_id', $params['package_id']);
        if (!empty($params['operation_date'])) $query->where('operation_date', $params['operation_date']);
        if (isset($params['operation_type']) && $params['operation_type'] !== '') $query->where('operation_type', $params['operation_type']);
        if (!empty($params['start_date'])) $query->where('operation_date', '>=', $params['start_date']);
        if (!empty($params['end_date'])) $query->where('operation_date', '<=', $params['end_date']);
        if (!empty($params['product_name'])) $query->where('product_name', 'like', '%' . $params['product_name'] . '%');
        if (!empty($params['operator_user_id'])) $query->where('operator_user_id', $params['operator_user_id']);
        if (!empty($params['satisfaction'])) $query->where('satisfaction', $params['satisfaction']);
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('record_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function insertRecord($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        if (empty($data['operation_date'])) $data['operation_date'] = date('Y-m-d');
        if (empty($data['operation_type'])) $data['operation_type'] = '0';

        if (empty($data['operation_batch_id'])) {
            $data['operation_batch_id'] = 'OB' . date('YmdHis') . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        }

        if ($data['operation_type'] === '0' && !empty($data['package_item_id'])) {
            $packageItem = BizPackageItem::find($data['package_item_id']);
            if ($packageItem) {
                if (empty($data['consume_amount'])) {
                    $data['consume_amount'] = round($packageItem->unit_price * intval($data['operation_quantity'] ?? 1), 2);
                }

                $packageItem->used_quantity += intval($data['operation_quantity'] ?? 1);
                $packageItem->remaining_quantity = $packageItem->total_quantity - $packageItem->used_quantity;
                if ($packageItem->remaining_quantity < 0) $packageItem->remaining_quantity = 0;
                $packageItem->save();

                if ($packageItem->remaining_quantity <= 0) {
                    $allUsed = BizPackageItem::where('package_id', $packageItem->package_id)
                        ->where('remaining_quantity', '>', 0)->count();
                    if ($allUsed === 0) {
                        BizCustomerPackage::where('package_id', $packageItem->package_id)
                            ->update(['status' => '1', 'update_time' => date('Y-m-d H:i:s')]);
                    }
                }

                if (empty($data['enterprise_name']) || empty($data['store_name'])) {
                    $package = BizCustomerPackage::find($packageItem->package_id);
                    if ($package) {
                        if (empty($data['enterprise_name'])) $data['enterprise_name'] = $package->enterprise_name ?? '';
                        if (empty($data['store_name'])) $data['store_name'] = $package->store_name ?? '';
                    }
                }
            }
        }

        if ($data['operation_type'] === '1') {
            $data['package_id'] = null;
            $data['package_no'] = null;
            $data['package_item_id'] = null;
            $data['consume_amount'] = 0;
        }

        $record = BizOperationRecord::create($data);

        try {
            $archiveService = new BizCustomerArchiveService();
            $result = $archiveService->insertArchiveFromOperation($record);
            if (!$result) {
                \support\Log::warning('操作档案返回null', [
                    'record_id' => $record->record_id,
                    'customer_id' => $record->customer_id ?? 'NULL'
                ]);
            }
        } catch (\Exception $e) {
            \support\Log::error('写入操作档案失败: ' . $e->getMessage(), [
                'record_id' => $record->record_id ?? 'unknown',
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }

        return $record;
    }

    public function deleteRecordByIds($recordIds)
    {
        return BizOperationRecord::whereIn('record_id', $recordIds)->delete();
    }

    public function getRecordById($id)
    {
        return BizOperationRecord::find($id);
    }

    public function getRecordDetailById($id)
    {
        $record = BizOperationRecord::find($id);
        if (!$record) return null;

        if (!empty($record->operation_batch_id)) {
            $batchRecords = BizOperationRecord::where('operation_batch_id', $record->operation_batch_id)
                ->orderBy('record_id', 'asc')
                ->get();
        } elseif ($record->operation_type === '1') {
            $batchRecords = collect([$record]);
        } else {
            $batchRecords = collect([$record]);
        }

        $enterpriseName = $record->enterprise_name;
        $storeName = $record->store_name;

        if (!$enterpriseName || !$storeName) {
            if (!empty($record->package_id)) {
                $pkg = \app\model\BizCustomerPackage::where('package_id', $record->package_id)->first();
                if ($pkg) {
                    if (!$enterpriseName) $enterpriseName = $pkg->enterprise_name;
                    if (!$storeName) $storeName = $pkg->store_name;
                    if (!$storeName && !empty($pkg->order_id)) {
                        $order = \app\model\BizSalesOrder::find($pkg->order_id);
                        if ($order) {
                            if (!$enterpriseName) $enterpriseName = $order->enterprise_name;
                            $storeName = $order->store_name;
                        }
                    }
                }
            }
            if ((!$enterpriseName || !$storeName) && !empty($record->customer_id)) {
                $customer = \app\model\BizCustomer::find($record->customer_id);
                if ($customer) {
                    if (!$enterpriseName) $enterpriseName = $customer->enterprise_name;
                    if (!$storeName) $storeName = $customer->store_name;
                }
            }
        }

        return [
            'record' => $record->toArray(),
            'items' => $batchRecords->toArray(),
            'enterprise_name' => $enterpriseName,
            'store_name' => $storeName,
            'total_amount' => $batchRecords->sum('consume_amount') + $batchRecords->sum('trial_price'),
            'item_count' => $batchRecords->count()
        ];
    }
}
