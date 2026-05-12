<?php

namespace app\model;

use support\Model;

class SysDictData extends Model
{
    protected $table = 'sys_dict_data';
    protected $primaryKey = 'dict_code';
    public $timestamps = false;

    protected $fillable = [
        'dict_sort', 'dict_label', 'dict_value', 'dict_type', 'css_class',
        'list_class', 'is_default', 'status', 'create_by', 'create_time',
        'update_by', 'update_time', 'remark'
    ];
}
