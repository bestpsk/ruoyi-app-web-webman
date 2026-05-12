<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/support/bootstrap.php';
use support\Db;

echo "=== 库存表 ===\n";
$inv = Db::table('biz_inventory')->get();
foreach ($inv as $i) {
    echo "PRODUCT_ID:{$i->product_id} QTY:{$i->quantity}\n";
}

echo "\n=== 入库单（测试1相关）===\n";
$stockIns = Db::table('biz_stock_in')->get();
foreach ($stockIns as $si) {
    echo "ID:{$si->stock_in_id} NO:{$si->stock_in_no} STATUS:{$si->status} TOTAL_QTY:{$si->total_quantity}\n";
}

echo "\n=== 入库明细 ===\n";
$items = Db::table('biz_stock_in_item')->get();
foreach ($items as $item) {
    echo "STOCK_IN_ID:{$item->stock_in_id} PRODUCT:{$item->product_name} QTY:{$item->quantity} UNIT_TYPE:{$item->unit_type} ORIGINAL:{$item->original_quantity}\n";
}
