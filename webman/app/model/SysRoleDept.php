<?php

namespace app\model;

use support\Model;

class SysRoleDept extends Model
{
    protected $table = 'sys_role_dept';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ['role_id', 'dept_id'];
}
