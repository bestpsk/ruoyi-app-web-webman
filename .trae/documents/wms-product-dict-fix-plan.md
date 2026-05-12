# 货品管理字典显示问题修复计划

## 问题描述

### 问题1：单位和规格显示数字而非中文
- **现象**：添加货品时，单位下拉框显示"5"，规格下拉框显示"1"
- **原因**：数据库中可能缺少 `biz_product_unit` 和 `biz_product_spec` 的字典数据
- **分析**：
  - 原始 `biz_wms.sql` 中**没有定义这两个字典**
  - 升级脚本 `biz_wms_upgrade_product.sql` 虽然包含字典数据，但执行时可能因重复而跳过
  - 前端 `useDict("biz_product_unit", "biz_product_spec")` 从后端获取数据为空时，el-select 无法匹配选项，直接显示 value 值（数字）

### 问题2：用户不理解填写逻辑
- 需要解释包装换算方案的概念和填写方式

---

## 解决方案

### 第1步：验证并补充字典数据
检查并确保以下字典数据存在于数据库：

**单位字典 (biz_product_unit)**：
| 值 | 标签 | 说明 |
|----|------|------|
| 1 | 箱 | 大包装单位 |
| 2 | 件 | 中等包装 |
| 3 | 套 | 套装 |
| 4 | 罐 | 罐装 |
| 5 | 盒 | 盒装（默认） |
| 6 | 袋 | 袋装 |
| 7 | 包 | 包装 |

**规格字典 (biz_product_spec)**：
| 值 | 标签 | 说明 |
|----|------|------|
| 1 | 支 | 单支（默认） |
| 2 | 瓶 | 瓶装 |
| 3 | 件 | 件数 |
| 4 | 套 | 套数 |
| 5 | 片 | 片数 |
| 6 | 个 | 个数 |

### 第2步：编写SQL脚本强制更新字典数据
使用 `INSERT ... ON DUPLICATE KEY UPDATE` 或先删除再插入的方式确保数据完整

### 第3步：优化前端用户体验
- 添加字段提示文字说明各字段的含义
- 在包装数量字段旁边添加更清晰的示例说明

---

## 实施步骤

### 步骤1：创建字典修复SQL脚本
文件：`d:\fuchenpro\webman\sql\fix_product_dict.sql`

```sql
-- 1. 确保字典类型存在
INSERT INTO sys_dict_type (dict_name, dict_type, status, create_by, create_time, remark)
VALUES ('货品单位', 'biz_product_unit', '0', 'admin', NOW(), '货品单位列表')
ON DUPLICATE KEY UPDATE dict_type = dict_type;

INSERT INTO sys_dict_type (dict_name, dict_type, status, create_by, create_time, remark)
VALUES ('货品规格', 'biz_product_spec', '0', 'admin', NOW(), '货品规格列表')
ON DUPLICATE KEY UPDATE dict_type = dict_type;

-- 2. 删除旧数据重新插入（确保数据完整）
DELETE FROM sys_dict_data WHERE dict_type = 'biz_product_unit';
DELETE FROM sys_dict_data WHERE dict_type = 'biz_product_spec';

-- 3. 插入单位字典数据
INSERT INTO sys_dict_data VALUES ...;

-- 4. 插入规格字典数据  
INSERT INTO sys_dict_data VALUES ...;
```

### 步骤2：执行SQL脚本
通过PHP脚本执行SQL

### 步骤3：优化前端页面
在 [product/index.vue](front/src/views/wms/product/index.vue) 中：
- 为单位和规格字段添加 placeholder 提示
- 为包装数量字段添加更详细的帮助文本
- 添加示例说明

---

## 货品填写逻辑说明（用户指南）

### 字段含义说明

| 字段 | 说明 | 示例 |
|------|------|------|
| **品名** | 产品名称 | 玻尿酸原液面膜 |
| **货品编码** | 自定义编码 | MFS-001 |
| **供货商** | 供应商名称 | 广州XX生物科技 |
| **类别** | 产品分类 | 院装-面部 |
| **单位** | 主单位（进货/整件出货） | 盒 |
| **规格** | 副单位（拆分出货） | 支 |
| **包装数量** | 1主单位=多少副单位 | 10（表示1盒=10支） |
| **进货价** | 按主单位的进货价 | 100元/盒 |
| **出货价(整)** | 按主单位的出货价 | 150元/盒 |
| **出货价(拆)** | 按副单位的出货价 | 18元/支 |

### 业务场景示例

**场景1：面膜产品**
- 单位：盒 | 规格：支 | 包装数量：10
- 进货价：100元/盒 | 出货价(整)：150元/盒 | 出货价(拆)：18元/支
- 含义：进货100元一盒（10支），整盒卖150元，单支卖18元

**场景2：单瓶精华**
- 单位：瓶 | 规格：瓶 | 包装数量：1
- 进货价：80元/瓶 | 出货价(整)：120元/瓶 | 出货价(拆)：120元/瓶
- 含义：不可拆分的产品，包装数量设为1

**场景3：套装配方**
- 单位：套 | 规格：个 | 包装数量：5
- 进货价：200元/套 | 出货价(整)：300元/套 | 出货价(拆)：70元/个
- 含义：一套含5个单品

---

## 预期结果

1. ✅ 单位下拉框正常显示：箱、件、套、罐、盒、袋、包
2. ✅ 规格下拉框正常显示：支、瓶、件、套、片、个
3. ✅ 用户能理解各字段的含义和填写方式
4. ✅ 包装数量显示正确的换算关系
