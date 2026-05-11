<?php

namespace app\service;

use app\model\BizSupplier;

class BizSupplierService
{
    public function selectSupplierList($params = [])
    {
        $query = BizSupplier::query();
        if (!empty($params['supplier_name'])) {
            $query->where('supplier_name', 'like', '%' . $params['supplier_name'] . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (!empty($params['contact_person'])) {
            $query->where('contact_person', 'like', '%' . $params['contact_person'] . '%');
        }
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('supplier_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectSupplierById($supplierId)
    {
        return BizSupplier::find($supplierId);
    }

    public function searchSupplier($keyword = '')
    {
        $query = BizSupplier::query()->where('status', '0');
        if (!empty($keyword)) {
            $query->where('supplier_name', 'like', '%' . $keyword . '%');
        }
        return $query->orderBy('supplier_id', 'desc')->limit(50)->get();
    }

    public function insertSupplier($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return BizSupplier::create($data);
    }

    public function updateSupplier($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return BizSupplier::where('supplier_id', $data['supplier_id'])->update($data);
    }

    public function deleteSupplierByIds($supplierIds)
    {
        return BizSupplier::whereIn('supplier_id', $supplierIds)->delete();
    }
}
