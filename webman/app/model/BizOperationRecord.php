<?php

namespace app\model;

use support\Model;

class BizOperationRecord extends Model
{
    protected $table = 'biz_operation_record';
    protected $primaryKey = 'record_id';
    public $timestamps = false;

    protected $fillable = [
        'operation_type', 'customer_id', 'customer_name', 'package_id', 'package_no', 'operation_batch_id', 'package_item_id',
        'product_name', 'operation_quantity', 'consume_amount', 'trial_price',
        'customer_feedback', 'satisfaction',
        'before_photo', 'after_photo', 'operator_user_id', 'operator_user_name',
        'operation_date', 'enterprise_id', 'store_id', 'remark', 'create_by', 'create_time'
    ];
}
