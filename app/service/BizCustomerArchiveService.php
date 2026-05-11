<?php

namespace app\service;

use app\model\BizCustomerArchive;
use app\model\BizSalesOrder;
use app\model\BizOrderItem;
use app\model\BizOperationRecord;
use app\model\BizRepaymentRecord;

class BizCustomerArchiveService
{
    public function selectArchiveList($params = [])
    {
        $query = BizCustomerArchive::query();
        if (!empty($params['customer_id'])) $query->where('customer_id', $params['customer_id']);
        if (!empty($params['enterprise_id'])) $query->where('enterprise_id', $params['enterprise_id']);
        if (!empty($params['store_id'])) $query->where('store_id', $params['store_id']);
        if (isset($params['source_type']) && $params['source_type'] !== '') $query->where('source_type', $params['source_type']);
        if (!empty($params['start_date'])) $query->where('archive_date', '>=', $params['start_date']);
        if (!empty($params['end_date'])) $query->where('archive_date', '<=', $params['end_date']);
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 50);
        return $query->orderBy('archive_date', 'desc')->orderBy('archive_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function insertArchive($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        if (empty($data['archive_date'])) $data['archive_date'] = date('Y-m-d');
        if (isset($data['plan_items']) && is_array($data['plan_items'])) {
            $data['plan_items'] = json_encode($data['plan_items'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['photos']) && is_array($data['photos'])) {
            $data['photos'] = json_encode($data['photos'], JSON_UNESCAPED_UNICODE);
        }
        return BizCustomerArchive::create($data);
    }

    public function deleteArchiveByIds($archiveIds)
    {
        return BizCustomerArchive::whereIn('archive_id', $archiveIds)->delete();
    }

    public function insertArchiveFromOrder($order)
    {
        if (!$order) return null;
        $orderId = $order->order_id ?? $order['order_id'] ?? null;
        if (!$orderId) return null;

        $exists = BizCustomerArchive::where('source_type', '0')->where('source_id', $orderId)->first();
        if ($exists) return $exists;

        $orderModel = is_array($order) ? BizSalesOrder::with('items')->find($orderId) : BizSalesOrder::with('items')->find($order->order_id);
        if (!$orderModel) return null;

        if (!$orderModel->customer_id) return null;

        $planItems = [];
        foreach ($orderModel->items as $item) {
            $planItems[] = ['name' => $item->product_name ?? '', 'quantity' => intval($item->quantity ?? 1)];
        }

        $data = [
            'customer_id' => $orderModel->customer_id,
            'customer_name' => $orderModel->customer_name ?? '',
            'enterprise_id' => $orderModel->enterprise_id,
            'enterprise_name' => $orderModel->enterprise_name ?? '',
            'store_id' => $orderModel->store_id,
            'store_name' => $orderModel->store_name ?? '',
            'archive_date' => substr($orderModel->create_time ?? date('Y-m-d H:i:s'), 0, 10),
            'archive_type' => 'sales',
            'source_type' => '0',
            'source_id' => $orderModel->order_id,
            'plan_items' => json_encode($planItems, JSON_UNESCAPED_UNICODE),
            'amount' => $orderModel->deal_amount ?? 0,
            'satisfaction' => null,
            'photos' => null,
            'customer_feedback' => $orderModel->remark ?? null,
            'operator_user_id' => $orderModel->creator_user_id ?? null,
            'operator_user_name' => $orderModel->creator_user_name ?? null,
            'remark' => '套餐: ' . ($orderModel->package_name ?? ''),
            'create_by' => $orderModel->create_by ?? '',
            'create_time' => date('Y-m-d H:i:s')
        ];
        return BizCustomerArchive::create($data);
    }

    public function insertArchiveFromOperation($record)
    {
        if (!$record) return null;
        $recordId = $record->record_id ?? $record['record_id'] ?? null;
        if (!$recordId) return null;

        $recordModel = is_array($record) ? BizOperationRecord::find($recordId) : $record;
        if (!$recordModel) return null;

        if (!$recordModel->customer_id) return null;

        $newItem = ['name' => $recordModel->product_name ?? '', 'quantity' => intval($recordModel->operation_quantity ?? 1)];
        $newAmount = $recordModel->operation_type === '1'
            ? ($recordModel->trial_price ?? 0)
            : ($recordModel->consume_amount ?? 0);

        $newPhotos = [];
        if (!empty($recordModel->before_photo)) {
            $before = json_decode($recordModel->before_photo, true);
            if (is_array($before)) $newPhotos = array_merge($newPhotos, $before);
            else $newPhotos[] = $recordModel->before_photo;
        }
        if (!empty($recordModel->after_photo)) {
            $after = json_decode($recordModel->after_photo, true);
            if (is_array($after)) $newPhotos = array_merge($newPhotos, $after);
            else $newPhotos[] = $recordModel->after_photo;
        }

        $archiveDate = $recordModel->operation_date ?? date('Y-m-d');

        $enterpriseName = $recordModel->enterprise_name ?? null;
        $storeName = $recordModel->store_name ?? null;

        if (!$enterpriseName || !$storeName) {
            if (!empty($recordModel->package_id)) {
                $pkg = \app\model\BizCustomerPackage::where('package_id', $recordModel->package_id)->first();
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
        }

        $existing = BizCustomerArchive::where('source_type', '1')
            ->where('customer_id', $recordModel->customer_id)
            ->where('archive_date', $archiveDate)
            ->where('operator_user_id', $recordModel->operator_user_id ?? 0)
            ->first();

        if ($existing) {
            $existingItems = json_decode($existing->plan_items, true) ?: [];
            $existingItems[] = $newItem;

            $existing->amount = round(floatval($existing->amount ?? 0) + $newAmount, 2);

            $existingPhotos = json_decode($existing->photos, true) ?: [];
            $mergedPhotos = array_unique(array_merge($existingPhotos, $newPhotos));

            $existing->satisfaction = $recordModel->satisfaction ?? $existing->satisfaction;
            $existing->customer_feedback = $recordModel->customer_feedback ?? $existing->customer_feedback;
            if (!empty($recordModel->remark)) {
                $existing->remark = trim(($existing->remark ?? '') . '; ' . $recordModel->remark, '; ');
            }
            $existing->source_id = $recordModel->record_id;
            if (!$existing->enterprise_name && $enterpriseName) $existing->enterprise_name = $enterpriseName;
            if (!$existing->store_name && $storeName) $existing->store_name = $storeName;
            $existing->plan_items = json_encode($existingItems, JSON_UNESCAPED_UNICODE);
            $existing->photos = !empty($mergedPhotos) ? json_encode(array_values($mergedPhotos), JSON_UNESCAPED_UNICODE) : null;
            $existing->save();

            return $existing;
        }

        $data = [
            'customer_id' => $recordModel->customer_id,
            'customer_name' => $recordModel->customer_name ?? '',
            'enterprise_id' => $recordModel->enterprise_id,
            'enterprise_name' => $enterpriseName ?? '',
            'store_id' => $recordModel->store_id,
            'store_name' => $storeName ?? '',
            'archive_date' => $archiveDate,
            'archive_type' => $recordModel->operation_type === '1' ? 'after_sales' : 'sales',
            'source_type' => '1',
            'source_id' => $recordModel->record_id,
            'plan_items' => json_encode([$newItem], JSON_UNESCAPED_UNICODE),
            'amount' => $newAmount,
            'satisfaction' => $recordModel->satisfaction ?? null,
            'photos' => !empty($newPhotos) ? json_encode($newPhotos, JSON_UNESCAPED_UNICODE) : null,
            'customer_feedback' => $recordModel->customer_feedback ?? null,
            'operator_user_id' => $recordModel->operator_user_id ?? null,
            'operator_user_name' => $recordModel->operator_user_name ?? null,
            'remark' => $recordModel->remark ?? null,
            'create_by' => $recordModel->create_by ?? '',
            'create_time' => date('Y-m-d H:i:s')
        ];
        return BizCustomerArchive::create($data);
    }

    public function insertArchiveFromRepayment($repayment)
    {
        if (!$repayment) return null;
        $repaymentId = $repayment->repayment_id ?? $repayment['repayment_id'] ?? null;
        if (!$repaymentId) return null;

        $exists = BizCustomerArchive::where('source_type', '2')->where('source_id', $repaymentId)->first();
        if ($exists) return $exists;

        $repaymentModel = is_array($repayment) ? BizRepaymentRecord::find($repaymentId) : $repayment;
        if (!$repaymentModel) return null;

        if (!$repaymentModel->customer_id) return null;

        $planItems = [['name' => $repaymentModel->package_name ?? '还款', 'quantity' => 1]];

        $data = [
            'customer_id' => $repaymentModel->customer_id,
            'customer_name' => $repaymentModel->customer_name ?? '',
            'enterprise_id' => $repaymentModel->enterprise_id,
            'enterprise_name' => $repaymentModel->enterprise_name ?? '',
            'store_id' => $repaymentModel->store_id,
            'store_name' => $repaymentModel->store_name ?? '',
            'archive_date' => substr($repaymentModel->create_time ?? date('Y-m-d H:i:s'), 0, 10),
            'archive_type' => 'sales',
            'source_type' => '2',
            'source_id' => $repaymentModel->repayment_id,
            'plan_items' => json_encode($planItems, JSON_UNESCAPED_UNICODE),
            'amount' => $repaymentModel->repayment_amount ?? 0,
            'satisfaction' => null,
            'photos' => null,
            'customer_feedback' => null,
            'operator_user_id' => $repaymentModel->creator_user_id ?? null,
            'operator_user_name' => $repaymentModel->creator_user_name ?? null,
            'remark' => $repaymentModel->remark ?? null,
            'create_by' => $repaymentModel->create_by ?? '',
            'create_time' => date('Y-m-d H:i:s')
        ];
        return BizCustomerArchive::create($data);
    }
}
