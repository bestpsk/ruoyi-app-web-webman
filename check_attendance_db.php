<?php

echo "=== 考勤打卡数据库诊断 ===\n\n";

$configFile = __DIR__ . '/config/database.php';
if (!file_exists($configFile)) {
    echo "✗ 配置文件不存在: $configFile\n";
    exit(1);
}

$config = include $configFile;
$mysql = $config['connections']['mysql'] ?? null;

if (!$mysql) {
    echo "✗ 无法读取MySQL配置\n";
    exit(1);
}

echo "数据库配置:\n";
echo "  主机: " . ($mysql['host'] ?? 'localhost') . "\n";
echo "  端口: " . ($mysql['port'] ?? 3306) . "\n";
echo "  数据库: " . ($mysql['database'] ?? '未知') . "\n";
echo "  用户名: " . ($mysql['username'] ?? 'root') . "\n\n";

try {
    $dsn = "mysql:host={$mysql['host']};port={$mysql['port']};dbname={$mysql['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $mysql['username'], $mysql['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✓ 数据库连接成功\n\n";

    echo "检查表结构...\n";

    $tables = $pdo->query("SHOW TABLES LIKE 'biz_attendance%'")->fetchAll(PDO::FETCH_COLUMN);
    echo "存在的考勤表:\n";
    if (empty($tables)) {
        echo "  (无)\n";
    } else {
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    }
    echo "\n";

    $hasClockTable = in_array('biz_attendance_clock', $tables);
    if ($hasClockTable) {
        echo "✓ biz_attendance_clock 表存在\n";
        $columns = $pdo->query("SHOW COLUMNS FROM biz_attendance_clock")->fetchAll(PDO::FETCH_ASSOC);
        echo "  字段列表:\n";
        foreach ($columns as $col) {
            echo "    - {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "✗ biz_attendance_clock 表不存在！\n";
    }
    echo "\n";

    if (in_array('biz_attendance_record', $tables)) {
        $mainColumns = $pdo->query("SHOW COLUMNS FROM biz_attendance_record")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($mainColumns, 'Field');

        echo "检查 biz_attendance_record 表字段...\n";
        $requiredFields = ['clock_count', 'first_clock_time', 'last_clock_time'];
        $allFieldsExist = true;
        foreach ($requiredFields as $field) {
            if (in_array($field, $columnNames)) {
                echo "  ✓ $field 存在\n";
            } else {
                echo "  ✗ $field 不存在！\n";
                $allFieldsExist = false;
            }
        }
        echo "\n";
    } else {
        echo "✗ biz_attendance_record 表不存在！\n";
        $allFieldsExist = false;
    }

    echo "=== 诊断完成 ===\n";

    if (!$hasClockTable || !$allFieldsExist) {
        echo "\n⚠️  需要执行数据库迁移脚本！\n";
        echo "\n执行方法：\n";
        echo "1. 使用MySQL命令行:\n";
        echo "   mysql -u root -p {$mysql['database']} < webman/sql/biz_attendance_clock.sql\n\n";
        echo "2. 或使用Navicat/MySQL Workbench:\n";
        echo "   打开并执行: d:\\fuchenpro\\webman\\sql\\biz_attendance_clock.sql\n";
    } else {
        echo "\n✓ 数据库结构完整，打卡功能应该可以正常工作\n";
    }

} catch (PDOException $e) {
    echo "✗ 数据库连接失败: " . $e->getMessage() . "\n";
    echo "\n请检查:\n";
    echo "  1. MySQL服务是否启动\n";
    echo "  2. 数据库配置是否正确 (config/database.php)\n";
    echo "  3. 数据库是否存在\n";
} catch (Exception $e) {
    echo "✗ 错误: " . $e->getMessage() . "\n";
}
