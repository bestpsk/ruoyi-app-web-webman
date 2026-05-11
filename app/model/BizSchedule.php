<?php

namespace app\model;

use support\Model;

class BizSchedule extends Model
{
    protected $table = 'biz_schedule';
    protected $primaryKey = 'schedule_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'enterprise_id', 'enterprise_name',
        'schedule_date', 'purpose', 'remark', 'status',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];
}
