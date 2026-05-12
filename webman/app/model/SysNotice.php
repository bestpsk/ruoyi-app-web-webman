<?php

namespace app\model;

use support\Model;

class SysNotice extends Model
{
    protected $table = 'sys_notice';
    protected $primaryKey = 'notice_id';
    public $timestamps = false;

    protected $fillable = [
        'notice_title', 'notice_type', 'notice_content', 'status', 'create_by',
        'create_time', 'update_by', 'update_time', 'remark'
    ];

    protected $casts = [
        'notice_content' => 'string',
    ];

    public function readUsers()
    {
        return $this->hasMany(SysNoticeRead::class, 'notice_id', 'notice_id');
    }
}
