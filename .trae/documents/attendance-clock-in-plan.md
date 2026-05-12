# 考勤打卡功能实施计划（双端）

## 一、功能概述

实现员工考勤打卡功能，覆盖两个前端：

| 端 | 项目路径 | 定位 | 核心功能 |
|---|---|---|---|
| **移动端（uni-app）** | `d:\fuchenpro\AppV3` | 员工使用 | GPS定位、拍照打卡、查看个人记录 |
| **Web管理端（front）** | `d:\fuchenpro\front` | 管理员使用 | 考勤规则配置、记录查询、月度统计 |

共享后端：`d:\fuchenpro\webman`

---

## 二、数据库设计

### 2.1 考勤规则表 `biz_attendance_rule`

| 字段 | 类型 | 说明 |
|------|------|------|
| rule_id | bigint AUTO_INCREMENT | 主键 |
| rule_name | varchar(100) | 规则名称（如"标准班"） |
| work_start_time | time | 上班时间（如 09:00:00） |
| work_end_time | time | 下班时间（如 18:00:00） |
| late_threshold | int | 迟到容忍分钟数（如 0 表示准点即迟到） |
| early_leave_threshold | int | 早退容忍分钟数 |
| work_latitude | decimal(10,7) | 考勤点纬度 |
| work_longitude | decimal(10,7) | 考勤点经度 |
| allowed_distance | int | 允许打卡距离（米），如 500 |
| status | char(1) | 状态 0正常/1停用 |
| remark | varchar(500) | 备注 |
| create_by | varchar(64) | 创建者 |
| create_time | datetime | 创建时间 |
| update_by | varchar(64) | 更新者 |
| update_time | datetime | 更新时间 |

### 2.2 考勤记录表 `biz_attendance_record`

| 字段 | 类型 | 说明 |
|------|------|------|
| record_id | bigint AUTO_INCREMENT | 主键 |
| user_id | bigint | 用户ID |
| user_name | varchar(30) | 用户姓名（冗余） |
| attendance_date | date | 考勤日期 |
| clock_in_time | datetime | 上班打卡时间 |
| clock_out_time | datetime | 下班打卡时间 |
| clock_in_latitude | decimal(10,7) | 上班打卡纬度 |
| clock_in_longitude | decimal(10,7) | 上班打卡经度 |
| clock_in_address | varchar(255) | 上班打卡地址 |
| clock_in_photo | varchar(500) | 上班打卡照片URL |
| clock_out_latitude | decimal(10,7) | 下班打卡纬度 |
| clock_out_longitude | decimal(10,7) | 下班打卡经度 |
| clock_out_address | varchar(255) | 下班打卡地址 |
| clock_out_photo | varchar(500) | 下班打卡照片URL |
| attendance_status | char(1) | 考勤状态：0正常/1迟到/2早退/3迟到+早退/4缺勤 |
| rule_id | bigint | 关联考勤规则ID |
| remark | varchar(500) | 备注 |
| create_by | varchar(64) | 创建者 |
| create_time | datetime | 创建时间 |
| update_by | varchar(64) | 更新者 |
| update_time | datetime | 更新时间 |

**唯一约束**：`UNIQUE(user_id, attendance_date)` — 每人每天只有一条记录

---

## 三、后端实现（Webman）

### 3.1 新建文件清单

| 文件 | 说明 |
|------|------|
| `app/model/BizAttendanceRule.php` | 考勤规则模型 |
| `app/model/BizAttendanceRecord.php` | 考勤记录模型 |
| `app/service/BizAttendanceRuleService.php` | 考勤规则服务 |
| `app/service/BizAttendanceRecordService.php` | 考勤记录服务 |
| `app/controller/business/BizAttendanceController.php` | 考勤控制器（打卡+记录+规则） |
| `sql/biz_attendance.sql` | 建表SQL + 字典 + 菜单 |

### 3.2 Model 结构

**BizAttendanceRule**：
- `$table = 'biz_attendance_rule'`，`$primaryKey = 'rule_id'`，`$timestamps = false`
- `$fillable` 包含所有业务字段

**BizAttendanceRecord**：
- `$table = 'biz_attendance_record'`，`$primaryKey = 'record_id'`，`$timestamps = false`
- `$fillable` 包含所有业务字段

### 3.3 Service 层

**BizAttendanceRuleService**：
- `selectRuleList($params)` — 分页列表
- `selectRuleById($ruleId)` — 详情
- `insertRule($data)` — 新增
- `updateRule($data)` — 修改
- `deleteRuleByIds($ruleIds)` — 删除
- `getActiveRule()` — 获取当前生效的考勤规则（status=0 的第一条）

**BizAttendanceRecordService**：
- `selectRecordList($params)` — 分页列表（支持按用户/日期范围/状态筛选）
- `selectRecordById($recordId)` — 详情
- `getTodayRecord($userId)` — 获取今日打卡记录
- `clockIn($data)` — 上班打卡
- `clockOut($data)` — 下班打卡
- `generateDailyAbsence()` — 生成缺勤记录（预留，定时任务调用）
- `getMonthStats($userId, $month)` — 月度统计（正常/迟到/早退/缺勤天数）

### 3.4 Controller + 路由

**BizAttendanceController**：

| 方法 | 路由 | 说明 | 使用端 |
|------|------|------|--------|
| `todayRecord` | GET `/business/attendance/todayRecord` | 今日打卡状态 | 移动端 |
| `clockIn` | POST `/business/attendance/clockIn` | 上班打卡 | 移动端 |
| `clockOut` | POST `/business/attendance/clockOut` | 下班打卡 | 移动端 |
| `monthStats` | GET `/business/attendance/monthStats` | 月度统计 | 双端 |
| `recordList` | GET `/business/attendance/record/list` | 打卡记录列表 | 双端 |
| `recordInfo` | GET `/business/attendance/record/{recordId}` | 记录详情 | Web端 |
| `ruleList` | GET `/business/attendance/rule/list` | 考勤规则列表 | Web端 |
| `ruleInfo` | GET `/business/attendance/rule/{ruleId}` | 规则详情 | Web端 |
| `addRule` | POST `/business/attendance/rule` | 新增规则 | Web端 |
| `editRule` | PUT `/business/attendance/rule` | 修改规则 | Web端 |
| `removeRule` | DELETE `/business/attendance/rule/{ruleIds}` | 删除规则 | Web端 |

在 `config/route.php` 的 `business/employeeConfig` 路由之后注册。

### 3.5 打卡核心逻辑

**上班打卡 `clockIn`**：
1. 获取当前用户
2. 接收参数：`latitude`、`longitude`、`address`、`photo`
3. 查询今日是否已有记录
4. 若已有上班卡 → 返回错误"已打过上班卡"
5. 获取生效考勤规则 `getActiveRule()`
6. Haversine 公式计算距离，超过 `allowed_distance` → 返回错误"不在考勤范围内"
7. 判断迟到：当前时间 > `work_start_time + late_threshold`
8. 创建/更新记录，返回打卡结果

**下班打卡 `clockOut`**：
1. 获取当前用户
2. 接收参数：`latitude`、`longitude`、`address`、`photo`
3. 查询今日记录
4. 若无上班卡 → 返回错误"请先打上班卡"
5. 若已有下班卡 → 返回错误"已打过下班卡"
6. 判断早退：当前时间 < `work_end_time - early_leave_threshold`
7. 若原状态为迟到且现在早退 → 状态改为"迟到+早退"(3)
8. 更新记录，返回打卡结果

---

## 四、移动端实现（uni-app）

### 4.1 新建文件

| 文件 | 说明 |
|------|------|
| `src/pages/attendance/index.vue` | 打卡主页面 |
| `src/pages/attendance/record.vue` | 打卡记录页面 |
| `src/api/attendance.js` | 考勤API |

### 4.2 修改文件

| 文件 | 修改内容 |
|------|----------|
| `src/pages.json` | 注册2个新页面路由 |
| `src/components/home/QuickMenu.vue` | 打卡按钮 path → `/pages/attendance/index` |
| `src/manifest.json` | 添加定位权限 |

### 4.3 API 定义 `api/attendance.js`

```javascript
import request from '@/utils/request'
import upload from '@/utils/upload'

export function getTodayRecord() {
  return request({ url: '/business/attendance/todayRecord', method: 'get' })
}
export function clockIn(data) {
  return request({ url: '/business/attendance/clockIn', method: 'post', data })
}
export function clockOut(data) {
  return request({ url: '/business/attendance/clockOut', method: 'post', data })
}
export function getMonthStats(params) {
  return request({ url: '/business/attendance/monthStats', method: 'get', params })
}
export function getRecordList(params) {
  return request({ url: '/business/attendance/record/list', method: 'get', params })
}
export function uploadAttendancePhoto(data) {
  return upload({ url: '/common/upload', name: 'file', filePath: data.filePath })
}
```

### 4.4 打卡页面 `pages/attendance/index.vue`

**页面结构**（对齐首页色调 #3D6DF7）：
1. **顶部状态区** — 日期 + 问候语
2. **定位信息区** — 当前位置（地址 + 经纬度），定位失败显示重试
3. **打卡按钮区** — 中央大按钮
   - 未打上班卡 → "上班打卡"（绿色 #52c41a）
   - 已打上班卡未打下班卡 → "下班打卡"（蓝色 #3D6DF7）
   - 已完成 → "已完成打卡"（灰色不可点）
4. **今日打卡信息卡片** — 上班/下班时间 + 状态标签
5. **拍照区域** — 打卡前必须拍照

**交互流程**：
1. 进入页面 → 自动 `uni.getLocation({ type: 'gcj02' })` → 调用 `getTodayRecord`
2. 点击打卡 → 检查定位 → 检查是否已拍照
3. 未拍照 → `uni.chooseImage({ sourceType: ['camera'] })` → `uploadAttendancePhoto` 上传
4. 上传成功 → 调用 `clockIn` 或 `clockOut`
5. 打卡成功 → 刷新今日记录

### 4.5 打卡记录页面 `pages/attendance/record.vue`

- 月度日历视图，每日标记考勤状态颜色
- 下方列表显示当月打卡记录详情
- 支持切换月份

### 4.6 配置修改

**pages.json** 添加：
```json
{ "path": "pages/attendance/index", "style": { "navigationBarTitleText": "考勤打卡" } },
{ "path": "pages/attendance/record", "style": { "navigationBarTitleText": "打卡记录" } }
```

**manifest.json** `mp-weixin` 下添加：
```json
"permission": {
  "scope.userLocation": { "desc": "你的位置信息将用于考勤打卡" }
}
```

**QuickMenu.vue** 打卡按钮 `path: ''` → `path: '/pages/attendance/index'`

---

## 五、Web管理端实现（front）

### 5.1 新建文件

| 文件 | 说明 |
|------|------|
| `src/api/business/attendance.js` | 考勤API |
| `src/views/business/attendance/record.vue` | 考勤记录管理页 |
| `src/views/business/attendance/rule.vue` | 考勤规则管理页 |

### 5.2 修改文件

| 文件 | 修改内容 |
|------|----------|
| 无需修改路由文件 | 路由由后端菜单动态生成 |

### 5.3 API 定义 `api/business/attendance.js`

遵循项目 API 规范（`listXxx/getXxx/addXxx/updateXxx/delXxx`）：

```javascript
import request from '@/utils/request'

export function listAttendanceRecord(query) {
  return request({ url: '/business/attendance/record/list', method: 'get', params: query })
}
export function getAttendanceRecord(recordId) {
  return request({ url: '/business/attendance/record/' + recordId, method: 'get' })
}
export function listAttendanceRule(query) {
  return request({ url: '/business/attendance/rule/list', method: 'get', params: query })
}
export function getAttendanceRule(ruleId) {
  return request({ url: '/business/attendance/rule/' + ruleId, method: 'get' })
}
export function addAttendanceRule(data) {
  return request({ url: '/business/attendance/rule', method: 'post', data })
}
export function updateAttendanceRule(data) {
  return request({ url: '/business/attendance/rule', method: 'put', data })
}
export function delAttendanceRule(ruleId) {
  return request({ url: '/business/attendance/rule/' + ruleId, method: 'delete' })
}
export function getAttendanceMonthStats(query) {
  return request({ url: '/business/attendance/monthStats', method: 'get', params: query })
}
```

### 5.4 考勤记录管理页 `views/business/attendance/record.vue`

遵循项目标准 CRUD 页面模式（参考 enterprise/index.vue）：

**页面结构**：
1. **搜索表单** `el-form :inline="true"`
   - 员工姓名（输入框）
   - 考勤日期范围（`el-date-picker` type="daterange"）
   - 考勤状态（`el-select` + 字典 `biz_attendance_status`）
2. **操作按钮行** — 导出按钮 + `<right-toolbar>`
3. **数据表格** `el-table`
   - 列：员工姓名、考勤日期、上班时间、下班时间、考勤状态（`dict-tag`）、操作
   - 操作列：查看详情
4. **分页组件** `<pagination>`
5. **详情弹窗** `el-dialog` + `el-descriptions`
   - 展示完整打卡信息：时间、地址、经纬度、照片（`image-preview`）

**脚本模式**：
```javascript
import { listAttendanceRecord, getAttendanceRecord } from "@/api/business/attendance"
const { proxy } = getCurrentInstance()
const { biz_attendance_status } = useDict("biz_attendance_status")
// 标准 CRUD 变量和方法...
```

### 5.5 考勤规则管理页 `views/business/attendance/rule.vue`

**页面结构**：
1. **搜索表单** — 规则名称、状态
2. **操作按钮行** — 新增/修改/删除 + `<right-toolbar>`
3. **数据表格** — 规则名称、上班时间、下班时间、迟到容忍、早退容忍、考勤地点、允许距离、状态、操作
4. **分页组件**
5. **新增/修改弹窗** `el-dialog` + `el-form`
   - 规则名称（输入框）
   - 上班时间（`el-time-picker`）
   - 下班时间（`el-time-picker`）
   - 迟到容忍分钟数（`el-input-number`）
   - 早退容忍分钟数（`el-input-number`）
   - 考勤点纬度/经度（输入框，支持地图选点）
   - 允许打卡距离（`el-input-number`，单位米）
   - 状态（`el-radio` + 字典 `sys_normal_disable`）
   - 备注（`el-input` textarea）

### 5.6 后端菜单配置（SQL）

在 `sql/biz_attendance.sql` 中插入菜单数据，菜单结构：

```
考勤管理（一级菜单，目录）
├── 考勤记录（二级菜单，页面）→ component: business/attendance/record
└── 考勤规则（二级菜单，页面）→ component: business/attendance/rule
```

菜单权限标识：
- `business:attendance:record:list` / `query` / `detail`
- `business:attendance:rule:list` / `query` / `add` / `edit` / `remove`

---

## 六、字典数据

| 字典类型 | 字典标签 | 字典值 | 样式 |
|----------|---------|--------|------|
| `biz_attendance_status` | 正常 | 0 | success（绿色） |
| | 迟到 | 1 | warning（橙色） |
| | 早退 | 2 | warning（橙色） |
| | 迟到+早退 | 3 | danger（红色） |
| | 缺勤 | 4 | danger（红色） |

---

## 七、考勤状态判断规则

| 场景 | 上班卡时间 | 下班卡时间 | 状态 |
|------|-----------|-----------|------|
| 正常 | ≤ 上班时间 + 迟到容忍 | ≥ 下班时间 - 早退容忍 | 0 正常 |
| 迟到 | > 上班时间 + 迟到容忍 | ≥ 下班时间 - 早退容忍 | 1 迟到 |
| 早退 | ≤ 上班时间 + 迟到容忍 | < 下班时间 - 早退容忍 | 2 早退 |
| 迟到+早退 | > 上班时间 + 迟到容忍 | < 下班时间 - 早退容忍 | 3 迟到+早退 |
| 缺勤 | 无上班卡 | 无下班卡 | 4 缺勤 |

---

## 八、实施步骤（按顺序执行）

### 第1步：数据库
- 创建 `webman/sql/biz_attendance.sql`
- 包含：建表语句 + 字典类型/数据 + 菜单 + 默认考勤规则

### 第2步：后端 Model
- 创建 `BizAttendanceRule.php`
- 创建 `BizAttendanceRecord.php`

### 第3步：后端 Service
- 创建 `BizAttendanceRuleService.php`（规则CRUD + getActiveRule）
- 创建 `BizAttendanceRecordService.php`（记录CRUD + clockIn + clockOut + 月度统计）

### 第4步：后端 Controller + 路由
- 创建 `BizAttendanceController.php`（11个接口方法）
- 在 `config/route.php` 注册路由

### 第5步：移动端 API + 配置
- 创建 `AppV3/src/api/attendance.js`
- 修改 `AppV3/src/pages.json` 注册页面
- 修改 `AppV3/src/manifest.json` 添加定位权限
- 修改 `AppV3/src/components/home/QuickMenu.vue` 打卡路径

### 第6步：移动端打卡页面
- 创建 `AppV3/src/pages/attendance/index.vue`（定位+拍照+打卡）

### 第7步：移动端记录页面
- 创建 `AppV3/src/pages/attendance/record.vue`（日历+列表）

### 第8步：Web端 API
- 创建 `front/src/api/business/attendance.js`

### 第9步：Web端考勤记录页
- 创建 `front/src/views/business/attendance/record.vue`（搜索+表格+详情弹窗）

### 第10步：Web端考勤规则页
- 创建 `front/src/views/business/attendance/rule.vue`（搜索+表格+新增/修改弹窗）
