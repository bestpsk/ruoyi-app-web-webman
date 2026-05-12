# 开单单次价计算与持卡明细成交金额调整计划

## 需求

### 1. 开单Tab - 单次价调整
- **移动位置**：把"单次价"列从"方案价格"后面移到"成交金额"后面
- **计算公式**：从 `方案价格 ÷ 次数` 改为 `成交金额 ÷ 次数`

### 2. 操作Tab - 持卡明细显示成交金额
- **字段改名**：把"方案总价"改为"成交金额"
- **数据源修改**：显示实际成交金额，而不是方案价格

---

## 当前问题

`biz_package_item` 表没有 `deal_price`（成交金额）字段，只有：
- `plan_price` - 方案总价
- `unit_price` - 单次价格

---

## 实施步骤

### 步骤1：添加数据库字段

在 `biz_package_item` 表添加 `deal_price` 字段：

```sql
ALTER TABLE biz_package_item 
ADD COLUMN deal_price DECIMAL(12,2) DEFAULT 0.00 COMMENT '成交金额' 
AFTER plan_price;
```

### 步骤2：后端 - 保存成交金额并计算单次价

**文件**：`webman/app/service/BizSalesOrderService.php` 第151-165行

**修改内容**：
```php
foreach ($items as $item) {
    $quantity = intval($item['quantity'] ?? 1);
    $planPrice = floatval($item['plan_price'] ?? 0);
    $dealPrice = floatval($item['deal_amount'] ?? 0);  // 新增：成交金额
    $unitPrice = $quantity > 0 ? round($dealPrice / $quantity, 2) : 0;  // 改为：成交金额÷次数
    BizPackageItem::create([
        'package_id' => $package->package_id,
        'product_name' => $item['product_name'],
        'unit_price' => $unitPrice,
        'plan_price' => $planPrice,
        'deal_price' => $dealPrice,  // 新增
        'total_quantity' => $quantity,
        'used_quantity' => 0,
        'remaining_quantity' => $quantity,
        'remark' => $item['remark'] ?? null
    ]);
}
```

### 步骤3：前端开单Tab - 调整列顺序和计算公式

**文件**：`front/src/views/business/sales/index.vue` 第89-108行

**修改内容**：
1. 删除原"单次价"列（第94-98行）
2. 在"成交金额"列后面添加新的"单次价"列
3. 计算公式改为：`dealAmount / quantity`

### 步骤4：前端操作Tab - 字段改名和数据源修改

**文件**：`front/src/views/business/sales/index.vue` 第149行

**修改内容**：
```vue
<el-table-column label="成交金额" prop="dealPrice" width="90" align="center" />
```

---

## 涉及文件

| 文件 | 修改内容 |
|------|----------|
| `webman/sql/biz_sales.sql` | 添加 deal_price 字段定义 |
| `webman/app/service/BizSalesOrderService.php` | 保存成交金额，计算单次价 |
| `front/src/views/business/sales/index.vue` | 开单Tab列顺序+计算公式，操作Tab字段改名 |

## 注意事项
- 单次价计算依赖成交金额，成交金额为0时单次价显示0.00
- 历史数据 `deal_price` 默认为0，可后续通过SQL更新
