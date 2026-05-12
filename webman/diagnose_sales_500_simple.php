<?php
/**
 * 销售开单500错误深度诊断工具 - 简化版
 */

$startTime = microtime(true);

function logMsg($msg, $type = 'INFO') {
    $time = date('H:i:s');
    echo "[{$time}] {$msg}" . PHP_EOL;
}

// 连接数据库
$config = require __DIR__ . '/config/database.php';
$dbConfig = $config['connections']['mysql'];

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    logMsg("✅ 数据库连接成功");
} catch (PDOException $e) {
    logMsg("❌ 数据库连接失败: " . $e->getMessage());
    exit(1);
}

logMsg("");
logMsg("=" . str_repeat("=", 58));
logMsg("  销售开单500错误深度诊断工具");
logMsg("=" . str_repeat("=", 58));

// 测试1: 检查表是否存在
logMsg("");
logMsg("[测试1] 检查表存在性...");

$tables = array('biz_sales_order', 'biz_order_item', 'biz_customer_package', 'biz_package_item', 'biz_customer');
foreach ($tables as $table) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}'");
    $exists = $stmt->fetchColumn() > 0;
    logMsg("  " . ($exists ? "✅" : "❌") . " {$table}");
}

// 测试2: 模拟订单插入（核心测试）
logMsg("");
logMsg("[测试2] 模拟完整订单插入流程...");
logMsg("-" . str_repeat("-", 58));

try {
    $pdo->beginTransaction();
    logMsg("🔄 开始事务");

    // 获取测试客户
    $stmt = $pdo->query("SELECT customer_id, customer_name FROM biz_customer LIMIT 1");
    $customer = $stmt->fetch();

    if (!$customer) {
        logMsg("⚠️ 无测试客户，使用虚拟数据");
        $customerId = 999999;
        $customerName = 'TEST';
        $enterpriseId = 1;
        $storeId = 1;
    } else {
        $customerId = $customer['customer_id'];
        $customerName = $customer['customer_name'];

        $stmt2 = $pdo->query("SELECT enterprise_id, store_id FROM biz_customer WHERE customer_id = {$customerId}");
        $custData = $stmt2->fetch();
        $enterpriseId = $custData['enterprise_id'] ?? 1;
        $storeId = $custData['store_id'] ?? 1;

        logMsg("✅ 使用客户: ID={$customerId}, 名称={$customerName}");
    }

    // 生成订单号
    $date = date('Ymd');
    $stmt3 = $pdo->query("SELECT order_no FROM biz_sales_order WHERE order_no LIKE 'SO{$date}%' ORDER BY order_id DESC LIMIT 1");
    $lastOrder = $stmt3->fetch();
    $seq = $lastOrder ? intval(substr($lastOrder['order_no'], -4)) + 1 : 1;
    $orderNo = "SO{$date}" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    logMsg("✅ 订单号: {$orderNo}");

    // 插入主订单
    logMsg("");
    logMsg("步骤1: 插入主订单...");

    $sql1 = "INSERT INTO biz_sales_order (
        order_no, customer_id, customer_name,
        enterprise_id, enterprise_name, store_id, store_name,
        deal_amount, paid_amount, owed_amount,
        order_status, package_name, remark,
        create_by, creator_user_id, creator_user_name,
        create_time, enterprise_audit_status, finance_audit_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), '0', '0')";

    $data1 = [
        $orderNo, $customerId, $customerName,
        $enterpriseId, 'Test Enterprise', $storeId, 'Test Store',
        960.00, 800.00, 160.00,
        '1', 'Diagnostic Test Order', 'Test remark',
        'diagnostic_tool', 0, 'Diagnostic Tool'
    ];

    $pdo->prepare($sql1)->execute($data1);
    $orderId = $pdo->lastInsertId();
    logMsg("✅ 主订单插入成功! ID={$orderId}");

    // 插入订单明细
    logMsg("");
    logMsg("步骤2: 插入订单明细...");

    $sql2 = "INSERT INTO biz_order_item (
        order_id, product_name, quantity,
        deal_amount, paid_amount, unit_price, owed_amount,
        create_time
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $items = array(
        array($orderId, '品项名称', 10, 380.00, 300.00, 38.00, 80.00),
        array($orderId, '尽快还款计划', 1, 580.00, 500.00, 580.00, 80.00)
    );

    foreach ($items as $idx => $item) {
        $pdo->prepare($sql2)->execute($item);
        $itemId = $pdo->lastInsertId();
        logMsg("✅ 明细 " . ($idx+1) . " 插入成功! ID={$itemId}");
    }

    // 生成套餐
    logMsg("");
    logMsg("步骤3: 生成客户套餐...");

    $packageNo = "PK{$date}" . str_pad($seq, 4, '0', STR_PAD_LEFT);

    $sql3 = "INSERT INTO biz_customer_package (
        package_no, customer_id, customer_name,
        order_id, order_no, enterprise_id, store_id,
        package_name, total_amount, paid_amount, owed_amount,
        status, remark, create_by, create_time
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $data3 = [
        $packageNo, $customerId, $customerName,
        $orderId, $orderNo, $enterpriseId, $storeId,
        'Diagnostic Test Package', 960.00, 800.00, 160.00,
        '1', 'Test', 'diagnostic_tool'
    ];

    $pdo->prepare($sql3)->execute($data3);
    $packageId = $pdo->lastInsertId();
    logMsg("✅ 客户套餐插入成功! ID={$packageId}");

    // 生成套餐明细
    logMsg("");
    logMsg("步骤4: 生成套餐明细...");

    $sql4 = "INSERT INTO biz_package_item (
        package_id, product_name,
        unit_price, plan_price, deal_price,
        paid_amount, owed_amount,
        total_quantity, used_quantity, remaining_quantity,
        remark
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $pkgItems = array(
        array($packageId, '品项名称', 38.00, 0.00, 380.00, 300.00, 80.00, 10, 0, 10, NULL),
        array($packageId, '尽快还款计划', 580.00, 0.00, 580.00, 500.00, 80.00, 1, 0, 1, NULL)
    );

    foreach ($pkgItems as $idx => $pkgItem) {
        $pdo->prepare($sql4)->execute($pkgItem);
        $pkgItemId = $pdo->lastInsertId();
        logMsg("✅ 套餐明细 " . ($idx+1) . " 插入成功! ID={$pkgItemId}");
    }

    // 回滚事务
    logMsg("");
    logMsg("🔄 回滚事务（清理测试数据）...");
    $pdo->rollBack();
    logMsg("✅ 事务已回滚");

    logMsg("");
    logMsg("-" . str_repeat("-", 58));
    logMsg("🎉 所有数据库操作成功完成！");
    logMsg("");
    logMsg("结论：");
    logMsg("  ✅ 数据库结构正常");
    logMsg("  ✅ 字段完整性正常");
    logMsg("  ✅ INSERT操作正常");
    logMsg("");
    logMsg("如果仍然出现500错误，可能原因：");
    logMsg("  1️⃣ 前端传递的数据格式或字段名不正确");
    logMsg("  2️⃣ 后端验证逻辑失败（如必填字段为空）");
    logMsg("  3️⃣ Webman框架异常处理问题");
    logMsg("  4️⃣ 中间件拦截或权限问题");
    logMsg("-" . str_repeat("-", 58));

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    logMsg("");
    logMsg("-" . str_repeat("-", 58));
    logMsg("❌ 发现错误！这就是500错误的真正原因：");
    logMsg("-" . str_repeat("-", 58));
    logMsg("");
    logMsg("错误代码: " . $e->getCode());
    logMsg("错误信息: " . $e->getMessage());
    logMsg("");

    if (strpos($e->getMessage(), 'Unknown column') !== false) {
        logMsg("🔍 问题类型：缺少字段");
        logMsg("解决方案：执行 ALTER TABLE 添加缺失的字段");
    } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        logMsg("🔍 问题类型：唯一键冲突");
        logMsg("解决方案：检查订单号生成逻辑");
    } elseif (strpos($e->getMessage(), 'Cannot be null') !== false) {
        logMsg("🔍 问题类型：必填字段为空");
        logMsg("解决方案：确保前端提交了所有必要数据");
    } elseif (strpos($e->getMessage(), 'Data too long') !== false) {
        logMsg("🔍 问题类型：数据超出长度限制");
        logMsg("解决方案：检查输入数据的长度");
    } elseif (strpos($e->getMessage(), 'Foreign key') !== false) {
        logMsg("🔍 问题类型：外键约束违反");
        logMsg("解决方案：确保关联的数据存在（如customer_id）");
    }
}

// 输出统计信息
$endTime = microtime(true);
$duration = round($endTime - $startTime, 3);
logMsg("");
logMsg("总耗时: {$duration} 秒");
logMsg("");
