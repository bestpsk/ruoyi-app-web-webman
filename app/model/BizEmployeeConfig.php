<?php

namespace app\model;

use support\Model;

class BizEmployeeConfig extends Model
{
    protected $table = 'biz_employee_config';
    protected $primaryKey = 'config_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'post_id', 'post_name', 'dept_id', 'dept_name',
        'is_schedulable', 'rest_dates', 'status', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];
}
