<?php

namespace app\model;

use support\Model;

class BizRepaymentRecord extends Model
{
    protected $table = 'biz_repayment_record';
    protected $primaryKey = 'repayment_id';
    public $timestamps = false;

    protected $fillable = [
        'repayment_no', 'customer_id', 'customer_name',
        'package_id', 'package_no', 'package_name',
        'order_id', 'order_no',
        'repayment_order_id', 'repayment_order_no',
        'repayment_amount', 'repayment_type', 'payment_method',
        'status', 'remark',
        'enterprise_id', 'enterprise_name',
        'store_id', 'store_name',
        'create_by', 'creator_user_id', 'creator_user_name',
        'create_time', 'update_by', 'update_time',
        'audit_by', 'audit_time'
    ];

    public function customer()
    {
        return $this->belongsTo(BizCustomer::class, 'customer_id', 'customer_id');
    }

    public function package()
    {
        return $this->belongsTo(BizCustomerPackage::class, 'package_id', 'package_id');
    }

    public function order()
    {
        return $this->belongsTo(BizSalesOrder::class, 'order_id', 'order_id');
    }
}
