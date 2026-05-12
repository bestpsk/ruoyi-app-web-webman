# 门店管理模块Bug修复计划

## 问题分析

### 问题1: 保存报错 `business_time_range` 字段不存在
**原因**：前端表单中的 `businessTimeRange` 是时间范围选择器的临时值，在提交时被一起发送到了后端，但后端数据库表中只有 `business_hours` 字段。

**解决方案**：在 `submitForm()` 提交前，从 form 对象中删除 `businessTimeRange` 字段。

### 问题2: 服务员工显示数字而不是姓名
**原因**：下拉选项的 label 绑定的是 `item.realName`，但可能用户列表返回的数据中 `realName` 为空或未定义。

**解决方案**：
- 确保用户列表 API 返回的数据包含 `realName` 字段
- 如果 `realName` 为空则回退到 `userName`

### 问题3: 创建人不显示
**原因**：修改门店时，`creatorName` 可能未被正确回显；新增时 `userStore.realName` 可能还未加载完成。

**解决方案**：
- 新增时确保 `userStore.realName` 正确获取
- 修改时确保后端返回的 `creatorName` 被正确赋值

---

## 修复步骤

### 步骤1: 修复 submitForm() 删除多余字段
**文件**: `front/src/views/business/store/index.vue`

在提交前删除 `businessTimeRange` 字段：
```javascript
function submitForm() {
  proxy.$refs["storeRef"].validate(valid => {
    if (valid) {
      if (form.value.businessTimeRange && form.value.businessTimeRange.length === 2) {
        form.value.businessHours = form.value.businessTimeRange[0] + ' - ' + form.value.businessTimeRange[1]
      } else {
        form.value.businessHours = undefined
      }
      // 删除不需要提交的字段
      delete form.value.businessTimeRange
      
      const submitData = { ...form.value }
      // ... 提交逻辑
    }
  })
}
```

### 步骤2: 修复服务员工显示问题
确保下拉选项的 label 显示正确的字段名：
```vue
<el-option
  v-for="item in userOptions"
  :key="item.userId"
  :label="item.realName || item.userName || item.userId"
  :value="item.userId"
/>
```

### 步骤3: 修复创建人显示
- 确保 `userStore.realName` 在页面初始化时可用
- 创建人字段始终显示（移除 v-if 条件）

---

## 文件修改清单

| 文件路径 | 操作 |
|----------|------|
| `front/src/views/business/store/index.vue` | 修复三个bug |
