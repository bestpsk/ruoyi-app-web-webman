<?php

namespace app\service;

use app\model\BizStockOut;
use app\model\BizStockOutItem;
use app\model\BizInventory;
use app\model\BizProduct;
use app\model\SysUser;
use app\model\BizEnterprise;

class BizStockOutService
{
    public function selectStockOutList($params = [])
    {
        $query = BizStockOut::query();
        if (!empty($params['stock_out_no'])) {
            $query->where('stock_out_no', 'like', '%' . $params['stock_out_no'] . '%');
        }
        if (isset($params['stock_out_type']) && $params['stock_out_type'] !== '') {
            $query->where('stock_out_type', $params['stock_out_type']);
        }
        if (!empty($params['enterprise_id'])) {
            $query->where('enterprise_id', $params['enterprise_id']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (!empty($params['stock_out_date_start'])) {
            $query->where('stock_out_date', '>=', $params['stock_out_date_start']);
        }
        if (!empty($params['stock_out_date_end'])) {
            $query->where('stock_out_date', '<=', $params['stock_out_date_end']);
        }
        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        $list = $query->orderBy('stock_out_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
        
        foreach ($list->items() as $stockOut) {
            $firstItem = BizStockOutItem::where('stock_out_id', $stockOut->stock_out_id)->first();
            if ($firstItem) {
                $stockOut->display_unit_type = $firstItem->unit_type ?? '1';
                $stockOut->display_original_qty = $firstItem->original_quantity ?? $firstItem->quantity;
                $stockOut->display_pack_qty = $firstItem->pack_qty ?? 1;
                $stockOut->display_unit = $firstItem->unit;
                $stockOut->display_spec = $firstItem->spec;
            }
        }
        
        return $list;
    }

    public function selectStockOutById($stockOutId)
    {
        $stockOut = BizStockOut::find($stockOutId);
        if ($stockOut) {
            $items = BizStockOutItem::where('stock_out_id', $stockOutId)->get()->toArray();
            $stockOut->items = array_map(function ($item) {
                return [
                    'itemId' => $item['id'] ?? null,
                    'productId' => $item['product_id'],
                    'productName' => $item['product_name'],
                    'spec' => $item['spec'],
                    'unit' => $item['unit'],
                    'unitType' => $item['unit_type'] ?? '1',
                    'packQty' => $item['pack_qty'] ?? 1,
                    'originalQuantity' => $item['original_quantity'] ?? $item['quantity'],
                    'quantity' => $item['quantity'],
                    'salePrice' => floatval($item['sale_price']),
                    'amount' => $item['amount'],
                    'remark' => $item['remark'],
                ];
            }, $items);

            $stockOutArray = $stockOut->toArray();

            return [
                'stockOutId' => $stockOutArray['stock_out_id'],
                'stockOutNo' => $stockOutArray['stock_out_no'],
                'stockOutType' => $stockOutArray['stock_out_type'],
                'outTargetType' => $stockOutArray['out_target_type'] ?? '1',
                'enterpriseId' => $stockOutArray['enterprise_id'],
                'enterpriseName' => $stockOutArray['enterprise_name'] ?? '-',
                'contactEmployeeId' => $stockOutArray['contact_employee_id'],
                'contactEmployeeName' => $stockOutArray['contact_employee_name'] ?? '-',
                'responsibleId' => $stockOutArray['responsible_id'],
                'responsibleName' => $stockOutArray['responsible_name'] ?? '-',
                'totalQuantity' => $stockOutArray['total_quantity'],
                'totalAmount' => $stockOutArray['total_amount'],
                'stockOutDate' => $stockOutArray['stock_out_date'],
                'status' => $stockOutArray['status'],
                'remark' => $stockOutArray['remark'],
                'createBy' => $stockOutArray['create_by'],
                'createTime' => $stockOutArray['create_time'],
                'updateBy' => $stockOutArray['update_by'] ?? null,
                'updateTime' => $stockOutArray['update_time'] ?? null,
                'items' => $stockOut->items,
            ];
        }
        return null;
    }

    public function generateStockOutNo()
    {
        $prefix = 'CK' . date('Ymd');
        $last = BizStockOut::where('stock_out_no', 'like', $prefix . '%')
            ->orderBy('stock_out_id', 'desc')
            ->first();
        $seq = 1;
        if ($last) {
            $lastSeq = intval(substr($last->stock_out_no, -3));
            $seq = $lastSeq + 1;
        }
        return $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    public function insertStockOut($data)
    {
        $items = $data['items'] ?? [];
        unset($data['items']);
        
        $camelToSnake = function ($str) {
            return strtolower(preg_replace('/([A-Z])/', '_$1', $str));
        };
        
        $convertedData = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match('/[A-Z]/', $key)) {
                $convertedData[$camelToSnake($key)] = $value;
            } else {
                $convertedData[$key] = $value;
            }
        }
        $data = $convertedData;
        
        $data['stock_out_no'] = $this->generateStockOutNo();
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
                    $item['sale_price'] = bcdiv($item['_main_price'], $packQty, 4);
                }
            } else {
                $item['original_quantity'] = intval($item['quantity']);
            }
            
            $item['amount'] = bcmul($item['quantity'] ?? 0, $item['sale_price'] ?? 0, 2);
            $totalQuantity += intval($item['quantity'] ?? 0);
            $totalAmount = bcadd($totalAmount, $item['amount'], 2);
        }
        unset($item);
        $data['total_quantity'] = $totalQuantity;
        $data['total_amount'] = $totalAmount;
        $stockOut = BizStockOut::create($data);
        foreach ($items as $item) {
            $item['stock_out_id'] = $stockOut->stock_out_id;
            BizStockOutItem::create($item);
        }
        return $stockOut;
    }

    public function updateStockOut($data)
    {
        $stockOutId = $data['stock_out_id'] ?? 0;
        $stockOut = BizStockOut::find($stockOutId);
        if (!$stockOut) {
            return false;
        }
        if ($stockOut->status === '1') {
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
                    $item['sale_price'] = bcdiv($item['_main_price'], $packQty, 4);
                }
            } else {
                $item['original_quantity'] = intval($item['quantity']);
            }
            
            $item['amount'] = bcmul($item['quantity'] ?? 0, $item['sale_price'] ?? 0, 2);
            $totalQuantity += intval($item['quantity'] ?? 0);
            $totalAmount = bcadd($totalAmount, $item['amount'], 2);
        }
        unset($item);
        $data['total_quantity'] = $totalQuantity;
        $data['total_amount'] = $totalAmount;
        BizStockOut::where('stock_out_id', $stockOutId)->update($data);
        BizStockOutItem::where('stock_out_id', $stockOutId)->delete();
        foreach ($items as $item) {
            $item['stock_out_id'] = $stockOutId;
            BizStockOutItem::create($item);
        }
        return true;
    }

    public function deleteStockOutByIds($stockOutIds)
    {
        foreach ($stockOutIds as $id) {
            $stockOut = BizStockOut::find($id);
            if ($stockOut && $stockOut->status === '1') {
                return false;
            }
        }
        BizStockOutItem::whereIn('stock_out_id', $stockOutIds)->delete();
        return BizStockOut::whereIn('stock_out_id', $stockOutIds)->delete();
    }

    public function confirmStockOut($stockOutId)
    {
        $stockOut = BizStockOut::find($stockOutId);
        if (!$stockOut) {
            return ['success' => false, 'msg' => '出库单不存在'];
        }
        if ($stockOut->status === '1') {
            return ['success' => false, 'msg' => '出库单已确认，请勿重复操作'];
        }
        $items = BizStockOutItem::where('stock_out_id', $stockOutId)->get();
        if ($items->isEmpty()) {
            return ['success' => false, 'msg' => '出库单明细为空'];
        }
        foreach ($items as $item) {
            $actualQty = intval($item->quantity);
            $inventory = BizInventory::where('product_id', $item->product_id)->first();
            if (!$inventory || $inventory->quantity < $actualQty) {
                $product = BizProduct::find($item->product_id);
                $productName = $product ? $product->product_name : $item->product_name;
                $currentQty = $inventory ? $inventory->quantity : 0;
                return ['success' => false, 'msg' => "货品【{$productName}】库存不足，当前库存：{$currentQty}，出库数量：{$actualQty}"];
            }
        }
        foreach ($items as $item) {
            $actualQty = intval($item->quantity);
            $inventory = BizInventory::where('product_id', $item->product_id)->first();
            $inventory->quantity = $inventory->quantity - $actualQty;
            $inventory->last_stock_out_time = date('Y-m-d H:i:s');
            $inventory->update_time = date('Y-m-d H:i:s');
            $inventory->save();
        }
        BizStockOut::where('stock_out_id', $stockOutId)->update([
            'status' => '1',
            'update_time' => date('Y-m-d H:i:s'),
        ]);
        return ['success' => true, 'msg' => '出库确认成功'];
    }

    public function cancelConfirmStockOut($stockOutId)
    {
        $stockOut = BizStockOut::find($stockOutId);
        if (!$stockOut) {
            return ['success' => false, 'msg' => '出库单不存在'];
        }
        if ($stockOut->status === '0') {
            return ['success' => false, 'msg' => '出库单未确认，无需取消'];
        }

        $items = BizStockOutItem::where('stock_out_id', $stockOutId)->get();
        foreach ($items as $item) {
            $actualQty = intval($item->quantity);
            $inventory = BizInventory::where('product_id', $item->product_id)->first();
            if ($inventory) {
                $inventory->quantity = $inventory->quantity + $actualQty;
                $inventory->last_stock_out_time = date('Y-m-d H:i:s');
                $inventory->update_time = date('Y-m-d H:i:s');
                $inventory->save();
            }
        }

        BizStockOut::where('stock_out_id', $stockOutId)->update([
            'status' => '0',
            'update_time' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true, 'msg' => '已取消确认'];
    }
}
