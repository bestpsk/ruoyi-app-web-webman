<?php

namespace app\model;

use support\Model;

class BizCustomerPackage extends Model
{
    protected $table = 'biz_customer_package';
    protected $primaryKey = 'package_id';
    public $timestamps = false;

    protected $fillable = [
        'package_no', 'customer_id', 'customer_name', 'order_id', 'order_no',
        'enterprise_id', 'store_id', 'package_name', 'total_amount',
        'paid_amount', 'owed_amount',
        'status', 'expire_date', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function items()
    {
        return $this->hasMany(BizPackageItem::class, 'package_id', 'package_id');
    }
}
