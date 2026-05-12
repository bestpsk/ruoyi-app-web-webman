<?php

namespace app\model;

use support\Model;

class SysJob extends Model
{
    protected $table = 'sys_job';
    protected $primaryKey = 'job_id';
    public $timestamps = false;

    protected $fillable = [
        'job_name', 'job_group', 'invoke_target', 'cron_expression', 'misfire_policy',
        'concurrent', 'status', 'create_by', 'create_time', 'update_by', 'update_time', 'remark'
    ];
}
