# 用户管理导入导出功能实现计划

## 问题分析

用户管理模块存在以下问题：
1. 点击新增按钮报错 - `getInfo` 方法不支持空 `userId` 参数
2. 导入按钮提示资源不存在 - 缺少导入接口
3. 导出按钮提示资源不存在 - 缺少导出接口

## 参考源代码分析

### Spring Boot 原始实现

#### 1. SysUserController.java 中的导入导出接口

```java
// 导出用户列表
@Log(title = "用户管理", businessType = BusinessType.EXPORT)
@PreAuthorize("@ss.hasPermi('system:user:export')")
@PostMapping("/export")
public void export(HttpServletResponse response, SysUser user) {
    List<SysUser> list = userService.selectUserList(user);
    ExcelUtil<SysUser> util = new ExcelUtil<SysUser>(SysUser.class);
    util.exportExcel(response, list, "用户数据");
}

// 导入用户数据
@Log(title = "用户管理", businessType = BusinessType.IMPORT)
@PreAuthorize("@ss.hasPermi('system:user:import')")
@PostMapping("/importData")
public AjaxResult importData(MultipartFile file, boolean updateSupport) throws Exception {
    ExcelUtil<SysUser> util = new ExcelUtil<SysUser>(SysUser.class);
    List<SysUser> userList = util.importExcel(file.getInputStream());
    String operName = getUsername();
    String message = userService.importUser(userList, updateSupport, operName);
    return success(message);
}

// 下载导入模板
@PostMapping("/importTemplate")
public void importTemplate(HttpServletResponse response) {
    ExcelUtil<SysUser> util = new ExcelUtil<SysUser>(SysUser.class);
    util.importTemplateExcel(response, "用户数据");
}
```

#### 2. SysUserServiceImpl.java 中的 importUser 方法

```java
public String importUser(List<SysUser> userList, Boolean isUpdateSupport, String operName) {
    if (StringUtils.isNull(userList) || userList.size() == 0) {
        throw new ServiceException("导入用户数据不能为空！");
    }
    int successNum = 0;
    int failureNum = 0;
    StringBuilder successMsg = new StringBuilder();
    StringBuilder failureMsg = new StringBuilder();
    for (SysUser user : userList) {
        try {
            SysUser u = userMapper.selectUserByUserName(user.getUserName());
            if (StringUtils.isNull(u)) {
                // 新增用户
                BeanValidators.validateWithException(validator, user);
                deptService.checkDeptDataScope(user.getDeptId());
                String password = configService.selectConfigByKey("sys.user.initPassword");
                user.setPassword(SecurityUtils.encryptPassword(password));
                user.setCreateBy(operName);
                userMapper.insertUser(user);
                successNum++;
                successMsg.append("<br/>" + successNum + "、账号 " + user.getUserName() + " 导入成功");
            } else if (isUpdateSupport) {
                // 更新用户
                BeanValidators.validateWithException(validator, user);
                checkUserAllowed(u);
                checkUserDataScope(u.getUserId());
                deptService.checkDeptDataScope(user.getDeptId());
                user.setUserId(u.getUserId());
                user.setUpdateBy(operName);
                userMapper.updateUser(user);
                successNum++;
                successMsg.append("<br/>" + successNum + "、账号 " + user.getUserName() + " 更新成功");
            } else {
                failureNum++;
                failureMsg.append("<br/>" + failureNum + "、账号 " + user.getUserName() + " 已存在");
            }
        } catch (Exception e) {
            failureNum++;
            String msg = "<br/>" + failureNum + "、账号 " + user.getUserName() + " 导入失败：";
            failureMsg.append(msg + e.getMessage());
            log.error(msg, e);
        }
    }
    if (failureNum > 0) {
        failureMsg.insert(0, "很抱歉，导入失败！共 " + failureNum + " 条数据格式不正确，错误如下：");
        throw new ServiceException(failureMsg.toString());
    } else {
        successMsg.insert(0, "恭喜您，数据已全部导入成功！共 " + successNum + " 条，数据如下：");
    }
    return successMsg.toString();
}
```

#### 3. SysUser.java 中的 Excel 注解

```java
@Excel(name = "用户序号", type = Type.EXPORT, cellType = ColumnType.NUMERIC, prompt = "用户编号")
private Long userId;

@Excel(name = "部门编号", type = Type.IMPORT)
private Long deptId;

@Excel(name = "登录名称")
private String userName;

@Excel(name = "用户名称")
private String nickName;

@Excel(name = "用户邮箱")
private String email;

@Excel(name = "手机号码", cellType = ColumnType.TEXT)
private String phonenumber;

@Excel(name = "用户性别", readConverterExp = "0=男,1=女,2=未知")
private String sex;

@Excel(name = "账号状态", readConverterExp = "0=正常,1=停用")
private String status;

@Excel(name = "最后登录IP", type = Type.EXPORT)
private String loginIp;

@Excel(name = "最后登录时间", width = 30, dateFormat = "yyyy-MM-dd HH:mm:ss", type = Type.EXPORT)
private Date loginDate;
```

## 实现步骤

### 步骤 1: 安装 PhpSpreadsheet 库

```bash
composer require phpoffice/phpspreadsheet
```

**注意**: 需要确保 PHP fileinfo 扩展已启用。

### 步骤 2: 创建 Excel 工具类

创建文件 `d:\fuchenpro\webman\app\common\ExcelUtil.php`

功能包括：
- `exportExcel($list, $sheetName)` - 导出数据到 Excel
- `importExcel($file)` - 从 Excel 导入数据
- `importTemplateExcel($sheetName)` - 生成导入模板
- 支持注解配置（通过类属性注解或配置数组）
- 支持字典转换
- 支持日期格式化
- 支持表达式转换（如 `0=男,1=女`）

### 步骤 3: 创建 Excel 注解属性类

创建文件 `d:\fuchenpro\webman\app\common\Excel.php`

用于定义导出字段的属性：
- `$name` - 字段名称
- `$width` - 列宽
- `$dateFormat` - 日期格式
- `$dictType` - 字典类型
- `$readConverterExp` - 表达式转换
- `$type` - 类型（ALL/EXPORT/IMPORT）

### 步骤 4: 修改 SysUser 模型

添加 Excel 导出配置：

```php
class SysUser extends Model
{
    // Excel 导出字段配置
    public static $excelFields = [
        'user_id' => ['name' => '用户序号', 'type' => 'export', 'cellType' => 'numeric'],
        'dept_id' => ['name' => '部门编号', 'type' => 'import'],
        'user_name' => ['name' => '登录名称'],
        'nick_name' => ['name' => '用户名称'],
        'email' => ['name' => '用户邮箱'],
        'phonenumber' => ['name' => '手机号码', 'cellType' => 'text'],
        'sex' => ['name' => '用户性别', 'readConverterExp' => '0=男,1=女,2=未知'],
        'status' => ['name' => '账号状态', 'readConverterExp' => '0=正常,1=停用'],
        'login_ip' => ['name' => '最后登录IP', 'type' => 'export'],
        'login_date' => ['name' => '最后登录时间', 'width' => 30, 'dateFormat' => 'yyyy-MM-dd HH:mm:ss', 'type' => 'export'],
    ];
}
```

### 步骤 5: 修改 SysUserController

添加三个新方法：

```php
// 导出用户列表
public function export(Request $request)
{
    $params = $request->all();
    $params['login_user'] = $request->loginUser;
    $service = new SysUserService();
    $list = $service->selectUserList($params)->items();
    
    $excelUtil = new ExcelUtil(SysUser::class);
    return $excelUtil->exportExcel($list, '用户数据');
}

// 导入用户数据
public function importData(Request $request)
{
    $file = $request->file('file');
    $updateSupport = $request->post('updateSupport', false);
    
    $excelUtil = new ExcelUtil(SysUser::class);
    $userList = $excelUtil->importExcel($file);
    
    $operName = $request->loginUser->user->user_name ?? '';
    $service = new SysUserService();
    $message = $service->importUser($userList, $updateSupport, $operName);
    
    return AjaxResult::success($message);
}

// 下载导入模板
public function importTemplate(Request $request)
{
    $excelUtil = new ExcelUtil(SysUser::class);
    return $excelUtil->importTemplateExcel('用户数据');
}
```

### 步骤 6: 修改 getInfo 方法

支持空 `userId` 参数（用于新增表单）：

```php
public function getInfo(Request $request)
{
    $userId = $request->input('user_id', 0);
    if (!$userId) {
        $parts = explode('/', $request->path());
        $userId = end($parts);
    }
    
    // 当 userId 为空时，返回角色和岗位列表（用于新增表单）
    if (empty($userId) || $userId === 'system' || $userId === 'user') {
        $roleService = new \app\service\SysRoleService();
        $roles = $roleService->selectAllRoles();
        $postService = new \app\service\SysPostService();
        $posts = $postService->selectPostAll();
        
        return AjaxResult::success('', [
            'roles' => $roles,
            'posts' => $posts,
        ]);
    }
    
    // 原有逻辑...
}
```

### 步骤 7: 添加 importUser 方法到 SysUserService

```php
public function importUser($userList, $isUpdateSupport, $operName)
{
    if (empty($userList)) {
        throw new \Exception('导入用户数据不能为空！');
    }
    
    $successNum = 0;
    $failureNum = 0;
    $successMsg = '';
    $failureMsg = '';
    
    foreach ($userList as $user) {
        try {
            $existingUser = $this->selectUserByUserName($user['user_name'] ?? '');
            
            if (!$existingUser) {
                // 新增用户
                $initPwd = SysConfigService::selectConfigByKey('sys.user.initPassword');
                $user['password'] = PasswordService::encrypt($initPwd ?: '123456');
                $user['create_by'] = $operName;
                $this->insertUser($user);
                $successNum++;
                $successMsg .= "<br/>{$successNum}、账号 {$user['user_name']} 导入成功";
            } else if ($isUpdateSupport) {
                // 更新用户
                $user['user_id'] = $existingUser->user_id;
                $user['update_by'] = $operName;
                $this->updateUser($user);
                $successNum++;
                $successMsg .= "<br/>{$successNum}、账号 {$user['user_name']} 更新成功";
            } else {
                $failureNum++;
                $failureMsg .= "<br/>{$failureNum}、账号 {$user['user_name']} 已存在";
            }
        } catch (\Exception $e) {
            $failureNum++;
            $failureMsg .= "<br/>{$failureNum}、账号 " . ($user['user_name'] ?? '未知') . " 导入失败：" . $e->getMessage();
        }
    }
    
    if ($failureNum > 0) {
        throw new \Exception("很抱歉，导入失败！共 {$failureNum} 条数据格式不正确，错误如下：{$failureMsg}");
    }
    
    return "恭喜您，数据已全部导入成功！共 {$successNum} 条，数据如下：{$successMsg}";
}
```

### 步骤 8: 更新路由配置

在 `config/route.php` 中添加新路由：

```php
// 用户导入导出路由（放在动态路由之前）
Route::post('/system/user/export', [app\controller\system\SysUserController::class, 'export']);
Route::post('/system/user/importData', [app\controller\system\SysUserController::class, 'importData']);
Route::post('/system/user/importTemplate', [app\controller\system\SysUserController::class, 'importTemplate']);
```

### 步骤 9: 创建下载目录

创建文件下载目录：
```
d:\fuchenpro\webman\public\profile\download\
```

## 文件清单

| 文件路径 | 操作 | 说明 |
|---------|------|------|
| `app/common/ExcelUtil.php` | 新建 | Excel 工具类 |
| `app/common/Excel.php` | 新建 | Excel 注解属性类 |
| `app/model/SysUser.php` | 修改 | 添加 Excel 导出配置 |
| `app/controller/system/SysUserController.php` | 修改 | 添加导出、导入、模板下载方法 |
| `app/service/SysUserService.php` | 修改 | 添加 importUser 方法 |
| `config/route.php` | 修改 | 添加导入导出路由 |
| `public/profile/download/` | 新建 | 文件下载目录 |

## 注意事项

1. **PHP 扩展**: 确保 PHP fileinfo 扩展已启用
2. **路由顺序**: 静态路由必须放在动态路由之前
3. **内存限制**: 大数据量导出时可能需要调整 PHP 内存限制
4. **文件权限**: 确保下载目录有写入权限
5. **权限控制**: 导入导出功能需要对应的权限（`system:user:export`、`system:user:import`）

## 测试验证

1. 点击新增按钮，验证表单正常显示
2. 点击导出按钮，验证 Excel 文件下载
3. 上传 Excel 文件，验证导入功能
4. 下载导入模板，验证模板格式正确
