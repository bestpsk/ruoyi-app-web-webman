<?php

namespace app\model;

use support\Model;

class SysUserDetail extends Model
{
    protected $table = 'sys_user_detail';
    protected $primaryKey = 'detail_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'wechat', 'birthday', 'id_card', 'address',
        'hire_date', 'employment_status', 'resign_date',
        'create_by', 'create_time', 'update_by', 'update_time', 'remark'
    ];

    protected $casts = [
        'birthday' => 'date',
        'hire_date' => 'date',
        'resign_date' => 'date',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(SysUser::class, 'user_id', 'user_id');
    }
}
