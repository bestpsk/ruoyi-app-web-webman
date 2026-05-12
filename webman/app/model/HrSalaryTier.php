<?php

namespace app\model;

use support\Model;

class HrSalaryTier extends Model
{
    protected $table = 'hr_salary_tier';
    protected $primaryKey = 'tier_id';
    public $timestamps = false;

    protected $fillable = [
        'salary_id', 'tier_level', 'min_amount', 'max_amount', 'commission_rate', 'create_time'
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'commission_rate' => 'decimal:4',
        'create_time' => 'datetime',
    ];

    public function userSalary()
    {
        return $this->belongsTo(HrUserSalary::class, 'salary_id', 'salary_id');
    }
}
