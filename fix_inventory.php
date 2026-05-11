<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/support/bootstrap.php';
use support\Db;

echo "=== 修复入库单并同步库存 ===\n\n";

$stockInId = 10;
$stockIn = Db::table('biz_stock_in')->where('stock_in_id', $stockInId)->first();

if (!$stockIn) {
    echo "入库单不存在\n"; exit;
}

echo "入库单: {$stockIn->stock_in_no} 当前状态: {$stockIn->status}\n";

if ($stockIn->status === '1') {
    echo "已确认，无需处理\n";
} else {
    Db::table('biz_stock_in')->where('stock_in_id', $stockInId)->update([
        'status' => '1',
        'update_time' => date('Y-m-d H:i:s'),
    ]);
    echo "✓ 已将状态更新为 '1'（已确认）\n";
}

echo "\n--- 同步库存 ---\n";
$products = Db::table('biz_product')->where('status', '0')->get();

foreach ($products as $product) {
    $productId = $product->product_id;
    
    $totalIn = Db::table('biz_stock_in_item')
        ->join('biz_stock_in', 'biz_stock_in_item.stock_in_id', '=', 'biz_stock_in.stock_in_id')
        ->where('biz_stock_in.status', '1')
        ->where('biz_stock_in_item.product_id', $productId)
        ->sum('biz_stock_in_item.quantity');
    
    $totalOut = Db::table('biz_stock_out_item')
        ->join('biz_stock_out', 'biz_stock_out_item.stock_out_id', '=', 'biz_stock_out.stock_out_id')
        ->where('biz_stock_out.status', '1')
        ->where('biz_stock_out_item.product_id', $productId)
        ->sum('biz_stock_out_item.quantity');
    
    $correctQty = intval($totalIn) - intval($totalOut);
    
    $inventory = Db::table('biz_inventory')->where('product_id', $productId)->first();
    if ($inventory) {
        $oldQty = $inventory->quantity;
        Db::table('biz_inventory')
            ->where('product_id', $productId)
            ->update(['quantity' => $correctQty, 'update_time' => date('Y-m-d H:i:s')]);
        echo "[{$product->product_code}] {$product->product_name}: {$oldQty} → {$correctQty} (入库:{$totalIn} 出库:{$totalOut})\n";
    }
}

echo "\n=== 修复完成，请刷新页面验证 ===\n";
