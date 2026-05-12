<?php

namespace app\model;

use support\Model;

class SysOperLog extends Model
{
    protected $table = 'sys_oper_log';
    protected $primaryKey = 'oper_id';
    public $timestamps = false;

    protected $fillable = [
        'title', 'business_type', 'method', 'request_method', 'operator_type',
        'oper_name', 'dept_name', 'oper_url', 'oper_ip', 'oper_location',
        'oper_param', 'json_result', 'status', 'error_msg', 'oper_time', 'cost_time'
    ];
}
