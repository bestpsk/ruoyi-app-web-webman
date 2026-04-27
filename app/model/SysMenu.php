<?php

namespace app\model;

use support\Model;

class SysMenu extends Model
{
    protected $table = 'sys_menu';
    protected $primaryKey = 'menu_id';
    public $timestamps = false;

    protected $fillable = [
        'menu_name', 'parent_id', 'order_num', 'path', 'component', 'query',
        'route_name', 'is_frame', 'is_cache', 'menu_type', 'visible', 'status',
        'perms', 'icon', 'create_by', 'create_time', 'update_by', 'update_time', 'remark'
    ];

    public function children()
    {
        return $this->hasMany(SysMenu::class, 'parent_id', 'menu_id');
    }

    public function parent()
    {
        return $this->belongsTo(SysMenu::class, 'parent_id', 'menu_id');
    }
}
