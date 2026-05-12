<?php

namespace app\model;

use support\Model;

class SysDictType extends Model
{
    protected $table = 'sys_dict_type';
    protected $primaryKey = 'dict_id';
    public $timestamps = false;

    protected $fillable = [
        'dict_name', 'dict_type', 'status', 'create_by', 'create_time',
        'update_by', 'update_time', 'remark'
    ];

    public function dictData()
    {
        return $this->hasMany(SysDictData::class, 'dict_type', 'dict_type');
    }
}
