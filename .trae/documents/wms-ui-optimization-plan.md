# 货品管理/入库管理 UI 优化与功能增强计划

## 需求汇总

### 一、货品管理页面优化
1. 列表列宽自适应调整（品名太宽，其他列过窄）
2. 状态列：tag 标签改为 switch 开关

### 二、入库管理页面优化
1. 列表列宽调整（当前太窄）
2. 状态列：tag 标签改为 switch 开关
3. 操作列：去掉"确认"按钮（只保留查看、修改、删除）

### 三、新增入库弹窗优化
1. 弹窗宽度加宽
2. 单位类型切换联动：
   - 切换主单位 → 数量按主单位计算
   - 切换副单位 → 数量按副单位计算
   - 进货单价自动切换对应价格
   - 金额自动重新计算

### 四、货品有效期功能（新需求）
- 货品表添加有效期字段
- 入库时记录生产日期/有效期
- 盘点时可识别临期/过期商品

---

## 实施步骤

### 步骤1：修改货品管理页面
文件：`front/src/views/wms/product/index.vue`

**列宽调整**：
| 列名 | 当前 | 调整 |
|------|------|------|
| 货品编码 | 120px | 130px |
| 品名 | min-width:140px | min-width:150px |
| 供货商 | 120px | 120px |
| 类别 | 110px | 100px |
| 单位(整) | 90px | 80px |
| 规格(拆) | 90px | 80px |
| 包装数量 | 90px | 85px |
| 进货价 | 90px | 85px |
| 出货价(整) | 90px | 95px |
| 出货价(拆) | 90px | 95px |
| 预警数量 | 90px | 80px |
| 状态 | 80px | 70px |
| 操作 | 150px | 120px |

**状态改为开关**：
```vue
<el-table-column label="状态" prop="status" width="70" align="center">
  <template #default="scope">
    <el-switch v-model="scope.row.status" active-value="0" inactive-value="1"
      @change="(val) => handleStatusChange(scope.row, val)" />
  </template>
</el-table-column>
```

### 步骤2：修改入库管理页面
文件：`front/src/views/wms/stockIn/index.vue`

**列宽调整**：
| 列名 | 当前 | 调整 |
|------|------|------|
| 入库单号 | 160px | 180px |
| 入库类型 | 100px | 100px |
| 总数量 | 90px | 80px |
| 总金额 | 110px | 110px |
| 入库日期 | 110px | 105px |
| 操作人 | 90px | 85px |
| 状态 | 90px | 75px |
| 创建时间 | 160px | 155px |
| 操作 | 200px | 140px |

**状态改为开关 + 去掉确认按钮**：
```vue
<!-- 状态改为开关 -->
<el-table-column label="状态" prop="status" width="75" align="center">
  <template #default="scope">
    <el-switch v-model="scope.row.status" active-value="0" inactive-value="1" disabled />
  </template>
</el-table-column>

<!-- 操作列去掉确认按钮 -->
<el-button link type="primary" icon="View" @click="handleView(scope.row)">查看</el-button>
<el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)" ...>修改</el-button>
<el-button link type="primary" icon="Delete" @click="handleDelete(scope.row)" ...>删除</el-button>
```

### 步骤3：入库弹窗加宽 + 单位联动
文件：`front/src/views/wms/stockIn/index.vue`

**弹窗宽度**：1000px → 1150px 或 1200px

**单位类型切换联动逻辑**：
```javascript
function onUnitTypeChange(index) {
  const item = form.value.items[index]
  
  if (item.unitType === '1') {
    // 主单位模式
    item.purchasePrice = item._mainPrice || item.purchasePrice || 0
  } else {
    // 副单位模式
    // 保存主单位价格
    if (!item._mainPrice) {
      item._mainPrice = item.purchasePrice
    }
    // 计算副单位单价 = 主价 / 包装数
    const packQty = item.packQty || 1
    item.purchasePrice = Math.round((item._mainPrice / packQty) * 100) / 100
  }
  
  calcAmount(index)
}
```

### 步骤4：添加有效期功能（可选增强）
**数据库变更**：
```sql
-- 货品表添加有效期相关字段
ALTER TABLE `biz_product` ADD COLUMN `shelf_life_days` int DEFAULT NULL COMMENT '保质期(天)' AFTER `sale_price_spec`;
ALTER TABLE `biz_product` ADD COLUMN `has_expiry` char(1) DEFAULT '0' COMMENT '是否有有效期(0否 1是)' AFTER `shelf_life_days`;

-- 入库明细表添加有效期字段
ALTER TABLE `biz_stock_in_item` ADD COLUMN `production_date` date DEFAULT NULL COMMENT '生产日期' AFTER `remark`;
ALTER TABLE `biz_stock_in_item` ADD COLUMN `expiry_date` date DEFAULT NULL COMMENT '有效期至' AFTER `production_date`;

-- 库存表添加预警日期字段
ALTER TABLE `biz_inventory` ADD COLUMN `earliest_expiry` date DEFAULT NULL COMMENT '最早批次有效期' AFTER `quantity`;
```

---

## 文件修改清单

| 文件 | 修改内容 |
|------|----------|
| `front/src/views/wms/product/index.vue` | 列宽调整、状态改开关 |
| `front/src/views/wms/stockIn/index.vue` | 列宽调整、状态改开关、去确认按钮、弹窗加宽、单位联动 |
| `webman/sql/add_expiry_fields.sql` | 新增（有效期字段） |

---

## 预期效果

### 货品管理列表
- ✅ 各列宽度更合理均匀
- ✅ 状态显示为开关样式

### 入库管理列表
- ✅ 列宽更宽松
- ✅ 状态显示为开关样式
- ✅ 操作按钮精简（无确认按钮）

### 新增入库弹窗
- ✅ 弹窗更宽敞
- ✅ 切换单位类型自动更新价格和金额

### 有效期功能（可选）
- ✅ 货品可设置保质期天数
- ✅ 入库可记录生产日期和有效期
- ✅ 库存显示最早到期时间
