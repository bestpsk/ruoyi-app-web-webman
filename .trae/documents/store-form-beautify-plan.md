# 门店表单样式美化计划

## 目标
参考企业管理表单的紧凑设计，重新设计门店表单：
- ✅ 每条数据显示一行
- ✅ 标题文字显示在输入框内部（作为 placeholder）
- ✅ 输入数据后标题文字仍存在（placeholder 效果）
- ✅ 每个字段前添加合适的图标标签

---

## 设计规范（参考 enterprise/form.vue）

### 输入框结构
```
[图标] [标题文字(placeholder)] [输入内容]
```

### 图标映射表
| 字段 | 图标名称 | 说明 |
|-----|---------|------|
| 所属企业 | `home-fill` | 企业归属 |
| 门店名称 | `shop` | 店铺标识 |
| 负责人 | `account` | 人员信息 |
| 联系电话 | `phone` | 电话联系 |
| 微信 | `chat` | 社交账号 |
| 开始营业 | `clock` | 时间开始 |
| 结束营业 | `clock` | 时间结束 |
| 地址 | `map` | 地理位置 |
| 年业绩 | `red-packet-fill` | 业绩收入 |
| 常来顾客 | `group` | 人群数量 |
| 服务员工 | `man-add` | 服务人员 |
| 备注 | `edit-pen` | 文本编辑 |

---

## 实施步骤

### 步骤1：重构模板结构
移除 `<u-form>` 和 `<u-form-item>` 组件包裹，改为扁平化 view 结构：

**当前结构（复杂）：**
```vue
<u-form ref="formRef" :model="form" :rules="rules">
  <u-form-item label="所属企业" prop="enterpriseId">
    <u-input v-model="form.enterpriseName" ...></u-input>
  </u-form-item>
  ...
</u-form>
```

**目标结构（简洁）：**
```vue
<view class="form-section">
  <!-- 所属企业 - 选择器 -->
  <view class="form-field" @click="showEnterprisePicker = mode !== 'view'">
    <view class="field-input-box">
      <u-icon name="home-fill" size="18" color="#86909C"></u-icon>
      <input class="field-input" :value="form.enterpriseName"
        placeholder="* 所属企业" disabled :disabledColor="'#fff'" />
      <u-icon v-if="mode !== 'view'" name="arrow-right" size="14" color="#C9CDD4"></u-icon>
    </view>
  </view>

  <!-- 门店名称 - 文本输入 -->
  <view class="form-field">
    <view class="field-input-box">
      <u-icon name="shop" size="18" color="#86909C"></u-icon>
      <input class="field-input" type="text" v-model="form.storeName"
        placeholder="* 门店名称" placeholder-class="field-placeholder" />
    </view>
  </view>

  <!-- ... 其他字段类似 ... -->

  <!-- 营业时间 - 并排两列 -->
  <view class="form-row">
    <view class="form-field half-width">
      <view class="field-input-box">
        <u-icon name="clock" size="16" color="#86909C"></u-icon>
        <input class="field-input" v-model="form.businessStart"
          placeholder="开始时间" placeholder-class="field-placeholder" />
      </view>
    </view>
    <view class="form-field half-width">
      <view class="field-input-box">
        <u-icon name="clock" size="16" color="#86909C"></u-icon>
        <input class="field-input" v-model="form.businessEnd"
          placeholder="结束时间" placeholder-class="field-placeholder" />
      </view>
    </view>
  </view>

  <!-- 地址 - 多行文本 -->
  <view class="form-field">
    <view class="field-textarea-box">
      <view class="textarea-prefix">
        <u-icon name="map" size="18" color="#86909C"></u-icon>
        <text class="prefix-text">地址</text>
      </view>
      <textarea class="field-textarea" v-model="form.address"
        placeholder="请输入地址" :maxlength="200" auto-height></textarea>
    </view>
  </view>

  <!-- 状态 - 自定义 Radio -->
  <view class="form-field status-field">
    <view class="status-options">
      <view class="status-item" :class="{ active: form.status === '0' }"
        @click="mode !== 'view' && (form.status = '0')">
        <view class="status-radio" :class="{ checked: form.status === '0' }"></view>
        <text>正常</text>
      </view>
      <view class="status-item" :class="{ active: form.status === '1' }"
        @click="mode !== 'view' && (form.status = '1')">
        <view class="status-radio" :class="{ checked: form.status === '1' }"></view>
        <text>停用</text>
      </view>
    </view>
  </view>

  <!-- 备注 - 多行文本 -->
  <view class="form-field">
    <view class="field-textarea-box">
      <view class="textarea-prefix">
        <u-icon name="edit-pen" size="18" color="#86909C"></u-icon>
        <text class="prefix-text">备注</text>
      </view>
      <textarea class="field-textarea" v-model="form.remark"
        placeholder="请输入备注信息" :maxlength="500" auto-height></textarea>
    </view>
  </view>
</view>
```

### 步骤2：清理脚本代码
- 移除 `formRef` 和 `rules` 表单验证引用（改用 submitForm 内手动验证）
- 保留其他业务逻辑不变

### 步骤3：添加样式
复制 enterprise/form.vue 的样式并微调：

```scss
page { background-color: #F5F7FA; }

.form-container { min-height: 100vh; padding-bottom: 140rpx; }

.form-section {
  margin: 24rpx;
  background: #fff;
  border-radius: 20rpx;
  padding: 32rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

.form-field { margin-bottom: 20rpx; &:last-child { margin-bottom: 0; } }

.field-input-box {
  display: flex;
  align-items: center;
  background: #F7F8FA;
  border-radius: 12rpx;
  padding: 0 20rpx;
  height: 88rpx;
  gap: 16rpx;
  border: 2rpx solid transparent;
  transition: all 0.2s;

  &:active { background: #EFF0F1; }
}

.field-input {
  flex: 1;
  font-size: 30rpx;
  color: #1D2129;
  height: 88rpx;
  line-height: 88rpx;
}

.field-placeholder { color: #C9CDD4; font-size: 30rpx; }

.field-textarea-box {
  display: flex;
  flex-direction: column;
  background: #F7F8FA;
  border-radius: 12rpx;
  padding: 16rpx 20rpx;
  gap: 8rpx;
  border: 2rpx solid transparent;
}

.textarea-prefix {
  display: flex;
  align-items: center;
  gap: 10rpx;
}

.prefix-text {
  font-size: 26rpx;
  color: #86909C;
  font-weight: 500;
}

.field-textarea {
  width: 100%;
  min-height: 120rpx;
  font-size: 28rpx;
  color: #1D2129;
  line-height: 1.6;
}

.form-row { display: flex; gap: 20rpx; }

.half-width {
  flex: 1;
  min-width: 0;

  .field-input-box { height: 80rpx; }
  .field-input { height: 80rpx; line-height: 80rpx; font-size: 28rpx; }
}

.status-field { margin-top: 8rpx; margin-bottom: 24rpx; }

.status-options {
  display: flex;
  gap: 48rpx;
  padding: 8rpx 4rpx;
}

.status-item {
  display: flex;
  align-items: center;
  gap: 12rpx;
  font-size: 28rpx;
  color: #4E5969;

  &.active { color: #1D2129; font-weight: 500; }
  &.disabled { opacity: 0.5; }
}

.status-radio {
  width: 36rpx;
  height: 36rpx;
  border-radius: 50%;
  border: 3rpx solid #C9CDD4;
  transition: all 0.2s;

  &.checked {
    background: #3D6DF7;
    border-color: #3D6DF7;
    position: relative;

    &::after {
      content: '';
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      width: 14rpx;
      height: 14rpx;
      border-radius: 50%;
      background: #fff;
    }
  }
}

.form-actions {
  position: fixed;
  left: 24rpx;
  right: 24rpx;
  bottom: 40rpx;
  display: flex;
  gap: 20rpx;
  z-index: 100;

  .u-button {
    flex: 1;
    height: 88rpx;
    border-radius: 44rpx;
    font-size: 30rpx;
    font-weight: 600;
  }
}
```

### 步骤4：调整验证逻辑
由于移除了 u-form 组件，需要在 submitForm() 中手动验证：

```javascript
async function submitForm() {
  if (!form.enterpriseId) { uni.showToast({ title: '请选择所属企业', icon: 'none' }); return }
  if (!form.storeName) { uni.showToast({ title: '请输入门店名称', icon: 'none' }); return }
  // ... 继续原有提交逻辑
}
```

---

## 预期效果对比

| 对比项 | 改造前 | 改造后 |
|-------|--------|--------|
| 布局方式 | label + input 分离 | 图标 + placeholder + input 一体化 |
| 视觉效果 | 松散、表单感强 | 紧凑、卡片式现代风格 |
| 占用空间 | 较大（每个字段两行） | 较小（每个字段一行） |
| 图标标识 | 无 | 每个字段有语义化图标 |

---

## 验证方法
1. Vite 热更新自动生效
2. 手机访问 http://192.168.31.126:9091/
3. 进入 **门店管理 → 新增门店** → 验证页面渲染正常
4. 测试各字段输入、选择功能
5. 验证保存提交成功
