<?php

namespace app\model;

use support\Model;

class BizProduct extends Model
{
    protected $table = 'biz_product';
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $fillable = [
        'product_name', 'product_code', 'supplier_id', 'category',
        'unit', 'spec', 'pack_qty', 'purchase_price', 'sale_price', 'sale_price_spec',
        'shelf_life_days', 'has_expiry',
        'warn_qty', 'status', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function supplier()
    {
        return $this->belongsTo(BizSupplier::class, 'supplier_id', 'supplier_id');
    }

    public function inventory()
    {
        return $this->hasOne(BizInventory::class, 'product_id', 'product_id');
    }
}
