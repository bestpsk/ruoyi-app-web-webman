<?php

namespace app\service;

use app\model\BizInventory;
use app\model\BizProduct;

class BizInventoryService
{
    public function selectInventoryList($params = [])
    {
        $query = BizInventory::query()->with('product');
        if (!empty($params['product_name'])) {
            $query->whereHas('product', function ($q) use ($params) {
                $q->where('product_name', 'like', '%' . $params['product_name'] . '%');
            });
        }
        if (!empty($params['product_code'])) {
            $query->whereHas('product', function ($q) use ($params) {
                $q->where('product_code', 'like', '%' . $params['product_code'] . '%');
            });
        }
        if (isset($params['category']) && $params['category'] !== '') {
            $query->whereHas('product', function ($q) use ($params) {
                $q->where('category', $params['category']);
            });
        }
        if (isset($params['is_warn']) && $params['is_warn'] === '1') {
            $query->whereColumn('quantity', '<=', 'warn_qty');
        }
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('inventory_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectWarnList($params = [])
    {
        $params['is_warn'] = '1';
        return $this->selectInventoryList($params);
    }

    public function selectInventoryByProductId($productId)
    {
        $inventory = BizInventory::where('product_id', $productId)->with('product')->first();
        return $inventory;
    }
}
