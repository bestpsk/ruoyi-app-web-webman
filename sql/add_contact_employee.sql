-- 添加对接员工字段
ALTER TABLE `biz_stock_out` ADD COLUMN `contact_employee_id` int DEFAULT NULL COMMENT '对接员工ID' AFTER `enterprise_name`;
ALTER TABLE `biz_stock_out` ADD COLUMN `contact_employee_name` varchar(50) DEFAULT NULL COMMENT '对接员工姓名' AFTER `contact_employee_id`;
