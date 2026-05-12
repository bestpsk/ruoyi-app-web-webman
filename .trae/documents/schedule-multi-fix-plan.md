# 行程安排多问题修复计划

## 问题清单

### 问题1：行程列表 - 员工姓名样式
**当前：** 员工姓名用 tag 包裹显示
**目标：** 姓名不用 tag，前面加上图标

**修改文件：** `schedule/index.vue`

**修改内容：**
```vue
<!-- 修改前 -->
<view class="user-badge">{{ item.userName || '-' }}</view>

<!-- 修改后 -->
<view class="user-info">
  <u-icon name="account-fill" size="16" color="#3D6DF7"></u-icon>
  <text class="user-name">{{ item.userName || '-' }}</text>
</view>
```

---

### 问题2：行程列表 - +N 数字不对
**当前：** 显示数量判断条件是 `> 3`，计算是 `length - 3`
**问题：** 实际显示6个，但判断条件没更新

**修改文件：** `schedule/index.vue`

**修改内容：**
```vue
<!-- 修改前 -->
<view class="date-tag more" v-if="item.scheduleDates.length > 3">
  +{{ item.scheduleDates.length - 3 }}
</view>

<!-- 修改后 -->
<view class="date-tag more" v-if="item.scheduleDates.length > 6">
  +{{ item.scheduleDates.length - 6 }}
</view>
```

---

### 问题3：新建行程 - 员工选择器
**当前：** 员工姓名是普通输入框
**目标：** 点击显示员工列表，可筛选选择

**修改文件：** `schedule/form.vue`

**修改内容：**
1. 添加员工列表 API
2. 添加员工选择器弹窗
3. 支持输入筛选

---

### 问题4：新建行程 - 企业选择器优化
**当前：** 使用 u-picker 选择器
**目标：** 改为带搜索的选择器

**修改文件：** `schedule/form.vue`

**修改内容：**
1. 改用弹窗 + 列表形式
2. 支持输入筛选企业名称

---

### 问题5：下店目的和状态改为选项卡
**当前：** 使用 u-picker 选择器
**目标：** 改为选项卡形式，快速选择

**修改文件：** `schedule/form.vue`

**修改内容：**
```vue
<!-- 下店目的选项卡 -->
<view class="form-item">
  <view class="form-label">下店目的</view>
  <view class="option-cards">
    <view v-for="item in purposeColumns" :key="item.value"
      class="option-card"
      :class="{ active: form.purpose === item.value }"
      @click="form.purpose = item.value; form.purposeName = item.label">
      {{ item.label }}
    </view>
  </view>
</view>

<!-- 状态选项卡 -->
<view class="form-item">
  <view class="form-label">状态</view>
  <view class="option-cards">
    <view v-for="item in statusColumns" :key="item.value"
      class="option-card"
      :class="{ active: form.status === item.value }"
      @click="form.status = item.value; form.statusName = item.label">
      {{ item.label }}
    </view>
  </view>
</view>
```

---

### 问题6：选择企业后提示"请选择企业"
**原因：** 表单验证规则问题，`enterpriseId` 字段验证未正确触发

**修改文件：** `schedule/form.vue`

**修改内容：**
在 `submitForm()` 中手动验证：
```javascript
async function submitForm() {
  if (!form.userName) { uni.showToast({ title: '请输入员工姓名', icon: 'none' }); return }
  if (!form.enterpriseId) { uni.showToast({ title: '请选择企业', icon: 'none' }); return }
  if (!form.startDate) { uni.showToast({ title: '请选择开始日期', icon: 'none' }); return }
  if (!form.endDate) { uni.showToast({ title: '请选择结束日期', icon: 'none' }); return }
  if (!form.purpose) { uni.showToast({ title: '请选择下店目的', icon: 'none' }); return }

  // 继续提交逻辑...
}
```

---

## 实施步骤

### 步骤1：修复行程列表问题
1. 修改员工姓名显示样式
2. 修复 +N 数字计算

### 步骤2：添加员工列表 API
```javascript
// api/system/user.js
export function listUser(params) {
  return request({ url: '/system/user/list', method: 'get', params })
}
```

### 步骤3：重构行程表单
1. 移除 u-form 验证，改用手动验证
2. 员工选择器改为弹窗 + 列表
3. 企业选择器改为弹窗 + 列表
4. 下店目的改为选项卡
5. 状态改为选项卡

### 步骤4：添加样式
```scss
.user-info {
  display: flex;
  align-items: center;
  gap: 8rpx;
}

.user-name {
  font-size: 28rpx;
  font-weight: 600;
  color: #1D2129;
}

.option-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 16rpx;
}

.option-card {
  padding: 16rpx 28rpx;
  background: #F7F8FA;
  border-radius: 8rpx;
  font-size: 26rpx;
  color: #4E5969;
  border: 2rpx solid transparent;

  &.active {
    background: #E8F0FE;
    color: #3D6DF7;
    border-color: #3D6DF7;
  }
}
```

---

## 验证方法
1. Vite 热更新自动生效
2. 行程列表 → 验证员工姓名显示
3. 行程列表 → 验证 +N 数字正确
4. 新建行程 → 验证员工选择器
5. 新建行程 → 验证企业选择器
6. 新建行程 → 验证选项卡选择
7. 新建行程 → 验证提交成功
