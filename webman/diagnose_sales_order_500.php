<?php
/**
 * 销售开单500错误深度诊断工具
 *
 * 功能：模拟完整的开单流程，精确定位500错误原因
 * 用法：php diagnose_sales_order_500.php
 */

// ============================================
// 配置和初始化
// ============================================

$startTime = microtime(true);
$logMessages = [];

function logMessage($message, $level = 'INFO') {
    global $logMessages;
    $timestamp = date('Y-m-d H:i:s');
    $formattedMsg = "[{$timestamp}] [{$level}] {$message}";
    $logMessages[] = $formattedMsg;

    // 命令行颜色输出
    $colors = [
        'INFO' => "\033[0m",
        'SUCCESS' => "\033[32m",
        'WARNING' => "\033[33m",
        'ERROR' => "\033[31m",
        'HEADER' => "\033[36m",
        'DEBUG' => "\033[35m",
    ];

    echo ($colors[$level] ?? '') . $formattedMsg . "\033[0m" . PHP_EOL;
}

function getDatabaseConfig() {
    $configFile = __DIR__ . '/config/database.php';
    if (!file_exists($configFile)) {
        logMessage("❌ 找不到配置文件: {$configFile}", 'ERROR');
        exit(1);
    }
    $config = require $configFile;
    return $config['connections']['mysql'];
}

function createPDOConnection($config) {
    $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        logMessage("❌ 数据库连接失败: " . $e->getMessage(), 'ERROR');
        exit(1);
    }
}

// ============================================
// 诊断测试函数
// ============================================

/**
 * 测试1: 检查基础表是否存在
 */
function testTableExistence($pdo) {
    logMessage(PHP_EOL . str_repeat('=', 60), 'HEADER');
    logMessage("测试1: 检查所有相关表是否存在", 'HEADER');
    logMessage(str_repeat('=', 60), 'HEADER');

    $tables = [
        'biz_sales_order',
        'biz_order_item',
        'biz_customer_package',
        'biz_package_item',
        'biz_customer',
        'biz_customer_archive'
    ];

    $allExists = true;
    foreach ($tables as $table) {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$table]);
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            logMessage("✅ 表 {$table} 存在", 'SUCCESS');
        } else {
            logMessage("❌ 表 {$table} 不存在！", 'ERROR');
            $allExists = false;
        }
    }

    return $allExists;
}

/**
 * 测试2: 检查字段完整性（详细版）
 */
function testFieldCompleteness($pdo) {
    logMessage(PHP_EOL . str_repeat('=', 60), 'HEADER');
    logMessage("测试2: 详细检查关键字段", 'HEADER');
    logMessage(str_repeat('=', 60), 'HEADER);

    // 定义关键业务字段
    $criticalFields = [
        'biz_sales_order' => ['order_id', 'order_no', 'customer_id', 'enterprise_id', 'store_id', 'deal_amount', 'paid_amount', 'owed_amount', 'package_name'],
        'biz_order_item' => ['item_id', 'order_id', 'product_name', 'quantity', 'deal_amount', 'paid_amount', 'unit_price', 'owed_amount'],
        'biz_customer_package' => ['package_id', 'package_no', 'customer_id', 'order_id', 'total_amount', 'paid_amount', 'owed_amount'],
        'biz_package_item' => ['package_item_id', 'package_id', 'product_name', 'unit_price', 'deal_price', 'paid_amount', 'owed_amount']
    ];

    $allComplete = true;
    foreach ($criticalFields as $table => $fields) {
        logMessage(PHP_EOL . "📋 检查表: {$table}", 'INFO');

        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$table]);
        $existingColumns = array_map('strtolower', $stmt->fetchAll(PDO::FETCH_COLUMN));

        foreach ($fields as $field) {
            if (in_array(strtolower($field), $existingColumns)) {
                logMessage("  ✅ {$field}", 'INFO');
            } else {
                logMessage("  ❌ 缺失: {$field}！", 'ERROR');
                $allComplete = false;
            }
        }
    }

    return $allComplete;
}

/**
 * 测试3: 测试插入订单（模拟实际操作）
 */
function testInsertOrder($pdo) {
    logMessage(PHP_EOL . str_repeat('=', 60), 'HEADER');
    logMessage("测试3: 模拟订单插入（关键测试）", 'HEADER');
    logMessage(str_repeat('=', 60), 'HEADER');

    try {
        // 开始事务
        $pdo->beginTransaction();
        logMessage("🔄 开始事务...", 'INFO');

        // 步骤1: 获取一个有效的客户ID用于测试
        logMessage(PHP_EOL . "步骤1: 获取测试客户...", 'INFO');
        $stmt = $pdo->query("SELECT customer_id, customer_name, enterprise_id, store_id FROM biz_customer LIMIT 1");
        $testCustomer = $stmt->fetch();

        if (!$testCustomer) {
            logMessage("⚠️ 数据库中没有客户，使用虚拟数据进行测试", 'WARNING');
            $customerId = 999999;
            $customerName = '测试客户';
            $enterpriseId = 1;
            $storeId = 1;
        } else {
            $customerId = $testCustomer['customer_id'];
            $customerName = $testCustomer['customer_name'];
            $enterpriseId = $testCustomer['enterprise_id'] ?? 1;
            $storeId = $testCustomer['store_id'] ?? 1;
            logMessage("✅ 使用客户: ID={$customerId}, 名称={$customerName}", 'SUCCESS');
        }

        // 步骤2: 生成订单号
        logMessage(PHP_EOL . "步骤2: 生成订单号...", 'INFO");
        $date = date('Ymd');
        $stmt = $pdo->prepare("SELECT order_no FROM biz_sales_order WHERE order_no LIKE ? ORDER BY order_id DESC LIMIT 1");
        $stmt->execute(['SO' . $date . '%']);
        $lastOrder = $stmt->fetch();

        if ($lastOrder) {
            $seq = intval(substr($lastOrder['order_no'], -4)) + 1;
        } else {
            $seq = 1;
        }

        $orderNo = 'SO' . $date . str_pad($seq, 4, '0', STR_PAD_LEFT);
        logMessage("✅ 订单号: {$orderNo}", 'SUCCESS');

        // 步骤3: 插入主订单
        logMessage(PHP_EOL . "步骤3: 插入主订单...", 'INFO');

        $orderData = [
            'order_no' => $orderNo,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'enterprise_id' => $enterpriseId,
            'enterprise_name' => '测试企业',
            'store_id' => $storeId,
            'store_name' => '测试门店',
            'deal_amount' => 960.00,
            'paid_amount' => 800.00,
            'owed_amount' => 160.00,
            'order_status' => '1',
            'package_name' => '测试套餐-诊断',
            'remark' => '这是诊断测试订单',
            'create_by' => 'diagnostic_tool',
            'creator_user_id' => 0,
            'creator_user_name' => '诊断工具',
            'create_time' => date('Y-m-d H:i:s'),
            'enterprise_audit_status' => '0',
            'finance_audit_status' => '0'
        ];

        $columns = implode(', ', array_keys($orderData));
        $placeholders = ':' . implode(', :', array_keys($orderData));
        $sql = "INSERT INTO biz_sales_order ({$columns}) VALUES ({$placeholders})";

        logMessage("DEBUG SQL: {$sql}", 'DEBUG');
        logMessage("DEBUG DATA: " . json_encode($orderData, JSON_UNESCAPED_UNICODE), 'DEBUG');

        $stmt = $pdo->prepare($sql);
        $stmt->execute($orderData);
        $orderId = $pdo->lastInsertId();

        logMessage("✅ 主订单插入成功! ID={$orderId}", 'SUCCESS');

        // 步骤4: 插入订单明细
        logMessage(PHP_EOL . "步骤4: 插入订单明细...", 'INFO');

        $items = [
            [
                'order_id' => $orderId,
                'product_name' => '品项名称',
                'quantity' => 10,
                'deal_amount' => 380.00,
                'paid_amount' => 300.00,
                'unit_price' => 38.00,
                'owed_amount' => 80.00,
                'remark' => NULL,
                'create_time' => date('Y-m-d H:i:s')
            ],
            [
                'order_id' => $orderId,
                'product_name' => '尽快还款计划',
                'quantity' => 1,
                'deal_amount' => 580.00,
                'paid_amount' => 500.00,
                'unit_price' => 580.00,
                'owed_amount' => 80.00,
                'remark' => NULL,
                'create_time' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($items as $index => $item) {
            $columns = implode(', ', array_keys($item));
            $placeholders = ':' . implode(', :', array_keys($item));
            $sql = "INSERT INTO biz_order_item ({$columns}) VALUES ({$placeholders})";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($item);
            $itemId = $pdo->lastInsertId();

            logMessage("✅ 明细 " . ($index + 1) . " 插入成功! ID={$itemId}", 'SUCCESS');
        }

        // 步骤5: 生成套餐
        logMessage(PHP_EOL . "步骤5: 生成客户套餐...", 'INFO");

        $packageNo = 'PK' . $date . str_pad($seq, 4, '0', STR_PAD_LEFT);

        $packageData = [
            'package_no' => $packageNo,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'order_id' => $orderId,
            'order_no' => $orderNo,
            'enterprise_id' => $enterpriseId,
            'store_id' => $storeId,
            'package_name' => '测试套餐-诊断',
            'total_amount' => 960.00,
            'paid_amount' => 800.00,
            'owed_amount' => 160.00,
            'status' => '1',
            'remark' => '诊断测试',
            'create_by' => 'diagnostic_tool',
            'create_time' => date('Y-m-d H:i:s')
        ];

        $columns = implode(', ', array_keys($packageData));
        $placeholders = ':' . implode(', :', array_keys($packageData));
        $sql = "INSERT INTO biz_customer_package ({$columns}) VALUES ({$placeholders})";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($packageData);
        $packageId = $pdo->lastInsertId();

        logMessage("✅ 客户套餐插入成功! ID={$packageId}", 'SUCCESS');

        // 步骤6: 生成套餐明细
        logMessage(PHP_EOL . "步骤6: 生成套餐明细...", 'INFO');

        foreach ($items as $index => $item) {
            $pkgItem = [
                'package_id' => $packageId,
                'product_name' => $item['product_name'],
                'unit_price' => $item['unit_price'],
                'plan_price' => 0.00,
                'deal_price' => $item['deal_amount'],
                'paid_amount' => $item['paid_amount'],
                'owed_amount' => $item['owed_amount'],
                'total_quantity' => $item['quantity'],
                'used_quantity' => 0,
                'remaining_quantity' => $item['quantity'],
                'remark' => NULL
            ];

            $columns = implode(', ', array_keys($pkgItem));
            $placeholders = ':' . implode(', :', array_keys($pkgItem));
            $sql = "INSERT INTO biz_package_item ({$columns}) VALUES ({$placeholders})";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($pkgItem);
            $pkgItemId = $pdo->lastInsertId();

            logMessage("✅ 套餐明细 " . ($index + 1) . " 插入成功! ID={$pkgItemId}", 'SUCCESS');
        }

        // 回滚事务（不保存测试数据）
        logMessage(PHP_EOL . "🔄 回滚事务（清理测试数据）...", 'INFO');
        $pdo->rollBack();
        logMessage("✅ 事务已回滚，测试完成", 'SUCCESS');

        logMessage(PHP_EOL . str_repeat('-', 60), 'SUCCESS');
        logMessage("🎉 所有步骤执行成功！数据库操作正常。", 'SUCCESS');
        logMessage("   如果仍然出现500错误，问题可能在于：", 'INFO');
        logMessage("   1. 前端传递的数据格式不正确", 'INFO');
        logMessage("   2. 后端验证逻辑失败", 'INFO');
        logMessage("   3. 其他依赖服务或中间件问题", 'INFO');
        logMessage(str_repeat('-', 60), 'SUCCESS');

        return true;

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        logMessage(PHP_EOL . str_repeat('-', 60), 'ERROR');
        logMessage("❌ 数据库操作失败！这就是500错误的原因：", 'ERROR');
        logMessage("   错误代码: " . $e->getCode(), 'ERROR');
        logMessage("   错误信息: " . $e->getMessage(), 'ERROR');
        logMessage("   错误文件: " . $e->getFile() . ":" . $e->getLine(), 'ERROR');
        logMessage(str_repeat('-', 60), 'ERROR');

        logMessage(PHP_EOL, 'INFO');
        logMessage("🔍 错误分析：", 'INFO');
        logMessage("   - 如果是 'Unknown column' → 字段确实缺失（需添加）", 'INFO');
        logMessage("   - 如果是 'Duplicate entry' → 主键/唯一键冲突", 'INFO');
        logMessage("   - 如果是 'Cannot be null' → 必填字段为空", 'INFO');
        logMessage("   - 如果是 'Data too long' → 数据超出字段长度限制", 'INFO');
        logMessage("   - 如果是 'Foreign key constraint' → 外键约束违反", 'INFO');

        return false;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        logMessage(PHP_EOL . "❌ 发生未预期的错误: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * 测试4: 检查外键约束
 */
function testForeignKeyConstraints($pdo) {
    logMessage(PHP_EOL . str_repeat('=', 60), 'HEADER');
    logMessage("测试4: 检查外键约束", 'HEADER');
    logMessage(str_repeat('=', 60), 'HEADER');

    try {
        $sql = "
            SELECT
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE
                TABLE_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME IS NOT NULL
                AND TABLE_NAME IN ('biz_sales_order', 'biz_order_item', 'biz_customer_package', 'biz_package_item')
        ";

        $stmt = $pdo->query($sql);
        $foreignKeys = $stmt->fetchAll();

        if (empty($foreignKeys)) {
            logMessage("ℹ️ 未找到外键约束", 'INFO');
            return true;
        }

        foreach ($foreignKeys as $fk) {
            logMessage("🔗 {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}", 'INFO');
        }

        return true;
    } catch (Exception $e) {
        logMessage("⚠️ 检查外键时出错: " . $e->getMessage(), 'WARNING');
        return true; // 不算致命错误
    }
}

/**
 * 测试5: 检查最近的真实错误日志
 */
function checkRecentErrors() {
    logMessage(PHP_EOL . str_repeat('=', 60), 'HEADER');
    logMessage("测试5: 检查应用错误日志", 'HEADER');
    logMessage(str_repeat('=', 60), 'HEADER);

    $logPaths = [
        __DIR__ . '/runtime/logs/',
        __DIR__ . '/../logs/',
        __DIR__ . '/logs/'
    ];

    $foundLogs = false;
    foreach ($logPaths as $logPath) {
        if (is_dir($logPath)) {
            $files = glob($logPath . '*.log');
            if (!empty($files)) {
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });

                $latestLog = $files[0];
                $logContent = file_get_contents($latestLog);

                if (strlen($logContent) > 2000) {
                    $logContent = substr($logContent, -2000);
                }

                $lines = explode("\n", $logContent);
                $errorLines = array_filter($lines, function($line) {
                    return stripos($line, 'error') !== false ||
                           stripos($line, 'exception') !== false ||
                           stripos($line, '500') !== false;
                });

                if (!empty($errorLines)) {
                    logMessage("📄 发现最近的错误日志: " . basename($latestLog), 'WARNING');
                    foreach (array_slice($errorLines, -5) as $line) {
                        logMessage("   " . trim($line), 'ERROR');
                    }
                    $foundLogs = true;
                }
            }
        }
    }

    if (!$foundLogs) {
        logMessage("ℹ️ 未发现明显的错误日志文件", 'INFO');
        logMessage("   建议：查看 webman/runtime/logs/ 目录下的日志", 'INFO');
    }

    return true;
}

// ============================================
// 主程序
// ============================================

echo PHP_EOL . PHP_EOL;

logMessage("╔══════════════════════════════════════════════════╗", 'HEADER');
logMessage("║     销售开单500错误深度诊断工具 v2.0           ║", 'HEADER');
logMessage("╚══════════════════════════════════════════════════╝", 'HEADER');
logMessage("", 'INFO');
logMessage("开始时间: " . date('Y-m-d H:i:s'), 'INFO');

// 连接数据库
$config = getDatabaseConfig();
$pdo = createPDOConnection($config);
logMessage("✅ 已连接到数据库: {$config['host']}:{$config['port']}/{$config['database']}", 'SUCCESS');

// 执行所有测试
$results = [];

$results[] = ['表存在性检查', testTableExistence($pdo)];
$results[] = ['字段完整性检查', testFieldCompleteness($pdo)];
$results[] = ['外键约束检查', testForeignKeyConstraints($pdo)];
$results[] = ['模拟订单插入（核心测试）', testInsertOrder($pdo)];
$results[] = ['错误日志检查', checkRecentErrors()];

// 输出总结
$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 3);

logMessage(PHP_EOL . PHP_EOL . str_repeat('═', 60), 'HEADER');
logMessage("  诊断报告总结", 'HEADER');
logMessage(str_repeat('═', 60), 'HEADER');

foreach ($results as $result) {
    $status = $result[1] ? '✅ 通过' : '❌ 失败';
    $color = $result[1] ? 'SUCCESS' : 'ERROR';
    logMessage("  {$status} - {$result[0]}", $color);
}

logMessage(str_repeat('═', 60), 'HEADER');
logMessage("  总耗时: {$executionTime} 秒", 'HEADER');
logMessage(str_repeat('═', 60) . PHP_EOL . PHP_EOL, 'HEADER');

// 保存日志
$logFile = __DIR__ . '/logs/diagnose_' . date('Ymd_His') . '.log';
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}
file_put_contents($logFile, implode(PHP_EOL, $logMessages) . PHP_EOL);
logMessage("📝 完整诊断日志已保存至:", 'INFO');
logMessage("   {$logFile}", 'INFO');
logMessage("", 'INFO');

$allPassed = array_column($results, 1);
exit(in_array(false, $allPassed, true) ? 1 : 0);
