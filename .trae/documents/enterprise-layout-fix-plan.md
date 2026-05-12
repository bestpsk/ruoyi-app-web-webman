# 企业管理页面布局修复计划

## 问题分析

### 问题1: 搜索框白边
- **原因**：`.filter-btn` 使用了 `margin-right: -24rpx` 负边距来对齐右边缘
- **现象**：筛选按钮超出容器后，右侧露出白色间隙

### 问题2: 右侧内容被遮盖
- **原因**：`.list-scroll` 的 padding 只有 `24rpx`
- **现象**：企业卡片右侧的编辑/删除按钮被截断或紧贴边缘

## 修改文件
`AppV3/src/pages/business/enterprise/index.vue`

## 修复方案

### 1. 搜索框区域修复

**当前代码问题：**
```scss
.search-box {
  border-radius: 36rpx 0 0 36rpx;  // 左侧圆角
  padding: 0 0 0 24rpx;             // 无右padding
}

.filter-btn {
  border-radius: 0 36rpx 36rpx 0;    // 右侧圆角
  margin-right: -24rpx;             // 负边距导致溢出
}
```

**修改为：**
```scss
.search-section {
  padding: 20rpx 16rpx;             // 减小左右padding，给搜索框更多空间
}

.search-box {
  width: 100%;                      // 明确宽度
  box-sizing: border-box;           // 包含padding和border
  border-radius: 36rpx;             // 完整圆角
  padding: 0 8rpx 0 24rpx;          // 右侧留一点空间给筛选按钮
  overflow: hidden;                 // 防止溢出
}

.filter-btn {
  margin-right: 0;                  // 去掉负边距
  flex-shrink: 0;                   // 不收缩
}
```

### 2. 列表区域右侧间距修复

**当前代码：**
```scss
.list-scroll {
  padding: 20rpx 24rpx;
}
```

**修改为：**
```scss
.list-scroll {
  padding: 20rpx 28rpx 20rpx 24rpx;  // 右侧增加间距
}
```

或者统一调整容器：
```scss
.enterprise-container {
  padding-left: 20rpx;              // 左侧稍小
  padding-right: 28rpx;             // 右侧更大
}
```

### 3. 卡片操作按钮区域确保不被遮挡

检查 `.action-btns` 和 `.card-footer` 是否有足够的空间显示编辑/删除按钮。

## 预期效果

1. 搜索框与页面边缘有适当间距，无白边露出
2. 企业卡片右侧内容完整显示，编辑/删除按钮可见
3. 整体布局左右对称协调
