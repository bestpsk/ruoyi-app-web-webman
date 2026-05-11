-- ----------------------------
-- 员工详情表
-- ----------------------------
DROP TABLE IF EXISTS sys_user_detail;
CREATE TABLE sys_user_detail (
  detail_id         bigint(20)      NOT NULL AUTO_INCREMENT  COMMENT '详情ID',
  user_id           bigint(20)      NOT NULL                 COMMENT '用户ID',
  wechat            varchar(50)     DEFAULT ''               COMMENT '微信号',
  birthday          date            DEFAULT NULL             COMMENT '生日',
  id_card           varchar(18)     DEFAULT ''               COMMENT '身份证号',
  address           varchar(200)    DEFAULT ''               COMMENT '住址',
  hire_date         date            DEFAULT NULL             COMMENT '入职日期',
  employment_status char(1)         DEFAULT '0'              COMMENT '在职状态（0在职 1离职）',
  resign_date       date            DEFAULT NULL             COMMENT '离职日期',
  create_by         varchar(64)     DEFAULT ''               COMMENT '创建者',
  create_time       datetime                                 COMMENT '创建时间',
  update_by         varchar(64)     DEFAULT ''               COMMENT '更新者',
  update_time       datetime                                 COMMENT '更新时间',
  remark            varchar(500)    DEFAULT NULL             COMMENT '备注',
  PRIMARY KEY (detail_id),
  UNIQUE KEY uk_user_id (user_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 COMMENT='员工详情表';

-- ----------------------------
-- 薪资架构类型表
-- ----------------------------
DROP TABLE IF EXISTS hr_salary_type;
CREATE TABLE hr_salary_type (
  type_id           bigint(20)      NOT NULL AUTO_INCREMENT  COMMENT '类型ID',
  type_code         varchar(50)     NOT NULL                 COMMENT '类型编码',
  type_name         varchar(100)    NOT NULL                 COMMENT '类型名称',
  calc_formula      varchar(500)    DEFAULT ''               COMMENT '计算公式说明',
  status            char(1)         DEFAULT '0'              COMMENT '状态（0正常 1停用）',
  create_by         varchar(64)     DEFAULT ''               COMMENT '创建者',
  create_time       datetime                                 COMMENT '创建时间',
  update_by         varchar(64)     DEFAULT ''               COMMENT '更新者',
  update_time       datetime                                 COMMENT '更新时间',
  remark            varchar(500)    DEFAULT NULL             COMMENT '备注',
  PRIMARY KEY (type_id),
  UNIQUE KEY uk_type_code (type_code)
) ENGINE=InnoDB AUTO_INCREMENT=100 COMMENT='薪资架构类型表';

-- ----------------------------
-- 初始化薪资类型数据
-- ----------------------------
INSERT INTO hr_salary_type (type_code, type_name, calc_formula, status, create_by, create_time, remark) VALUES
('BASE_SALARY', '底薪', '固定金额', '0', 'admin', NOW(), '每月固定发放'),
('SALES_COMMISSION', '销售业绩提成', '销售业绩 × 提成比例', '0', 'admin', NOW(), '按销售业绩计算'),
('PAYMENT_COMMISSION', '回款业绩提成', '回款金额 × 提成比例', '0', 'admin', NOW(), '按回款金额计算'),
('PROFIT_COMMISSION', '利润提成', '(回款金额 - 成本) × 提成比例', '0', 'admin', NOW(), '按利润计算'),
('TIERED_SALES', '阶梯销售提成', '按销售业绩阶梯计算提成', '0', 'admin', NOW(), '阶梯式销售提成'),
('TIERED_PAYMENT', '阶梯回款提成', '按回款业绩阶梯计算提成', '0', 'admin', NOW(), '阶梯式回款提成');

-- ----------------------------
-- 用户薪资配置表
-- ----------------------------
DROP TABLE IF EXISTS hr_user_salary;
CREATE TABLE hr_user_salary (
  salary_id         bigint(20)      NOT NULL AUTO_INCREMENT  COMMENT '薪资配置ID',
  user_id           bigint(20)      NOT NULL                 COMMENT '用户ID',
  type_id           bigint(20)      NOT NULL                 COMMENT '薪资类型ID',
  base_amount       decimal(12,2)   DEFAULT 0.00             COMMENT '基础金额/底薪',
  commission_rate   decimal(5,4)    DEFAULT 0.0000           COMMENT '提成比例（如0.05表示5%）',
  tier_config       text            DEFAULT NULL             COMMENT '阶梯配置（JSON格式）',
  effective_date    date            DEFAULT NULL             COMMENT '生效日期',
  expire_date       date            DEFAULT NULL             COMMENT '失效日期',
  status            char(1)         DEFAULT '0'              COMMENT '状态（0正常 1停用）',
  create_by         varchar(64)     DEFAULT ''               COMMENT '创建者',
  create_time       datetime                                 COMMENT '创建时间',
  update_by         varchar(64)     DEFAULT ''               COMMENT '更新者',
  update_time       datetime                                 COMMENT '更新时间',
  remark            varchar(500)    DEFAULT NULL             COMMENT '备注',
  PRIMARY KEY (salary_id),
  KEY idx_user_id (user_id),
  KEY idx_type_id (type_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 COMMENT='用户薪资配置表';

-- ----------------------------
-- 薪资阶梯配置表
-- ----------------------------
DROP TABLE IF EXISTS hr_salary_tier;
CREATE TABLE hr_salary_tier (
  tier_id           bigint(20)      NOT NULL AUTO_INCREMENT  COMMENT '阶梯ID',
  salary_id         bigint(20)      NOT NULL                 COMMENT '薪资配置ID',
  tier_level        int             DEFAULT 1                COMMENT '阶梯级别',
  min_amount        decimal(12,2)   DEFAULT 0.00             COMMENT '最小金额',
  max_amount        decimal(12,2)   DEFAULT NULL             COMMENT '最大金额（NULL表示无上限）',
  commission_rate   decimal(5,4)    DEFAULT 0.0000           COMMENT '提成比例',
  create_time       datetime                                 COMMENT '创建时间',
  PRIMARY KEY (tier_id),
  KEY idx_salary_id (salary_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 COMMENT='薪资阶梯配置表';
