# 用户管理新增/导入/导出问题修复计划

## 问题分析

### 问题 1: 点击新增报错 "error"
前端调用 `GET /system/user/` (空 userId)，但后端路由无法匹配。

原始 Spring Boot 后端路由：`@GetMapping(value = { "/", "/{userId}" })`，可同时匹配两种路径。

### 问题 2: 导入导出接口不存在
前端期望的接口：
- `POST /system/user/importData` - 导入数据
- `POST /system/user/importTemplate` - 下载模板
- `POST /system/user/export` - 导出数据

## 修复方案

### 步骤 1: 安装 PhpSpreadsheet 库
```bash
composer require phpoffice/phpspreadsheet
```

### 步骤 2: 创建 Excel 工具类
参考原始后端的 ExcelUtil，创建 PHP 版本的 Excel 导入导出工具类。

### 步骤 3: 修改 getInfo 方法
参考原始后端，当 userId 为空时只返回 roles 和 posts；当 userId 有值时返回完整数据。

### 步骤 4: 添加导入导出接口
- `export` - 导出用户列表为 Excel
- `importData` - 导入 Excel 数据
- `importTemplate` - 下载导入模板

### 步骤 5: 添加 SysUserService.importUser 方法
处理导入逻辑：新增/更新用户。

### 步骤 6: 更新路由
注册新接口，并修改 getInfo 路由支持空 userId。
