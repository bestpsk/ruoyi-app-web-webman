<?php

namespace app\model;

use support\Model;

class BizSupplier extends Model
{
    protected $table = 'biz_supplier';
    protected $primaryKey = 'supplier_id';
    public $timestamps = false;

    protected $fillable = [
        'supplier_name', 'contact_person', 'contact_phone', 'address',
        'cooperation_start_date', 'status', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];
}
