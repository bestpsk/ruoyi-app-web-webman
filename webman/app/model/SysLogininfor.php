<?php

namespace app\model;

use support\Model;

class SysLogininfor extends Model
{
    protected $table = 'sys_logininfor';
    protected $primaryKey = 'info_id';
    public $timestamps = false;

    protected $fillable = [
        'user_name', 'ipaddr', 'login_location', 'browser', 'os', 'status', 'msg', 'login_time'
    ];
}
