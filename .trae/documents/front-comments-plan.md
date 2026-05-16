# Front 项目注释完善计划

## 项目概况

Front 项目共有 **45+ 个 JS/Vue 文件**需要添加或完善注释，当前注释覆盖率仅约 13%（文件头）和 56%（函数级），整体质量偏低。

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
1. **描述具体作用**，不重复函数名（❌ `// 登录` → ✅ `// 使用用户名密码调用后端登录接口，成功后存储Token到Cookie`）
2. **说明业务含义**，不仅描述技术操作（❌ `// 设置token` → ✅ `// 将JWT认证令牌存储到Cookie，有效期1天`）
3. **复杂逻辑加步骤说明**（如防重复提交、路由转换、权限校验）
4. **安全模块标注注意事项**（如Token管理、加解密）

## 分批实施计划

### 第一批：核心模块（8个文件）

| 序号 | 文件路径 | 当前状态 | 需要添加的注释 |
|------|---------|---------|--------------|
| 1 | `src/main.js` | 仅有零星行内注释 | 文件头 + 各注册步骤说明 |
| 2 | `src/permission.js` | 少量行内注释 | 文件头 + 白名单机制 + 锁屏逻辑 + 动态路由加载流程 |
| 3 | `src/utils/auth.js` | 完全无注释 | 文件头 + Token存储策略说明 + 三个方法的业务含义 |
| 4 | `src/utils/request.js` | 部分行内注释 | 文件头 + 防重复提交机制 + 401处理流程 + download参数说明 |
| 5 | `src/utils/ruoyi.js` | 有文件头，函数注释中等 | 完善handleTree参数说明 + resetForm的this依赖说明 + tansParams逻辑说明 |
| 6 | `src/utils/jsencrypt.js` | 仅`// 加密``// 解密` | 文件头 + RSA加密用途 + 密钥管理安全说明 + 方法业务含义 |
| 7 | `src/store/modules/user.js` | 仅`// 登录`等 | 文件头 + 登录流程 + getInfo内部逻辑 + 退出流程 |
| 8 | `src/store/modules/permission.js` | 部分行内注释 | 文件头 + 路由转换流程 + filterAsyncRouter参数说明 + 权限过滤逻辑 |

### 第二批：工具和插件模块（12个文件）

| 序号 | 文件路径 | 当前状态 | 需要添加的注释 |
|------|---------|---------|--------------|
| 9 | `src/utils/index.js` | 部分有JSDoc | 文件头 + makeMap/beautifierConf/isNumberStr等缺少注释的函数 |
| 10 | `src/utils/validate.js` | 有JSDoc但部分缺描述 | 文件头 + validUsername/validURL等补充功能描述 |
| 11 | `src/utils/dict.js` | 仅一行注释 | 文件头 + 缓存策略说明 + useDict完整JSDoc |
| 12 | `src/utils/dynamicTitle.js` | 简短JSDoc | 文件头 + 标题拼接规则说明 |
| 13 | `src/utils/errorCode.js` | 无注释 | 文件头 + 错误码使用场景 |
| 14 | `src/utils/theme.js` | 部分行内注释 | 文件头 + 颜色混合算法说明 + 暗色模式柔化逻辑 |
| 15 | `src/utils/scroll-to.js` | 有JSDoc | 文件头 + 缓动函数说明 |
| 16 | `src/plugins/download.js` | 完全无注释 | 文件头 + 三种下载方式的区别和适用场景 + 方法参数说明 |
| 17 | `src/plugins/cache.js` | 仅分类注释 | 文件头 + session/local区别 + 各方法JSDoc |
| 18 | `src/plugins/modal.js` | 行内注释 | 文件头 + 各方法参数和返回值说明 |
| 19 | `src/plugins/tab.js` | 行内注释 | 文件头 + 各方法参数说明 |
| 20 | `src/plugins/auth.js` | 行内注释较好 | 文件头 + 完善JSDoc格式 |

### 第三批：Store 和路由模块（8个文件）

| 序号 | 文件路径 | 当前状态 | 需要添加的注释 |
|------|---------|---------|--------------|
| 21 | `src/store/modules/app.js` | 完全无注释 | 文件头 + state各字段说明 + mutation/action说明 |
| 22 | `src/store/modules/dict.js` | 仅重复函数名 | 文件头 + 缓存策略 + initDict空函数说明 |
| 23 | `src/store/modules/settings.js` | 行内注释 | 文件头 + state各配置项详细说明 |
| 24 | `src/store/modules/tagsView.js` | 几乎无注释 | 文件头 + 持久化策略 + 各action说明（约20个） |
| 25 | `src/store/modules/lock.js` | 行内注释 | 文件头 + localStorage key约定 |
| 26 | `src/store/index.js` | 无注释 | 文件头 |
| 27 | `src/router/index.js` | 注释优秀 | 保持现状，无需修改 |
| 28 | `src/settings.js` | 注释优秀 | 保持现状，无需修改 |

### 第四批：API 接口模块（14个文件）

| 序号 | 文件路径 | 当前状态 | 需要添加的注释 |
|------|---------|---------|--------------|
| 29 | `src/api/login.js` | 行内注释 | 文件头 + 各接口参数和返回值说明 |
| 30 | `src/api/menu.js` | 仅一行注释 | 文件头 + getRouters完整JSDoc |
| 31 | `src/api/business/customer.js` | 完全无注释 | 文件头 + 各接口JSDoc |
| 32 | `src/api/business/store.js` | 完全无注释 | 文件头 + 各接口JSDoc |
| 33 | `src/api/business/enterprise.js` | 行内注释 | 文件头 + 完善JSDoc |
| 34 | `src/api/business/attendance.js` | 完全无注释 | 文件头 + 各接口JSDoc（接口多且复杂） |
| 35 | `src/api/business/salesOrder.js` | 完全无注释 | 文件头 + 各接口JSDoc |
| 36 | `src/api/business/customerPackage.js` | 未检查 | 文件头 + 各接口JSDoc |
| 37 | `src/api/business/operationRecord.js` | 未检查 | 文件头 + 各接口JSDoc |
| 38 | `src/api/business/plan.js` | 未检查 | 文件头 + 各接口JSDoc |
| 39 | `src/api/business/repayment.js` | 未检查 | 文件头 + 各接口JSDoc |
| 40 | `src/api/business/schedule.js` | 未检查 | 文件头 + 各接口JSDoc |
| 41 | `src/api/business/shipment.js` | 未检查 | 文件头 + 各接口JSDoc |
| 42 | `src/api/system/user.js` | 行内注释 | 文件头 + 完善JSDoc（约20个接口） |

### 第五批：指令和剩余模块（7个文件）

| 序号 | 文件路径 | 当前状态 | 需要添加的注释 |
|------|---------|---------|--------------|
| 43 | `src/directive/index.js` | 无注释 | 文件头 + 各指令说明 |
| 44 | `src/directive/permission/hasPermi.js` | 有文件头 | 完善mounted内部逻辑注释 |
| 45 | `src/directive/permission/hasRole.js` | 有文件头 | 完善mounted内部逻辑注释 |
| 46 | `src/directive/common/copyText.js` | 有文件头 | 完善copyTextToClipboard的JSDoc |
| 47 | `src/utils/passwordRule.js` | 注释优秀 | 保持现状，无需修改 |
| 48 | `src/utils/pinyin.js` | 注释优秀 | 保持现状，无需修改 |
| 49 | `src/utils/permission.js` | 注释较好 | 保持现状，无需修改 |

### 第六批：剩余 API 和未检查文件（约20个文件）

| 目录 | 文件数 | 说明 |
|------|--------|------|
| `src/api/system/` | 8个 | dict/type.js, config.js, dept.js, menu.js, notice.js, post.js, role.js |
| `src/api/monitor/` | 7个 | cache.js, job.js, jobLog.js, logininfor.js, online.js, operlog.js, server.js |
| `src/api/tool/` | 1个 | gen.js |
| `src/api/wms/` | 7个 | inventory.js, product.js, report.js, stockCheck.js, stockIn.js, stockOut.js, supplier.js |
| `src/utils/generator/` | 6个 | config.js, css.js, drawingDefault.js, html.js, js.js, render.js |

## 执行策略

### 每个文件的注释步骤
1. 读取文件内容，理解代码逻辑
2. 添加文件头注释（模块名称、职责说明、核心功能）
3. 为每个函数/方法添加 JSDoc 注释（功能描述、参数说明、返回值）
4. 为复杂逻辑添加行内注释（步骤说明、算法解释）
5. 确保注释描述具体作用，不重复函数名
6. 不修改任何功能代码，仅添加注释

### 质量标准
- ✅ 文件头注释：模块名称 + 职责说明 + 核心功能概述
- ✅ 函数注释：JSDoc格式，包含 @param（类型+含义）和 @returns
- ✅ 行内注释：复杂逻辑的步骤说明，关键决策的原因
- ✅ 不重复函数名：`login()` → `使用用户名密码调用后端登录接口，成功后存储Token`
- ✅ 不影响功能：仅添加注释，不修改任何功能代码

## 预期成果

### 注释覆盖率提升
| 指标 | 改进前 | 改进后 |
|------|--------|--------|
| 文件头注释 | 13% (6/45) | 100% (49/49) |
| 函数级注释 | 56% (25/45) | 100% (49/49) |
| 高质量注释 | 18% (8/45) | 80%+ |

### 预计工作量
- 第一批（核心模块）：8个文件
- 第二批（工具和插件）：12个文件
- 第三批（Store和路由）：8个文件
- 第四批（API接口）：14个文件
- 第五批（指令和剩余）：7个文件
- 第六批（剩余API和generator）：约20个文件
- **总计：约69个文件**

## 注意事项

1. **不修改功能代码** - 仅添加注释，不改变任何逻辑
2. **注释要具体** - 描述业务含义和具体作用，不重复函数名
3. **保持代码风格一致** - 使用统一的JSDoc格式
4. **分批执行** - 按优先级分批实施，每批完成后验证
5. **中文注释** - 所有注释使用中文，与项目风格一致
