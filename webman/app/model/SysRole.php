<?php

namespace app\model;

use support\Model;

class SysRole extends Model
{
    protected $table = 'sys_role';
    protected $primaryKey = 'role_id';
    public $timestamps = false;

    protected $fillable = [
        'role_name', 'role_key', 'role_sort', 'data_scope', 'menu_check_strictly',
        'dept_check_strictly', 'status', 'del_flag', 'create_by', 'create_time',
        'update_by', 'update_time', 'remark'
    ];

    public function menus()
    {
        return $this->belongsToMany(SysMenu::class, SysRoleMenu::class, 'role_id', 'menu_id', 'role_id', 'menu_id');
    }

    public function depts()
    {
        return $this->belongsToMany(SysDept::class, SysRoleDept::class, 'role_id', 'dept_id', 'role_id', 'dept_id');
    }

    public function users()
    {
        return $this->belongsToMany(SysUser::class, SysUserRole::class, 'role_id', 'user_id', 'role_id', 'user_id');
    }

    public function isAdmin()
    {
        return $this->role_id === 1;
    }
}
