<?php

namespace app\model;

use support\Model;

class BizAttendanceRecord extends Model
{
    protected $table = 'biz_attendance_record';
    protected $primaryKey = 'record_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'user_name', 'attendance_date',
        'clock_in_time', 'clock_out_time',
        'clock_in_latitude', 'clock_in_longitude', 'clock_in_address', 'clock_in_photo',
        'clock_out_latitude', 'clock_out_longitude', 'clock_out_address', 'clock_out_photo',
        'attendance_status', 'clock_type', 'outside_reason', 'rule_id', 'remark',
        'clock_count', 'first_clock_time', 'last_clock_time',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];
}
