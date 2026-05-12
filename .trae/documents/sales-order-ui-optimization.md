# 销售开单页面UI优化计划

## 需求概述
对销售开单页面的开单tab进行UI布局调整：
1. **顾客反馈区域调整**：占满一整行，并添加"顾客反馈"标题
2. **合计与提交订单位置调整**：移至最下方独立显示，不与顾客反馈同行
3. **添加品项按钮控制**：隐藏添加品项按钮（母亲角色无法添加品项）

## 当前代码结构分析

### 文件位置
`d:/fuchenpro/front/src/views/business/sales/index.vue`

### 当前布局（第96-104行）
```vue
<el-row :gutter="20" style="margin-top: 12px">
  <el-col :span="12">
    <el-input v-model="orderCustomerFeedback" type="textarea" :rows="2" placeholder="顾客反馈" size="small" />
  </el-col>
  <el-col :span="12" style="text-align: right; padding-top: 6px">
    方案合计: <b>{{ totalPlanAmount }}</b> 元 | 成交合计: <b style="color: #409eff">{{ totalDealAmount }}</b> 元
    <el-button type="primary" @click="submitOrder" style="margin-left: 12px">提交订单</el-button>
  </el-col>
</el-row>
```

### 现有问题
1. 顾客反馈只占一半宽度（span=12），右侧是合计信息
2. 顾客反馈缺少明确的标签/标题
3. 合计信息和提交订单与顾客反馈混在一起
4. 缺少添加品项的按钮（虽然`addOrderItemRow`函数已定义但未使用）

---

## 实施步骤

### 步骤1：调整顾客反馈区域布局
**目标**：让顾客反馈占满整行并添加标题

**修改位置**：第96-104行

**具体修改**：
- 将顾客反馈的`<el-col>`改为`span="24"`（占满整行）
- 在输入框前添加"顾客反馈"标签或标题
- 使用`<el-form-item label="顾客反馈">`或独立的`<div>`标签包裹

**预期效果**：
```vue
<div style="margin-top: 12px">
  <div style="margin-bottom: 6px; font-weight: bold">顾客反馈</div>
  <el-input v-model="orderCustomerFeedback" type="textarea" :rows="2" placeholder="请输入顾客反馈..." size="small" />
</div>
```

### 步骤2：将合计和提交订单移至下方独立显示
**目标**：合计信息和提交订单按钮在顾客反馈下方独占一行

**修改位置**：紧接步骤1之后

**具体修改**：
- 新增一个独立的`<el-row>`用于显示合计和提交按钮
- 设置右对齐样式
- 保持原有的金额计算逻辑不变

**预期效果**：
```vue
<el-row style="margin-top: 12px; text-align: right">
  <el-col :span="24">
    <span style="margin-right: 12px">方案合计: <b>{{ totalPlanAmount }}</b> 元</span>
    <span style="margin-right: 12px">成交合计: <b style="color: #409eff">{{ totalDealAmount }}</b> 元</span>
    <el-button type="primary" @click="submitOrder">提交订单</el-button>
  </el-col>
</el-row>
```

### 步骤3：处理添加品项按钮的显示控制
**目标**：确保母亲角色无法看到/使用添加品项按钮

**分析当前状态**：
- `addOrderItemRow()`函数已在第504-508行定义
- 但目前模板中没有调用此函数的按钮
- 需要确认是否需要添加该按钮并根据权限控制

**方案选择**：
- **方案A**：如果之前有添加品项按钮但被删除了，需恢复并根据角色权限用`v-if`控制显示
- **方案B**：如果从未有过该按钮，则需要在表格上方添加按钮并用权限控制

**推荐实现**：
在品项表格（第54行）前添加添加品项按钮：
```vue
<el-button v-if="canAddItem" type="primary" plain icon="Plus" size="small" @click="addOrderItemRow" style="margin-bottom: 8px">添加品项</el-button>
```

其中`canAddItem`为权限判断变量，需要：
1. 在script中定义权限判断逻辑（基于当前用户角色）
2. 母亲角色时`canAddItem = false`
3. 其他角色时`canAddItem = true`

**权限判断逻辑建议**：
- 检查当前用户的角色标识
- 如果角色包含"母亲"或特定权限标记，则隐藏按钮
- 可通过用户store、字典配置或后端返回的权限列表判断

---

## 最终布局结构预览

```
[品项表格 - 完整宽度]
[操作列包含删除按钮]

[顾客反馈]              ← 标题
[________________]      ← 输入框占满整行（textarea）

                    [方案合计: X元 | 成交合计: Y元] [提交订单]  ← 右对齐，独立一行
```

---

## 涉及修改的代码范围

1. **模板部分**（第96-104行）：重构布局结构
2. **可能新增**：表格前的添加品项按钮（第53-54行之间）
3. **脚本部分**：可能需要添加权限判断变量（约第417行附近）

## 注意事项
- 保持所有现有的数据绑定和事件处理不变
- 不改变提交订单的业务逻辑
- 确保响应式布局在不同屏幕尺寸下正常显示
- 权限控制需符合系统的角色权限体系
