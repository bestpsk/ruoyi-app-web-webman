<?php

namespace app\model;

use support\Model;

class SysRoleMenu extends Model
{
    protected $table = 'sys_role_menu';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ['role_id', 'menu_id'];
}
