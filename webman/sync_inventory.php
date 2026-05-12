<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/support/bootstrap.php';

use support\Db;

echo "=== 开始重新同步库存 ===\n";

$products = Db::table('biz_product')->where('status', '0')->get();
echo "共 {$products->count()} 个货品\n\n";

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
    } else {
        if ($correctQty > 0 || intval($totalIn) > 0) {
            Db::table('biz_inventory')->insert([
                'product_id' => $productId,
                'quantity' => $correctQty,
                'warn_qty' => $product->warn_qty ?? 0,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ]);
            echo "[{$product->product_code}] {$product->product_name}: 新建库存 = {$correctQty}\n";
        }
    }
}

echo "\n=== 库存同步完成 ===\n";
