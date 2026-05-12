<?php
require_once __DIR__ . '/vendor/autoload.php';

$stockOutId = 17;

$stockOut = \app\model\BizStockOut::find($stockOutId);
if ($stockOut) {
    $stockOutArray = $stockOut->toArray();
    
    echo "=== 原始数据库值 ===\n";
    echo "enterprise_id: " . ($stockOutArray['enterprise_id'] ?? 'NULL') . "\n";
    echo "enterprise_name: " . ($stockOutArray['enterprise_name'] ?? 'NULL') . "\n";
    echo "contact_employee_id: " . ($stockOutArray['contact_employee_id'] ?? 'NULL') . "\n";
    echo "contact_employee_name: " . ($stockOutArray['contact_employee_name'] ?? 'NULL') . "\n";
    
    echo "\n=== 判断逻辑 ===\n";
    
    $enterpriseName = $stockOutArray['enterprise_name'] ?? '';
    echo "enterprise_name raw: '$enterpriseName'\n";
    echo "is_numeric(enterpriseName): " . (is_numeric($enterpriseName) ? 'true' : 'false') . "\n";
    echo "empty(enterpriseName): " . (empty($enterpriseName) ? 'true' : 'false') . "\n";
    
    if ((is_numeric($enterpriseName) || empty($enterpriseName)) && !empty($stockOutArray['enterprise_id'])) {
        $enterprise = \app\model\BizEnterprise::find($stockOutArray['enterprise_id']);
        if ($enterprise) {
            $enterpriseName = $enterprise->enterprise_name;
            echo "→ 查询企业名称: $enterpriseName\n";
        }
    } else {
        echo "→ 使用原始名称: $enterpriseName\n";
    }
    
    echo "\n=== 最终返回值 ===\n";
    echo "enterpriseName: '$enterpriseName'\n";
} else {
    echo "出库单不存在!\n";
}
