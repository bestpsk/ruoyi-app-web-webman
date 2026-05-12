# 入库管理优化方案 v2.0 - 供货商按货品存储 + 百分比列宽

## 核心改进思路

### 💡 用户提出的正确观点
> "每个货品的供货商是不一样的，所以最好的办法是根据数据库中货品对应的供货商来做储存更合理"

**完全同意！** 这是更合理的设计：

| 方案 | 说明 | 优劣 |
|------|------|------|
| ❌ 旧方案 | 入库单表头存一个供货商 | 无法处理多供货商场景 |
| ✅ **新方案** | 每条明细存该货品的供货商 | 准确、灵活、符合业务实际 |

---

## 实施方案

### 第一部分：数据库结构调整

#### 1.1 给入库明细表添加供货商字段

**SQL语句：**
```sql
-- 给入库明细表添加供货商字段
ALTER TABLE `biz_stock_in_item` 
ADD COLUMN `supplier_id` bigint DEFAULT NULL COMMENT '供货商ID' AFTER `product_name`,
ADD COLUMN `supplier_name` varchar(100) DEFAULT NULL COMMENT '供货商名称' AFTER `supplier_id`;
```

#### 1.2 更新后端模型

**文件：** [BizStockInItem.php](file:///d:/fuchenpro/webman/app/model/BizStockInItem.php)

**修改 fillable 字段：**
```php
protected $fillable = [
    'stock_in_id', 'product_id', 'product_name', 
    'supplier_id', 'supplier_name',  // 新增
    'spec', 'unit', 'unit_type',
    'quantity', 'purchase_price', 'amount',
    'production_date', 'expiry_date', 'remark'
];
```

---

### 第二部分：后端逻辑调整

#### 2.1 修改查询逻辑

**文件：** [BizStockInService.php](file:///d:/fuchenpro/webman/app/service/BizStockInService.php) 的 `selectStockInById` 方法

**修改点（第46-62行）：**
```php
$stockIn->items = array_map(function ($item) {
    return [
        'itemId' => $item['id'] ?? null,
        'productId' => $item['product_id'],
        'productName' => $item['product_name'],
        'supplierId' => $item['supplier_id'],           // 新增
        'supplierName' => $item['supplier_name'],       // 新增
        'spec' => $item['spec'],
        // ... 其他字段不变
    ];
}, $items);
```

#### 2.2 修改保存逻辑

**insertStockIn 和 updateStockIn 方法**
- 确保保存时包含 supplier_id 和 supplier_name 字段
- 无需额外改动，只要前端传了这些字段就会自动保存

---

### 第三部分：前端优化

#### 3.1 移除/弱化表头的供货商字段

**当前代码（第81-87行）：**
```vue
<el-col :span="8">
  <el-form-item label="供货商" prop="supplierId">
    <el-select ...>
    </el-select>
  </el-form-item>
</el-col>
```

**改为可选显示或移除：**
- **方案A（推荐）**：保留但标记为"主供货商"，仅用于筛选/统计
- **方案B**：完全移除，因为每行明细已有供货商

**建议采用方案A**，保留用于：
- 快速筛选该批次的主要供货商
- 统计报表时使用
- 向后兼容旧数据

#### 3.2 明细表格增加供货商列 + 百分比宽度

**新的列配置：**

| 列名 | 宽度 | 说明 |
|------|------|------|
| 货品 | **16%** | 缩小 |
| **供货商** | **10%** | 🆕 新增列 |
| 规格 | 6% | 短文本 |
| 单位类型 | 8% | 下拉框 |
| 换算 | 7% | 显示文本 |
| 数量 | 7% | 数字输入 |
| 进货单价 | 10% | 金额输入 |
| 金额 | 8% | 只读 |
| 备注 | 7% | 文本输入 |
| 生产日期 | 9% | 日期选择器 |
| 有效期至 | 9% | 日期选择器 |
| 操作 | **固定60px** | 删除按钮 |

**总计：** 16+10+6+8+7+7+10+8+7+9+9 = 97% + 固定操作列

#### 3.3 自动填充供货商逻辑

**修改 onProductSelect 函数（约第317行）：**

```javascript
function onProductSelect(index) {
  const product = productOptions.value.find(p => p.productId === form.value.items[index].productId)
  if (product) {
    // ... 现有逻辑（规格、单位、价格等）...
    
    // 🆕 自动填充该货品的供货商到当前行
    form.value.items[index].supplierId = product.supplierId
    form.value.items[index].supplierName = product.supplierName || '未知供货商'
    
    calcAmount(index)
  }
}
```

#### 3.4 addItem 函数增加供货商字段

**修改位置（约第297行）：**

```javascript
function addItem() {
  form.value.items.push({
    productId: undefined,
    productName: undefined,
    supplierId: undefined,      // 🆕 新增
    supplierName: undefined,    // 🆕 新增
    spec: undefined,
    unit: undefined,
    // ... 其他字段不变
  })
}
```

#### 3.5 明细表格模板更新

**新增供货商列（在货品列后面）：**
```vue
<el-table-column label="供货商" width="10%" align="center">
  <template #default="scope">
    <span>{{ scope.row.supplierName || '-' }}</span>
  </template>
</el-table-column>
```

**其他列全部改为百分比宽度。**

---

## 第四部分：完整代码改动清单

### 需要创建的文件：
1. **SQL脚本文件**（可选）：`add_supplier_to_stockin_item.sql`
   - 包含 ALTER TABLE 语句

### 需要修改的文件：

#### 1️⃣ [BizStockInItem.php](file:///d:/fuchenpro/webman/app/model/BizStockInItem.php)
- 第13-17行：fillable 数组添加 supplier_id, supplier_name

#### 2️⃣ [BizStockInService.php](file:///d:/fuchenpro/webman/app/service/BizStockInService.php)
- 第46-62行：selectStockInById 返回数据增加 supplierId, supplierName

#### 3️⃣ [stockIn/index.vue](file:///d:/fuchenpro/front/src/views/wms/stockIn/index.vue)
- **第102行**：货品列 → width="16%"
- **第109行后**：🆕 新增供货商列 → width="10%"
- **第110行**：规格列 → width="6%"
- **第115行**：单位类型列 → width="8%"
- **第124行**：换算列 → width="7%"
- **第132行**：数量列 → width="7%"
- **第138行**：进货单价列 → width="10%"
- **第144行**：金额列 → width="8%"
- **第149行**：备注列 → width="7%"
- **第155行**：生产日期列 → width="9%"
- **第161行**：有效期至列 → width="9%"
- **第167行**：操作列 → width="60"（固定）
- **第297-313行**：addItem 函数增加 supplierId, supplierName 字段
- **第317-329行**：onProductSelect 函数增加供货商自动填充逻辑

---

## 实施步骤顺序

### 步骤1：执行数据库变更
```bash
# 在MySQL中执行
ALTER TABLE `biz_stock_in_item` 
ADD COLUMN `supplier_id` bigint DEFAULT NULL COMMENT '供货商ID' AFTER `product_name`,
ADD COLUMN `supplier_name` varchar(100) DEFAULT NULL COMMENT '供货商名称' AFTER `supplier_id';
```

### 步骤2：更新后端模型和服务
- 修改 BizStockInItem.php 的 fillable
- 修改 BizStockInService.php 的查询返回值

### 步骤3：优化前端界面
- 修改表格列为百分比宽度
- 新增供货商列
- 实现自动填充逻辑
- 更新 addItem 和 onProductSelect 函数

### 步骤4：测试验证
- 新增入库单测试
- 编辑已有入库单测试
- 多供货商混合入库测试
- 不同屏幕分辨率下的UI测试

---

## 预期效果对比

### 改进前 ❌
```
入库单表头：[供货商: XXX] ← 一个供货商覆盖所有货品
明细表格：
| 货品(45%) | 规格 | 单位类型 | 数量(窄) | 单价(窄) | ...
| GCS-p7    | 支   | 主...     | 1       | 2580     | ...
```

### 改进后 ✅
```
入库单表头：[供货商: XXX] ← 可选的主供货商（用于筛选）
明细表格：
| 货品(16%) | 供货商(10%) | 规格(6%) | 单位类型(8%) | 数量(7%) | 单价(10%) | ...
| GCS-p7    | 广州XX公司  | 支      | 主单位(整)   | 1        | 2580.00    | ...
```

**优势：**
- ✅ 每行显示该货品的真实供货商
- ✅ 列宽均衡，不再有某列过宽/过窄的问题
- ✅ 选择货品时自动带出供货商，减少手动操作
- ✅ 数据更准确，符合"一货品一供货商"的业务实际
- ✅ 百分比布局自适应不同屏幕尺寸

---

## 风险评估与注意事项

### 风险等级：🟡 中等风险（涉及数据库变更）

### 注意事项：

1. **数据库备份**
   - 执行 ALTER TABLE 前备份数据库
   - 如果有历史入库数据，supplier_id 和 supplier_name 会为 NULL（可接受）

2. **向后兼容**
   - 旧数据的入库明细没有供货商信息
   - 前端显示时用 `scope.row.supplierName || '-'` 处理空值
   - 编辑旧数据时可以选择性补充供货商

3. **性能影响**
   - 新增两个字段对性能影响极小
   - 查询时无额外 JOIN 操作（因为是冗余存储）

4. **数据一致性**
   - 供货商信息从货品表冗余存储到明细表
   - 如果货品的供货商变更，历史入库记录不受影响（快照式存储）

---

## 完成标准

✅ 数据库成功添加 supplier_id 和 supplier_name 字段  
✅ 后端能正确保存和返回供货商信息  
✅ 前端明细表格新增供货商列并正确显示  
✅ 选择货品时自动带出该货品的供货商  
✅ 所有列使用百分比宽度（除操作列固定）  
✅ 货品列宽度适中（约16%）  
✅ 单位类型、数量、进货单价等列有足够空间  
✅ 新增、编辑、查看模式均正常工作  
✅ 无控制台错误  
