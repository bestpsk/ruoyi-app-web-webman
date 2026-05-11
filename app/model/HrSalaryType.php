<?php

namespace app\model;

use support\Model;

class HrSalaryType extends Model
{
    protected $table = 'hr_salary_type';
    protected $primaryKey = 'type_id';
    public $timestamps = false;

    protected $fillable = [
        'type_code', 'type_name', 'calc_formula', 'status',
        'create_by', 'create_time', 'update_by', 'update_time', 'remark'
    ];

    protected $casts = [
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];

    public function userSalaries()
    {
        return $this->hasMany(HrUserSalary::class, 'type_id', 'type_id');
    }
}
