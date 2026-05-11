<?php

namespace app\model;

use support\Model;

class BizStockCheckItem extends Model
{
    protected $table = 'biz_stock_check_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'stock_check_id', 'product_id', 'product_name', 'spec', 'unit',
        'system_quantity', 'actual_quantity', 'diff_quantity', 'remark'
    ];

    public function stockCheck()
    {
        return $this->belongsTo(BizStockCheck::class, 'stock_check_id', 'stock_check_id');
    }

    public function product()
    {
        return $this->belongsTo(BizProduct::class, 'product_id', 'product_id');
    }
}
