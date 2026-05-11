<?php

namespace app\model;

use support\Model;

class BizStockIn extends Model
{
    protected $table = 'biz_stock_in';
    protected $primaryKey = 'stock_in_id';
    public $timestamps = false;

    protected $fillable = [
        'stock_in_no', 'stock_in_type', 'supplier_id', 'total_quantity', 'total_amount',
        'stock_in_date', 'operator_id', 'operator_name', 'status', 'remark',
        'create_by', 'create_time', 'update_by', 'update_time'
    ];

    public function items()
    {
        return $this->hasMany(BizStockInItem::class, 'stock_in_id', 'stock_in_id');
    }

    public function supplier()
    {
        return $this->belongsTo(BizSupplier::class, 'supplier_id', 'supplier_id');
    }
}
