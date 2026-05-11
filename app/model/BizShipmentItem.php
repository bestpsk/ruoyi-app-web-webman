<?php

namespace app\model;

use support\Model;

class BizShipmentItem extends Model
{
    protected $table = 'biz_shipment_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'shipment_id', 'plan_item_id', 'product_id', 'product_name',
        'supplier_id', 'supplier_name', 'unit_type', 'pack_qty',
        'quantity', 'spec', 'sale_price', 'discount_price', 'amount', 'remark'
    ];

    public function shipment()
    {
        return $this->belongsTo(BizShipment::class, 'shipment_id', 'shipment_id');
    }

    public function product()
    {
        return $this->belongsTo(BizProduct::class, 'product_id', 'product_id');
    }
}
