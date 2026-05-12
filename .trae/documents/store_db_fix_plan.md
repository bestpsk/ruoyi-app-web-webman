# 门店管理 - 数据库字段缺失修复

## 问题原因
数据库 `biz_store` 表中缺少以下字段：
- `annual_performance` (年业绩)
- `regular_customers` (常来顾客数)
- `creator_name` (创建人)

## 解决方案
在MySQL中执行以下SQL语句：

```sql
-- 1. 为biz_store表添加年业绩字段
ALTER TABLE `biz_store` ADD COLUMN `annual_performance` decimal(12,2) DEFAULT 0.00 COMMENT '年业绩' AFTER `business_hours`;

-- 2. 为biz_store表添加常来顾客数字段
ALTER TABLE `biz_store` ADD COLUMN `regular_customers` int DEFAULT 0 COMMENT '常来顾客数' AFTER `annual_performance`;

-- 3. 为biz_store表添加创建人字段
ALTER TABLE `biz_store` ADD COLUMN `creator_name` varchar(50) DEFAULT NULL COMMENT '创建人' AFTER `regular_customers`;
```

## 执行步骤
1. 打开MySQL客户端（Navicat、phpMyAdmin或命令行）
2. 选择 `fuchenpro` 数据库
3. 执行上述3条ALTER语句
4. 重启后端服务
5. 刷新前端页面测试

## 验证方法
执行后可以用以下SQL验证字段是否添加成功：
```sql
DESCRIBE biz_store;
```
应该能看到 `annual_performance`, `regular_customers`, `creator_name` 三个字段。
