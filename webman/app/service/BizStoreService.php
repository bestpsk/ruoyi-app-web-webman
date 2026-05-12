<?php

namespace app\service;

use app\model\BizStore;
use app\model\BizEnterprise;

class BizStoreService
{
    public function selectStoreList($params = [])
    {
        $query = BizStore::query();

        if (!empty($params['store_name'])) {
            $query->where('store_name', 'like', '%' . $params['store_name'] . '%');
        }
        if (!empty($params['enterprise_id'])) {
            $query->where('enterprise_id', $params['enterprise_id']);
        }
        if (!empty($params['manager_name'])) {
            $query->where('manager_name', 'like', '%' . $params['manager_name'] . '%');
        }
        if (!empty($params['phone'])) {
            $query->where('phone', 'like', '%' . $params['phone'] . '%');
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('store_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectStoreById($storeId)
    {
        return BizStore::find($storeId);
    }

    public function insertStore($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        if (!empty($data['enterprise_id'])) {
            $enterprise = BizEnterprise::find($data['enterprise_id']);
            if ($enterprise) {
                $data['enterprise_name'] = $enterprise->enterprise_name;
            }
        }
        return BizStore::create($data);
    }

    public function updateStore($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        if (!empty($data['enterprise_id'])) {
            $enterprise = BizEnterprise::find($data['enterprise_id']);
            if ($enterprise) {
                $data['enterprise_name'] = $enterprise->enterprise_name;
            }
        }
        return BizStore::where('store_id', $data['store_id'])->update($data);
    }

    public function selectStoreForSearch($keyword, $enterpriseId = null)
    {
        $query = BizStore::query();
        
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('store_name', 'like', '%' . $keyword . '%')
                  ->orWhere('enterprise_name', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($enterpriseId)) {
            $query->where('enterprise_id', $enterpriseId);
        }
        
        return $query->where('status', '0')
                    ->orderBy('store_name', 'asc')
                    ->limit(50)
                    ->get();
    }

    public function deleteStoreByIds($storeIds)
    {
        return BizStore::whereIn('store_id', $storeIds)->delete();
    }
}
