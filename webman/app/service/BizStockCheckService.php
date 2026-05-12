<?php

namespace app\service;

use app\model\BizStockCheck;
use app\model\BizStockCheckItem;
use app\model\BizInventory;
use app\model\BizProduct;

class BizStockCheckService
{
    public function selectStockCheckList($params = [])
    {
        $query = BizStockCheck::query();
        if (!empty($params['stock_check_no'])) {
            $query->where('stock_check_no', 'like', '%' . $params['stock_check_no'] . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (!empty($params['check_date_start'])) {
            $query->where('check_date', '>=', $params['check_date_start']);
        }
        if (!empty($params['check_date_end'])) {
            $query->where('check_date', '<=', $params['check_date_end']);
        }
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('stock_check_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectStockCheckById($stockCheckId)
    {
        $stockCheck = BizStockCheck::find($stockCheckId);
        if ($stockCheck) {
            $items = BizStockCheckItem::where('stock_check_id', $stockCheckId)->get();
            $stockCheck->items = $items->map(function ($item) {
                $product = BizProduct::find($item->product_id);
                $item->pack_qty = $product ? ($product->pack_qty ?? 1) : 1;
                return $item;
            });
        }
        return $stockCheck;
    }

    public function generateStockCheckNo()
    {
        $prefix = 'PD' . date('Ymd');
        $last = BizStockCheck::where('stock_check_no', 'like', $prefix . '%')
            ->orderBy('stock_check_id', 'desc')
            ->first();
        $seq = 1;
        if ($last) {
            $lastSeq = intval(substr($last->stock_check_no, -3));
            $seq = $lastSeq + 1;
        }
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function insertStockCheck($data)
    {
        $items = $data['items'] ?? [];
        unset($data['items']);
        $data['stock_check_no'] = $this->generateStockCheckNo();
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['status'] = '0';
        $totalQuantity = 0;
        $totalDiffQuantity = 0;
        foreach ($items as &$item) {
            $item['diff_quantity'] = intval($item['actual_quantity'] ?? 0) - intval($item['system_quantity'] ?? 0);
            $totalQuantity += intval($item['actual_quantity'] ?? 0);
            $totalDiffQuantity += $item['diff_quantity'];
        }
        unset($item);
        $data['total_quantity'] = $totalQuantity;
        $data['total_diff_quantity'] = $totalDiffQuantity;
        $stockCheck = BizStockCheck::create($data);
        foreach ($items as $item) {
            $item['stock_check_id'] = $stockCheck->stock_check_id;
            BizStockCheckItem::create($item);
        }
        return $stockCheck;
    }

    public function updateStockCheck($data)
    {
        $stockCheckId = $data['stock_check_id'] ?? 0;
        $stockCheck = BizStockCheck::find($stockCheckId);
        if (!$stockCheck) {
            return false;
        }
        if ($stockCheck->status === '1') {
            return false;
        }
        $items = $data['items'] ?? [];
        unset($data['items']);
        $data['update_time'] = date('Y-m-d H:i:s');
        $totalQuantity = 0;
        $totalDiffQuantity = 0;
        foreach ($items as &$item) {
            $item['diff_quantity'] = intval($item['actual_quantity'] ?? 0) - intval($item['system_quantity'] ?? 0);
            $totalQuantity += intval($item['actual_quantity'] ?? 0);
            $totalDiffQuantity += $item['diff_quantity'];
        }
        unset($item);
        $data['total_quantity'] = $totalQuantity;
        $data['total_diff_quantity'] = $totalDiffQuantity;
        BizStockCheck::where('stock_check_id', $stockCheckId)->update($data);
        BizStockCheckItem::where('stock_check_id', $stockCheckId)->delete();
        foreach ($items as $item) {
            $item['stock_check_id'] = $stockCheckId;
            BizStockCheckItem::create($item);
        }
        return true;
    }

    public function deleteStockCheckByIds($stockCheckIds)
    {
        foreach ($stockCheckIds as $id) {
            $stockCheck = BizStockCheck::find($id);
            if ($stockCheck && $stockCheck->status === '1') {
                return false;
            }
        }
        BizStockCheckItem::whereIn('stock_check_id', $stockCheckIds)->delete();
        return BizStockCheck::whereIn('stock_check_id', $stockCheckIds)->delete();
    }

    public function confirmStockCheck($stockCheckId)
    {
        $stockCheck = BizStockCheck::find($stockCheckId);
        if (!$stockCheck) {
            return ['success' => false, 'msg' => '盘点单不存在'];
        }
        if ($stockCheck->status === '1') {
            return ['success' => false, 'msg' => '盘点单已确认，请勿重复操作'];
        }
        $items = BizStockCheckItem::where('stock_check_id', $stockCheckId)->get();
        if ($items->isEmpty()) {
            return ['success' => false, 'msg' => '盘点单明细为空'];
        }
        foreach ($items as $item) {
            if ($item->diff_quantity == 0) {
                continue;
            }
            $inventory = BizInventory::where('product_id', $item->product_id)->first();
            if (!$inventory) {
                $product = BizProduct::find($item->product_id);
                $inventory = BizInventory::create([
                    'product_id' => $item->product_id,
                    'quantity' => 0,
                    'warn_qty' => $product->warn_qty ?? 0,
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
            }
            $inventory->quantity = $inventory->quantity + $item->diff_quantity;
            $inventory->update_time = date('Y-m-d H:i:s');
            $inventory->save();
        }
        BizStockCheck::where('stock_check_id', $stockCheckId)->update([
            'status' => '1',
            'update_time' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'msg' => '盘点确认成功'];
    }

    public function loadInventoryData()
    {
        $products = BizProduct::where('status', '0')->get();
        $items = [];
        foreach ($products as $product) {
            $inventory = BizInventory::where('product_id', $product->product_id)->first();
            $items[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'spec' => $product->spec,
                'unit' => $product->unit,
                'pack_qty' => $product->pack_qty ?? 1,
                'system_quantity' => $inventory ? $inventory->quantity : 0,
                'actual_quantity' => $inventory ? $inventory->quantity : 0,
                'diff_quantity' => 0,
            ];
        }
        return $items;
    }
}
