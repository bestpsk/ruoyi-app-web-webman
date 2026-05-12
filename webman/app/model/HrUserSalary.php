<?php

namespace app\model;

use support\Model;

class HrUserSalary extends Model
{
    protected $table = 'hr_user_salary';
    protected $primaryKey = 'salary_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'type_id', 'base_amount', 'commission_rate', 'tier_config',
        'effective_date', 'expire_date', 'status',
        'create_by', 'create_time', 'update_by', 'update_time', 'remark'
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_rate' => 'decimal:4',
        'effective_date' => 'date',
        'expire_date' => 'date',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(SysUser::class, 'user_id', 'user_id');
    }

    public function salaryType()
    {
        return $this->belongsTo(HrSalaryType::class, 'type_id', 'type_id');
    }

    public function tiers()
    {
        return $this->hasMany(HrSalaryTier::class, 'salary_id', 'salary_id')->orderBy('tier_level');
    }

    public function getTierConfigAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setTierConfigAttribute($value)
    {
        $this->attributes['tier_config'] = is_array($value) ? json_encode($value) : $value;
    }
}
