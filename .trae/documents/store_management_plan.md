# 门店管理模块实现计划

## 需求概述
在"业务管理"菜单下增加"门店管理"功能，实现完整的CRUD操作。

### 门店字段
| 字段 | 说明 | 类型 |
|------|------|------|
| store_id | 门店ID | bigint (自增主键) |
| enterprise_id | 所属企业ID | bigint (外键) |
| store_name | 门店名称 | varchar(100) |
| manager_name | 门店负责人 | varchar(50) |
| phone | 联系电话 | varchar(20) |
| wechat | 微信 | varchar(50) |
| address | 地址 | varchar(255) |
| business_hours | 营业时间 | varchar(100) |
| server_user_id | 服务员工ID | bigint |
| server_user_name | 服务员工姓名 | varchar(50) |
| status | 状态 | char(1) 默认'0' |
| remark | 备注 | text |
| create_by | 创建者 | varchar(64) |
| create_time | 创建时间 | datetime |
| update_by | 更新者 | varchar(64) |
| update_time | 更新时间 | datetime |

---

## 实现步骤

### 步骤1：创建数据库表和SQL脚本
**文件**: `webman/sql/biz_store.sql`

内容：
- 创建 `biz_store` 表
- 插入菜单数据（门店管理菜单，挂在业务管理下）
- 插入按钮权限（查询、新增、修改、删除、导出）
- 为管理员角色分配菜单权限

### 步骤2：创建后端Model
**文件**: `webman/app/model/BizStore.php`

参考 `BizEnterprise.php` 的结构：
- 定义表名 `biz_store`
- 定义主键 `store_id`
- 定义 `$fillable` 属性

### 步骤3：创建后端Service
**文件**: `webman/app/service/BizStoreService.php`

参考 `BizEnterpriseService.php` 的结构：
- `selectStoreList($params)` - 分页查询门店列表
- `selectStoreById($storeId)` - 根据ID查询门店
- `insertStore($data)` - 新增门店
- `updateStore($data)` - 更新门店
- `deleteStoreByIds($storeIds)` - 批量删除门店
- `selectStoreForSearch($keyword)` - 搜索门店（支持企业名称关联搜索）

### 步骤4：创建后端Controller
**文件**: `webman/app/controller/business/BizStoreController.php`

参考 `BizEnterpriseController.php` 的结构：
- `list()` - 获取门店列表
- `getInfo()` - 获取门店详情
- `search()` - 搜索门店
- `add()` - 新增门店
- `edit()` - 修改门店
- `remove()` - 删除门店

### 步骤5：配置后端路由
**文件**: `webman/config/route.php`

添加门店管理相关路由：
```php
Route::get('/business/store/list', [app\controller\business\BizStoreController::class, 'list']);
Route::get('/business/store/search', [app\controller\business\BizStoreController::class, 'search']);
Route::post('/business/store', [app\controller\business\BizStoreController::class, 'add']);
Route::put('/business/store', [app\controller\business\BizStoreController::class, 'edit']);
Route::delete('/business/store/{storeIds}', [app\controller\business\BizStoreController::class, 'remove']);
Route::get('/business/store/{storeId}', [app\controller\business\BizStoreController::class, 'getInfo']);
```

### 步骤6：创建前端API
**文件**: `front/src/api/business/store.js`

参考 `enterprise.js` 的结构：
- `listStore(query)` - 查询门店列表
- `getStore(storeId)` - 查询门店详细
- `addStore(data)` - 新增门店
- `updateStore(data)` - 修改门店
- `delStore(storeId)` - 删除门店
- `searchStore(keyword)` - 搜索门店

### 步骤7：创建前端页面
**文件**: `front/src/views/business/store/index.vue`

参考 `enterprise/index.vue` 的结构：

**查询条件**：
- 门店名称（模糊查询）
- 所属企业（下拉选择）
- 门店负责人（模糊查询）
- 联系电话（模糊查询）
- 状态（下拉选择）

**列表字段**：
- 门店名称
- 所属企业名称
- 门店负责人
- 联系电话
- 微信
- 地址
- 营业时间
- 服务员工
- 状态
- 创建时间
- 操作（修改、删除）

**表单字段**：
- 所属企业（下拉选择，使用企业搜索API）
- 门店名称（必填）
- 门店负责人
- 联系电话
- 微信
- 地址
- 营业时间
- 服务员工（下拉选择，使用用户列表API）
- 状态（单选：正常/停用）
- 备注

---

## 文件清单

### 后端文件
| 文件路径 | 操作 |
|----------|------|
| `webman/sql/biz_store.sql` | 新建 |
| `webman/app/model/BizStore.php` | 新建 |
| `webman/app/service/BizStoreService.php` | 新建 |
| `webman/app/controller/business/BizStoreController.php` | 新建 |
| `webman/config/route.php` | 修改 |

### 前端文件
| 文件路径 | 操作 |
|----------|------|
| `front/src/api/business/store.js` | 新建 |
| `front/src/views/business/store/index.vue` | 新建 |

---

## 执行顺序
1. 创建SQL脚本文件
2. 创建后端Model
3. 创建后端Service
4. 创建后端Controller
5. 修改路由配置
6. 创建前端API
7. 创建前端页面
