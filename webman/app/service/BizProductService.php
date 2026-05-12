<?php

namespace app\service;

use app\model\BizProduct;
use app\model\BizInventory;

class BizProductService
{
    public function selectProductList($params = [])
    {
        $query = BizProduct::with('supplier');
        if (!empty($params['product_name'])) {
            $query->where('product_name', 'like', '%' . $params['product_name'] . '%');
        }
        if (!empty($params['product_code'])) {
            $query->where('product_code', 'like', '%' . $params['product_code'] . '%');
        }
        if (isset($params['category']) && $params['category'] !== '') {
            $query->where('category', $params['category']);
        }
        if (!empty($params['supplier_id'])) {
            $query->where('supplier_id', $params['supplier_id']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        $result = $query->orderBy('product_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
        
        foreach ($result as $product) {
            $product->supplier_name = $product->supplier ? $product->supplier->supplier_name : null;
            $inventory = BizInventory::where('product_id', $product->product_id)->first();
            $product->inventory_qty = $inventory ? $inventory->quantity : 0;
        }
        
        return $result;
    }

    public function selectProductById($productId)
    {
        return BizProduct::find($productId);
    }

    public function searchProduct($keyword = '')
    {
        $query = BizProduct::query()->where('status', '0');
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('product_name', 'like', '%' . $keyword . '%')
                  ->orWhere('product_code', 'like', '%' . $keyword . '%');
            });
        }
        $products = $query->with('supplier')->orderBy('product_id', 'desc')->limit(50)->get();
        foreach ($products as $product) {
            $inventory = BizInventory::where('product_id', $product->product_id)->first();
            $product->inventory_qty = $inventory ? $inventory->quantity : 0;
            $product->supplier_name = $product->supplier ? $product->supplier->supplier_name : null;
        }
        return $products;
    }

    public function insertProduct($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        $product = BizProduct::create($data);
        BizInventory::create([
            'product_id' => $product->product_id,
            'quantity' => 0,
            'warn_qty' => $data['warn_qty'] ?? 0,
            'create_time' => date('Y-m-d H:i:s'),
        ]);
        return $product;
    }

    public function updateProduct($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        $result = BizProduct::where('product_id', $data['product_id'])->update($data);
        if (isset($data['warn_qty'])) {
            BizInventory::where('product_id', $data['product_id'])->update([
                'warn_qty' => $data['warn_qty'],
                'update_time' => date('Y-m-d H:i:s'),
            ]);
        }
        return $result;
    }

    public function deleteProductByIds($productIds)
    {
        BizInventory::whereIn('product_id', $productIds)->delete();
        return BizProduct::whereIn('product_id', $productIds)->delete();
    }
}
