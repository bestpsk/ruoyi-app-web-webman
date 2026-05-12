<?php

namespace app\model;

use support\Model;

class SysUserPost extends Model
{
    protected $table = 'sys_user_post';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ['user_id', 'post_id'];
}
