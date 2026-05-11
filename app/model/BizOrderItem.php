<?php

namespace app\model;

use support\Model;

class BizOrderItem extends Model
{
    protected $table = 'biz_order_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'product_name', 'quantity', 'plan_price',
        'is_deal', 'deal_amount', 'paid_amount', 'unit_price', 'owed_amount',
        'customer_feedback', 'remark', 'create_time'
    ];
}
