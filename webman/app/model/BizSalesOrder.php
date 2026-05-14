<?php

namespace app\model;

use support\Model;

class BizSalesOrder extends Model
{
    protected $table = 'biz_sales_order';
    protected $primaryKey = 'order_id';
    public $timestamps = false;

    protected $fillable = [
        'order_no', 'customer_id', 'customer_name', 'enterprise_id', 'enterprise_name',
        'store_id', 'store_name', 'store_dealer', 'deal_amount', 'paid_amount', 'owed_amount', 'order_status', 'package_name',
        'enterprise_audit_status', 'finance_audit_status',
        'enterprise_audit_by', 'enterprise_audit_time', 'finance_audit_by', 'finance_audit_time',
        'creator_user_id', 'creator_user_name', 'customer_feedback', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function items()
    {
        return $this->hasMany(BizOrderItem::class, 'order_id', 'order_id');
    }
}
