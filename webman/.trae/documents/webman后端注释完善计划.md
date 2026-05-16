# Webman 后端注释完善计划

## 一、问题概述

当前 webman 后端代码存在严重的注释质量问题，主要表现为：

1. **"执行xxx操作"模式**（约 80+ 处）：如 `// 执行audit操作`、`// 执行change Status操作`，仅将方法名用中文"执行...操作"包裹，零信息量
2. **"新增记录/修改记录/更新记录"模式**（约 80+ 处）：千篇一律的 CRUD 注释，未说明操作的是什么业务实体，也未说明附带业务逻辑
3. **"获取数据/设置数据"模式**（约 20+ 处）：极度模糊，任何方法都可以说是"获取数据"
4. **错误注释**（约 10+ 处）：如 `ServerController::getInfo` 注释为"根据ID获取详细信息"但实际不需要 ID；`updateSort` 注释为"更新记录"但实际是更新排序
5. **Model 类完全无注释**（53 个文件）：无类级文档、无关联方法注释、无访问器/修改器注释
6. **Service 私有方法几乎全部无注释**（约 30 处）
7. **Middleware、Common、Functions 文件几乎全部无注释**

## 二、注释规范

### 2.1 类级文档注释（PHPDoc）

每个类必须包含 PHPDoc 块，说明类的业务含义和核心职责：

```php
/**
 * 考勤配置管理控制器
 *
 * 提供考勤规则、班次配置的管理功能，支持按用户或部门配置考勤规则
 *
 * @package app\controller\business
 */
```

### 2.2 方法注释（单行注释）

使用 `//` 单行注释，放在方法上方一行，必须说明：
- **做什么**：具体业务操作
- **对什么**：操作的业务对象
- **关键副作用**：如自动扣减库存、刷新缓存等

```php
// 根据配置ID查询考勤配置详情
public function info(Request $request, $configId)

// 新增考勤配置，记录创建人
public function add(Request $request)

// 修改考勤配置，记录更新人
public function edit(Request $request)

// 批量删除考勤配置
public function remove(Request $request)

// 获取当前登录用户适用的考勤规则
public function getUserRule(Request $request)
```

### 2.3 禁止的注释模式

- ❌ `// 执行xxx操作` — 仅重复方法名
- ❌ `// 新增记录` / `// 修改记录` / `// 更新记录` — 未说明操作什么
- ❌ `// 获取数据` / `// 设置数据` — 极度模糊
- ❌ `// 根据ID获取详细信息` — 未说明获取什么信息
- ❌ `// 执行__construct操作` — 完全无意义

### 2.4 Model 关联方法注释

```php
// 关联考勤规则
public function rule()

// 关联所属部门
public function dept()
```

### 2.5 Model 访问器/修改器注释

```php
// 将 user_ids 字段从逗号分隔字符串转为整数数组
public function getUserIdsArrayAttribute()
```

## 三、实施步骤

### 第一批：Controller 层（38 个文件）

#### 1.1 business 模块（14 个文件）

| 文件 | 需修改注释数 | 关键注释示例 |
|------|-------------|-------------|
| BizAttendanceConfigController.php | 5 | `info` → "根据配置ID查询考勤配置详情"；`getUserRule` → "获取当前登录用户适用的考勤规则" |
| BizAttendanceController.php | 14 | 全部为"执行xxx操作"，需全部重写。如 `clockIn` → "上班打卡，记录打卡时间和位置"；`monthStats` → "获取指定月份的考勤统计" |
| BizCustomerArchiveController.php | 1 | `add` → "新增客户档案，记录服务历史" |
| BizCustomerPackageController.php | 1 | `getByCustomer` → "根据客户ID查询其关联的套餐列表" |
| BizEmployeeConfigController.php | 5 | `updateSchedulable` → "更新员工是否可排班状态"；`saveRestDates` → "保存员工休息日期配置"；`getRestDates` → "获取员工休息日期配置" |
| BizEnterpriseController.php | 3 | `add` → "新增企业信息"；`edit` → "修改企业信息"；`changeStatus` → "切换企业启用/停用状态" |
| BizOperationRecordController.php | 1 | `add` → "新增客户操作记录，自动扣减套餐数量并写入档案" |
| BizPlanController.php | 7 | `enterpriseList` → "查询企业列表用于方案关联"；`submitAudit` → "提交方案审核"；`audit` → "审核方案（通过或驳回）"；`changeStatus` → "切换方案状态" |
| BizRepaymentController.php | 4 | `owedPackages` → "查询客户欠款套餐列表"；`add` → "新增还款记录，自动更新套餐欠款金额"；`audit` → "审核还款记录"；`cancel` → "取消还款记录" |
| BizScheduleController.php | 7 | `calendar` → "按日历视图查询排班数据"；`dates` → "查询有排班的日期列表"；`employeeSchedule` → "按员工查询排班"；`enterpriseSchedule` → "按企业查询排班"；`addBatch` → "批量新增排班记录" |
| BizShipmentController.php | 6 | `audit` → "审核出货单"；`ship` → "确认发货，扣减库存并更新方案出货金额"；`confirmReceipt` → "确认收货" |
| BizStoreController.php | 2 | `add` → "新增门店信息"；`edit` → "修改门店信息" |
| BizCustomerController.php | 0 | 注释质量较好，无需修改 |
| BizSalesOrderController.php | 4 | `enterpriseAudit` → "企业审核销售订单"；`financeAudit` → "财务审核销售订单"；`add` → "新增销售订单，自动生成套餐和客户档案"；`edit` → "修改销售订单及订单明细" |

#### 1.2 system 模块（11 个文件）

| 文件 | 需修改注释数 | 关键注释示例 |
|------|-------------|-------------|
| HrUserSalaryController.php | 4 | `typeList` → "查询薪资类型列表"；`listByUser` → "按用户查询薪资记录" |
| SysConfigController.php | 5 | `getConfigKey` → "根据参数键名查询配置值"；`refreshCache` → "刷新系统配置缓存"；`add` → "新增系统参数配置" |
| SysDeptController.php | 5 | `excludeChild` → "查询排除指定部门及其子部门后的部门列表"；`treeselect` → "获取部门树形下拉选择数据"；`updateSort` → "更新部门排序" |
| SysDictDataController.php | 3 | `dictType` → "根据字典类型查询字典数据"；`add` → "新增字典数据，刷新字典缓存"；`edit` → "修改字典数据，刷新字典缓存" |
| SysDictTypeController.php | 4 | `refreshCache` → "刷新字典缓存"；`optionselect` → "获取字典类型下拉选择数据"；`add` → "新增字典类型"；`edit` → "修改字典类型" |
| SysMenuController.php | 5 | `treeselect` → "获取菜单树形下拉选择数据"；`roleMenuTreeselect` → "获取角色已分配的菜单树形数据"；`updateSort` → "更新菜单排序" |
| SysNoticeController.php | 7 | `listTop` → "查询最新通知公告列表"；`markRead` → "标记通知为已读"；`markReadAll` → "标记所有通知为已读"；`readUsersList` → "查询通知已读用户列表" |
| SysPostController.php | 2 | `add` → "新增岗位"；`edit` → "修改岗位" |
| SysRoleController.php | 12 | `dataScope` → "设置角色数据权限范围"；`changeStatus` → "切换角色启用/停用状态"；`optionselect` → "获取角色下拉选择数据"；`allocatedList` → "查询已分配该角色的用户列表"；`unallocatedList` → "查询未分配该角色的用户列表"；`cancelAuthUser` → "取消用户角色授权"；`cancelAuthUserAll` → "批量取消用户角色授权"；`selectAuthUserAll` → "批量授权用户角色"；`deptTree` → "获取部门树形数据用于数据权限配置" |
| SysUserController.php | 8 | `importData` → "从Excel导入用户数据"；`importTemplate` → "下载用户导入模板"；`resetPwd` → "重置用户密码"；`changeStatus` → "切换用户启用/停用状态"；`profile` → "获取当前登录用户个人资料"；`updateProfile` → "修改当前登录用户个人资料"；`updatePwd` → "修改当前登录用户密码（需验证旧密码）"；`avatar` → "上传并更新用户头像"；`authRole` → "查询用户已授权角色和可选角色"；`insertAuthRole` → "授权用户角色"；`deptTree` → "获取部门树形下拉选择数据" |
| SysUserDetailController.php | 2 | `add` → "新增用户详情"；`edit` → "修改用户详情" |

#### 1.3 monitor 模块（6 个文件）

| 文件 | 需修改注释数 | 关键注释示例 |
|------|-------------|-------------|
| CacheController.php | 5 | `getInfo` → "获取Redis缓存监控信息（内存、命令统计等）"；`getNames` → "获取Redis缓存键名分组列表"；`getKeys` → "根据缓存名前缀获取键列表"；`getValue` → "根据缓存键获取缓存值"；`clearCacheName` → "清空指定前缀的所有缓存"；`clearCacheKey` → "删除指定缓存键"；`clearCacheAll` → "清空所有Redis缓存" |
| ServerController.php | 12 | `getInfo` → "获取服务器监控信息（CPU、内存、磁盘、运行时间）"；`getCpuInfo` → "获取CPU使用率信息"；`getMemInfo` → "获取内存使用信息"；`getWebmanInfo` → "获取Webman运行信息" 等 |
| SysJobController.php | 3 | `changeStatus` → "切换定时任务启用/暂停状态"；`run` → "立即执行一次定时任务" |
| SysJobLogController.php | 1 | `clean` → "清空所有任务执行日志" |
| SysLogininforController.php | 2 | `clean` → "清空所有登录日志"；`unlock` → "解锁被锁定的用户账号" |
| SysOperlogController.php | 1 | `clean` → "清空所有操作日志" |
| SysUserOnlineController.php | 1 | `forceLogout` → "强制下线指定在线用户" |

#### 1.4 wms 模块（6 个文件）

| 文件 | 需修改注释数 | 关键注释示例 |
|------|-------------|-------------|
| BizInventoryController.php | 2 | `warn` → "查询库存预警列表（低于预警值的产品）"；`getInfo` → "根据产品ID查询库存详情" |
| BizProductController.php | 2 | `add` → "新增产品，自动初始化库存记录"；`edit` → "修改产品信息，同步更新库存预警数量" |
| BizStockCheckController.php | 4 | `confirm` → "确认盘点，自动调整库存至盘点数量"；`loadInventory` → "加载当前库存数据用于新建盘点" |
| BizStockInController.php | 4 | `confirm` → "确认入库，自动增加库存数量"；`cancelConfirm` → "取消入库确认，自动回退库存数量" |
| BizStockOutController.php | 4 | `confirm` → "确认出库，自动扣减库存数量并校验库存不足"；`cancelConfirm` → "取消出库确认，自动回退库存数量" |
| BizSupplierController.php | 2 | `add` → "新增供应商"；`edit` → "修改供应商信息" |
| BizWmsReportController.php | 4 | `stockInSummary` → "入库汇总统计"；`stockOutSummary` → "出库汇总统计"；`inventoryTurnover` → "库存周转率统计"；`productFlow` → "产品出入库流水统计" |

#### 1.5 tool 模块 + 根控制器（4 个文件）

| 文件 | 需修改注释数 | 关键注释示例 |
|------|-------------|-------------|
| GenController.php | 8 | `dbList` → "查询数据库表列表用于代码生成导入"；`importTable` → "导入数据库表到代码生成器"；`preview` → "预览代码生成结果"；`synchDb` → "同步数据库表结构到代码生成器"；`download` → "下载生成的代码压缩包"；`batchGenCode` → "批量生成代码并下载" |
| CaptchaController.php | 1 | `captchaImage` → "生成图形验证码（数学运算），存入Redis" |
| CommonController.php | 2 | `upload` → "通用文件上传（支持本地存储和腾讯云COS）"；`downloads` → "通用文件下载" |
| IndexController.php | 3 | `index` → "系统首页接口"；`view` → "渲染首页视图"；`json` → "返回JSON测试数据" |
| SysLoginController.php | 5 | `login` → "用户登录认证，返回JWT令牌"；`logout` → "用户登出，清除Token"；`getInfo` → "获取当前登录用户信息及权限"；`getRouters` → "获取当前登录用户的路由菜单"；`unlockscreen` → "解锁屏幕" |

### 第二批：Service 层（49 个文件）

#### 2.1 业务服务（22 个文件）

每个服务文件需要：
1. 保留现有类级文档注释（质量较好）
2. 重写所有模糊方法注释
3. 补充私有方法注释
4. 标注关键业务副作用

重点文件及注释示例：

| 文件 | 需修改/新增注释数 | 关键注释示例 |
|------|-------------------|-------------|
| BizAttendanceRecordService.php | 12 | `clock` → "执行考勤打卡，判断上班/下班类型并更新记录"；`determineClockType` → "判断打卡类型（上班/下班）"；`calculateAttendanceStatus` → "计算考勤状态（正常/迟到/早退/缺卡）"；`calculateDistance` → "计算用户位置与考勤点之间的距离" |
| BizSalesOrderService.php | 8 | `insertOrder` → "新增销售订单，自动生成客户套餐和档案"；`generatePackage` → "根据订单明细生成客户套餐"；`enterpriseAudit` → "企业审核销售订单"；`financeAudit` → "财务审核销售订单" |
| BizShipmentService.php | 8 | `audit` → "审核出货单"；`ship` → "确认发货，扣减库存并更新方案出货金额"；`confirmReceipt` → "确认收货，扣减库存和更新方案出货金额" |
| BizStockInService.php | 6 | `confirmStockIn` → "确认入库，自动增加库存数量"；`cancelConfirmStockIn` → "取消入库确认，自动回退库存数量" |
| BizStockOutService.php | 6 | `confirmStockOut` → "确认出库，自动扣减库存数量并校验库存不足"；`cancelConfirmStockOut` → "取消出库确认，自动回退库存数量" |
| BizStockCheckService.php | 6 | `confirmStockCheck` → "确认盘点，自动调整库存至盘点数量"；`loadInventoryData` → "加载当前库存数据用于新建盘点" |
| BizPlanService.php | 10 | `submitAudit` → "提交方案审核"；`audit` → "审核方案（通过或驳回）"；`syncPlanItems` → "同步方案明细（新增/更新/删除）"；`updateShippedAmount` → "更新方案已出货金额" |
| BizRepaymentService.php | 10 | `insertRepayment` → "新增还款记录，自动审核并更新套餐欠款金额"；`createRepaymentOrder` → "创建还款关联订单"；`updatePackageOwedAmount` → "更新套餐欠款金额" |
| BizCustomerArchiveService.php | 5 | `insertArchiveFromOrder` → "根据销售订单创建客户档案"；`insertArchiveFromOperation` → "根据操作记录创建或合并客户档案"；`insertArchiveFromRepayment` → "根据还款记录创建客户档案" |
| BizEmployeeConfigService.php | 8 | `updateSchedulable` → "更新员工是否可排班状态"；`updateRestDates` → "更新员工休息日期配置"；`getRestDatesByUserId` → "获取员工休息日期配置"；`isRestDate` → "判断指定日期是否为员工休息日" |
| BizEnterpriseService.php | 6 | `getPinyin` → "将中文名称转为拼音首字母用于索引"；`updateEnterpriseStatus` → "更新企业启用/停用状态" |
| BizInventoryService.php | 3 | `selectWarnList` → "查询库存预警列表（低于预警值的产品）"；`selectInventoryByProductId` → "根据产品ID查询库存记录" |
| BizScheduleService.php | 8 | `selectScheduleByDateRange` → "按日期范围查询排班记录"；`selectScheduleDates` → "查询有排班的日期列表"；`insertScheduleBatch` → "批量新增排班记录"；`selectEmployeeSchedule` → "按员工查询排班数据"；`selectEnterpriseSchedule` → "按企业查询排班数据" |
| BizProductService.php | 4 | `insertProduct` → "新增产品，自动初始化库存记录"；`updateProduct` → "修改产品信息，同步更新库存预警数量"；`deleteProductByIds` → "批量删除产品及关联库存记录" |
| BizCustomerPackageService.php | 2 | `selectPackagesByCustomer` → "根据客户ID查询关联套餐列表" |
| BizStoreService.php | 3 | `selectStoreForSearch` → "查询门店下拉搜索数据"；`insertStore` → "新增门店，自动填充企业名称"；`updateStore` → "修改门店信息，自动填充企业名称" |
| BizSupplierService.php | 2 | `searchSupplier` → "搜索供应商（支持关键字模糊匹配）" |
| BizWmsReportService.php | 4 | `stockInSummary` → "入库汇总统计（按产品/供应商/日期维度）"；`stockOutSummary` → "出库汇总统计"；`inventoryTurnover` → "库存周转率统计"；`productFlow` → "产品出入库流水统计" |
| BizAttendanceConfigService.php | 3 | `getUserRule` → "获取用户适用的考勤规则（优先用户配置，其次部门配置）" |
| BizAttendanceRuleService.php | 2 | `getActiveRule` → "获取启用的默认考勤规则" |
| BizOperationRecordService.php | 3 | `getRecordDetailById` → "获取操作记录详情（含批次信息）"；`insertRecord` → "新增操作记录，自动扣减套餐数量并写入档案" |

#### 2.2 系统服务（27 个文件）

| 文件 | 需修改/新增注释数 | 关键注释示例 |
|------|-------------------|-------------|
| SysUserService.php | 12 | `insertUser` → "新增用户，加密密码并保存角色岗位关联"；`updateUser` → "修改用户，重建角色岗位关联"；`deleteUserByIds` → "批量删除用户及关联数据"；`resetPwd` → "重置用户密码"；`checkUserNameUnique` → "校验用户名唯一性"；`checkPhoneUnique` → "校验手机号唯一性"；`checkEmailUnique` → "校验邮箱唯一性"；`updateUserProfile` → "更新用户个人资料"；`importUser` → "从Excel导入用户数据" |
| SysRoleService.php | 16 | `authDataScope` → "设置角色数据权限范围"；`selectAllRoles` → "查询所有角色列表"；`insertRole` → "新增角色，保存角色菜单关联"；`updateRole` → "修改角色，重建角色菜单关联"；`deleteRoleByIds` → "批量删除角色及关联数据"；`selectRolePermissionByUserId` → "根据用户ID查询角色权限标识"；`checkRoleNameUnique` → "校验角色名称唯一性"；`checkRoleKeyUnique` → "校验角色权限字符唯一性"；`allocatedUserList` → "查询已分配该角色的用户列表"；`unallocatedUserList` → "查询未分配该角色的用户列表"；`cancelAuthUser` → "取消用户角色授权"；`cancelAuthUserAll` → "批量取消用户角色授权"；`selectAuthUserAll` → "批量授权用户角色"；`insertRoleMenu` → "保存角色菜单关联数据"；`insertRoleDept` → "保存角色部门关联数据" |
| SysMenuService.php | 14 | `selectMenuTreeByUserId` → "根据用户ID查询菜单树"；`buildMenus` → "构建前端路由菜单数据"；`getChildPerms` → "递归获取子权限列表"；`buildMenuTree` → "构建菜单树形结构"；`getRouteName` → "生成路由名称"；`isExternalLink` → "判断是否为外链"；`getRouterPath` → "获取路由路径"；`getComponent` → "获取组件路径"；`isMenuFrame` → "判断是否为菜单框架页"；`isInnerLink` → "判断是否为内链"；`innerLinkReplaceEach` → "内链路径替换处理" |
| SysDeptService.php | 8 | `insertDept` → "新增部门，自动计算祖级列表"；`updateDept` → "修改部门，自动更新祖级列表"；`deleteDeptById` → "删除部门（需无子部门且无关联用户）"；`deptTreeSelect` → "构建部门树形下拉选择数据"；`excludeChildDeptList` → "查询排除指定部门及其子部门后的部门列表"；`buildDeptTree` → "构建部门树形结构"；`buildDeptTreeSelect` → "构建部门树形下拉选择数据" |
| SysDictTypeService.php | 8 | `selectDictDataByType` → "根据字典类型查询字典数据（含Redis缓存）"；`optionselect` → "获取字典类型下拉选择数据"；`resetDictCache` → "重置字典缓存"；`getDictLabel` → "根据字典值获取标签"；`getDictValue` → "根据字典标签获取值"；`updateDictType` → "修改字典类型，同步更新字典数据的type字段"；`deleteDictTypeByIds` → "批量删除字典类型及关联字典数据" |
| SysDictDataService.php | 4 | `insertDictData` → "新增字典数据，刷新字典缓存"；`updateDictData` → "修改字典数据，刷新字典缓存"；`deleteDictDataByIds` → "批量删除字典数据，刷新字典缓存" |
| SysConfigService.php | 9 | `selectConfigByKey` → "根据参数键名查询配置值（含Redis缓存）"；`insertConfig` → "新增系统配置，写入Redis缓存"；`updateConfig` → "修改系统配置，更新Redis缓存"；`deleteConfigByIds` → "批量删除系统配置，清理Redis缓存"；`resetConfigCache` → "重置系统配置缓存"；`selectCaptchaEnabled` → "查询验证码是否启用" |
| SysNoticeService.php | 7 | `listTop` → "查询最新通知公告列表"；`markRead` → "标记通知为已读"；`markReadAll` → "标记当前用户所有通知为已读"；`readUsersList` → "查询通知已读用户列表"；`deleteNoticeByIds` → "批量删除通知及关联阅读记录" |
| SysPermissionService.php | 7 | `getRolePermission` → "获取用户角色权限标识集合"；`getMenuPermission` → "获取用户菜单权限标识集合"；`getMenuPermsByRoleId` → "根据角色ID获取菜单权限标识" |
| TokenService.php | 10 | `createToken` → "创建JWT令牌并存储登录信息到Redis"；`getLoginUser` → "从请求中获取当前登录用户信息"；`verifyToken` → "验证Token有效性并自动续期"；`refreshToken` → "刷新Token过期时间"；`removeToken` → "删除Token（用于登出）"；`getToken` → "从请求头提取Bearer Token"；`getUuidFromToken` → "从JWT中提取UUID标识"；`setUserAgent` → "设置用户代理信息（IP、浏览器、OS）"；`refreshPermissionByRoleId` → "根据角色ID刷新权限缓存" |
| PasswordService.php | 4 | `encrypt` → "使用bcrypt加密密码"；`verify` → "验证密码是否匹配"；`validate` → "密码策略校验（含错误次数锁定机制）"；`isDefaultPassword` → "判断是否为默认初始密码" |
| RedisService.php | 16 | 所有方法均需补充注释 |
| CaptchaService.php | 4 | `getCaptcha` → "生成数学运算验证码图片，存入Redis"；`validateCaptcha` → "校验验证码是否正确"；`generateMathCaptcha` → "生成数学运算验证码表达式"；`fastUUID` → "生成快速UUID用于验证码唯一标识" |
| CosService.php | 7 | `upload` → "上传本地文件到腾讯云COS"；`uploadContent` → "上传内容字符串到COS"；`delete` → "删除COS上的文件"；`getSignedUrl` → "获取预签名URL"；`getUrl` → "获取文件访问URL"；`isConfigured` → "检查COS是否已配置"；`getClient` → "获取COS客户端单例" |
| DataScopeService.php | 1 | `applyDataScope` → "根据角色数据权限过滤查询（全部/自定义/本部门/本部门及以下/仅本人）" |
| GenTableService.php | 10 | `importGenTable` → "导入数据库表到代码生成器"；`synchDb` → "同步数据库表结构到代码生成器"；`previewCode` → "预览代码生成结果"；`generateCode` → "生成代码并返回文件列表"；`downloadCode` → "打包下载生成的代码"；`prepareContext` → "准备代码生成模板上下文数据"；`getTemplateList` → "获取代码生成模板列表"；`getFileName` → "计算生成文件的输出路径" |
| HrUserSalaryService.php | 6 | `selectSalaryTypeList` → "查询薪资类型列表"；`selectUserSalaryList` → "查询用户薪资记录列表"；`saveTiers` → "保存薪资层级配置" |
| IpService.php | 1 | `getLocation` → "根据IP地址查询地理位置信息" |
| UserAgentService.php | 2 | `getBrowser` → "从User-Agent解析浏览器类型"；`getOS` → "从User-Agent解析操作系统" |
| SysJobService.php | 4 | `changeStatus` → "切换定时任务启用/暂停状态"；`run` → "立即执行一次定时任务" |
| SysJobLogService.php | 1 | `cleanJobLog` → "清空所有任务执行日志" |
| SysLogininforService.php | 3 | `insertLogininfor` → "记录登录日志"；`cleanLogininfor` → "清空所有登录日志"；`unlock` → "解锁被锁定的用户账号" |
| SysOperLogService.php | 2 | `insertOperLog` → "记录操作日志"；`cleanOperLog` → "清空所有操作日志" |
| SysPostService.php | 3 | `selectPostAll` → "查询所有岗位列表"；`insertPost` → "新增岗位"；`updatePost` → "修改岗位信息" |
| SysUserDetailService.php | 4 | `selectDetailByUserId` → "根据用户ID查询个人详情"；`insertDetail` → "新增用户详情"；`updateDetail` → "修改用户详情（不存在时自动新增）"；`deleteDetailByUserId` → "根据用户ID删除个人详情" |
| SysUserOnlineService.php | 2 | `selectOnlineList` → "查询在线用户列表"；`forceLogout` → "强制下线指定在线用户" |

### 第三批：Model 层（53 个文件）

每个 Model 文件需要：
1. 新增类级 PHPDoc 文档注释，说明业务含义和对应数据表
2. 为所有关联方法添加注释
3. 为访问器/修改器添加注释
4. 为其他自定义方法添加注释

| 文件 | 需新增注释 |
|------|-----------|
| BizAttendanceClock.php | 类注释 + `record()` |
| BizAttendanceConfig.php | 类注释 + `rule()` + `dept()` + `getUserIdsArrayAttribute()` + `getUsersAttribute()` |
| BizAttendanceRecord.php | 类注释 |
| BizAttendanceRule.php | 类注释 |
| BizCustomer.php | 类注释 |
| BizCustomerArchive.php | 类注释 |
| BizCustomerPackage.php | 类注释 + `items()` |
| BizEmployeeConfig.php | 类注释 |
| BizEnterprise.php | 类注释 + `plans()` |
| BizInventory.php | 类注释 + `product()` |
| BizOperationRecord.php | 类注释 |
| BizOrderItem.php | 类注释 |
| BizPackageItem.php | 类注释 |
| BizPlan.php | 类注释 + `enterprise()` + `items()` + `shipments()` |
| BizPlanItem.php | 类注释 + `plan()` + `product()` |
| BizProduct.php | 类注释 + `supplier()` + `inventory()` |
| BizRepaymentRecord.php | 类注释 + `customer()` + `package()` + `order()` |
| BizSalesOrder.php | 类注释 + `items()` |
| BizSchedule.php | 类注释 |
| BizShipment.php | 类注释 + `plan()` + `items()` + `enterprise()` |
| BizShipmentItem.php | 类注释 + `shipment()` + `product()` |
| BizStockCheck.php | 类注释 + `items()` |
| BizStockCheckItem.php | 类注释 + `stockCheck()` + `product()` |
| BizStockIn.php | 类注释 + `items()` + `supplier()` |
| BizStockInItem.php | 类注释 + `stockIn()` + `product()` |
| BizStockOut.php | 类注释 + `items()` |
| BizStockOutItem.php | 类注释 + `stockOut()` + `product()` |
| BizStore.php | 类注释 |
| BizSupplier.php | 类注释 |
| GenTable.php | 类注释 + `columns()` |
| GenTableColumn.php | 类注释 |
| HrSalaryTier.php | 类注释 + `userSalary()` |
| HrSalaryType.php | 类注释 + `userSalaries()` |
| HrUserSalary.php | 类注释 + `user()` + `salaryType()` + `tiers()` + `getTierConfigAttribute()` + `setTierConfigAttribute()` |
| SysConfig.php | 类注释 |
| SysDept.php | 类注释 + `children()` + `parent()` |
| SysDictData.php | 类注释 |
| SysDictType.php | 类注释 + `dictData()` |
| SysJob.php | 类注释 |
| SysJobLog.php | 类注释 |
| SysLogininfor.php | 类注释 |
| SysMenu.php | 类注释 + `children()` + `parent()` |
| SysNotice.php | 类注释 + `readUsers()` |
| SysNoticeRead.php | 类注释 |
| SysOperLog.php | 类注释 |
| SysPost.php | 类注释 |
| SysRole.php | 类注释 + `menus()` + `depts()` + `users()` + `isAdmin()` |
| SysRoleDept.php | 类注释 |
| SysRoleMenu.php | 类注释 |
| SysUser.php | 类注释 + `getExcelFields()` + `dept()` + `roles()` + `posts()` + `isAdmin()` + `detail()` + `salaries()` |
| SysUserDetail.php | 类注释 + `user()` |
| SysUserPost.php | 类注释 |
| SysUserRole.php | 类注释 |

### 第四批：Middleware + Common + Process + Functions（16 个文件）

| 文件 | 需新增/修改注释 |
|------|----------------|
| AuthMiddleware.php | 类注释 + `process()` 注释（Token验证、白名单放行、登录用户注入） |
| CorsMiddleware.php | 类注释 + `process()` 注释（CORS跨域处理、OPTIONS预检） |
| LogMiddleware.php | 类注释 + `process()` 注释 + `getTitle()` 注释 + `getBusinessType()` 注释 |
| StaticFile.php | 重写类注释 + `process()` 注释 |
| AjaxResult.php | 类注释 + 全部8个方法注释 |
| Constants.php | 类注释 + 常量分组注释 |
| ExcelUtil.php | 类注释 + 全部17个方法注释 |
| GenConstants.php | 类注释 + 常量注释 + `arraysContains()` 注释 |
| GenTemplateEngine.php | 类注释 + 全部12个方法注释 |
| GenUtils.php | 类注释 + 全部14个方法注释 |
| Helpers.php | 类注释 + 全部3个方法注释 |
| LoginUser.php | 类注释 + 全部4个方法注释 + 属性注释 |
| TableDataInfo.php | 类注释 + 全部3个方法注释 |
| Http.php | 类注释 |
| Monitor.php | 优化现有英文注释为中文 |
| functions.php | 文件注释 + 全部3个函数注释 |

## 四、执行顺序

1. **Controller 层**（38 个文件）— 最直接面向开发者，注释改善效果最明显
2. **Service 层**（49 个文件）— 包含核心业务逻辑，注释改善价值最高
3. **Model 层**（53 个文件）— 数据模型定义，类注释和关联方法注释
4. **Middleware + Common + Process + Functions**（16 个文件）— 基础设施层

## 五、预计修改量

| 层级 | 文件数 | 修改/新增注释数（估计） |
|------|--------|------------------------|
| Controller | 38 | ~200 处 |
| Service | 49 | ~300 处 |
| Model | 53 | ~120 处 |
| 其他 | 16 | ~80 处 |
| **合计** | **156** | **~700 处** |
