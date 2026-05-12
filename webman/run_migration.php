<?php

echo "=== 执行考勤打卡数据库迁移 ===\n\n";

$configFile = __DIR__ . '/config/database.php';
$config = include $configFile;
$mysql = $config['connections']['mysql'];

try {
    $dsn = "mysql:host={$mysql['host']};port={$mysql['port']};dbname={$mysql['database']};charset=utf8mb4";
    $pdo = new PDO($dsn, $mysql['username'], $mysql['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "✓ 数据库连接成功\n\n";

    $sqlFile = __DIR__ . '/sql/biz_attendance_clock.sql';
    if (!file_exists($sqlFile)) {
        echo "✗ SQL文件不存在: $sqlFile\n";
        exit(1);
    }

    echo "读取SQL文件...\n";
    $sql = file_get_contents($sqlFile);

    echo "执行迁移脚本...\n\n";

    $pdo->exec($sql);

    echo "✓ SQL脚本执行成功\n\n";

    echo "验证表结构...\n";

    $tables = $pdo->query("SHOW TABLES LIKE 'biz_attendance%'")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('biz_attendance_clock', $tables)) {
        echo "  ✓ biz_attendance_clock 表已创建\n";
    } else {
        echo "  ✗ biz_attendance_clock 表未创建\n";
    }

    $columns = $pdo->query("SHOW COLUMNS FROM biz_attendance_record")->fetchAll(PDO::FETCH_COLUMN);
    $requiredFields = ['clock_count', 'first_clock_time', 'last_clock_time'];
    $allFieldsExist = true;
    foreach ($requiredFields as $field) {
        if (in_array($field, $columns)) {
            echo "  ✓ $field 字段已添加\n";
        } else {
            echo "  ✗ $field 字段未添加\n";
            $allFieldsExist = false;
        }
    }

    if ($allFieldsExist && in_array('biz_attendance_clock', $tables)) {
        echo "\n✓ 迁移成功！打卡功能现在应该可以正常工作了。\n";
    } else {
        echo "\n⚠️  部分迁移失败，请检查错误信息\n";
    }

} catch (PDOException $e) {
    echo "✗ 迁移失败: " . $e->getMessage() . "\n";
    echo "\n错误详情:\n";
    echo $e->getTraceAsString() . "\n";
}
