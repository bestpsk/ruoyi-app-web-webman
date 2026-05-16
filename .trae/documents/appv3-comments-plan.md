# AppV3 项目注释完善计划

## 项目概况

AppV3 项目共有 **28 个 JS 文件**需要添加注释，当前注释覆盖率为 **0%**，所有文件均无任何注释。

## 注释规范

### 文件头注释格式
```javascript
/**
 * @description 模块名称 - 模块简要说明
 * @description 详细描述模块的职责、核心功能和依赖关系
 */
```

### 函数注释格式
```javascript
/**
 * 函数功能的具体描述（不要重复函数名）
 * @param {类型} 参数名 - 参数含义说明
 * @returns {类型} 返回值说明
 */
```

### 注释原则
1. **描述具体作用**，不重复函数名（❌ `// 登录` → ✅ `// 调用后端登录接口验证账号密码，成功后存储Token到本地`)
2. **说明业务含义**，不仅描述技术操作（❌ `// 设置token` → ✅ `// 将JWT认证令牌存储到uni-app本地存储，键名为App-Token`)
3. **复杂逻辑加步骤说明**（如请求拦截、权限校验、路由守卫）
4. **安全模块标注注意事项**（如Token管理、权限校验）
5. **不修改任何功能代码**，仅添加注释

## 分批实施计划

### 第一批：核心模块（6个文件）

| 序号 | 文件路径 | 文件功能 | 需要添加的注释 |
|------|---------|---------|--------------|
| 1 | `src/main.js` | Vue应用入口，创建SSR实例 | 文件头 + createSSRApp说明 + Pinia/uview注册说明 |
| 2 | `src/permission.js` | 路由权限控制 | 文件头 + 白名单机制 + 四种路由拦截说明 + 登录重定向逻辑 |
| 3 | `src/config.js` | 全局配置 | 文件头 + 各配置项含义（baseUrl/应用信息/高德Key） |
| 4 | `src/utils/auth.js` | Token管理 | 文件头 + Token存储策略 + 三个方法的业务含义 |
| 5 | `src/utils/request.js` | HTTP请求封装 | 文件头 + Token注入逻辑 + 状态码处理流程 + 参数序列化 + 401重登录机制 |
| 6 | `src/store/modules/user.js` | 用户状态管理 | 文件头 + state各字段说明 + 三个action的完整JSDoc + 头像处理逻辑 |

### 第二批：工具模块（7个文件）

| 序号 | 文件路径 | 文件功能 | 需要添加的注释 |
|------|---------|---------|--------------|
| 7 | `src/utils/common.js` | 通用工具函数 | 文件头 + tansParams嵌套对象序列化说明 + toast/showConfirm的JSDoc |
| 8 | `src/utils/validate.js` | 数据验证工具 | 文件头 + 四个验证函数的JSDoc |
| 9 | `src/utils/permission.js` | 权限校验工具 | 文件头 + 超级权限标识说明 + checkPermi/checkRole的JSDoc |
| 10 | `src/utils/storage.js` | 本地存储管理 | 文件头 + 聚合存储设计意图 + 允许的key列表说明 + 四个方法的JSDoc |
| 11 | `src/utils/upload.js` | 文件上传封装 | 文件头 + 上传流程说明 + Token注入 + 状态码处理 |
| 12 | `src/utils/errorCode.js` | 错误码映射 | 文件头 + 各错误码含义和使用场景 |
| 13 | `src/utils/constant.js` | 存储键名常量 | 文件头 + 各常量的用途说明 |

### 第三批：API接口模块（15个文件）

| 序号 | 文件路径 | 接口数量 | 需要添加的注释 |
|------|---------|---------|--------------|
| 14 | `src/api/login.js` | 5个 | 文件头 + 各接口JSDoc（含不携带Token的接口说明） |
| 15 | `src/api/home.js` | 3个 | 文件头 + 各接口JSDoc |
| 16 | `src/api/attendance.js` | 10个 | 文件头 + 各接口JSDoc（含上传接口说明） |
| 17 | `src/api/business/customer.js` | 6个 | 文件头 + 各接口JSDoc |
| 18 | `src/api/business/enterprise.js` | 5个 | 文件头 + 各接口JSDoc |
| 19 | `src/api/business/store.js` | 6个 | 文件头 + 各接口JSDoc |
| 20 | `src/api/business/salesOrder.js` | 7个 | 文件头 + 各接口JSDoc（含审核接口说明） |
| 21 | `src/api/business/operationRecord.js` | 4个 | 文件头 + 各接口JSDoc |
| 22 | `src/api/business/schedule.js` | 9个 | 文件头 + 各接口JSDoc（含批量接口说明） |
| 23 | `src/api/business/customerPackage.js` | 2个 | 文件头 + 各接口JSDoc |
| 24 | `src/api/business/employeeConfig.js` | 4个 | 文件头 + 各接口JSDoc |
| 25 | `src/api/business/archive.js` | 3个 | 文件头 + 各接口JSDoc |
| 26 | `src/api/business/repayment.js` | 6个 | 文件头 + 各接口JSDoc（含审核/取消接口说明） |
| 27 | `src/api/system/user.js` | 5个 | 文件头 + 各接口JSDoc（含上传头像接口说明） |
| 28 | `src/api/system/dict/data.js` | 1个 | 文件头 + 接口JSDoc |

## 执行策略

### 每个文件的注释步骤
1. 读取文件内容，理解代码逻辑
2. 添加文件头注释（模块名称、职责说明、核心功能）
3. 为每个函数/方法添加 JSDoc 注释（功能描述、参数说明、返回值）
4. 为复杂逻辑添加行内注释（步骤说明、算法解释）
5. 确保注释描述具体作用，不重复函数名
6. 不修改任何功能代码，仅添加注释

### API文件注释模板
```javascript
/**
 * @description 模块名称 - 模块简要说明
 * @description 详细描述模块管理的业务领域和提供的接口能力
 */
import request from '@/utils/request'

/**
 * 接口功能的具体业务描述
 * @param {类型} 参数名 - 参数含义
 * @returns {Promise} 接口返回的Promise对象
 */
export function apiFunction(params) {
  return request({ url: '/path', method: 'get', params })
}
```

## 预期成果

### 注释覆盖率提升
| 指标 | 改进前 | 改进后 |
|------|--------|--------|
| 文件头注释 | 0% (0/28) | 100% (28/28) |
| 函数级注释 | 0% (0/28) | 100% (28/28) |

### 预计工作量
- 第一批（核心模块）：6个文件
- 第二批（工具模块）：7个文件
- 第三批（API接口）：15个文件
- **总计：28个文件**

## 注意事项

1. **不修改功能代码** - 仅添加注释，不改变任何逻辑
2. **注释要具体** - 描述业务含义和具体作用，不重复函数名
3. **保持代码风格一致** - 使用统一的JSDoc格式
4. **分批执行** - 按优先级分批实施，每批完成后验证
5. **中文注释** - 所有注释使用中文，与项目风格一致
6. **uni-app特性说明** - 对uni-app特有API（如uni.request、uni.uploadFile、uni.addInterceptor）的使用场景进行说明
