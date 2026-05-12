<?php

namespace app\model;

use support\Model;

class BizAttendanceClock extends Model
{
    protected $table = 'biz_attendance_clock';
    protected $primaryKey = 'clock_id';
    public $timestamps = false;

    protected $fillable = [
        'record_id',
        'user_id',
        'user_name',
        'clock_time',
        'clock_type',
        'work_type',
        'latitude',
        'longitude',
        'address',
        'photo',
        'outside_reason',
        'remark',
    ];

    protected $casts = [
        'clock_time' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function record()
    {
        return $this->belongsTo(BizAttendanceRecord::class, 'record_id', 'record_id');
    }
}
