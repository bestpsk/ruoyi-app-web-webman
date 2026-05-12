<?php

$sqlFile = __DIR__ . '/fix_employee_config_post.sql';

$config = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE') ?: 'fuchenpro',
    'username' => getenv('DB_USERNAME') ?: 'fuchenpro',
    'password' => getenv('DB_PASSWORD') ?: '123456',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];

echo "=== 修复员工配置岗位信息 ===\n";
echo "数据库: {$config['database']}\n\n";

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "数据库连接成功\n\n";
    
    $sql = file_get_contents($sqlFile);
    $pdo->exec($sql);
    
    $rowCount = $pdo->query("SELECT COUNT(*) FROM biz_employee_config WHERE post_id IS NOT NULL")->fetchColumn();
    
    echo "✓ 岗位信息更新成功\n";
    echo "已填充岗位信息的员工数: {$rowCount}\n";
    
} catch (PDOException $e) {
    die("错误: " . $e->getMessage() . "\n");
}
