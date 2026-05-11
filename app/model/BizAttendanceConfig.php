<?php

namespace app\model;

use support\Model;

class BizAttendanceConfig extends Model
{
    protected $table = 'biz_attendance_config';
    protected $primaryKey = 'config_id';
    public $timestamps = false;

    protected $fillable = [
        'config_name',
        'rule_id',
        'config_type',
        'user_ids',
        'dept_id',
        'status',
        'remark',
        'create_by',
        'create_time',
        'update_by',
        'update_time'
    ];

    public function rule()
    {
        return $this->belongsTo(BizAttendanceRule::class, 'rule_id', 'rule_id');
    }

    public function dept()
    {
        return $this->belongsTo(SysDept::class, 'dept_id', 'dept_id');
    }

    public function getUserIdsArrayAttribute()
    {
        if (empty($this->user_ids)) {
            return [];
        }
        return array_map('intval', array_filter(explode(',', $this->user_ids)));
    }

    public function getUsersAttribute()
    {
        $userIds = $this->user_ids_array;
        if (empty($userIds)) {
            return [];
        }
        return SysUser::whereIn('user_id', $userIds)->get();
    }
}
