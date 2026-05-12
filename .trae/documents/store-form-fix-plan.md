# 门店表单报错修复计划

## 问题分析

### 问题1：组件导入失败（主要错误）
**错误信息：** `Failed to resolve import "uview-plus/components/u-input/u-input.vue"`

**根因：** 门店表单使用了 **uview-plus 2.x 的旧命名方式**（双横线 `u--input`），但当前安装的 uview-plus 版本使用**单横线命名**（`u-input`）。

| 错误用法 | 正确用法 |
|---------|---------|
| `<u--form>` | `<u-form>` |
| `<u--input>` | `<u-input>` |
| `<u--textarea>` | `<u-textarea>` |

**影响文件：** [form.vue:4-49](file:///d:/fuchenpro/AppV3/src/pages/business/store/form.vue#L4-L49)

### 问题2：提交数据格式问题
**与企业管理相同的问题：** `submitForm()` 中使用 `{ ...form }` 直接复制整个表单对象，包含前端专用字段：

```javascript
// ❌ 当前代码 (第161行)
const formData = { ...form }
// 包含 enterpriseName, businessStart, businessEnd 等无用/冲突字段
```

**后端可接受字段（来自 BizStore.php）：**
```
enterprise_id, store_name, manager_name, phone, wechat, address,
business_hours, annual_performance, regular_customers,
server_user_id, server_user_name, status, remark
```

---

## 修复步骤

### 步骤1：修复组件名称（6处修改）
将所有双横线组件名改为单横线：

1. 第4行：`<u--form>` → `<u-form>`
2. 第6行：`<u--input>` → `<u-input>`
3. 第9行：`<u--input>` → `<u-input>`
4. 第12行：`<u--input>` → `<u-input>`
5. 第15行：`<u--input>` → `<u-input>`
6. 第18行：`<u--input>` → `<u-input>`
7. 第22行：`<u--input>` → `<u-input>`
8. 第25行：`<u--input>` → `<u-input>`
9. 第29行：`<u--textarea>` → `<u-textarea>`
10. 第33行：`<u--input>` → `<u-input>`
11. 第36行：`<u--input>` → `<u-input>`
12. 第40行：`<u--input>` → `<u-input>`
13. 第48行：`<u--textarea>` → `<u-textarea>`

### 步骤2：重构 submitForm() 提交逻辑
精确构建提交数据对象，只包含后端需要的字段：

```javascript
const formData = {
  storeId: form.storeId || undefined,
  enterpriseId: form.enterpriseId,
  storeName: form.storeName,
  managerName: form.managerName || null,
  phone: form.phone || null,
  wechat: form.wechat || null,
  address: form.address || null,
  businessHours: (form.businessStart && form.businessEnd)
    ? `${form.businessStart} - ${form.businessEnd}` : null,
  annualPerformance: form.annualPerformance ? parseFloat(form.annualPerformance) : 0,
  regularCustomers: form.regularCustomers ? parseInt(form.regularCustomers) : 0,
  serverUserId: form.serverUserId || null,
  serverUserName: form.serverUserName || null,
  status: form.status,
  remark: form.remark || null
}
```

### 步骤3：改善错误提示
在 catch 块中添加用户友好的错误提示：

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
3. 进入 门店管理 → 新增门店 → 验证页面正常渲染
4. 填写信息保存 → 验证提交成功
5. 编辑已有门店 → 验证修改成功
