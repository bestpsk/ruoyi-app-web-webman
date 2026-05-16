# 销售开单 - 企业和门店数据持久化方案

## 问题分析

**当前问题**：
- 销售开单页面（[index.vue](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue)）的企业和门店选择存储在 Vue 的 `ref` 中
- 页面刷新后，所有状态丢失，用户需要重新选择企业和门店
- 这给用户带来不便，特别是需要频繁刷新的场景

**当前代码结构**：
- [index.vue:117-120](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue#L117-L120) - 企业和门店的响应式变量
- [index.vue:154-168](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue#L154-L168) - 选择企业时的处理逻辑
- [index.vue:170-176](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue#L170-L176) - 选择门店时的处理逻辑
- [index.vue:228](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue#L228) - onMounted 只加载企业列表，不恢复之前的选择

## 解决方案

使用 **uni-app 本地存储** 实现企业和门店选择的持久化：

### 技术方案
1. 使用 `uni.setStorageSync()` / `uni.getStorageSync()` 进行本地存储
2. 存储键名规范：`sales_selected_enterprise` 和 `sales_selected_store`
3. 存储内容：包含 ID 和 Name 的完整信息

### 需要持久化的数据
```javascript
// 企业信息
{
  enterpriseId: 'xxx',
  enterpriseName: '企业名称'
}

// 门店信息
{
  storeId: 'xxx',
  storeName: '门店名称'
}
```

## 实施步骤

### 步骤 1：添加数据持久化工具函数
在页面中添加保存和读取本地存储的辅助函数：
- `saveSelectionToStorage()` - 保存当前选择到本地
- `loadSelectionFromStorage()` - 从本地读取之前的选择

### 步骤 2：修改企业选择逻辑
修改 [onEnterpriseSelect()](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue#L154) 函数：
- 选择企业后，立即保存到本地存储
- 如果切换企业，清空门店选择并更新存储

### 步骤 3：修改门店选择逻辑
修改 [onStoreSelect()](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue#L170) 函数：
- 选择门店后，立即保存到本地存储

### 步骤 4：修改页面初始化逻辑
修改 [onMounted()](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue#L228) 钩子：
- 加载企业列表后，检查本地是否有缓存的选择
- 如果有缓存，恢复企业和门店的选择状态
- 根据缓存的门店ID自动加载客户列表

### 步骤 5：优化用户体验
- 添加清除选择的选项（可选）
- 确保数据一致性：当企业列表变化时，验证缓存的企业是否仍然有效

## 代码改动位置

### 文件：[index.vue](file:///f:/fuchen/AppV3/src/pages/business/sales/index.vue)

**新增常量定义**（在 script setup 顶部）：
```javascript
const STORAGE_KEYS = {
  enterprise: 'sales_selected_enterprise',
  store: 'sales_selected_store'
}
```

**新增函数**：
1. `saveSelectionToStorage()` - 约10行
2. `loadSelectionFromStorage()` - 约20行

**修改现有函数**：
1. `onEnterpriseSelect()` - 添加保存调用（+2行）
2. `onStoreSelect()` - 添加保存调用（+2行）
3. `onMounted()` - 添加恢复逻辑（+8行）

**预计总改动量**：约40-50行新增/修改代码

## 数据流说明

### 正常流程（带持久化）
```
用户打开页面
    ↓
onMounted 触发
    ↓
加载企业列表 + 从本地存储读取缓存
    ↓
有缓存？ → 是 → 恢复企业和门店状态 → 加载客户列表
    ↓ 否
显示空白状态，等待用户选择
    ↓
用户选择企业 → 保存到本地 → 加载门店列表
    ↓
用户选择门店 → 保存到本地 → 加载客户列表
```

### 刷新流程（改进后）
```
用户刷新页面
    ↓
页面重新加载
    ↓
从本地存储读取上次的选择 ✅
    ↓
自动恢复企业和门店状态
    ↓
自动加载客户列表 ✅
    ↓
用户无需重新选择 👍
```

## 注意事项

1. **数据有效性验证**
   - 恢复缓存时，需验证企业ID在企业列表中存在
   - 验证门店ID在门店列表中存在
   - 如果不存在，清除无效缓存

2. **性能考虑**
   - 使用同步API（setStorageSync/getStorageSync）确保数据一致性
   - 存储数据量小，不会影响性能

3. **兼容性**
   - uni-app 的 Storage API 在所有平台（H5、小程序、App）都支持
   - 无需额外的 polyfill 或适配

4. **安全性**
   - 仅存储业务数据（ID和名称），不涉及敏感信息
   - 符合数据安全要求

## 测试验证点

1. ✅ 选择企业和门店后刷新页面，选择状态保持
2. ✅ 切换企业后，门店选择正确重置
3. ✅ 清除应用缓存后，恢复正常初始状态
4. ✅ 在不同平台（H5、微信小程序、App）测试兼容性
5. ✅ 验证客户列表正确加载

## 预期效果

**改进前**：
- ❌ 每次刷新都需要重新选择企业
- ❌ 选择企业后还需要重新选择门店
- ❌ 用户体验差，操作繁琐

**改进后**：
- ✅ 刷新后自动恢复上次的企�ite和门店选择
- ✅ 自动加载对应的客户列表
- ✅ 用户只需在首次使用或主动切换时才需要选择
- ✅ 大幅提升用户体验和工作效率
