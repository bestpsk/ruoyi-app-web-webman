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

Route::any('/{path:.+}', function ($request, $path) {
    return json(['code' => 404, 'msg' => '接口不存在']);
});
