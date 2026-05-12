# 行程表单优化计划

## 需求分析

### 需求1：下店目的和状态从字典获取
**当前问题：** 下店目的和状态使用固定值

**字典类型：**
- `biz_schedule_purpose` - 下店目的
- `biz_schedule_status` - 行程状态

**解决方案：** 调用字典API获取实际数据

---

### 需求2：日期冲突检查
**两种方案对比：**

| 方案 | 优点 | 缺点 |
|-----|------|------|
| 保存时检查 | 实现简单 | 用户体验差，选择后才知道冲突 |
| 日历弹窗显示 | 直观，体验好 | 实现复杂，需要获取已有行程数据 |

**推荐方案：日历弹窗**
- 用户可直观看到哪些日期已有安排（红点标记）
- 已有安排的日期不可选中
- 避免选择后再提示错误的不良体验

---

## 实施步骤

### 步骤1：添加字典获取功能

**修改文件：** `schedule/form.vue`

```javascript
import { getDicts } from '@/api/system/dict/data'

const purposeColumns = ref([])
const statusColumns = ref([])

async function loadDictData() {
  try {
    const [purposeRes, statusRes] = await Promise.all([
      getDicts('biz_schedule_purpose'),
      getDicts('biz_schedule_status')
    ])
    purposeColumns.value = (purposeRes.data || []).map(p => ({
      label: p.dictLabel,
      value: p.dictValue
    }))
    statusColumns.value = (statusRes.data || []).map(p => ({
      label: p.dictLabel,
      value: p.dictValue
    }))
  } catch (e) {
    console.error('加载字典数据失败:', e)
  }
}
```

---

### 步骤2：添加获取已有行程日期的API

**修改文件：** `api/business/schedule.js`

```javascript
export function getScheduleDates(params) {
  return request({
    url: '/business/schedule/dates',
    method: 'get',
    params
  })
}
```

**后端接口说明：**
- 请求参数：`userId`（员工ID）、`yearMonth`（年月）
- 返回数据：已安排的日期数组 `['2026-05-01', '2026-05-02', ...]`

---

### 步骤3：实现日历选择器组件

**方案：使用 u-calendar 组件**

```vue
<u-calendar
  :show="showCalendarPicker"
  mode="range"
  :maxDate="maxDate"
  :minDate="minDate"
  :formatter="calendarFormatter"
  @confirm="onCalendarConfirm"
  @close="showCalendarPicker = false"
></u-calendar>
```

**日历标记逻辑：**
```javascript
const bookedDates = ref([])

async function loadBookedDates() {
  if (!form.userId) return
  try {
    const response = await getScheduleDates({
      userId: form.userId,
      yearMonth: queryParams.yearMonth
    })
    bookedDates.value = response.data || []
  } catch (e) {
    console.error('加载已安排日期失败:', e)
  }
}

function calendarFormatter(day) {
  const dateStr = `${day.year}-${String(day.month).padStart(2, '0')}-${String(day.day).padStart(2, '0')}`
  if (bookedDates.value.includes(dateStr)) {
    day.bottomInfo = '已安排'
    day.type = 'disabled'
  }
  return day
}
```

---

### 步骤4：修改日期选择交互

**当前：** 点击分别选择开始日期和结束日期

**修改为：** 点击弹出日历，范围选择

```vue
<view class="form-field" @click="showCalendarPicker = mode !== 'view'">
  <view class="field-input-box">
    <u-icon name="calendar" size="18" color="#86909C"></u-icon>
    <input class="field-input" :value="dateRangeText" placeholder="* 选择日期范围" placeholder-class="field-placeholder" disabled :disabledColor="'#fff'" />
    <u-icon v-if="mode !== 'view'" name="arrow-right" size="14" color="#C9CDD4"></u-icon>
  </view>
</view>
```

```javascript
const dateRangeText = computed(() => {
  if (form.startDate && form.endDate) {
    if (form.startDate === form.endDate) {
      return form.startDate
    }
    return `${form.startDate} 至 ${form.endDate}`
  }
  return ''
})

function onCalendarConfirm(e) {
  const [startDate, endDate] = e
  form.startDate = `${startDate.year}-${String(startDate.month).padStart(2, '0')}-${String(startDate.day).padStart(2, '0')}`
  form.endDate = `${endDate.year}-${String(endDate.month).padStart(2, '0')}-${String(endDate.day).padStart(2, '0')}`
  showCalendarPicker.value = false
}
```

---

### 步骤5：后端接口实现（可选）

**如果后端没有 `/business/schedule/dates` 接口，需要添加：**

**文件：** `webman/app/controller/business/ScheduleController.php`

```php
public function dates(Request $request)
{
    $userId = $request->get('user_id');
    $yearMonth = $request->get('year_month');
    
    $dates = BizSchedule::where('user_id', $userId)
        ->where('schedule_date', 'like', $yearMonth . '%')
        ->pluck('schedule_date')
        ->toArray();
    
    return json(['code' => 200, 'data' => $dates]);
}
```

---

## 简化方案（如果日历实现复杂）

如果日历方案实现复杂，可以先实现**保存时检查**：

```javascript
async function submitForm() {
  // ... 验证逻辑 ...

  // 检查日期冲突
  try {
    const checkResponse = await checkScheduleConflict({
      userId: form.userId,
      startDate: form.startDate,
      endDate: form.endDate,
      excludeId: form.scheduleId
    })
    if (checkResponse.data && checkResponse.data.length > 0) {
      uni.showModal({
        title: '日期冲突',
        content: `以下日期已有安排：${checkResponse.data.join('、')}`,
        showCancel: false
      })
      return
    }
  } catch (e) {
    console.error('检查日期冲突失败:', e)
  }

  // ... 继续提交 ...
}
```

---

## 验证方法
1. Vite 热更新自动生效
2. 新建行程 → 验证下店目的和状态从字典获取
3. 新建行程 → 选择日期 → 验证已有安排的日期标记
4. 新建行程 → 选择已有安排的日期 → 验证不可选中
