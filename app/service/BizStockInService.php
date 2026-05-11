<?php

namespace app\service;

use app\model\BizStockIn;
use app\model\BizStockInItem;
use app\model\BizInventory;
use app\model\BizProduct;
use app\model\SysUser;
use support\Db;

class BizStockInService
{
    public function selectStockInList($params = [])
    {
        $query = BizStockIn::query();
        if (!empty($params['stock_in_no'])) {
            $query->where('stock_in_no', 'like', '%' . $params['stock_in_no'] . '%');
        }
        if (isset($params['stock_in_type']) && $params['stock_in_type'] !== '') {
            $query->where('stock_in_type', $params['stock_in_type']);
        }
        if (!empty($params['supplier_id'])) {
            $query->where('supplier_id', $params['supplier_id']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (!empty($params['stock_in_date_start'])) {
            $query->where('stock_in_date', '>=', $params['stock_in_date_start']);
        }
        if (!empty($params['stock_in_date_end'])) {
            $query->where('stock_in_date', '<=', $params['stock_in_date_end']);
        }
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        $list = $query->orderBy('stock_in_id', 'desc')
            ->paginate($pageSize, ['*', Db::raw('(SELECT MIN(expiry_date) FROM biz_stock_in_item WHERE biz_stock_in_item.stock_in_id = biz_stock_in.stock_in_id) as earliest_expiry')], 'page', $pageNum);
        
        foreach ($list->items() as $stockIn) {
            $firstItem = BizStockInItem::where('stock_in_id', $stockIn->stock_in_id)->first();
            if ($firstItem) {
                $stockIn->display_pack_qty = $firstItem->pack_qty ?? 1;
                $stockIn->display_unit = $firstItem->unit;
                $stockIn->display_spec = $firstItem->spec;
            }
        }
        
        return $list;
    }

    public function selectStockInById($stockInId)
    {
        $stockIn = BizStockIn::find($stockInId);
        if ($stockIn) {
            $items = BizStockInItem::where('stock_in_id', $stockInId)->get()->toArray();
            $stockIn->items = array_map(function ($item) {
                return [
                    'itemId' => $item['id'] ?? null,
                    'productId' => $item['product_id'],
                    'productName' => $item['product_name'],
                    'supplierId' => $item['supplier_id'],
                    'supplierName' => $item['supplier_name'],
                    'spec' => $item['spec'],
                    'unit' => $item['unit'],
                    'unitType' => $item['unit_type'] ?? '1',
                    'packQty' => $item['pack_qty'] ?? 1,
                    'originalQuantity' => $item['original_quantity'] ?? $item['quantity'],
                    'quantity' => $item['quantity'],
                    'purchasePrice' => floatval($item['purchase_price']),
                    '_mainPrice' => floatval($item['purchase_price']),
                    'amount' => $item['amount'],
                    'productionDate' => $item['production_date'],
                    'expiryDate' => $item['expiry_date'],
                    'remark' => $item['remark'],
                ];
            }, $items);
        }

        return $stockIn;
    }

    public function generateStockInNo()
    {
        $prefix = 'RK' . date('Ymd');
        $last = BizStockIn::where('stock_in_no', 'like', $prefix . '%')
            ->orderBy('stock_in_id', 'desc')
            ->first();
        $seq = 1;
        if ($last) {
            $lastSeq = intval(substr($last->stock_in_no, -3));
            $seq = $lastSeq + 1;
        }
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function insertStockIn($data)
    {
        $items = $data['items'] ?? [];
        unset($data['items']);
        $data['stock_in_no'] = $this->generateStockInNo();
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['status'] = '0';
        $totalQuantity = 0;
        $totalAmount = 0;
        foreach ($items as &$item) {
            $unitType = $item['unit_type'] ?? '1';
            $packQty = intval($item['pack_qty'] ?? 1);
            
            if ($unitType === '1' && $packQty > 1) {
                $item['original_quantity'] = intval($item['quantity']);
                $item['quantity'] = intval($item['quantity']) * $packQty;
                if (isset($item['_main_price']) && $item['_main_price'] > 0) {
                    $item['purchase_price'] = bcdiv($item['_main_price'], $packQty, 4);
                }
            } else {
                $item['original_quantity'] = intval($item['quantity']);
            }
            
            $item['amount'] = bcmul($item['quantity'] ?? 0, $item['purchase_price'] ?? 0, 2);
            $totalQuantity += intval($item['quantity'] ?? 0);
            $totalAmount = bcadd($totalAmount, $item['amount'], 2);
        }
        unset($item);
        $data['total_quantity'] = $totalQuantity;
        $data['total_amount'] = $totalAmount;
        $stockIn = BizStockIn::create($data);
        foreach ($items as $item) {
            $item['stock_in_id'] = $stockIn->stock_in_id;
            BizStockInItem::create($item);
        }
        return $stockIn;
    }

    public function updateStockIn($data)
    {
        $stockInId = $data['stock_in_id'] ?? 0;
        $stockIn = BizStockIn::find($stockInId);
        if (!$stockIn) {
            return false;
        }
        if ($stockIn->status === '1') {
            return false;
        }
        $items = $data['items'] ?? [];
        unset($data['items']);
        $data['update_time'] = date('Y-m-d H:i:s');
        $totalQuantity = 0;
        $totalAmount = 0;
        foreach ($items as &$item) {
            $unitType = $item['unit_type'] ?? '1';
            $packQty = intval($item['pack_qty'] ?? 1);
            
            if ($unitType === '1' && $packQty > 1) {
                $item['original_quantity'] = intval($item['quantity']);
                $item['quantity'] = intval($item['quantity']) * $packQty;
                if (isset($item['_main_price']) && $item['_main_price'] > 0) {
                    $item['purchase_price'] = bcdiv($item['_main_price'], $packQty, 4);
                }
            } else {
                $item['original_quantity'] = intval($item['quantity']);
            }
            
            $item['amount'] = bcmul($item['quantity'] ?? 0, $item['purchase_price'] ?? 0, 2);
            $totalQuantity += intval($item['quantity'] ?? 0);
            $totalAmount = bcadd($totalAmount, $item['amount'], 2);
        }
        unset($item);
        $data['total_quantity'] = $totalQuantity;
        $data['total_amount'] = $totalAmount;
        BizStockIn::where('stock_in_id', $stockInId)->update($data);
        BizStockInItem::where('stock_in_id', $stockInId)->delete();
        foreach ($items as $item) {
            $item['stock_in_id'] = $stockInId;
            BizStockInItem::create($item);
        }
        return true;
    }

    public function deleteStockInByIds($stockInIds)
    {
        foreach ($stockInIds as $id) {
            $stockIn = BizStockIn::find($id);
            if ($stockIn && $stockIn->status === '1') {
                return false;
            }
        }
        BizStockInItem::whereIn('stock_in_id', $stockInIds)->delete();
        return BizStockIn::whereIn('stock_in_id', $stockInIds)->delete();
    }

    public function confirmStockIn($stockInId)
    {
        $stockIn = BizStockIn::find($stockInId);
        if (!$stockIn) {
            return ['success' => false, 'msg' => '入库单不存在'];
        }
        if ($stockIn->status === '1') {
            return ['success' => false, 'msg' => '入库单已确认，请勿重复操作'];
        }
        $items = BizStockInItem::where('stock_in_id', $stockInId)->get();
        if ($items->isEmpty()) {
            return ['success' => false, 'msg' => '入库单明细为空'];
        }
        foreach ($items as $item) {
            $inventory = BizInventory::where('product_id', $item->product_id)->first();
            if (!$inventory) {
                $product = BizProduct::find($item->product_id);
                $inventory = BizInventory::create([
                    'product_id' => $item->product_id,
                    'quantity' => 0,
                    'warn_qty' => $product ? ($product->warn_qty ?? 0) : 0,
                    'create_time' => date('Y-m-d H:i:s'),
                ]);
            }
            $actualQty = intval($item->quantity);
            $inventory->quantity = $inventory->quantity + $actualQty;
            $inventory->last_stock_in_time = date('Y-m-d H:i:s');
            $inventory->update_time = date('Y-m-d H:i:s');
            $inventory->save();
        }
        BizStockIn::where('stock_in_id', $stockInId)->update([
            'status' => '1',
            'update_time' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'msg' => '入库确认成功'];
    }

    public function cancelConfirmStockIn($stockInId)
    {
        $stockIn = BizStockIn::find($stockInId);
        if (!$stockIn) {
            return ['success' => false, 'msg' => '入库单不存在'];
        }
        if ($stockIn->status === '0') {
            return ['success' => false, 'msg' => '入库单未确认，无需取消'];
        }

        $items = BizStockInItem::where('stock_in_id', $stockInId)->get();
        foreach ($items as $item) {
            $inventory = BizInventory::where('product_id', $item->product_id)->first();
            if ($inventory) {
                $actualQty = intval($item->quantity);
                $inventory->quantity = max(0, $inventory->quantity - $actualQty);
                $inventory->last_stock_in_time = date('Y-m-d H:i:s');
                $inventory->update_time = date('Y-m-d H:i:s');
                $inventory->save();
            }
        }

        BizStockIn::where('stock_in_id', $stockInId)->update([
            'status' => '0',
            'update_time' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'msg' => '已取消确认'];
    }
}
