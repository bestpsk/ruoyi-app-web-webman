# 考勤打卡优化计划

## 一、三个问题分析

### 问题1：坐班/外勤打卡类型选择

**现状**：打卡页面只有一种打卡模式，没有区分坐班和外勤。

**方案**：在打卡页面上方（定位信息区下方）添加打卡类型切换，使用 `u-tabs` 或两个按钮切换：
- **坐班打卡**：需要定位验证（在考勤点范围内才能打卡）
- **外勤打卡**：不需要定位验证，但必须拍照+填写外勤事由

选择位置放在**打卡按钮上方**，作为 Tab 切换，视觉清晰且操作便捷。

**后端改动**：
- `biz_attendance_record` 表新增 `clock_type` 字段（char(1)：0坐班/1外勤）
- `biz_attendance_record` 表新增 `outside_reason` 字段（varchar(500)：外勤事由）
- 打卡接口增加 `clockType` 和 `outsideReason` 参数
- 外勤打卡时跳过距离校验

### 问题2：H5 定位失败

**原因**：`manifest.json` 的 `h5` 配置中缺少高德地图 SDK Key。在 H5 端，`uni.getLocation` 底层依赖高德 JS SDK，没有 Key 就无法定位和逆地理编码。

**方案**：
1. 在 `manifest.json` 的 `h5` 节点下添加 `sdkKey.map` 配置
2. 打卡页面增加定位失败时的降级处理：允许手动输入地址
3. 增加定位加载状态提示

**注意**：需要申请高德地图 Web 端 JS API Key（免费），填入配置即可。开发环境使用 localhost 不受 HTTPS 限制。

### 问题3：考勤规则经纬度可视化选择

**现状**：Web 端考勤规则表单中经纬度为纯文本输入，用户体验差。

**方案**：集成高德地图选点组件，点击输入框弹出地图，在地图上点击/搜索选点后自动回填经纬度和地址。

**技术选型**：使用 `@amap/amap-jsapi-loader` 加载高德地图 JS API 2.0，封装一个 `MapPicker` 弹窗组件。

---

## 二、具体实施步骤

### 步骤1：数据库变更
- 修改 `biz_attendance_record` 表，新增 `clock_type` 和 `outside_reason` 字段
- 新增字典 `biz_clock_type`（坐班/外勤）
- 更新 SQL 脚本

### 步骤2：后端 Model/Service/Controller 改动
- `BizAttendanceRecord` Model 的 `$fillable` 增加 `clock_type`、`outside_reason`
- `BizAttendanceRecordService.clockIn()` 和 `clockOut()` 增加 `clockType` 判断：
  - `clockType = '0'`（坐班）：执行距离校验逻辑
  - `clockType = '1'`（外勤）：跳过距离校验，但 `outsideReason` 必填
- Controller 接收新参数

### 步骤3：移动端打卡页面改造
- 在定位信息区下方添加打卡类型切换（坐班/外勤 Tab）
- 坐班模式：显示定位信息 + 距离校验
- 外勤模式：隐藏定位校验，显示外勤事由输入框 + 必须拍照
- 增加定位加载中状态（loading）
- 增加定位失败降级处理（允许手动输入地址）
- 打卡提交时传递 `clockType` 和 `outsideReason`

### 步骤4：manifest.json 配置高德地图 Key
- 在 `h5` 节点添加 `sdkKey.map` 配置
- 需要用户提供高德地图 Web 端 JS API Key

### 步骤5：Web 端集成高德地图选点组件
- 安装 `@amap/amap-jsapi-loader` 依赖
- 创建 `src/components/MapPicker/index.vue` 组件：
  - 弹窗形式，内嵌高德地图
  - 支持搜索地址
  - 点击地图选点
  - 确认后返回经纬度 + 地址
- 修改 `rule.vue`：将经纬度输入框改为点击弹出 MapPicker

### 步骤6：Web 端考勤记录页适配
- `record.vue` 表格新增"打卡类型"列（dict-tag 展示）
- 详情弹窗新增"打卡类型"和"外勤事由"字段

---

## 三、文件变更清单

### 新建文件
| 文件 | 说明 |
|------|------|
| `front/src/components/MapPicker/index.vue` | 高德地图选点弹窗组件 |

### 修改文件
| 文件 | 修改内容 |
|------|----------|
| `webman/sql/biz_attendance.sql` | 新增字段 + 字典 |
| `webman/app/model/BizAttendanceRecord.php` | fillable 增加字段 |
| `webman/app/service/BizAttendanceRecordService.php` | clockIn/clockOut 增加坐班/外勤逻辑 |
| `webman/app/controller/business/BizAttendanceController.php` | 接收新参数 |
| `AppV3/src/pages/attendance/index.vue` | 打卡类型切换 + 外勤事由 + 定位优化 |
| `AppV3/src/manifest.json` | h5.sdkKey.map 配置 |
| `front/src/views/business/attendance/rule.vue` | 经纬度改为地图选点 |
| `front/src/views/business/attendance/record.vue` | 新增打卡类型列和详情字段 |
| `front/package.json` | 新增 @amap/amap-jsapi-loader 依赖 |

---

## 四、高德地图 Key 说明

需要申请两个 Key：
1. **Web 端 JS API Key**（用于 front 项目地图选点）— 申请时选择"Web端(JS API)"
2. **Web 端 JS API Key**（用于 AppV3 H5 端定位）— 可复用同一个 Key

申请地址：https://console.amap.com/dev/key/app
- 服务平台选择"Web端(JS API)"
- 安全密钥可按需配置

如果暂时没有 Key，可先完成其他步骤，Key 配置后即可生效。
