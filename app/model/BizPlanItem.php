<?php

namespace app\model;

use support\Model;

class BizPlanItem extends Model
{
    protected $table = 'biz_plan_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'plan_id', 'product_id', 'product_name', 'supplier_id', 'supplier_name',
        'unit_type', 'pack_qty', 'quantity', 'spec', 'sale_price', 'amount',
        'shipped_quantity', 'remaining_quantity', 'remark'
    ];

    public function plan()
    {
        return $this->belongsTo(BizPlan::class, 'plan_id', 'plan_id');
    }

    public function product()
    {
        return $this->belongsTo(BizProduct::class, 'product_id', 'product_id');
    }
}
