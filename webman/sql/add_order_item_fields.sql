-- 为 biz_order_item 表添加单次价和欠款金额字段
ALTER TABLE biz_order_item ADD COLUMN unit_price DECIMAL(10,2) DEFAULT 0.00 COMMENT '单次价' AFTER paid_amount;
ALTER TABLE biz_order_item ADD COLUMN owed_amount DECIMAL(10,2) DEFAULT 0.00 COMMENT '欠款金额' AFTER unit_price;
