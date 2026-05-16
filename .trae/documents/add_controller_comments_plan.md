# Webman 控制器备注添加计划

## 概述

为 webman 项目的所有控制器添加 PHPDoc 注释，包括：
- 类注释：说明控制器的功能模块
- 方法注释：说明接口功能、参数、返回值

## 控制器文件清单（共 43 个）

### 1. 根级控制器（3个）
| 文件 | 功能说明 |
|------|---------|
| `IndexController.php` | 首页控制器 |
| `SysLoginController.php` | 登录认证控制器 |
| `CaptchaController.php` | 验证码控制器 |
| `CommonController.php` | 通用接口控制器（文件上传/下载） |

### 2. 系统管理模块 system/（9个）
| 文件 | 功能说明 |
|------|---------|
| `SysUserController.php` | 用户管理 |
| `SysUserDetailController.php` | 用户详情 |
| `SysRoleController.php` | 角色管理 |
| `SysMenuController.php` | 菜单管理 |
| `SysDeptController.php` | 部门管理 |
| `SysPostController.php` | 岗位管理 |
| `SysDictTypeController.php` | 字典类型管理 |
| `SysDictDataController.php` | 字典数据管理 |
| `SysConfigController.php` | 系统配置管理 |
| `SysNoticeController.php` | 通知公告管理 |
| `HrUserSalaryController.php` | 员工薪资管理 |

### 3. 业务管理模块 business/（12个）
| 文件 | 功能说明 |
|------|---------|
| `BizCustomerController.php` | 客户管理 |
| `BizCustomerArchiveController.php` | 客户档案管理 |
| `BizCustomerPackageController.php` | 客户套餐管理 |
| `BizEnterpriseController.php` | 企业管理 |
| `BizStoreController.php` | 门店管理 |
| `BizEmployeeConfigController.php` | 员工配置管理 |
| `BizSalesOrderController.php` | 销售订单管理 |
| `BizPlanController.php` | 方案计划管理 |
| `BizShipmentController.php` | 出货管理 |
| `BizScheduleController.php` | 排班管理 |
| `BizAttendanceController.php` | 考勤管理 |
| `BizAttendanceConfigController.php` | 考勤配置管理 |
| `BizOperationRecordController.php` | 操作记录管理 |
| `BizRepaymentController.php` | 还款管理 |

### 4. 仓库管理模块 wms/（7个）
| 文件 | 功能说明 |
|------|---------|
| `BizProductController.php` | 产品管理 |
| `BizInventoryController.php` | 库存管理 |
| `BizStockInController.php` | 入库管理 |
| `BizStockOutController.php` | 出库管理 |
| `BizStockCheckController.php` | 库存盘点 |
| `BizSupplierController.php` | 供应商管理 |
| `BizWmsReportController.php` | 仓库报表 |

### 5. 系统监控模块 monitor/（7个）
| 文件 | 功能说明 |
|------|---------|
| `ServerController.php` | 服务器监控 |
| `CacheController.php` | 缓存监控 |
| `SysJobController.php` | 定时任务管理 |
| `SysJobLogController.php` | 任务日志 |
| `SysOperlogController.php` | 操作日志 |
| `SysLogininforController.php` | 登录日志 |
| `SysUserOnlineController.php` | 在线用户 |

### 6. 工具模块 tool/（1个）
| 文件 | 功能说明 |
|------|---------|
| `GenController.php` | 代码生成器 |

## 注释规范

### 类注释格式
```php
/**
 * [模块名称]控制器
 * 
 * 功能说明：[详细描述该控制器的主要功能]
 * 
 * @package app\controller\[模块]
 * @author  system
 * @since   1.0.0
 */
```

### 方法注释格式
```php
/**
 * [方法功能简述]
 *
 * @param  Request $request 请求对象
 * @return Response         返回说明
 * 
 * @example
 * POST /api/xxx
 * 参数: { "field": "value" }
 * 返回: { "code": 200, "data": {...} }
 */
```

## 实施步骤

### 步骤 1：根级控制器（4个文件）
1. `IndexController.php` - 首页、视图、JSON测试接口
2. `SysLoginController.php` - 登录、登出、获取用户信息、路由、解锁屏幕
3. `CaptchaController.php` - 获取验证码图片
4. `CommonController.php` - 文件上传、文件下载

### 步骤 2：系统管理模块（11个文件）
按功能重要性排序，逐个添加注释

### 步骤 3：业务管理模块（14个文件）
核心业务模块，详细注释每个接口

### 步骤 4：仓库管理模块（7个文件）
WMS 仓库管理相关接口

### 步骤 5：监控模块（7个文件）
系统监控和日志相关接口

### 步骤 6：工具模块（1个文件）
代码生成器接口

## 预期成果

完成后，每个控制器文件将包含：
- 清晰的类功能说明
- 每个方法的详细注释
- 参数和返回值说明
- 接口调用示例（关键接口）

## 风险评估

- **低风险**：仅添加注释，不修改代码逻辑
- **兼容性**：PHPDoc 注释不影响运行
