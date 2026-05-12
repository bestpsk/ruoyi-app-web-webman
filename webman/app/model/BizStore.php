<?php

namespace app\model;

use support\Model;

class BizStore extends Model
{
    protected $table = 'biz_store';
    protected $primaryKey = 'store_id';
    public $timestamps = false;

    protected $fillable = [
        'enterprise_id', 'enterprise_name', 'store_name', 'manager_name', 'phone',
        'wechat', 'address', 'business_hours', 'annual_performance', 'regular_customers',
        'creator_name', 'server_user_id', 'server_user_name',
        'status', 'remark', 'create_by', 'create_time', 'update_by', 'update_time'
    ];
}
