<?php
/**
 * 销售开单500错误修复工具
 *
 * 功能：自动检测并修复销售开单相关的数据库表结构问题
 * 作者：AI Assistant
 * 日期：2026-05-12
 * 用法：php fix_sales_order_db.php [--dry-run] [--backup]
 */

// ============================================
// 配置区域
// ============================================

$startTime = microtime(true);
$logMessages = [];

// 是否仅检测不执行（dry run模式）
$dryRun = in_array('--dry-run', $argv) || in_array('-d', $argv);

// 是否在执行前备份表数据
$doBackup = in_array('--backup', $argv) || in_array('-b', $argv);

// ============================================
// 数据库连接配置（从项目配置文件读取）
// ============================================

function getDatabaseConfig() {
    $configFile = __DIR__ . '/config/database.php';

    if (!file_exists($configFile)) {
        logMessage("❌ 错误：找不到数据库配置文件: {$configFile}");
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
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        logMessage("❌ 数据库连接失败: " . $e->getMessage());
        exit(1);
    }
}

// ============================================
// 日志函数
// ============================================

function logMessage($message, $level = 'INFO') {
    global $logMessages;
    $timestamp = date('Y-m-d H:i:s');
    $formattedMsg = "[{$timestamp}] [{$level}] {$message}";
    $logMessages[] = $formattedMsg;

    // 根据级别输出不同颜色（命令行）
    $colors = [
        'INFO' => "\033[0m",      // 默认颜色
        'SUCCESS' => "\033[32m",   // 绿色
        'WARNING' => "\033[33m",   // 黄色
        'ERROR' => "\033[31m",     // 红色
        'HEADER' => "\033[36m",    // 青色
    ];

    echo $colors[$level] ?? '' . $formattedMsg . "\033[0m" . PHP_EOL;
}

// ============================================
// 字段定义（根据ORM模型和业务逻辑）
// ============================================

function getFieldDefinitions() {
    return [
        // 客户套餐表 - 根据 BizCustomerPackage.php 和 BizSalesOrderService.php:161-211
        'biz_customer_package' => [
            'fields' => [
                'package_id' => "bigint(20) NOT NULL AUTO_INCREMENT COMMENT '套餐ID'",
                'package_no' => "varchar(30) NOT NULL COMMENT '套餐编号'",
                'customer_id' => "bigint(20) NOT NULL COMMENT '客户ID'",
                'customer_name' => "varchar(50) DEFAULT NULL COMMENT '客户姓名'",
                'order_id' => "bigint(20) DEFAULT NULL COMMENT '来源订单ID'",
                'order_no' => "varchar(30) DEFAULT NULL COMMENT '来源订单编号'",
                'enterprise_id' => "bigint(20) DEFAULT NULL COMMENT '企业ID'",
                'enterprise_name' => "varchar(100) DEFAULT NULL COMMENT '企业名称'",
                'store_id' => "bigint(20) DEFAULT NULL COMMENT '门店ID'",
                'store_name' => "varchar(100) DEFAULT NULL COMMENT '门店名称'",
                'package_name' => "varchar(100) DEFAULT NULL COMMENT '套餐名称'",
                'total_amount' => "decimal(12,2) DEFAULT '0.00' COMMENT '套餐总金额'",
                'paid_amount' => "decimal(12,2) DEFAULT '0.00' COMMENT '已付金额'",
                'owed_amount' => "decimal(12,2) DEFAULT '0.00' COMMENT '欠款金额'",
                'status' => "char(1) NOT NULL DEFAULT '0' COMMENT '状态(0有效1已用完2已过期3已退款)'",
                'expire_date' => "date DEFAULT NULL COMMENT '过期日期'",
                'remark' => "text COMMENT '备注'",
                'create_by' => "varchar(64) DEFAULT '' COMMENT '创建者'",
                'create_time' => "datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'",
                'update_by' => "varchar(64) DEFAULT '' COMMENT '更新者'",
                'update_time' => "datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'",
            ],
            // 字段添加顺序（AFTER哪个字段）
            'field_order' => [
                'paid_amount' => 'total_amount',
                'owed_amount' => 'paid_amount',
                'enterprise_name' => 'enterprise_id',
            ]
        ],

        // 订单明细表 - 根据 BizOrderItem.php 和 BizSalesOrderService.php:56-77
        'biz_order_item' => [
            'fields' => [
                'item_id' => "bigint(20) NOT NULL AUTO_INCREMENT COMMENT '明细ID'",
                'order_id' => "bigint(20) NOT NULL COMMENT '订单ID'",
                'product_name' => "varchar(100) NOT NULL COMMENT '品项名称'",
                'quantity' => "int(11) NOT NULL DEFAULT '1' COMMENT '次数'",
                'deal_amount' => "decimal(12,2) DEFAULT '0.00' COMMENT '成交金额'",
                'paid_amount' => "decimal(12,2) DEFAULT '0.00' COMMENT '实付金额'",
                'unit_price' => "decimal(10,2) DEFAULT '0.00' COMMENT '单次价'",
                'owed_amount' => "decimal(10,2) DEFAULT '0.00' COMMENT '欠款金额'",
                'is_our_operation' => "tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否我们操作(0否1是)'",
                'customer_feedback' => "varchar(500) DEFAULT NULL COMMENT '顾客反馈'",
                'before_photo' => "varchar(500) DEFAULT NULL COMMENT '操作前对比照'",
                'after_photo' => "varchar(500) DEFAULT NULL '操作后对比照'",
                'remark' => "text COMMENT '备注'",
                'create_time' => "datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间'",
            ],
            'field_order' => [
                'unit_price' => 'deal_amount',
                'owed_amount' => 'unit_price',
            ]
        ],

        // 套餐明细表 - 根据 BizPackageItem.php 和 BizSalesOrderService.php:192-210
        'biz_package_item' => [
            'fields' => [
                'package_item_id' => "bigint(20) NOT NULL AUTO_INCREMENT COMMENT '套餐明细ID'",
                'package_id' => "bigint(20) NOT NULL COMMENT '套餐ID'",
                'product_name' => "varchar(100) NOT NULL COMMENT '品项名称'",
                'unit_price' => "decimal(12,2) DEFAULT '0.00' COMMENT '单次价格'",
                'plan_price' => "decimal(12,2) DEFAULT '0.00' COMMENT '方案总价'",
                'deal_price' => "decimal(12,2) DEFAULT '0.00' COMMENT '成交金额'",
                'paid_amount' => "decimal(12,2) DEFAULT '0.00' COMMENT '实付金额'",
                'owed_amount' => "decimal(12,2) DEFAULT '0.00' COMMENT '欠款金额'",
                'total_quantity' => "int(11) NOT NULL DEFAULT '0' COMMENT '总次数'",
                'used_quantity' => "int(11) NOT NULL DEFAULT '0' COMMENT '已用次数'",
                'remaining_quantity' => "int(11) NOT NULL DEFAULT '0' COMMENT '剩余次数'",
                'remark' => "varchar(500) DEFAULT NULL COMMENT '备注'",
            ],
            'field_order' => [
                'paid_amount' => 'deal_price',
                'owed_amount' => 'paid_amount',
            ]
        ],
    ];
}

// ============================================
// 核心功能函数
// ============================================

/**
 * 获取表中已有的所有字段名
 */
function getExistingColumns($pdo, $tableName) {
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tableName]);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return array_map('strtolower', $columns);
}

/**
 * 备份表数据
 */
function backupTable($pdo, $tableName) {
    $backupTableName = $tableName . '_backup_' . date('YmdHis');

    logMessage("📦 正在备份表 {$tableName} -> {$backupTableName} ...", 'INFO');

    $sql = "CREATE TABLE {$backupTableName} AS SELECT * FROM {$tableName}";
    try {
        $pdo->exec($sql);
        logMessage("✅ 备份成功: {$backupTableName}", 'SUCCESS');
        return true;
    } catch (PDOException $e) {
        logMessage("⚠️ 备份失败（可能已存在）: " . $e->getMessage(), 'WARNING');
        return false;
    }
}

/**
 * 执行ALTER语句添加字段
 */
function addColumn($pdo, $tableName, $fieldName, $fieldDefinition, $afterField = null, $dryRun = false) {
    global $addedColumns;

    $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$fieldName}` {$fieldDefinition}";

    if ($afterField && checkColumnExists($pdo, $tableName, $afterField)) {
        $sql .= " AFTER `{$afterField}`";
    }

    if ($dryRun) {
        logMessage("🔍 [DRY-RUN] 将执行: {$sql}", 'WARNING');
        return true;
    }

    try {
        $pdo->exec($sql);
        $addedColumns++;
        logMessage("✅ 已成功添加字段: {$tableName}.{$fieldName}", 'SUCCESS');
        return true;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            logMessage("⚠️ 字段已存在（重复）: {$tableName}.{$fieldName}", 'WARNING');
            return true; // 不算错误
        }
        logMessage("❌ 添加字段失败: {$tableName}.{$fieldName} - " . $e->getMessage(), 'ERROR');
        return false;
    }
}

/**
 * 检查字段是否存在
 */
function checkColumnExists($pdo, $tableName, $columnName) {
    $existingColumns = getExistingColumns($pdo, $tableName);
    return in_array(strtolower($columnName), $existingColumns);
}

/**
 * 检查表是否存在
 */
function checkTableExists($pdo, $tableName) {
    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tableName]);
    return $stmt->fetchColumn() > 0;
}

/**
 * 主检查和修复函数
 */
function checkAndFixTables($pdo, $dryRun = false, $doBackup = false) {
    global $addedColumns;

    $fieldDefinitions = getFieldDefinitions();
    $totalTables = count($fieldDefinitions);
    $currentTable = 0;
    $addedColumns = 0;

    logMessage(PHP_EOL . str_repeat('=', 60), 'HEADER');
    logMessage("  销售开单数据库修复工具", 'HEADER');
    logMessage("  执行时间: " . date('Y-m-d H:i:s'), 'HEADER');
    logMessage("  模式: " . ($dryRun ? 'DRY-RUN（仅检测，不执行修改）' : '正常执行'), 'HEADER');
    logMessage(str_repeat('=', 60) . PHP_EOL, 'HEADER');

    foreach ($fieldDefinitions as $tableName => $tableDef) {
        $currentTable++;
        logMessage(PHP_EOL . "[{$currentTable}/{$totalTables}] 检查表: {$tableName}", 'INFO');

        // 检查表是否存在
        if (!checkTableExists($pdo, $tableName)) {
            logMessage("❌ 表不存在: {$tableName}", 'ERROR');
            continue;
        }

        // 可选：备份数据
        if ($doBackup && !$dryRun) {
            backupTable($pdo, $tableName);
        }

        // 获取已有字段
        $existingFields = getExistingColumns($pdo, $tableName);
        $requiredFields = array_keys($tableDef['fields']);
        $fieldOrder = $tableDef['field_order'] ?? [];

        logMessage("  ✅ 已有字段数: " . count($existingFields), 'INFO');

        // 对比找出缺失字段
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!in_array(strtolower($field), $existingFields)) {
                $missingFields[] = $field;
            }
        }

        if (empty($missingFields)) {
            logMessage("  ✅ 表结构完整，无需修改", 'SUCCESS');
        } else {
            logMessage("  ❌ 缺失字段 (" . count($missingFields) . "个): " . implode(', ', $missingFields), 'ERROR');

            // 添加缺失字段
            foreach ($missingFields as $field) {
                $definition = $tableDef['fields'][$field];
                $afterField = $fieldOrder[$field] ?? null;

                $success = addColumn($pdo, $tableName, $field, $definition, $afterField, $dryRun);

                if (!$success && !$dryRun) {
                    logMessage("  ⚠️ 跳过字段: {$field}", 'WARNING');
                }
            }
        }
    }

    return $addedColumns;
}

// ============================================
// 验证函数
// ============================================

function verifyFix($pdo) {
    logMessage(PHP_EOL . str_repeat('-', 60), 'INFO');
    logMessage("验证修复结果...", 'INFO');
    logMessage(str_repeat('-', 60), 'INFO');

    $fieldDefinitions = getFieldDefinitions();
    $allGood = true;

    foreach ($fieldDefinitions as $tableName => $tableDef) {
        if (!checkTableExists($pdo, $tableName)) {
            logMessage("❌ 表不存在: {$tableName}", 'ERROR');
            $allGood = false;
            continue;
        }

        $existingFields = getExistingColumns($pdo, $tableName);
        $requiredFields = array_keys($tableDef['fields']);
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!in_array(strtolower($field), $existingFields)) {
                $missingFields[] = $field;
            }
        }

        if (empty($missingFields)) {
            logMessage("✅ {$tableName}: 结构完整 (" . count($existingFields) . " 个字段)", 'SUCCESS');
        } else {
            logMessage("❌ {$tableName}: 仍缺少 " . count($missingFields) . " 个字段 - " . implode(', ', $missingFields), 'ERROR');
            $allGood = false;
        }
    }

    return $allGood;
}

// ============================================
// 主程序入口
// ============================================

echo PHP_EOL;

// 加载数据库配置
$config = getDatabaseConfig();
logMessage("📡 连接数据库: {$config['host']}:{$config['port']}/{$config['database']}", 'INFO');

// 创建连接
$pdo = createPDOConnection($config);
logMessage("✅ 数据库连接成功", 'SUCCESS');

// 执行检查和修复
$addedColumns = checkAndFixTables($pdo, $dryRun, $doBackup);

// 如果不是dry-run模式，执行验证
if (!$dryRun && $addedColumns > 0) {
    $verifySuccess = verifyFix($pdo);
} elseif ($dryRun) {
    logMessage(PHP_EOL . "💡 提示：这是DRY-RUN模式，未做任何实际修改。如需执行修复，请去掉 --dry-run 参数重新运行。", 'WARNING');
    $verifySuccess = true;
} else {
    $verifySuccess = true;
}

// 输出总结
$endTime = microtime(true);
$executionTime = round($endTime - $startTime, 3);

logMessage(PHP_EOL . str_repeat('=', 60), 'HEADER');
if ($dryRun) {
    logMessage("  检测完成！", 'HEADER');
    logMessage("  发现需要修复的问题，请使用正常模式执行修复", 'HEADER');
} elseif ($verifySuccess && $addedColumns >= 0) {
    logMessage("  🎉 修复完成！共添加 {$addedColumns} 个字段", 'HEADER');
    logMessage("  现在可以测试销售开单功能了", 'HEADER');
} else {
    logMessage("  ⚠️ 修复过程中遇到问题，请查看上方日志", 'ERROR');
}
logMessage("  总耗时: {$executionTime} 秒", 'HEADER');
logMessage(str_repeat('=', 60) . PHP_EOL, 'HEADER');

// 输出后续步骤建议
if (!$dryRun && $addedColumns >= 0) {
    logMessage("", 'INFO');
    logMessage("📋 后续步骤：", 'INFO');
    logMessage("  1. 测试销售开单功能 - 选择企业/门店/客户并提交订单", 'INFO');
    logMessage("  2. 验证订单数据保存到数据库", 'INFO');
    logMessage("  3. 检查金额计算是否正确（成交、实付、欠款）", 'INFO');
    logMessage("  4. 如有问题，可查看本脚本输出的详细日志", 'INFO');
    logMessage("", 'INFO');
    logMessage("🔧 验证SQL（可选）：", 'INFO');
    logMessage("  SELECT * FROM biz_sales ORDER BY order_id DESC LIMIT 5;", 'INFO');
    logMessage("  SELECT * FROM biz_customer_package ORDER BY package_id DESC LIMIT 5;", 'INFO');
}

// 保存日志到文件（可选）
$logFile = __DIR__ . '/logs/fix_sales_order_' . date('Ymd_His') . '.log';
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}
file_put_contents($logFile, implode(PHP_EOL, $logMessages) . PHP_EOL);
logMessage("", 'INFO');
logMessage("📝 详细日志已保存至: {$logFile}", 'INFO');

exit($verifySuccess ? 0 : 1);
