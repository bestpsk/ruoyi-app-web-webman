<?php

namespace app\model;

use support\Model;

class BizPlan extends Model
{
    protected $table = 'biz_plan';
    protected $primaryKey = 'plan_id';
    public $timestamps = false;

    protected $fillable = [
        'plan_no', 'enterprise_id', 'plan_name', 'commission_rate',
        'plan_amount', 'gift_amount', 'shipped_amount', 'remaining_amount',
        'effective_date', 'expiry_date', 'audit_status', 'audit_by',
        'audit_time', 'audit_remark', 'submit_by', 'submit_time',
        'status_change_by', 'status_change_time', 'status', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function enterprise()
    {
        return $this->belongsTo(BizEnterprise::class, 'enterprise_id', 'enterprise_id');
    }

    public function items()
    {
        return $this->hasMany(BizPlanItem::class, 'plan_id', 'plan_id');
    }

    public function shipments()
    {
        return $this->hasMany(BizShipment::class, 'plan_id', 'plan_id');
    }
}
