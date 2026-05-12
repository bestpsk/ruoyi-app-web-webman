# 货品管理优化计划 - 去保质期 + 页面美化

## 需求分析

### 需求1：去掉保质期字段
- 货品列表：删除"保质期"列
- 新增/修改弹窗：删除"保质期"输入框
- 数据库：保留字段但不再使用（或后续清理）

### 需求2：添加货品弹窗页面美化
**当前问题**：
- 每个字段下面都有提示文字（换算说明、价格说明等）
- 页面显得很乱，视觉噪音太多

**解决方案**：
- 删除所有字段下方的提示文字 div
- 在 **label 标签后面添加 el-tooltip 图标**
- 鼠标悬停在图标上时显示提示语
- 页面更干净整洁

---

## 实施方案

### 步骤1：删除保质期相关内容
1. **列表**：删除 `保质期` 列
2. **弹窗表单**：删除 `保质期` 输入行
3. **reset 函数**：移除 `shelfLifeDays: null`

### 步骤2：弹窗提示改为 Tooltip

| 字段 | 当前提示 | 改为 Tooltip |
|------|----------|-------------|
| 单位(整) | "请选择主单位（进货/整件）" | label 后加 ? 图标，tooltip: "选择进货时的主包装单位，如盒、箱等" |
| 规格(拆) | "请选择副单位（拆分出货）" | label 后加 ? 图标，tooltip: "选择拆分出货的最小单位，如支、瓶等" |
| 包装数量 | 换算 + 例：1盒=10支... | label 后加 ? 图标，tooltip: "1个主单位包含多少副单位，例：1盒=10支则填10" |
| 进货价 | "按主单位（盒）的价格" | label 后加 ? 图标，tooltip: "按主单位的采购价格" |
| 出货价(整) | "按主单位整件出货价..." | label 后加 ? 图标，tooltip: "整件出货的价格，默认等于进货价" |
| 出货价(拆) | "按副单位（支）..." | label 后加 ? 图标，tooltip: "拆分出货的单价 = 出货价(整) ÷ 包装数量，可修改" |

### 实现方式示例
```vue
<el-form-item label="单位(整)" prop="unit">
  <template #label>
    <span>单位(整)</span>
    <el-tooltip content="选择进货时的主包装单位，如盒、箱等" placement="top">
      <el-icon class="question-icon"><QuestionFilled /></el-icon>
    </el-tooltip>
  </template>
  <el-select ...>
</el-form-item>

<style scoped>
.question-icon {
  margin-left: 4px;
  color: #909399;
  cursor: help;
  vertical-align: middle;
}
</style>
```

---

## 文件修改清单

| 文件 | 修改内容 |
|------|----------|
| `front/src/views/wms/product/index.vue` | 删除保质期、提示改tooltip |

---

## 预期效果

- ✅ 弹窗页面更干净整洁
- ✅ 鼠标悬停查看帮助提示
- ✅ 无保质期字段干扰
