<?php

namespace app\model;

use support\Model;

class BizEnterprise extends Model
{
    protected $table = 'biz_enterprise';
    protected $primaryKey = 'enterprise_id';
    public $timestamps = false;

    protected $fillable = [
        'enterprise_name', 'pinyin', 'boss_name', 'phone', 'address', 'enterprise_type',
        'store_count', 'annual_performance', 'enterprise_level', 'server_user_id',
        'server_user_name', 'cooperation_start_date', 'cooperation_end_date',
        'status', 'remark', 'create_by', 'create_time',
        'update_by', 'update_time'
    ];

    public function plans()
    {
        return $this->hasMany(BizPlan::class, 'enterprise_id', 'enterprise_id');
    }
}
