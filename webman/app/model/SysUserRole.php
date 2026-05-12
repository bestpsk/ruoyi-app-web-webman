<?php

namespace app\model;

use support\Model;

class SysUserRole extends Model
{
    protected $table = 'sys_user_role';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ['user_id', 'role_id'];
}
