<?php

namespace app\model;

use support\Model;

class SysJobLog extends Model
{
    protected $table = 'sys_job_log';
    protected $primaryKey = 'job_log_id';
    public $timestamps = false;

    protected $fillable = [
        'job_name', 'job_group', 'invoke_target', 'job_message', 'status',
        'exception_info', 'start_time', 'end_time', 'create_time'
    ];
}
