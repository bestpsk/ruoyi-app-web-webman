# 修复 getProductById 导入错误计划

## 问题描述

**错误信息：**
```
SyntaxError: The requested module '/src/api/wms/product.js' does not provide an export named 'getProductById'
```

**错误位置：**
- 文件：[d:\fuchenpro\front\src\views\wms\stockIn\index.vue](file:///d:/fuchenpro/front/src/views/wms/stockIn/index.vue) 第189行
- 导入语句：`import { searchProduct, getProductById } from "@/api/wms/product"`

**根本原因：**
- [product.js](file:///d:/fuchenpro/front/src/api/wms/product.js) 中导出的函数名是 **`getProduct`**（第7-9行），而不是 **`getProductById`**
- [stockIn/index.vue](file:///d:/fuchenpro/front/src/views/wms/stockIn/index.vue) 第189行使用了错误的函数名导入

## 解决方案

### 方案选择

#### ✅ 推荐方案：修改导入语句（最小改动）
**修改文件：** [d:\fuchenpro\front\src\views\wms\stockIn\index.vue](file:///d:/fuchenpro/front/src/views/wms/stockIn/index.vue)

**修改内容：**
1. **第189行** - 修改导入语句：
   ```javascript
   // 修改前（错误）
   import { searchProduct, getProductById } from "@/api/wms/product"
   
   // 修改后（正确）
   import { searchProduct, getProduct } from "@/api/wms/product"
   ```

2. **第264行** - 修改函数调用：
   ```javascript
   // 修改前（错误）
   const results = await Promise.all(productIds.map(id => getProductById(id)))
   
   // 修改后（正确）
   const results = await Promise.all(productIds.map(id => getProduct(id)))
   ```

**优点：**
- ✅ 改动最小，只修改2处代码
- ✅ 不影响其他使用 `getProduct` 函数的地方
- ✅ 保持API文件的一致性

#### 备选方案：在 product.js 添加别名导出
**修改文件：** [d:\fuchenpro\front\src\api\wms\product.js](file:///d:/fuchenpro/front/src/api/wms/product.js)

**修改内容：**
在文件末尾添加：
```javascript
export { getProduct as getProductById }
```

**缺点：**
- ❌ 增加不必要的冗余代码
- ❌ 可能造成命名混乱
- ❌ 不符合DRY原则（Don't Repeat Yourself）

## 实施步骤

### 步骤1：修改导入语句
- 打开 [stockIn/index.vue](file:///d:/fuchenpro/front/src/views/wms/stockIn/index.vue)
- 定位到第189行的import语句
- 将 `getProductById` 改为 `getProduct`

### 步骤2：修改函数调用
- 定位到第264行的 `loadProductOptionsForItems` 函数
- 将 `getProductById(id)` 改为 `getProduct(id)`

### 步骤3：验证修复
- 刷新浏览器页面
- 点击"入库管理"菜单
- 确认页面正常加载，无控制台错误
- 测试编辑入库单功能，确认货品信息正确显示

## 影响范围分析

### 受影响的代码段：
1. **[stockIn/index.vue:189](file:///d:/fuchenpro/front/src/views/wms/stockIn/index.vue#L189)** - import语句
2. **[stockIn/index.vue:264](file:///d:/fuchenpro/front/src/views/wms/stockIn/index.vue#L264)** - loadProductOptionsForItems函数中的调用

### 不受影响的代码：
- ✅ 其他页面的产品相关功能（货品管理页面本身使用的是 `getProduct`，不受影响）
- ✅ 后端API接口（`/wms/product/{id}` 接口保持不变）
- ✅ 数据库和模型层

## 预期效果

修复后的行为：
- ✅ 入库管理页面正常加载，不再报错
- ✅ 编辑入库单时能正确加载货品信息
- ✅ 货品下拉框显示正确的货品名称和编码
- ✅ 控制台无JavaScript错误

## 完成标准

✅ 修改 import 语句，使用正确的函数名  
✅ 修改所有调用该函数的地方  
✅ 页面加载无报错  
✅ 编辑功能正常工作  
✅ 货品信息正确显示  

## 风险评估

**风险等级：** 🟢 低风险

**理由：**
- 只是修正函数名的拼写错误
- 不改变任何业务逻辑
- API接口调用方式完全相同（都是 GET /wms/product/{id}）
- 只影响入库管理页面的编辑功能

**注意事项：**
- 修改后需要清除浏览器缓存或硬刷新（Ctrl+F5）
- 确保Vite开发服务器已重新编译
