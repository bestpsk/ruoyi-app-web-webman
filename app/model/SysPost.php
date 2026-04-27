<?php

namespace app\model;

use support\Model;

class SysPost extends Model
{
    protected $table = 'sys_post';
    protected $primaryKey = 'post_id';
    public $timestamps = false;

    protected $fillable = [
        'post_code', 'post_name', 'post_sort', 'status', 'create_by', 'create_time',
        'update_by', 'update_time', 'remark'
    ];
}
