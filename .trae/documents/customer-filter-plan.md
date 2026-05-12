# 客户列表增加筛选条件

## 需求
在门店选择器右边增加筛选条件：是否成交、满意度，用来筛选客户列表。

## 修改内容

### 1. 前端：sales/index.vue 模板
- 在门店选择器右边（原"创建门店"按钮位置）增加两个筛选下拉框
- 是否成交：全部 / 已成交 / 未成交
- 满意度：全部 / 1星 / 2星 / 3星 / 4星 / 5星

### 2. 前端：sales/index.vue script
- 增加 `filterHasDeal` 和 `filterSatisfaction` 响应式变量
- 修改 `loadCustomerList` 函数，传递筛选参数
- 修改 `handleSearchCustomer` 函数，传递筛选参数

### 3. 后端：BizCustomerController.php
- 修改 `searchCustomer` 方法，接收 `hasDeal` 和 `satisfaction` 参数

### 4. 后端：BizCustomerService.php
- 修改 `searchCustomer` 方法签名，增加筛选参数
- 根据 `hasDeal` 参数筛选：已成交/未成交
- 根据 `satisfaction` 参数筛选：满意度等于指定值
