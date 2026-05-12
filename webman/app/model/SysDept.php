<?php

namespace app\model;

use support\Model;

class SysDept extends Model
{
    protected $table = 'sys_dept';
    protected $primaryKey = 'dept_id';
    public $timestamps = false;

    protected $fillable = [
        'parent_id', 'ancestors', 'dept_name', 'order_num', 'leader', 'phone',
        'email', 'status', 'del_flag', 'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function children()
    {
        return $this->hasMany(SysDept::class, 'parent_id', 'dept_id');
    }

    public function parent()
    {
        return $this->belongsTo(SysDept::class, 'parent_id', 'dept_id');
    }
}
