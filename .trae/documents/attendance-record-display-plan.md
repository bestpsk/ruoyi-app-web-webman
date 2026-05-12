# 考勤记录显示优化计划

## 问题分析

### 问题1：考勤记录详情只显示上班/下班 ❌

**当前状态**：
- uni-app端的考勤记录页面（[record.vue](d:\fuchenpro\AppV3\src\pages\attendance\record.vue)）只显示主表的 `clockInTime` 和 `clockOutTime`
- web端的考勤记录详情弹窗也只显示主表数据

**用户需求**：
- 应该显示**每次打卡记录**
- 有几次就显示几次
- 数据来源：明细表 `biz_attendance_clock`

**当前代码**（record.vue 第62-71行）：
```html
<view class="record-times">
  <view class="time-item">
    <text class="time-label">上班</text>
    <text class="time-value">{{ formatTime(item.clockInTime) }}</text>
  </view>
  <view class="time-item">
    <text class="time-label">下班</text>
    <text class="time-value">{{ formatTime(item.clockOutTime) }}</text>
  </view>
</view>
```

---

### 问题2：web端列表员工姓名列太宽 ❌

**当前状态**：
- 员工姓名列没有设置宽度，自动撑开
- 占用了过多空间，其他列被压缩

**当前代码**（record.vue 第45行）：
```html
<el-table-column label="员工姓名" align="center" prop="userName" />
```

**需要修改**：
- 限制员工姓名列宽度
- 合理分配其他列的宽度

---

## 解决方案

### 方案1：uni-app端考勤记录显示优化 ⭐⭐⭐

#### 1.1 新增API接口获取打卡明细

**修改文件**：[attendance.js](d:\fuchenpro\AppV3\src\api\attendance.js)

**新增接口**：
```javascript
export function getClockListByRecordId(recordId) {
  return request({ url: '/business/attendance/clockList', method: 'get', params: { recordId } })
}
```

#### 1.2 后端新增接口

**修改文件**：[BizAttendanceController.php](d:\fuchenpro\webman\app\controller\business\BizAttendanceController.php)

**新增方法**：
```php
public function clockList(Request $request)
{
    $recordId = $request->get('record_id');
    $clocks = BizAttendanceClock::where('record_id', $recordId)
        ->orderBy('clock_time', 'asc')
        ->get();
    
    return AjaxResult::success($clocks);
}
```

#### 1.3 新增路由配置

**修改文件**：[route.php](d:\fuchenpro\webman\config\route.php)

**新增路由**：
```php
Route::get('/business/attendance/clockList', [app\controller\business\BizAttendanceController::class, 'clockList']);
```

#### 1.4 修改uni-app端考勤记录页面

**修改文件**：[record.vue](d:\fuchenpro\AppV3\src\pages\attendance\record.vue)

**修改内容**：

1. **引入新接口**：
```javascript
import { getRecordList, getMonthStats, getClockListByRecordId } from '@/api/attendance'
```

2. **添加数据结构**：
```javascript
const clockListMap = ref({})  // 存储每天的打卡明细
```

3. **修改加载逻辑**：
```javascript
async function loadData() {
  // 加载主表数据
  const res = await getRecordList(params)
  
  // 为每天加载打卡明细
  for (const item of res.rows || []) {
    if (item.recordId) {
      const clockRes = await getClockListByRecordId(item.recordId)
      clockListMap.value[item.attendanceDate] = clockRes.data || []
    }
  }
}
```

4. **修改模板显示**：
```html
<view v-for="(item, index) in recordList" :key="index" class="record-item">
  <view class="record-date">
    <text class="date-main">{{ item.attendanceDate }}</text>
    <view class="record-status-tag" :class="'tag-' + getStatusColor(item.attendanceStatus)">
      <text>{{ getStatusText(item.attendanceStatus) }}</text>
    </view>
  </view>
  
  <!-- 显示每次打卡记录 -->
  <view v-if="clockListMap[item.attendanceDate]?.length > 0" class="clock-list">
    <view 
      v-for="(clock, idx) in clockListMap[item.attendanceDate]" 
      :key="idx" 
      class="clock-detail-item"
    >
      <text class="clock-time">{{ formatTime(clock.clockTime) }}</text>
      <text class="clock-type-tag" :class="getClockTagClass(idx)">
        {{ getClockTagText(idx) }}
      </text>
      <text class="clock-address">{{ clock.address || '' }}</text>
    </view>
  </view>
</view>
```

5. **新增样式**：
```scss
.clock-list {
  display: flex;
  flex-direction: column;
  gap: 12rpx;
  margin-top: 16rpx;
}

.clock-detail-item {
  display: flex;
  align-items: center;
  gap: 16rpx;
  padding: 16rpx;
  background: #F7F8FA;
  border-radius: 10rpx;
}

.clock-time {
  font-size: 28rpx;
  font-weight: 600;
  color: #1D2129;
  width: 140rpx;
}

.clock-type-tag {
  padding: 4rpx 12rpx;
  border-radius: 6rpx;
  font-size: 22rpx;
  font-weight: 500;
}

.clock-address {
  font-size: 24rpx;
  color: #86909C;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
```

6. **新增辅助函数**：
```javascript
function getClockTagText(index) {
  if (index === 0) return '上班'
  if (index === 1) return '下班'
  return '补卡'
}

function getClockTagClass(index) {
  if (index === 0) return 'type-in'
  if (index === 1) return 'type-out'
  return 'type-supplement'
}
```

---

### 方案2：web端列表列宽优化 ⭐⭐

#### 2.1 修改表格列宽配置

**修改文件**：[record.vue](d:\fuchenpro\front\src\views\business\attendance\record.vue)

**修改位置**：第44-72行

**修改前**：
```html
<el-table-column label="员工姓名" align="center" prop="userName" />
<el-table-column label="考勤日期" align="center" prop="attendanceDate" width="120" />
<el-table-column label="上班时间" align="center" prop="clockInTime" width="160" />
<el-table-column label="下班时间" align="center" prop="clockOutTime" width="160" />
<el-table-column label="考勤状态" align="center" prop="attendanceStatus" width="100" />
<el-table-column label="打卡类型" align="center" prop="clockType" width="90" />
<el-table-column label="操作" align="center" width="100" />
```

**修改后**：
```html
<el-table-column label="员工姓名" align="center" prop="userName" width="100" min-width="80" show-overflow-tooltip />
<el-table-column label="考勤日期" align="center" prop="attendanceDate" width="110" />
<el-table-column label="上班时间" align="center" prop="clockInTime" width="150" />
<el-table-column label="下班时间" align="center" prop="clockOutTime" width="150" />
<el-table-column label="考勤状态" align="center" prop="attendanceStatus" width="90" />
<el-table-column label="打卡类型" align="center" prop="clockType" width="90" />
<el-table-column label="操作" align="center" class-name="small-padding fixed-width" width="90" />
```

#### 2.2 优化详情弹窗显示

**修改内容**：在详情弹窗中增加打卡明细列表

**新增区域**（在照片下方）：
```html
<!-- 打卡明细 -->
<div style="margin-top: 20px;">
  <el-divider content-position="left">打卡明细</el-divider>
  <el-table :data="detailClockList" size="small" border>
    <el-table-column label="#" type="index" width="50" align="center" />
    <el-table-column label="打卡时间" prop="clockTime" width="150" align="center" />
    <el-table-column label="类型" width="80" align="center">
      <template #default="scope">
        <dict-tag :options="biz_clock_type" :value="scope.row.clockType" size="small" />
      </template>
    </el-table-column>
    <el-table-column label="地址" prop="address" show-overflow-tooltip />
  </el-table>
</div>
```

**新增数据加载**：
```javascript
const detailClockList = ref([])

async function handleDetail(row) {
  detailData.value = row
  detailTitle.value = '考勤记录详情'
  detailOpen.value = true
  
  // 加载打卡明细
  if (row.recordId) {
    const res = await getClockListByRecordId(row.recordId)
    detailClockList.value = res.data || []
  }
}
```

---

## 文件修改清单

| 文件路径 | 修改内容 | 优先级 |
|---------|---------|--------|
| `AppV3/src/api/attendance.js` | 新增 `getClockListByRecordId` 接口 | ⭐⭐⭐ |
| `AppV3/src/pages/attendance/record.vue` | 重构为显示每次打卡记录 | ⭐⭐⭐ |
| `webman/config/route.php` | 新增 `/business/attendance/clockList` 路由 | ⭐⭐⭐ |
| `webman/app/controller/business/BizAttendanceController.php` | 新增 `clockList` 方法 | ⭐⭐⭐ |
| `front/src/api/business/attendance.js` | 新增 `getClockListByRecordId` 接口 | ⭐⭐ |
| `front/src/views/business/attendance/record.vue` | 调整列宽 + 详情弹窗增加明细 | ⭐⭐ |

---

## 视觉效果预期

### uni-app端考勤记录页面

**修改前**：
```
┌─────────────────────────────┐
│ 2026-04-30     [早退]       │
│                             │
│ 上班  09:05                │
│ 下班  --                   │
└─────────────────────────────┘
```

**修改后**：
```
┌─────────────────────────────┐
│ 2026-04-30     [正常]       │
│                             │
│ 09:05  [上班] 北京市朝阳区... │
│ 12:30  [下班] 北京市朝阳区... │
│ 14:00  [补卡] 北京市海淀区... │
│ 18:15  [补卡] 北京市海淀区... │
└─────────────────────────────┘
```

### web端数据列表

**修改前**：
```
员工姓名(很宽) | 考勤日期 | 上班时间 | 下班时间 | 状态 | 类型 | 操作
若依          | 2026-04-30 | 01:06:48 | 01:06:48 | 早退 | -- | 详情
```

**修改后**：
```
员工姓名(100px) | 考勤日期 | 上班时间 | 下班时间 | 状态 | 类型 | 操作
若依            | 2026-04-30 | 01:06:48 | 01:06:48 | 早退 | -- | 详情
```

---

## 实施顺序

### 阶段1：后端接口准备（优先级：高）
1. 在 `BizAttendanceController.php` 新增 `clockList` 方法
2. 在 `route.php` 新增路由配置

### 阶段2：前端API接口（优先级：高）
3. uni-app端：新增 `getClockListByRecordId` 接口
4. web端：新增 `getClockListByRecordId` 接口

### 阶段3：uni-app端UI重构（优先级：高）
5. 修改 `record.vue` 显示逻辑
6. 添加打卡明细列表样式
7. 测试多次打卡场景

### 阶段4：web端优化（优先级：中）
8. 调整表格列宽配置
9. 详情弹窗增加打卡明细表格
10. 测试显示效果

---

## 注意事项

1. **性能考虑**：避免一次性加载所有日期的打卡明细，采用按需加载或分页
2. **数据格式**：确保后端返回的时间格式与前端一致
3. **空数据处理**：当某天没有打卡时，显示"暂无打卡记录"
4. **标签颜色**：与主页保持一致（上班蓝色、下班绿色、补卡橙色）

---

## 验证清单

- [ ] 后端接口已新增
- [ ] uni-app端API已添加
- [ ] web端API已添加
- [ ] 考勤记录页面显示每次打卡
- [ ] 标签正确显示（上班/下班/补卡）
- [ ] web端列宽调整合理
- [ ] 详情弹窗显示打卡明细
- [ ] 多次打卡测试通过
