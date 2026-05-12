<?php

namespace app\model;

use support\Model;

class BizStockOut extends Model
{
    protected $table = 'biz_stock_out';
    protected $primaryKey = 'stock_out_id';
    public $timestamps = false;

    protected $fillable = [
        'stock_out_no', 'stock_out_type', 'out_target_type', 'enterprise_id', 'enterprise_name',
        'contact_employee_id', 'contact_employee_name',
        'responsible_id', 'responsible_name', 'total_quantity', 'total_amount',
        'stock_out_date', 'status', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function items()
    {
        return $this->hasMany(BizStockOutItem::class, 'stock_out_id', 'stock_out_id');
    }
}
