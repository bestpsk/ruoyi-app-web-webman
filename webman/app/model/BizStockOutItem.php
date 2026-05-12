<?php

namespace app\model;

use support\Model;

class BizStockOutItem extends Model
{
    protected $table = 'biz_stock_out_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'stock_out_id', 'product_id', 'product_name', 'spec', 'unit', 'unit_type', 'pack_qty',
        'original_quantity', 'quantity', 'sale_price', 'amount', 'remark'
    ];

    public function stockOut()
    {
        return $this->belongsTo(BizStockOut::class, 'stock_out_id', 'stock_out_id');
    }

    public function product()
    {
        return $this->belongsTo(BizProduct::class, 'product_id', 'product_id');
    }
}
