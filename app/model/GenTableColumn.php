<?php

namespace app\model;

use support\Model;

class GenTableColumn extends Model
{
    protected $table = 'gen_table_column';
    protected $primaryKey = 'column_id';
    public $timestamps = false;

    protected $fillable = [
        'table_id', 'column_name', 'column_comment', 'column_type', 'java_type',
        'java_field', 'is_pk', 'is_increment', 'is_required', 'is_insert', 'is_edit',
        'is_list', 'is_query', 'query_type', 'html_type', 'dict_type', 'sort',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];
}
