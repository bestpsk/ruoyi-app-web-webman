<?php

$sqlFile = __DIR__ . '/biz_schedule_upgrade.sql';

$config = [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE') ?: 'fuchenpro',
    'username' => getenv('DB_USERNAME') ?: 'fuchenpro',
    'password' => getenv('DB_PASSWORD') ?: '123456',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];

echo "=== 执行 SQL 升级脚本 ===\n";
echo "数据库: {$config['database']}\n";
echo "SQL文件: $sqlFile\n\n";

if (!file_exists($sqlFile)) {
    die("错误: SQL 文件不存在\n");
}

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "数据库连接成功\n\n";
    
    $sql = file_get_contents($sqlFile);
    
    $lines = explode("\n", $sql);
    $currentStatement = '';
    $currentComment = '';
    $success = 0;
    $skipped = 0;
    $failed = 0;
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (empty($line)) {
            continue;
        }
        
        if (strpos($line, '--') === 0) {
            $currentComment = trim(substr($line, 2));
            continue;
        }
        
        $currentStatement .= ' ' . $line;
        
        if (substr($line, -1) === ';') {
            $statement = trim($currentStatement);
            $currentStatement = '';
            
            if (empty($statement)) {
                continue;
            }
            
            try {
                $pdo->exec($statement);
                $success++;
                if ($currentComment) {
                    echo "✓ {$currentComment}\n";
                } else {
                    echo "✓ 执行成功\n";
                }
            } catch (PDOException $e) {
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'Duplicate') !== false || 
                    strpos($errorMsg, 'already exists') !== false ||
                    strpos($errorMsg, '1062') !== false) {
                    echo "○ 已存在，跳过: {$currentComment}\n";
                    $skipped++;
                } else {
                    echo "✗ 错误: {$errorMsg}\n";
                    $failed++;
                }
            }
            
            $currentComment = '';
        }
    }
    
    echo "\n=== 执行完成 ===\n";
    echo "成功: {$success} 条\n";
    if ($skipped > 0) {
        echo "跳过: {$skipped} 条\n";
    }
    if ($failed > 0) {
        echo "失败: {$failed} 条\n";
    }
    
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage() . "\n");
}
