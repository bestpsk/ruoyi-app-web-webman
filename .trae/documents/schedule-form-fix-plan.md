# 行程表单报错修复计划

## 问题分析

### 问题1：组件名称错误（主要错误）
**错误信息：** `Failed to resolve import "uview-plus/components/u--input/u--input.vue"`

**根因：** 行程表单使用了 **uview-plus 2.x 的旧命名方式**（双横线），但当前版本使用**单横线命名**。

| 错误用法 | 正确用法 | 出现次数 |
|---------|---------|---------|
| `<u--form>` | `<u-form>` | 1处 |
| `<u--input>` | `<u-input>` | 6处 |
| `<u--textarea>` | `<u-textarea>` | 1处 |

**影响文件：** [schedule/form.vue:4-28](file:///d:/fuchenpro/AppV3/src/pages/business/schedule/form.vue#L4-L28)

### 问题2：提交数据格式问题
**当前代码（第169行）：**
```javascript
await updateSchedule({
  scheduleId: form.scheduleId,
  userId: form.userId,
  userName: form.userName,
  enterpriseId: form.enterpriseId,
  enterpriseName: form.enterpriseName,  // ✅ 后端允许此字段
  scheduleDate: form.startDate,
  purpose: form.purpose,
  status: form.status,
  remark: form.remark
})
```

**后端允许字段（来自 BizSchedule.php）：**
```
user_id, user_name, enterprise_id, enterprise_name,
schedule_date, purpose, remark, status
```

**结论：** 提交数据格式基本正确，`enterprise_name` 是后端允许的字段。

---

## 修复步骤

### 步骤1：修复组件名称（8处修改）

**第4行：**
```vue
<!-- 修改前 -->
<u--form ref="formRef" :model="form" :rules="rules" labelPosition="top" :disabled="mode === 'view'">

<!-- 修改后 -->
<u-form ref="formRef" :model="form" :rules="rules" labelPosition="top" :disabled="mode === 'view'">
```

**第6、9、13、16、20、23行：**
```vue
<!-- 修改前 -->
<u--input v-model="..." ...></u--input>

<!-- 修改后 -->
<u-input v-model="..." ...></u-input>
```

**第26行：**
```vue
<!-- 修改前 -->
<u--textarea v-model="form.remark" ...></u--textarea>

<!-- 修改后 -->
<u-textarea v-model="form.remark" ...></u-textarea>
```

**第28行：**
```vue
<!-- 修改前 -->
</u--form>

<!-- 修改后 -->
</u-form>
```

### 步骤2：改善错误提示
在 `submitForm()` 的 catch 块中添加用户友好的错误提示：

```javascript
catch (e) {
  console.error('提交失败:', e)
  const msg = e?.msg || e?.message || '操作失败，请重试'
  uni.showToast({ title: msg, icon: 'none', duration: 2000 })
}
```

---

## 验证方法
1. Vite 热更新自动生效
2. 手机访问 http://192.168.31.126:9091/
3. 进入 **行程安排 → 新增行程** → 验证页面正常渲染
4. 进入 **行程安排 → 点击某行程 → 编辑** → 验证编辑功能正常
5. 修改信息保存 → 验证提交成功
