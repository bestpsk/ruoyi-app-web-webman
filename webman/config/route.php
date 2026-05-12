<?php

use Webman\Route;

Route::post('/login', [app\controller\SysLoginController::class, 'login']);
Route::post('/register', [app\controller\SysLoginController::class, 'login']);
Route::get('/captchaImage', [app\controller\CaptchaController::class, 'captchaImage']);
Route::post('/logout', [app\controller\SysLoginController::class, 'logout']);
Route::get('/getInfo', [app\controller\SysLoginController::class, 'getInfo']);
Route::get('/getRouters', [app\controller\SysLoginController::class, 'getRouters']);
Route::post('/unlockscreen', [app\controller\SysLoginController::class, 'unlockscreen']);
Route::post('/common/upload', [app\controller\CommonController::class, 'upload']);
Route::get('/common/download', [app\controller\CommonController::class, 'downloads']);

Route::get('/system/user/list', [app\controller\system\SysUserController::class, 'list']);
Route::post('/system/user/export', [app\controller\system\SysUserController::class, 'export']);
Route::post('/system/user/importData', [app\controller\system\SysUserController::class, 'importData']);
Route::post('/system/user/importTemplate', [app\controller\system\SysUserController::class, 'importTemplate']);
Route::get('/system/user/profile', [app\controller\system\SysUserController::class, 'profile']);
Route::put('/system/user/profile', [app\controller\system\SysUserController::class, 'updateProfile']);
Route::put('/system/user/profile/updatePwd', [app\controller\system\SysUserController::class, 'updatePwd']);
Route::post('/system/user/profile/avatar', [app\controller\system\SysUserController::class, 'avatar']);
Route::get('/system/user/deptTree', [app\controller\system\SysUserController::class, 'deptTree']);
Route::put('/system/user/resetPwd', [app\controller\system\SysUserController::class, 'resetPwd']);
Route::put('/system/user/changeStatus', [app\controller\system\SysUserController::class, 'changeStatus']);
Route::put('/system/user/authRole', [app\controller\system\SysUserController::class, 'insertAuthRole']);
Route::get('/system/user/authRole/{userId}', [app\controller\system\SysUserController::class, 'authRole']);
Route::post('/system/user', [app\controller\system\SysUserController::class, 'add']);
Route::put('/system/user', [app\controller\system\SysUserController::class, 'edit']);
Route::delete('/system/user/{userIds}', [app\controller\system\SysUserController::class, 'remove']);
Route::get('/system/user/', [app\controller\system\SysUserController::class, 'getInfo']);
Route::get('/system/user/{userId}', [app\controller\system\SysUserController::class, 'getInfo']);

Route::get('/system/role/list', [app\controller\system\SysRoleController::class, 'list']);
Route::get('/system/role/optionselect', [app\controller\system\SysRoleController::class, 'optionselect']);
Route::get('/system/role/authUser/allocatedList', [app\controller\system\SysRoleController::class, 'allocatedList']);
Route::get('/system/role/authUser/unallocatedList', [app\controller\system\SysRoleController::class, 'unallocatedList']);
Route::put('/system/role/authUser/cancel', [app\controller\system\SysRoleController::class, 'cancelAuthUser']);
Route::put('/system/role/authUser/cancelAll', [app\controller\system\SysRoleController::class, 'cancelAuthUserAll']);
Route::put('/system/role/authUser/selectAll', [app\controller\system\SysRoleController::class, 'selectAuthUserAll']);
Route::put('/system/role/dataScope', [app\controller\system\SysRoleController::class, 'dataScope']);
Route::put('/system/role/changeStatus', [app\controller\system\SysRoleController::class, 'changeStatus']);
Route::get('/system/role/deptTree/{roleId}', [app\controller\system\SysRoleController::class, 'deptTree']);
Route::post('/system/role', [app\controller\system\SysRoleController::class, 'add']);
Route::put('/system/role', [app\controller\system\SysRoleController::class, 'edit']);
Route::delete('/system/role/{roleIds}', [app\controller\system\SysRoleController::class, 'remove']);
Route::get('/system/role/{roleId}', [app\controller\system\SysRoleController::class, 'getInfo']);

Route::get('/system/menu/list', [app\controller\system\SysMenuController::class, 'list']);
Route::get('/system/menu/treeselect', [app\controller\system\SysMenuController::class, 'treeselect']);
Route::put('/system/menu/updateSort', [app\controller\system\SysMenuController::class, 'updateSort']);
Route::get('/system/menu/roleMenuTreeselect/{roleId}', [app\controller\system\SysMenuController::class, 'roleMenuTreeselect']);
Route::post('/system/menu', [app\controller\system\SysMenuController::class, 'add']);
Route::put('/system/menu', [app\controller\system\SysMenuController::class, 'edit']);
Route::delete('/system/menu/{menuId}', [app\controller\system\SysMenuController::class, 'remove']);
Route::get('/system/menu/{menuId}', [app\controller\system\SysMenuController::class, 'getInfo']);

Route::get('/system/dept/list/exclude/{deptId}', [app\controller\system\SysDeptController::class, 'excludeChild']);
Route::get('/system/dept/list', [app\controller\system\SysDeptController::class, 'list']);
Route::get('/system/dept/treeselect', [app\controller\system\SysDeptController::class, 'treeselect']);
Route::put('/system/dept/updateSort', [app\controller\system\SysDeptController::class, 'updateSort']);
Route::post('/system/dept', [app\controller\system\SysDeptController::class, 'add']);
Route::put('/system/dept', [app\controller\system\SysDeptController::class, 'edit']);
Route::delete('/system/dept/{deptId}', [app\controller\system\SysDeptController::class, 'remove']);
Route::get('/system/dept/{deptId}', [app\controller\system\SysDeptController::class, 'getInfo']);

Route::get('/system/post/list', [app\controller\system\SysPostController::class, 'list']);
Route::post('/system/post', [app\controller\system\SysPostController::class, 'add']);
Route::put('/system/post', [app\controller\system\SysPostController::class, 'edit']);
Route::delete('/system/post/{postIds}', [app\controller\system\SysPostController::class, 'remove']);
Route::get('/system/post/{postId}', [app\controller\system\SysPostController::class, 'getInfo']);

Route::get('/system/dict/type/list', [app\controller\system\SysDictTypeController::class, 'list']);
Route::get('/system/dict/type/optionselect', [app\controller\system\SysDictTypeController::class, 'optionselect']);
Route::delete('/system/dict/type/refreshCache', [app\controller\system\SysDictTypeController::class, 'refreshCache']);
Route::post('/system/dict/type', [app\controller\system\SysDictTypeController::class, 'add']);
Route::put('/system/dict/type', [app\controller\system\SysDictTypeController::class, 'edit']);
Route::delete('/system/dict/type/{dictIds}', [app\controller\system\SysDictTypeController::class, 'remove']);
Route::get('/system/dict/type/{dictId}', [app\controller\system\SysDictTypeController::class, 'getInfo']);

Route::get('/system/dict/data/list', [app\controller\system\SysDictDataController::class, 'list']);
Route::get('/system/dict/data/type/{dictType}', [app\controller\system\SysDictDataController::class, 'dictType']);
Route::post('/system/dict/data', [app\controller\system\SysDictDataController::class, 'add']);
Route::put('/system/dict/data', [app\controller\system\SysDictDataController::class, 'edit']);
Route::delete('/system/dict/data/{dictCodes}', [app\controller\system\SysDictDataController::class, 'remove']);
Route::get('/system/dict/data/{dictCode}', [app\controller\system\SysDictDataController::class, 'getInfo']);

Route::get('/system/config/list', [app\controller\system\SysConfigController::class, 'list']);
Route::get('/system/config/configKey/{configKey}', [app\controller\system\SysConfigController::class, 'getConfigKey']);
Route::delete('/system/config/refreshCache', [app\controller\system\SysConfigController::class, 'refreshCache']);
Route::post('/system/config', [app\controller\system\SysConfigController::class, 'add']);
Route::put('/system/config', [app\controller\system\SysConfigController::class, 'edit']);
Route::delete('/system/config/{configIds}', [app\controller\system\SysConfigController::class, 'remove']);
Route::get('/system/config/{configId}', [app\controller\system\SysConfigController::class, 'getInfo']);

Route::get('/system/notice/list', [app\controller\system\SysNoticeController::class, 'list']);
Route::get('/system/notice/listTop', [app\controller\system\SysNoticeController::class, 'listTop']);
Route::post('/system/notice/markRead', [app\controller\system\SysNoticeController::class, 'markRead']);
Route::post('/system/notice/markReadAll', [app\controller\system\SysNoticeController::class, 'markReadAll']);
Route::get('/system/notice/readUsers/list', [app\controller\system\SysNoticeController::class, 'readUsersList']);
Route::post('/system/notice', [app\controller\system\SysNoticeController::class, 'add']);
Route::put('/system/notice', [app\controller\system\SysNoticeController::class, 'edit']);
Route::delete('/system/notice/{noticeIds}', [app\controller\system\SysNoticeController::class, 'remove']);
Route::get('/system/notice/{noticeId}', [app\controller\system\SysNoticeController::class, 'getInfo']);

Route::get('/monitor/online/list', [app\controller\monitor\SysUserOnlineController::class, 'list']);
Route::delete('/monitor/online/{tokenId}', [app\controller\monitor\SysUserOnlineController::class, 'forceLogout']);

Route::get('/monitor/operlog/list', [app\controller\monitor\SysOperlogController::class, 'list']);
Route::delete('/monitor/operlog/clean', [app\controller\monitor\SysOperlogController::class, 'clean']);
Route::delete('/monitor/operlog/{operIds}', [app\controller\monitor\SysOperlogController::class, 'remove']);

Route::get('/monitor/logininfor/list', [app\controller\monitor\SysLogininforController::class, 'list']);
Route::get('/monitor/logininfor/unlock/{userName}', [app\controller\monitor\SysLogininforController::class, 'unlock']);
Route::delete('/monitor/logininfor/clean', [app\controller\monitor\SysLogininforController::class, 'clean']);
Route::delete('/monitor/logininfor/{infoIds}', [app\controller\monitor\SysLogininforController::class, 'remove']);

Route::get('/monitor/job/list', [app\controller\monitor\SysJobController::class, 'list']);
Route::put('/monitor/job/changeStatus', [app\controller\monitor\SysJobController::class, 'changeStatus']);
Route::put('/monitor/job/run', [app\controller\monitor\SysJobController::class, 'run']);
Route::post('/monitor/job', [app\controller\monitor\SysJobController::class, 'add']);
Route::put('/monitor/job', [app\controller\monitor\SysJobController::class, 'edit']);
Route::delete('/monitor/job/{jobIds}', [app\controller\monitor\SysJobController::class, 'remove']);
Route::get('/monitor/job/{jobId}', [app\controller\monitor\SysJobController::class, 'getInfo']);

Route::get('/monitor/jobLog/list', [app\controller\monitor\SysJobLogController::class, 'list']);
Route::delete('/monitor/jobLog/clean', [app\controller\monitor\SysJobLogController::class, 'clean']);
Route::delete('/monitor/jobLog/{jobLogIds}', [app\controller\monitor\SysJobLogController::class, 'remove']);

Route::get('/monitor/server', [app\controller\monitor\ServerController::class, 'getInfo']);
Route::get('/monitor/cache', [app\controller\monitor\CacheController::class, 'getInfo']);
Route::get('/monitor/cache/getNames', [app\controller\monitor\CacheController::class, 'getNames']);
Route::get('/monitor/cache/getKeys/{cacheName}', [app\controller\monitor\CacheController::class, 'getKeys']);
Route::get('/monitor/cache/getValue/{cacheName}/{cacheKey}', [app\controller\monitor\CacheController::class, 'getValue']);
Route::delete('/monitor/cache/clearCacheName/{cacheName}', [app\controller\monitor\CacheController::class, 'clearCacheName']);
Route::delete('/monitor/cache/clearCacheKey/{cacheKey}', [app\controller\monitor\CacheController::class, 'clearCacheKey']);
Route::delete('/monitor/cache/clearCacheAll', [app\controller\monitor\CacheController::class, 'clearCacheAll']);

Route::get('/tool/gen/list', [app\controller\tool\GenController::class, 'list']);
Route::get('/tool/gen/db/list', [app\controller\tool\GenController::class, 'dbList']);
Route::post('/tool/gen/importTable', [app\controller\tool\GenController::class, 'importTable']);
Route::put('/tool/gen', [app\controller\tool\GenController::class, 'edit']);
Route::get('/tool/gen/preview/{tableId}', [app\controller\tool\GenController::class, 'preview']);
Route::get('/tool/gen/synchDb/{tableName}', [app\controller\tool\GenController::class, 'synchDb']);
Route::get('/tool/gen/download/{tableName}', [app\controller\tool\GenController::class, 'download']);
Route::get('/tool/gen/batchGenCode', [app\controller\tool\GenController::class, 'batchGenCode']);
Route::delete('/tool/gen/{tableIds}', [app\controller\tool\GenController::class, 'remove']);
Route::get('/tool/gen/{tableId}', [app\controller\tool\GenController::class, 'getInfo']);

Route::get('/business/enterprise/list', [app\controller\business\BizEnterpriseController::class, 'list']);
Route::get('/business/enterprise/search', [app\controller\business\BizEnterpriseController::class, 'search']);
Route::put('/business/enterprise/status', [app\controller\business\BizEnterpriseController::class, 'changeStatus']);
Route::post('/business/enterprise', [app\controller\business\BizEnterpriseController::class, 'add']);
Route::put('/business/enterprise', [app\controller\business\BizEnterpriseController::class, 'edit']);
Route::delete('/business/enterprise/{enterpriseIds}', [app\controller\business\BizEnterpriseController::class, 'remove']);
Route::get('/business/enterprise/{enterpriseId}', [app\controller\business\BizEnterpriseController::class, 'getInfo']);

Route::get('/business/store/list', [app\controller\business\BizStoreController::class, 'list']);
Route::get('/business/store/search', [app\controller\business\BizStoreController::class, 'search']);
Route::post('/business/store', [app\controller\business\BizStoreController::class, 'add']);
Route::put('/business/store', [app\controller\business\BizStoreController::class, 'edit']);
Route::delete('/business/store/{storeIds}', [app\controller\business\BizStoreController::class, 'remove']);
Route::get('/business/store/{storeId}', [app\controller\business\BizStoreController::class, 'getInfo']);

Route::get('/business/customer/list', [app\controller\business\BizCustomerController::class, 'list']);
Route::get('/business/customer/search', [app\controller\business\BizCustomerController::class, 'search']);
Route::post('/business/customer', [app\controller\business\BizCustomerController::class, 'add']);
Route::put('/business/customer', [app\controller\business\BizCustomerController::class, 'edit']);
Route::delete('/business/customer/{customerIds}', [app\controller\business\BizCustomerController::class, 'remove']);
Route::get('/business/customer/{customerId}', [app\controller\business\BizCustomerController::class, 'getInfo']);

Route::get('/business/sales/list', [app\controller\business\BizSalesOrderController::class, 'list']);
Route::post('/business/sales', [app\controller\business\BizSalesOrderController::class, 'add']);
Route::put('/business/sales', [app\controller\business\BizSalesOrderController::class, 'edit']);
Route::delete('/business/sales/{orderIds}', [app\controller\business\BizSalesOrderController::class, 'remove']);
Route::get('/business/sales/{orderId}', [app\controller\business\BizSalesOrderController::class, 'getInfo']);
Route::post('/business/sales/enterpriseAudit', [app\controller\business\BizSalesOrderController::class, 'enterpriseAudit']);
Route::post('/business/sales/financeAudit', [app\controller\business\BizSalesOrderController::class, 'financeAudit']);

Route::get('/business/package/list', [app\controller\business\BizCustomerPackageController::class, 'list']);
Route::get('/business/package/byCustomer', [app\controller\business\BizCustomerPackageController::class, 'getByCustomer']);
Route::get('/business/package/{packageId}', [app\controller\business\BizCustomerPackageController::class, 'getInfo']);

Route::get('/business/operation/list', [app\controller\business\BizOperationRecordController::class, 'list']);
Route::post('/business/operation', [app\controller\business\BizOperationRecordController::class, 'add']);
Route::delete('/business/operation/{recordIds}', [app\controller\business\BizOperationRecordController::class, 'remove']);
// 操作记录 - 获取详情
Route::get('/business/operation/{id}', [app\controller\business\BizOperationRecordController::class, 'getInfo']);

Route::get('/business/repayment/list', [app\controller\business\BizRepaymentController::class, 'list']);
Route::get('/business/repayment/owedPackages', [app\controller\business\BizRepaymentController::class, 'owedPackages']);
Route::post('/business/repayment/add', [app\controller\business\BizRepaymentController::class, 'add']);
Route::post('/business/repayment/audit', [app\controller\business\BizRepaymentController::class, 'audit']);
Route::post('/business/repayment/cancel', [app\controller\business\BizRepaymentController::class, 'cancel']);
Route::get('/business/repayment/{repaymentId}', [app\controller\business\BizRepaymentController::class, 'getInfo']);

Route::get('/business/archive/list', [app\controller\business\BizCustomerArchiveController::class, 'list']);
Route::post('/business/archive/add', [app\controller\business\BizCustomerArchiveController::class, 'add']);
Route::delete('/business/archive/{archiveIds}', [app\controller\business\BizCustomerArchiveController::class, 'remove']);

Route::get('/business/plan/enterpriseList', [app\controller\business\BizPlanController::class, 'enterpriseList']);
Route::get('/business/plan/list', [app\controller\business\BizPlanController::class, 'list']);
Route::put('/business/plan/submitAudit/{planId}', [app\controller\business\BizPlanController::class, 'submitAudit']);
Route::put('/business/plan/audit', [app\controller\business\BizPlanController::class, 'audit']);
Route::put('/business/plan/changeStatus', [app\controller\business\BizPlanController::class, 'changeStatus']);
Route::post('/business/plan', [app\controller\business\BizPlanController::class, 'add']);
Route::put('/business/plan', [app\controller\business\BizPlanController::class, 'edit']);
Route::delete('/business/plan/{planIds}', [app\controller\business\BizPlanController::class, 'remove']);
Route::get('/business/plan/{planId}', [app\controller\business\BizPlanController::class, 'getInfo']);

Route::get('/business/shipment/list', [app\controller\business\BizShipmentController::class, 'list']);
Route::put('/business/shipment/audit', [app\controller\business\BizShipmentController::class, 'audit']);
Route::put('/business/shipment/ship', [app\controller\business\BizShipmentController::class, 'ship']);
Route::put('/business/shipment/confirmReceipt/{shipmentId}', [app\controller\business\BizShipmentController::class, 'confirmReceipt']);
Route::post('/business/shipment', [app\controller\business\BizShipmentController::class, 'add']);
Route::put('/business/shipment', [app\controller\business\BizShipmentController::class, 'edit']);
Route::delete('/business/shipment/{shipmentIds}', [app\controller\business\BizShipmentController::class, 'remove']);
Route::get('/business/shipment/{shipmentId}', [app\controller\business\BizShipmentController::class, 'getInfo']);

Route::get('/business/schedule/list', [app\controller\business\BizScheduleController::class, 'list']);
Route::get('/business/schedule/calendar', [app\controller\business\BizScheduleController::class, 'calendar']);
Route::get('/business/schedule/dates', [app\controller\business\BizScheduleController::class, 'dates']);
Route::get('/business/schedule/employee', [app\controller\business\BizScheduleController::class, 'employeeSchedule']);
Route::get('/business/schedule/enterprise', [app\controller\business\BizScheduleController::class, 'enterpriseSchedule']);
Route::post('/business/schedule', [app\controller\business\BizScheduleController::class, 'add']);
Route::post('/business/schedule/batch', [app\controller\business\BizScheduleController::class, 'addBatch']);
Route::put('/business/schedule', [app\controller\business\BizScheduleController::class, 'edit']);
Route::delete('/business/schedule/{scheduleIds}', [app\controller\business\BizScheduleController::class, 'remove']);
Route::get('/business/schedule/{scheduleId}', [app\controller\business\BizScheduleController::class, 'getInfo']);

Route::get('/business/employeeConfig/list', [app\controller\business\BizEmployeeConfigController::class, 'list']);
Route::get('/business/employeeConfig/search', [app\controller\business\BizEmployeeConfigController::class, 'search']);
Route::post('/business/employeeConfig', [app\controller\business\BizEmployeeConfigController::class, 'add']);
Route::put('/business/employeeConfig', [app\controller\business\BizEmployeeConfigController::class, 'edit']);
Route::put('/business/employeeConfig/updateSchedulable', [app\controller\business\BizEmployeeConfigController::class, 'updateSchedulable']);
Route::post('/business/employeeConfig/saveRestDates', [app\controller\business\BizEmployeeConfigController::class, 'saveRestDates']);
Route::get('/business/employeeConfig/getRestDates', [app\controller\business\BizEmployeeConfigController::class, 'getRestDates']);
Route::delete('/business/employeeConfig/{configIds}', [app\controller\business\BizEmployeeConfigController::class, 'remove']);
Route::get('/business/employeeConfig/{configId}', [app\controller\business\BizEmployeeConfigController::class, 'getInfo']);

Route::get('/business/attendance/todayRecord', [app\controller\business\BizAttendanceController::class, 'todayRecord']);
Route::post('/business/attendance/clock', [app\controller\business\BizAttendanceController::class, 'clock']);
Route::get('/business/attendance/todayClockList', [app\controller\business\BizAttendanceController::class, 'todayClockList']);
Route::get('/business/attendance/clockList', [app\controller\business\BizAttendanceController::class, 'clockList']);
Route::post('/business/attendance/clockIn', [app\controller\business\BizAttendanceController::class, 'clockIn']);
Route::post('/business/attendance/clockOut', [app\controller\business\BizAttendanceController::class, 'clockOut']);
Route::get('/business/attendance/monthStats', [app\controller\business\BizAttendanceController::class, 'monthStats']);
Route::get('/business/attendance/record/list', [app\controller\business\BizAttendanceController::class, 'recordList']);
Route::get('/business/attendance/record/{recordId}', [app\controller\business\BizAttendanceController::class, 'recordInfo']);
Route::get('/business/attendance/rule/list', [app\controller\business\BizAttendanceController::class, 'ruleList']);
Route::post('/business/attendance/rule', [app\controller\business\BizAttendanceController::class, 'addRule']);
Route::put('/business/attendance/rule', [app\controller\business\BizAttendanceController::class, 'editRule']);
Route::delete('/business/attendance/rule/{ruleIds}', [app\controller\business\BizAttendanceController::class, 'removeRule']);
Route::get('/business/attendance/rule/{ruleId}', [app\controller\business\BizAttendanceController::class, 'ruleInfo']);

Route::get('/business/attendance/config/list', [app\controller\business\BizAttendanceConfigController::class, 'list']);
Route::get('/business/attendance/config/userRule', [app\controller\business\BizAttendanceConfigController::class, 'getUserRule']);
Route::get('/business/attendance/config/{configId}', [app\controller\business\BizAttendanceConfigController::class, 'info']);
Route::post('/business/attendance/config', [app\controller\business\BizAttendanceConfigController::class, 'add']);
Route::put('/business/attendance/config', [app\controller\business\BizAttendanceConfigController::class, 'edit']);
Route::delete('/business/attendance/config', [app\controller\business\BizAttendanceConfigController::class, 'remove']);

Route::get('/system/user/detail/{userId}', [app\controller\system\SysUserDetailController::class, 'getInfo']);
Route::post('/system/user/detail', [app\controller\system\SysUserDetailController::class, 'add']);
Route::put('/system/user/detail', [app\controller\system\SysUserDetailController::class, 'edit']);

Route::get('/hr/salary/type/list', [app\controller\system\HrUserSalaryController::class, 'typeList']);
Route::get('/hr/salary/user/{userId}', [app\controller\system\HrUserSalaryController::class, 'listByUser']);
Route::get('/hr/salary/{salaryId}', [app\controller\system\HrUserSalaryController::class, 'getInfo']);
Route::post('/hr/salary', [app\controller\system\HrUserSalaryController::class, 'add']);
Route::put('/hr/salary', [app\controller\system\HrUserSalaryController::class, 'edit']);
Route::delete('/hr/salary/{salaryIds}', [app\controller\system\HrUserSalaryController::class, 'remove']);

// =============================================
// 进销存管理模块路由
// =============================================

// 供货商管理
Route::get('/wms/supplier/list', [app\controller\wms\BizSupplierController::class, 'list']);
Route::get('/wms/supplier/search', [app\controller\wms\BizSupplierController::class, 'search']);
Route::post('/wms/supplier', [app\controller\wms\BizSupplierController::class, 'add']);
Route::put('/wms/supplier', [app\controller\wms\BizSupplierController::class, 'edit']);
Route::delete('/wms/supplier/{supplierIds}', [app\controller\wms\BizSupplierController::class, 'remove']);
Route::get('/wms/supplier/{supplierId}', [app\controller\wms\BizSupplierController::class, 'getInfo']);

// 货品管理
Route::get('/wms/product/list', [app\controller\wms\BizProductController::class, 'list']);
Route::get('/wms/product/search', [app\controller\wms\BizProductController::class, 'search']);
Route::post('/wms/product', [app\controller\wms\BizProductController::class, 'add']);
Route::put('/wms/product', [app\controller\wms\BizProductController::class, 'edit']);
Route::delete('/wms/product/{productIds}', [app\controller\wms\BizProductController::class, 'remove']);
Route::get('/wms/product/{productId}', [app\controller\wms\BizProductController::class, 'getInfo']);

// 入库管理
Route::get('/wms/stockIn/list', [app\controller\wms\BizStockInController::class, 'list']);
Route::put('/wms/stockIn/confirm/{id}', [app\controller\wms\BizStockInController::class, 'confirm']);
Route::put('/wms/stockIn/cancelConfirm/{id}', [app\controller\wms\BizStockInController::class, 'cancelConfirm']);
Route::post('/wms/stockIn', [app\controller\wms\BizStockInController::class, 'add']);
Route::put('/wms/stockIn', [app\controller\wms\BizStockInController::class, 'edit']);
Route::delete('/wms/stockIn/{stockInIds}', [app\controller\wms\BizStockInController::class, 'remove']);
Route::get('/wms/stockIn/{stockInId}', [app\controller\wms\BizStockInController::class, 'getInfo']);

// 出库管理
Route::get('/wms/stockOut/list', [app\controller\wms\BizStockOutController::class, 'list']);
Route::put('/wms/stockOut/confirm/{id}', [app\controller\wms\BizStockOutController::class, 'confirm']);
Route::put('/wms/stockOut/cancelConfirm/{id}', [app\controller\wms\BizStockOutController::class, 'cancelConfirm']);
Route::post('/wms/stockOut', [app\controller\wms\BizStockOutController::class, 'add']);
Route::put('/wms/stockOut', [app\controller\wms\BizStockOutController::class, 'edit']);
Route::delete('/wms/stockOut/{stockOutIds}', [app\controller\wms\BizStockOutController::class, 'remove']);
Route::get('/wms/stockOut/{stockOutId}', [app\controller\wms\BizStockOutController::class, 'getInfo']);

// 库存查看
Route::get('/wms/inventory/list', [app\controller\wms\BizInventoryController::class, 'list']);
Route::get('/wms/inventory/warn', [app\controller\wms\BizInventoryController::class, 'warn']);
Route::get('/wms/inventory/{productId}', [app\controller\wms\BizInventoryController::class, 'getInfo']);

// 库存盘点
Route::get('/wms/stockCheck/list', [app\controller\wms\BizStockCheckController::class, 'list']);
Route::get('/wms/stockCheck/loadInventory', [app\controller\wms\BizStockCheckController::class, 'loadInventory']);
Route::put('/wms/stockCheck/confirm/{id}', [app\controller\wms\BizStockCheckController::class, 'confirm']);
Route::post('/wms/stockCheck', [app\controller\wms\BizStockCheckController::class, 'add']);
Route::put('/wms/stockCheck', [app\controller\wms\BizStockCheckController::class, 'edit']);
Route::delete('/wms/stockCheck/{stockCheckIds}', [app\controller\wms\BizStockCheckController::class, 'remove']);
Route::get('/wms/stockCheck/{stockCheckId}', [app\controller\wms\BizStockCheckController::class, 'getInfo']);

// 进销存报表
Route::get('/wms/report/stockInSummary', [app\controller\wms\BizWmsReportController::class, 'stockInSummary']);
Route::get('/wms/report/stockOutSummary', [app\controller\wms\BizWmsReportController::class, 'stockOutSummary']);
Route::get('/wms/report/inventoryTurnover', [app\controller\wms\BizWmsReportController::class, 'inventoryTurnover']);
Route::get('/wms/report/productFlow', [app\controller\wms\BizWmsReportController::class, 'productFlow']);

Route::any('/{path:.+}', function ($request, $path) {
    return json(['code' => 404, 'msg' => '接口不存在']);
});
