<?php
require __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config/database.php';
$conn = $config['connections'][$config['default'] ?? 'mysql'];
$pdo = new PDO('mysql:host='.$conn['host'].';port='.$conn['port'].';dbname='.$conn['database'], $conn['username'], $conn['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== sys_user 表字段 ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM sys_user");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['Field'] . " (" . $r['Type'] . ")\n";
}

echo "\n=== 用户数据 ===\n";
$stmt2 = $pdo->query("SELECT user_id, user_name, nick_name FROM sys_user WHERE del_flag='0'");
$rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $r) {
    echo "user_id={$r['user_id']} userName={$r['user_name']} nickName={$r['nick_name']}\n";
}
