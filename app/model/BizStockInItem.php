<?php

namespace app\model;

use support\Model;

class BizStockInItem extends Model
{
    protected $table = 'biz_stock_in_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'stock_in_id', 'product_id', 'product_name',
        'supplier_id', 'supplier_name',
        'spec', 'unit', 'unit_type', 'pack_qty',
        'original_quantity', 'quantity', 'purchase_price', 'amount',
        'production_date', 'expiry_date', 'remark'
    ];

    public function stockIn()
    {
        return $this->belongsTo(BizStockIn::class, 'stock_in_id', 'stock_in_id');
    }

    public function product()
    {
        return $this->belongsTo(BizProduct::class, 'product_id', 'product_id');
    }
}
