<?php

namespace app\model;

use support\Model;

class BizShipment extends Model
{
    protected $table = 'biz_shipment';
    protected $primaryKey = 'shipment_id';
    public $timestamps = false;

    protected $fillable = [
        'shipment_no', 'plan_id', 'enterprise_id', 'enterprise_name',
        'contact_person', 'contact_phone', 'shipping_address',
        'total_quantity', 'total_amount', 'logistics_company', 'logistics_no',
        'shipment_status', 'shipment_date', 'receipt_date',
        'audit_by', 'audit_time', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function plan()
    {
        return $this->belongsTo(BizPlan::class, 'plan_id', 'plan_id');
    }

    public function items()
    {
        return $this->hasMany(BizShipmentItem::class, 'shipment_id', 'shipment_id');
    }

    public function enterprise()
    {
        return $this->belongsTo(BizEnterprise::class, 'enterprise_id', 'enterprise_id');
    }
}
