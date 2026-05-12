<?php

namespace app\model;

use support\Model;

class BizOrderItem extends Model
{
    protected $table = 'biz_order_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'product_name', 'quantity',
        'deal_amount', 'paid_amount', 'unit_price', 'owed_amount',
        'remark', 'create_time'
    ];
}
