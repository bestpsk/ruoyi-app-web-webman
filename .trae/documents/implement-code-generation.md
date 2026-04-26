# 代码生成模块 - 生成 Webman 版本代码功能实现计划

## 功能概述

实现代码生成模块，在线配置表信息生成对应的 Webman (PHP) 版本代码，包含增删改查/排序/导出/权限控制等操作。

## 现状分析

### 已实现

* 前端页面：index.vue、editTable.vue、importTable.vue、createTable.vue、basicInfoForm.vue、genInfoForm.vue

* 后端基础：GenController、GenTableService（导入/编辑/同步/删除）

* 数据模型：GenTable、GenTableColumn

### 未实现

* **模板渲染引擎**：PHP 版本的模板渲染（替代 Java Velocity）

* **Webman 代码模板**：PHP Controller/Service/Model/Route + Vue3 页面模板

* **预览代码**：preview 接口目前只返回表数据，未渲染模板

* **生成代码下载**：batchGenCode/download 接口未实现

* **列属性初始化**：importGenTable 中未初始化 java\_type/java\_field/html\_type/query\_type 等字段

* **编辑保存**：updateGenTable 未保存列信息

## 实现步骤

### 步骤1：完善列属性初始化（GenTableService::importGenTable）

参考若依 `GenUtils.initColumnField()`，在导入表时初始化列属性：

* `java_field`：下划线转驼峰

* `java_type`：根据 column\_type 映射 PHP 类型（String/Integer/Long/BigDecimal/Date）

* `html_type`：根据字段名和类型推断（input/textarea/select/radio/checkbox/datetime）

* `query_type`：根据字段名推断（EQ/LIKE）

* `is_insert/is_edit/is_list/is_query`：根据字段名设置默认值

### 步骤2：完善编辑保存（GenTableService::updateGenTable）

参考若依实现，保存时同时更新列信息。

### 步骤3：创建 PHP 模板引擎

使用 PHP 原生字符串替换实现模板渲染（替代 Velocity），创建 `GenTemplateEngine` 类：

* 支持 `{{变量}}` 语法

* 支持 `#foreach` / `#end` 循环

* 支持 `#if` / `#elseif` / `#else` / `#end` 条件

* 支持内置变量和方法

### 步骤4：创建 Webman 代码模板

在 `d:\fuchenpro\webman\resources\template\` 目录下创建模板文件：

**PHP 后端模板：**

* `controller.php.vm` - 控制器模板

* `service.php.vm` - 服务层模板

* `model.php.vm` - 模型模板

**前端模板：**

* `api.js.vm` - API 请求文件模板

* `index.vue.vm` - 列表页面模板（含增删改查/排序/导出/权限控制）

* `view.vue.vm` - 详情页模板

**其他模板：**

* `route.php.vm` - 路由配置模板

* `menu.sql.vm` - 菜单 SQL 模板

### 步骤5：实现预览代码接口

修改 `GenController::preview()` 和 `GenTableService::previewCode()`：

* 查询表信息及列信息

* 准备模板上下文变量

* 渲染所有模板

* 返回模板名和渲染结果的映射

### 步骤6：实现生成代码下载接口

添加 `GenController::batchGenCode()` 和 `GenController::download()`：

* 渲染所有模板

* 打包为 ZIP 文件下载

### 步骤7：添加路由

在 `route.php` 中添加缺失的路由：

* `GET /tool/gen/batchGenCode` - 批量生成代码下载

* `GET /tool/gen/download/{tableName}` - 生成代码下载

### 步骤8：前端适配

修改前端页面适配 Webman 版本：

* 修改 genInfoForm.vue 中的模板类型选项（移除 Java 特有选项，添加 Webman 选项）

* 修改预览页面 tab 名称显示

## 关键文件清单

### 新增文件

* `d:\fuchenpro\webman\app\common\GenTemplateEngine.php` - 模板渲染引擎

* `d:\fuchenpro\webman\app\common\GenConstants.php` - 代码生成常量

* `d:\fuchenpro\webman\app\common\GenUtils.php` - 代码生成工具类

* `d:\fuchenpro\webman\resources\template\controller.php.vm` - 控制器模板

* `d:\fuchenpro\webman\resources\template\service.php.vm` - 服务层模板

* `d:\fuchenpro\webman\resources\template\model.php.vm` - 模型模板

* `d:\fuchenpro\webman\resources\template\api.js.vm` - API 模板

* `d:\fuchenpro\webman\resources\template\index.vue.vm` - 列表页模板

* `d:\fuchenpro\webman\resources\template\view.vue.vm` - 详情页模板

* `d:\fuchenpro\webman\resources\template\route.php.vm` - 路由模板

* `d:\fuchenpro\webman\resources\template\menu.sql.vm` - 菜单 SQL 模板

### 修改文件

* `d:\fuchenpro\webman\app\service\GenTableService.php` - 完善列初始化/编辑保存/预览/生成

* `d:\fuchenpro\webman\app\controller\tool\GenController.php` - 添加预览/下载/批量生成接口

* `d:\fuchenpro\webman\config\route.php` - 添加路由

* `d:\fuchenpro\front\src\views\tool\gen\genInfoForm.vue` - 适配 Webman 选项

## 预期结果

1. 导入表后，列属性自动初始化（Java类型、HTML控件、查询方式等）
2. 编辑配置后，列信息同步保存
3. 预览代码显示所有模板的渲染结果
4. 生成代码下载 ZIP 包含完整的 Webman 后端 + Vue3 前端代码
5. 生成的代码包含增删改查/排序/导出/权限控制等完整功能

