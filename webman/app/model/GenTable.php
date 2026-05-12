<?php

namespace app\model;

use support\Model;

class GenTable extends Model
{
    protected $table = 'gen_table';
    protected $primaryKey = 'table_id';
    public $timestamps = false;

    protected $fillable = [
        'table_name', 'table_comment', 'sub_table_name', 'sub_table_fk_name',
        'class_name', 'tpl_category', 'tpl_web_type', 'package_name', 'module_name',
        'business_name', 'function_name', 'function_author', 'form_col_num', 'gen_type',
        'gen_path', 'options', 'create_by', 'create_time', 'update_by', 'update_time', 'remark'
    ];

    public function columns()
    {
        return $this->hasMany(GenTableColumn::class, 'table_id', 'table_id');
    }
}
