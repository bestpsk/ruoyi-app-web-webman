<?php

namespace app\model;

use support\Model;

class BizStockCheck extends Model
{
    protected $table = 'biz_stock_check';
    protected $primaryKey = 'stock_check_id';
    public $timestamps = false;

    protected $fillable = [
        'stock_check_no', 'check_date', 'total_quantity', 'total_diff_quantity',
        'operator_id', 'operator_name', 'status', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function items()
    {
        return $this->hasMany(BizStockCheckItem::class, 'stock_check_id', 'stock_check_id');
    }
}
