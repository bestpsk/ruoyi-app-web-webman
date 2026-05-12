<?php

namespace app\model;

use support\Model;

class SysConfig extends Model
{
    protected $table = 'sys_config';
    protected $primaryKey = 'config_id';
    public $timestamps = false;

    protected $fillable = [
        'config_name', 'config_key', 'config_value', 'config_type', 'create_by',
        'create_time', 'update_by', 'update_time', 'remark'
    ];
}
