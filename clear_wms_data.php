<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/support/bootstrap.php';

use support\Db;

echo "开始清除进销存数据...\n";

$count = Db::table('biz_stock_in_item')->delete();
echo "清除入库单明细: {$count} 条\n";

$count = Db::table('biz_stock_in')->delete();
echo "清除入库单主表: {$count} 条\n";

$count = Db::table('biz_stock_out_item')->delete();
echo "清除出库单明细: {$count} 条\n";

$count = Db::table('biz_stock_out')->delete();
echo "清除出库单主表: {$count} 条\n";

$count = Db::table('biz_stock_check_item')->delete();
echo "清除库存盘点明细: {$count} 条\n";

$count = Db::table('biz_stock_check')->delete();
echo "清除库存盘点主表: {$count} 条\n";

$count = Db::table('biz_inventory')->update(['quantity' => 0, 'warn_qty' => 0]);
echo "重置库存数量: {$count} 条\n";

echo "清除完成!\n";
