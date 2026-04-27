<?php

namespace app\model;

use support\Model;

class SysNoticeRead extends Model
{
    protected $table = 'sys_notice_read';
    protected $primaryKey = 'read_id';
    public $timestamps = false;

    protected $fillable = [
        'notice_id', 'user_id', 'read_time'
    ];
}
