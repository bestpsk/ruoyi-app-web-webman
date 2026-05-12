<?php

namespace app\model;

use support\Model;

class BizInventory extends Model
{
    protected $table = 'biz_inventory';
    protected $primaryKey = 'inventory_id';
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'quantity', 'warn_qty', 'earliest_expiry',
        'last_stock_in_time', 'last_stock_out_time',
        'create_time', 'update_time'
    ];

    public function product()
    {
        return $this->belongsTo(BizProduct::class, 'product_id', 'product_id');
    }
}
