# 搜索区域UI优化计划

## 目标
优化企业管理列表页的搜索和筛选区域的UI布局

## 当前问题
1. 筛选标签显示 `filter` 图标 + "筛选" 文字
2. 搜索框右侧有独立的 "搜索" 按钮
3. 筛选按钮在搜索框下方单独一行

## 修改目标
1. **筛选按钮**：去掉 `filter` 图标，只显示 "筛选" 文字
2. **搜索按钮**：去掉右侧 "搜索" 按钮文字
3. **自动搜索**：输入框内容变化时自动触发搜索（使用防抖）
4. **布局调整**：将筛选按钮放入搜索框内部右侧

## 修改文件
`AppV3/src/pages/business/enterprise/index.vue`

## 实施步骤

### 步骤1: 修改模板结构

将原来的：
```
┌─────────────────────────────┐
│ [🔍 搜索输入框...    搜索] │ ← 搜索区
├─────────────────────────────┤
│ [filter] [筛选]             │ ← 筛选区(独立行)
└─────────────────────────────┘
```

改为：
```
┌─────────────────────────────┐
│ [🔍 搜索输入框...   筛选 ▼] │ ← 搜索+筛选合并
└─────────────────────────────┘
```

### 步骤2: 具体代码变更

#### 2.1 模板部分 (template)

```html
<!-- 删除 filter-section 整块 -->
<!-- 修改 search-section 内容 -->

<view class="search-section">
  <view class="search-box">
    <u-icon name="search" size="16" color="#86909C" class="search-icon"></u-icon>
    <input
      class="search-input"
      type="text"
      v-model="queryParams.keyword"
      placeholder="搜索企业名称/老板/电话"
      placeholder-class="search-placeholder"
      @input="onSearchInput"
    />
    <view v-if="queryParams.keyword" class="clear-btn" @click="clearKeyword">
      <u-icon name="close-circle-fill" size="14" color="#C9CDD4"></u-icon>
    </view>
    <view class="filter-btn" @click="toggleFilter">
      <text>筛选</text>
      <u-icon name="arrow-down" size="12" :class="{ rotate: showFilter }"></u-icon>
    </view>
  </view>
</view>

<!-- 已激活的筛选标签移到搜索框下方作为独立行显示 -->
<view v-if="hasActiveFilters" class="active-filters">
  <scroll-view scroll-x class="filter-scroll">
    <view class="filter-tags">
      <!-- 已选中的筛选项 -->
    </view>
  </scroll-view>
</view>
```

#### 2.2 脚本部分 (script)

添加防抖搜索函数：

```js
let searchTimer = null

function onSearchInput() {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    handleSearch()
  }, 500)
}

function clearKeyword() {
  queryParams.keyword = ''
  handleSearch()
}
```

添加计算属性判断是否有已激活的筛选：

```js
const hasActiveFilters = computed(() => {
  return queryParams.enterpriseType || queryParams.enterpriseLevel ||
         (queryParams.status !== '' && queryParams.status !== undefined)
})
```

#### 2.3 样式部分 (style)

- `.search-box`：白色圆角背景，flex 布局
- `.search-input`：flex: 1，无边框
- `.filter-btn`：蓝色文字 + 下拉箭头
- `.rotate`：箭头旋转动画

## 预期效果

```
┌──────────────────────────────────┐
│ 🔍 搜索企业名称/老板/电话    筛选 ▼│  ← 蓝色渐变背景
├──────────────────────────────────┤
│ [加盟店 ×] [A级 ×]               │  ← 有选中项时显示
├──────────────────────────────────┤
│                                  │
│   企业卡片列表...                │
│                                  │
└──────────────────────────────────┘
```
