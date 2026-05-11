<?php

namespace app\service;

use app\model\BizStockIn;
use app\model\BizStockInItem;
use app\model\BizStockOut;
use app\model\BizStockOutItem;
use app\model\BizInventory;
use app\model\BizProduct;
use support\Db;

class BizWmsReportService
{
    public function stockInSummary($params = [])
    {
        $query = BizStockInItem::query()
            ->join('biz_stock_in', 'biz_stock_in_item.stock_in_id', '=', 'biz_stock_in.stock_in_id')
            ->join('biz_product', 'biz_stock_in_item.product_id', '=', 'biz_product.product_id')
            ->where('biz_stock_in.status', '1');
        if (!empty($params['stock_in_date_start'])) {
            $query->where('biz_stock_in.stock_in_date', '>=', $params['stock_in_date_start']);
        }
        if (!empty($params['stock_in_date_end'])) {
            $query->where('biz_stock_in.stock_in_date', '<=', $params['stock_in_date_end']);
        }
        if (!empty($params['supplier_id'])) {
            $query->where('biz_stock_in.supplier_id', $params['supplier_id']);
        }
        if (isset($params['category']) && $params['category'] !== '') {
            $query->where('biz_product.category', $params['category']);
        }
        $results = $query->groupBy([
                'biz_stock_in_item.product_id',
                'biz_stock_in_item.product_name',
                'biz_product.category',
                'biz_product.unit',
                'biz_product.spec',
                'biz_product.pack_qty',
            ])
            ->selectRaw(
                'biz_stock_in_item.product_id, ' .
                'biz_stock_in_item.product_name, ' .
                'biz_product.category, ' .
                'biz_product.unit, ' .
                'biz_product.spec, ' .
                'biz_product.pack_qty, ' .
                'SUM(biz_stock_in_item.quantity) as total_quantity, ' .
                'SUM(biz_stock_in_item.amount) as total_amount'
            )
            ->get();
        return $results;
    }

    public function stockOutSummary($params = [])
    {
        $query = BizStockOutItem::query()
            ->join('biz_stock_out', 'biz_stock_out_item.stock_out_id', '=', 'biz_stock_out.stock_out_id')
            ->join('biz_product', 'biz_stock_out_item.product_id', '=', 'biz_product.product_id')
            ->where('biz_stock_out.status', '1');
        if (!empty($params['stock_out_date_start'])) {
            $query->where('biz_stock_out.stock_out_date', '>=', $params['stock_out_date_start']);
        }
        if (!empty($params['stock_out_date_end'])) {
            $query->where('biz_stock_out.stock_out_date', '<=', $params['stock_out_date_end']);
        }
        if (!empty($params['enterprise_id'])) {
            $query->where('biz_stock_out.enterprise_id', $params['enterprise_id']);
        }
        if (isset($params['category']) && $params['category'] !== '') {
            $query->where('biz_product.category', $params['category']);
        }
        $results = $query->groupBy([
                'biz_stock_out_item.product_id',
                'biz_stock_out_item.product_name',
                'biz_product.category',
                'biz_product.unit',
                'biz_product.spec',
                'biz_product.pack_qty',
            ])
            ->selectRaw(
                'biz_stock_out_item.product_id, ' .
                'biz_stock_out_item.product_name, ' .
                'biz_product.category, ' .
                'biz_product.unit, ' .
                'biz_product.spec, ' .
                'biz_product.pack_qty, ' .
                'SUM(biz_stock_out_item.quantity) as total_quantity, ' .
                'SUM(biz_stock_out_item.amount) as total_amount'
            )
            ->get();
        return $results;
    }

    public function inventoryTurnover($params = [])
    {
        $query = BizProduct::query()
            ->leftJoin('biz_inventory', 'biz_product.product_id', '=', 'biz_inventory.product_id')
            ->where('biz_product.status', '0');
        if (isset($params['category']) && $params['category'] !== '') {
            $query->where('biz_product.category', $params['category']);
        }
        $products = $query->select([
                'biz_product.product_id',
                'biz_product.product_name',
                'biz_product.product_code',
                'biz_product.category',
                'biz_product.unit',
                'biz_product.spec',
                'biz_product.pack_qty',
                'biz_inventory.quantity as current_quantity',
            ])
            ->get();
        $startDate = $params['start_date'] ?? date('Y-m-01');
        $endDate = $params['end_date'] ?? date('Y-m-d');
        foreach ($products as $product) {
            $stockInQty = BizStockInItem::query()
                ->join('biz_stock_in', 'biz_stock_in_item.stock_in_id', '=', 'biz_stock_in.stock_in_id')
                ->where('biz_stock_in.status', '1')
                ->where('biz_stock_in_item.product_id', $product->product_id)
                ->whereBetween('biz_stock_in.stock_in_date', [$startDate, $endDate])
                ->sum('biz_stock_in_item.quantity');
            $stockOutQty = BizStockOutItem::query()
                ->join('biz_stock_out', 'biz_stock_out_item.stock_out_id', '=', 'biz_stock_out.stock_out_id')
                ->where('biz_stock_out.status', '1')
                ->where('biz_stock_out_item.product_id', $product->product_id)
                ->whereBetween('biz_stock_out.stock_out_date', [$startDate, $endDate])
                ->sum('biz_stock_out_item.quantity');
            $product->period_in_quantity = intval($stockInQty);
            $product->period_out_quantity = intval($stockOutQty);
            $product->begin_quantity = intval($product->current_quantity) - intval($stockInQty) + intval($stockOutQty);
            $product->end_quantity = intval($product->current_quantity);
        }
        return $products;
    }

    public function productFlow($params = [])
    {
        $productId = $params['product_id'] ?? 0;
        if (!$productId) {
            return [];
        }
        $product = BizProduct::find($productId);
        $productInfo = $product ? [
            'unit' => $product->unit,
            'spec' => $product->spec,
            'pack_qty' => $product->pack_qty ?? 1,
        ] : ['unit' => null, 'spec' => null, 'pack_qty' => 1];
        $flows = [];
        $stockInItems = BizStockInItem::query()
            ->join('biz_stock_in', 'biz_stock_in_item.stock_in_id', '=', 'biz_stock_in.stock_in_id')
            ->where('biz_stock_in.status', '1')
            ->where('biz_stock_in_item.product_id', $productId);
        if (!empty($params['flow_date_start'])) {
            $stockInItems->where('biz_stock_in.stock_in_date', '>=', $params['flow_date_start']);
        }
        if (!empty($params['flow_date_end'])) {
            $stockInItems->where('biz_stock_in.stock_in_date', '<=', $params['flow_date_end']);
        }
        $stockInList = $stockInItems->select([
                'biz_stock_in.stock_in_no as doc_no',
                'biz_stock_in.stock_in_date as flow_date',
                'biz_stock_in_item.quantity',
                'biz_stock_in_item.amount',
                Db::raw("'入库' as flow_type"),
            ])->get();
        foreach ($stockInList as $item) {
            $item->unit = $productInfo['unit'];
            $item->spec = $productInfo['spec'];
            $item->pack_qty = $productInfo['pack_qty'];
            $flows[] = $item;
        }
        $stockOutItems = BizStockOutItem::query()
            ->join('biz_stock_out', 'biz_stock_out_item.stock_out_id', '=', 'biz_stock_out.stock_out_id')
            ->where('biz_stock_out.status', '1')
            ->where('biz_stock_out_item.product_id', $productId);
        if (!empty($params['flow_date_start'])) {
            $stockOutItems->where('biz_stock_out.stock_out_date', '>=', $params['flow_date_start']);
        }
        if (!empty($params['flow_date_end'])) {
            $stockOutItems->where('biz_stock_out.stock_out_date', '<=', $params['flow_date_end']);
        }
        $stockOutList = $stockOutItems->select([
                'biz_stock_out.stock_out_no as doc_no',
                'biz_stock_out.stock_out_date as flow_date',
                'biz_stock_out_item.quantity',
                'biz_stock_out_item.amount',
                Db::raw("'出库' as flow_type"),
            ])->get();
        foreach ($stockOutList as $item) {
            $item->unit = $productInfo['unit'];
            $item->spec = $productInfo['spec'];
            $item->pack_qty = $productInfo['pack_qty'];
            $flows[] = $item;
        }
        usort($flows, function ($a, $b) {
            return strcmp($a['flow_date'], $b['flow_date']);
        });
        $balance = 0;
        foreach ($flows as &$flow) {
            if ($flow['flow_type'] === '入库') {
                $balance += intval($flow['quantity']);
            } else {
                $balance -= intval($flow['quantity']);
            }
            $flow['balance'] = $balance;
        }
        unset($flow);
        $flows = array_reverse($flows);
        return $flows;
    }
}
