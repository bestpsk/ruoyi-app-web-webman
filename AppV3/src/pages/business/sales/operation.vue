<template>
  <view class="op-page">
    <view class="customer-info">
      <view class="info-row">
        <u-icon name="account-fill" size="18" color="#3D6DF7"></u-icon>
        <text class="customer-name">{{ customerName }}</text>
      </view>
      <view class="info-row">
        <u-icon name="map" size="14" color="#86909C"></u-icon>
        <text class="store-name">{{ enterpriseName }} · {{ storeName }}</text>
      </view>
    </view>

    <u-tabs :list="tabList" :current="currentTab" @click="onTabChange" :activeStyle="{ color: '#3D6DF7', fontWeight: 'bold' }" :lineColor="'#3D6DF7'" :scrollable="false"></u-tabs>

    <view class="tab-content">
      <view v-if="currentTab === 0" class="tab-panel">
        <view class="section-header">
          <view class="section-line"></view>
          <text class="section-title">选择要操作的品项</text>
          <text class="section-subtitle" v-if="packageList.length > 0">共 {{ packageList.length }} 个套餐</text>
        </view>

        <scroll-view scroll-y class="op-list-scroll">
          <view v-if="packageList.length > 0" class="pkg-list">
            <view v-for="pkg in packageList" :key="pkg.packageId" class="pkg-card">
              <view class="pkg-card-head">
                <text class="pkg-name">{{ pkg.packageName }}</text>
                <text class="pkg-status" :class="pkg.status === '2' ? 'exhausted' : 'active'">{{ pkg.status === '2' ? '已用完' : '已成交' }}</text>
                <text class="pkg-amount">¥{{ Number(pkg.totalAmount || 0).toFixed(2) }}</text>
              </view>
              <view v-for="item in (pkg.items || [])" :key="item.packageItemId" class="item-row" :class="{ disabled: item.remainingQuantity <= 0 || pkg.status === '2' }">
                <label class="item-check" :class="{ checked: isSelected(item.packageItemId) }" @click.stop="toggleItem(pkg, item, !isSelected(item.packageItemId))">
                  <view class="check-box"></view>
                </label>
                <text class="item-name">{{ item.productName }}</text>
                <text class="item-price">¥{{ Number(item.unitPrice || 0).toFixed(2) }}</text>
                <text class="item-remain">剩{{ item.remainingQuantity || 0 }}</text>
              </view>
              <view v-if="!pkg.items || pkg.items.length === 0" class="pkg-empty">暂无品项</view>
            </view>
          </view>
          <u-empty v-else mode="data" text="该客户暂无可用套餐" :marginTop="100"></u-empty>
        </scroll-view>

        <view class="op-bottom-bar">
          <view class="bar-info">
            <text class="bar-count">已选 <text class="count-num">{{ selectedItems.length }}</text> 项</text>
            <text class="bar-total" v-if="selectedItems.length > 0">合计 ¥{{ getTotalPrice() }}</text>
          </view>
          <button class="bar-btn" :class="{ active: selectedItems.length > 0 }" :disabled="selectedItems.length === 0" @click="openDetailDrawer">确认操作</button>
        </view>
      </view>

      <view v-if="currentTab === 1" class="tab-panel">
        <view v-if="operationList.length > 0" class="record-list">
          <view v-for="item in operationList" :key="item.operationId" class="record-card">
            <view class="rc-head-row">
              <u-icon name="list" size="16" color="#86909C"></u-icon>
              <text class="rc-product-head">{{ item.productName || '-' }}</text>
              <view class="rc-type-tag" :class="'type-' + (item.operationType || '0')">{{ item.operationType === '1' ? '体验' : '持卡' }}</view>
              <text class="rc-pkg-name">{{ item.packageName || item.package_name || '散项' }}</text>
            </view>

            <view class="rc-price-row">
              <text class="rc-unit-price">¥{{ Number((item.consume_amount || item.consumeAmount || 0) / (item.operation_quantity || item.operationQuantity || 1)).toFixed(2) }}/次</text>
              <text class="rc-price-sep">×</text>
              <text class="rc-qty-num">{{ item.operationQuantity || 1 }}</text>
              <text class="rc-price-eq">=</text>
              <text class="rc-total-price">¥{{ Number(item.consumeAmount || 0).toFixed(2) }}</text>
            </view>

            <view class="rc-meta-row">
              <view class="rc-operator"><u-icon name="account" size="14" color="#86909C"></u-icon><text>{{ item.operatorUserName || item.operator_user_name || item.operatorName || '-' }}</text></view>
              <view class="rc-satisfaction" v-if="item.satisfaction != null && item.satisfaction !== ''"><u-rate :modelValue="Number(item.satisfaction) || 0" :count="5" activeColor="#FF9900" inactiveColor="#E5E6EB" size="20" activeIcon="star-fill" inactiveIcon="star" :readonly="true"></u-rate></view>
              <text class="rc-date">{{ formatTimeShort(item.operationDate || item.createTime) }}</text>
            </view>

            <view class="rc-remark" v-if="item.remark"><u-icon name="edit-pen" size="14" color="#C9CDD4"></u-icon><text>{{ item.remark }}</text></view>
          </view>
        </view>
        <u-empty v-else mode="data" text="暂无操作记录" :marginTop="40"></u-empty>
      </view>
    </view>

    <u-popup :show="showDetailDrawer" mode="bottom" round="24" :closeable="true" @close="closeDetailDrawer" :customStyle="{ width: '100vw', maxWidth: '100vw', left: 0 }">
      <view class="detail-drawer">
        <view class="drawer-head">
          <view class="head-left">
            <u-icon name="grid" size="18" color="#3D6DF7"></u-icon>
            <text class="drawer-title">持卡操作</text>
          </view>
          <view class="head-tag">
            <u-icon name="account" size="13" color="#3D6DF7"></u-icon>
            <text>{{ customerName }}</text>
          </view>
        </view>

        <scroll-view scroll-y class="drawer-scroll" :style="{ height: drawerScrollHeight + 'px' }">
          <view v-if="selectedItems.length > 0" class="dd-section">
            <view class="section-label"><text>已选品项 ({{ selectedItems.length }})</text></view>

            <view class="item-card-list">
              <view v-for="(it, idx) in selectedItems" :key="it.packageItemId" class="item-card">
                <view class="ic-main-row">
                  <text class="ic-product">{{ it.productName }}</text>
                  <view class="ic-pkg-tag">{{ it.packageName }}</view>
                  <view class="ic-remove" @click="removeItem(idx)"><u-icon name="close" size="18" color="#C9CDD4"></u-icon></view>
                </view>
                <view class="ic-price-row">
                  <view class="col-price">¥{{ Number(it.unitPrice || 0).toFixed(2) }}</view>
                  <view class="col-qty">
                    <view class="qty-btn" @click="qtyChange(idx, -1)">−</view>
                    <text class="qty-val">{{ it.operationQuantity }}</text>
                    <view class="qty-btn" @click="qtyChange(idx, 1)">+</view>
                  </view>
                  <view class="col-amount">¥{{ it.consumeAmount }}</view>
                </view>
              </view>
            </view>

            <view class="total-card">
              <text class="total-left">合计消耗</text>
              <text class="total-right">¥{{ getTotalConsume() }}</text>
            </view>
          </view>

          <view class="dd-section">
            <view class="form-grid">
              <view class="form-cell">
                <view class="fc-label"><u-icon name="calendar" size="14" color="#86909C"></u-icon><text>操作时间</text></view>
                <view class="fc-field" @click="showDatePicker = true">
                  <text :class="{ placeholder: !form.operationDate }">{{ form.operationDate || '请选择日期' }}</text>
                  <u-icon name="arrow-right" size="12" color="#C9CDD4"></u-icon>
                </view>
              </view>
              <view class="form-cell">
                <view class="fc-label"><u-icon name="account" size="14" color="#86909C"></u-icon><text>操作人</text></view>
                <view class="fc-field" @click="showOperatorPicker = true">
                  <text :class="{ placeholder: !form.operatorName }">{{ form.operatorName || '选择操作人' }}</text>
                  <u-icon name="arrow-right" size="12" color="#C9CDD4"></u-icon>
                </view>
              </view>
            </view>
          </view>

          <view class="dd-section">
            <view class="satisfaction-card">
              <view class="sat-label"><u-icon name="star" size="14" color="#FF9900"></u-icon><text>满意度评价</text></view>
              <u-rate v-model="form.satisfaction" :count="5" activeColor="#FF9900" inactiveColor="#E5E6EB" size="24" activeIcon="star-fill" inactiveIcon="star" :allowHalf="true"></u-rate>
            </view>
          </view>

          <view class="dd-section">
            <view class="fc-label"><u-icon name="chat" size="14" color="#86909C"></u-icon><text>顾客反馈</text></view>
            <textarea class="fc-textarea" v-model="form.customerFeedback" placeholder="记录顾客的反馈意见..." maxlength="200"></textarea>
          </view>

          <view class="dd-section">
            <view class="photo-grid">
              <view class="photo-cell">
                <view class="pc-label"><u-icon name="camera" size="14" color="#86909C"></u-icon><text>操作前</text></view>
                <view class="pc-upload">
                  <u-upload
                    :fileList="form.beforePhoto"
                    :afterRead="(e) => onBeforePhoto(e)"
                    :delete="(e) => form.beforePhoto.splice(e.index, 1)"
                    :maxCount="2"
                    :maxSize="5 * 1024 * 1024"
                    width="140rpx"
                    height="140rpx"
                  ></u-upload>
                </view>
              </view>
              <view class="photo-cell">
                <view class="pc-label"><u-icon name="camera" size="14" color="#86909C"></u-icon><text>操作后</text></view>
                <view class="pc-upload">
                  <u-upload
                    :fileList="form.afterPhoto"
                    :afterRead="(e) => onAfterPhoto(e)"
                    :delete="(e) => form.afterPhoto.splice(e.index, 1)"
                    :maxCount="2"
                    :maxSize="5 * 1024 * 1024"
                    width="140rpx"
                    height="140rpx"
                  ></u-upload>
                </view>
              </view>
            </view>
          </view>

          <view class="dd-section">
            <view class="fc-label"><u-icon name="edit-pen" size="14" color="#86909C"></u-icon><text>备注</text></view>
            <textarea class="fc-textarea" v-model="form.remark" placeholder="补充说明或注意事项..." maxlength="200"></textarea>
          </view>
        </scroll-view>

        <view class="drawer-foot">
          <button class="submit-btn" :disabled="submitting || selectedItems.length === 0" @click="submitOperation">
            <u-icon v-if="!submitting" name="checkmark" size="16" color="#fff" style="margin-right: 8rpx"></u-icon>
            {{ submitting ? '提交中...' : '提交持卡操作' }}
          </button>
        </view>
      </view>
    </u-popup>

    <u-datetime-picker
      :show="showDatePicker"
      mode="date"
      v-model="datePickerValue"
      @confirm="onDateConfirm"
      @cancel="showDatePicker = false"
      @close="showDatePicker = false"
    ></u-datetime-picker>
    <u-action-sheet
      :show="showOperatorPicker"
      :actions="operatorList"
      title="选择操作人"
      @select="onOperatorSelect"
      @close="showOperatorPicker = false"
    ></u-action-sheet>
  </view>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { getPackageByCustomer } from '@/api/business/customerPackage'
import { listOperation, addOperation } from '@/api/business/operationRecord'
import { listEmployeeConfig } from '@/api/business/employeeConfig'
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()

const customerId = ref('')
const customerName = ref('')
const storeId = ref('')
const storeName = ref('')
const enterpriseId = ref('')
const enterpriseName = ref('')

const currentTab = ref(0)
const tabList = ref([{ name: '操作' }, { name: '操作记录' }])
const operationList = ref([])

const packageList = ref([])
const selectedItems = ref([])

const showDetailDrawer = ref(false)
const submitting = ref(false)
const showDatePicker = ref(false)
const datePickerValue = ref(Number(new Date()))
const showOperatorPicker = ref(false)
const operatorList = ref([])
const drawerScrollHeight = ref(600)

const form = reactive({
  operationType: '0',
  operationDate: '',
  operatorUserId: null,
  operatorName: '',
  satisfaction: 5,
  customerFeedback: '',
  beforePhoto: [],
  afterPhoto: [],
  remark: ''
})

function isSelected(packageItemId) {
  return selectedItems.value.some(i => i.packageItemId === packageItemId)
}

function toggleItem(pkg, item, checked) {
  if (checked) {
    if (!isSelected(item.packageItemId)) {
      selectedItems.value.push({
        ...item,
        packageName: pkg.packageName,
        packageId: pkg.packageId,
        customerId: customerId.value,
        customerName: customerName.value,
        enterpriseId: enterpriseId.value,
        storeId: storeId.value,
        operationQuantity: Math.min(1, item.remainingQuantity || 0),
        consumeAmount: Number((item.unitPrice || 0) * Math.min(1, item.remainingQuantity || 0)).toFixed(2)
      })
    }
  } else {
    selectedItems.value = selectedItems.value.filter(i => i.packageItemId !== item.packageItemId)
  }
}

function removeItem(idx) {
  selectedItems.value.splice(idx, 1)
}

function qtyChange(idx, delta) {
  const it = selectedItems.value[idx]
  if (!it) return
  let qty = parseInt(it.operationQuantity) || 0
  qty += delta
  const maxQty = parseInt(it.remainingQuantity) || 0
  if (qty < 1) qty = 1
  if (qty > maxQty) qty = maxQty
  it.operationQuantity = qty
  it.consumeAmount = Number((it.unitPrice || 0) * qty).toFixed(2)
}

function getTotalPrice() {
  return selectedItems.value.reduce((sum, it) => sum + parseFloat(it.consumeAmount || 0), 0).toFixed(2)
}

function openDetailDrawer() {
  if (selectedItems.value.length === 0) return
  form.operationDate = new Date().toISOString().slice(0, 10)
  form.operatorName = userStore.nickName || userStore.name || ''
  form.satisfaction = 5
  form.customerFeedback = ''
  form.beforePhoto = []
  form.afterPhoto = []
  form.remark = ''
  datePickerValue.value = Number(new Date())
  loadOperators()
  const sysInfo = uni.getSystemInfoSync()
  const safeBottom = sysInfo.safeAreaInsets?.bottom || 0
  const headH = uni.upx2px ? uni.upx2px(56) : 28
  const footH = uni.upx2px ? uni.upx2px(100) : 50 + safeBottom
  drawerScrollHeight.value = Math.floor(sysInfo.windowHeight * 0.93) - headH - footH
  showDetailDrawer.value = true
}

function closeDetailDrawer() {
  showDetailDrawer.value = false
}

async function loadOperators() {
  try {
    const res = await listEmployeeConfig({ pageNum: 1, pageSize: 100 })
    const data = res.data || res
    const list = data.rows || []
    operatorList.value = list.map(item => ({
      name: item.realName || item.nickName || item.userName || '-',
      userId: item.userId,
      subname: item.phonenumber || ''
    }))
  } catch (e) {
    console.error('加载员工列表失败:', e)
  }
}

function onOperatorSelect(e) {
  form.operatorName = e.name
  form.operatorUserId = e.userId
  showOperatorPicker.value = false
}

function getTotalConsume() {
  return selectedItems.value.reduce((sum, it) => sum + parseFloat(it.consumeAmount || 0), 0).toFixed(2)
}

async function submitOperation() {
  if (selectedItems.value.length === 0) {
    return uni.showToast({ title: '请选择操作品项', icon: 'none' })
  }
  submitting.value = true
  try {
    for (const item of selectedItems.value) {
      await addOperation({
        customerId: customerId.value,
        customerName: customerName.value,
        packageId: item.packageId,
        packageItemId: item.packageItemId,
        productName: item.productName,
        operationType: form.operationType,
        operationQuantity: item.operationQuantity,
        consumeAmount: item.consumeAmount,
        unitPrice: item.unitPrice,
        operationDate: form.operationDate,
        operatorUserId: form.operatorUserId,
        operatorUserName: form.operatorName,
        satisfaction: form.satisfaction,
        customerFeedback: form.customerFeedback,
        beforePhoto: form.beforePhoto.join(','),
        afterPhoto: form.afterPhoto.join(','),
        remark: form.remark,
        enterpriseId: enterpriseId.value,
        storeId: storeId.value
      })
    }
    uni.showToast({ title: '操作成功', icon: 'success' })
    closeDetailDrawer()
    selectedItems.value = []
    setTimeout(() => uni.navigateBack(), 1500)
  } catch (e) {
    console.error('操作失败:', e)
    uni.showToast({ title: '操作失败', icon: 'none' })
  } finally {
    submitting.value = false
  }
}

function onBeforePhoto(e) {
  if (e.file) form.beforePhoto.push({ url: e.url, name: e.name || 'photo' })
}

function onAfterPhoto(e) {
  if (e.file) form.afterPhoto.push({ url: e.url, name: e.name || 'photo' })
}

function onDateConfirm(e) {
  const d = new Date(Number(e))
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  form.operationDate = `${y}-${m}-${day}`
  showDatePicker.value = false
}

function onTabChange(e) {
  currentTab.value = e.index
  if (e.index === 1) loadOperations()
}

async function loadOperations() {
  if (!customerId.value) return
  try {
    const response = await listOperation({ customerId: customerId.value, pageNum: 1, pageSize: 50 })
    const data = response.data || response
    operationList.value = data.rows || []
  } catch (e) {
    console.error('加载操作记录失败:', e)
  }
}

function getOperationStatusName(status) {
  const map = { '0': '待操作', '1': '已成交', '2': '已完成' }
  return map[status] || '未知'
}

function formatTimeShort(time) { if (!time) return ''; return time.substring(5, 16).replace('-', '-').replace(' ', ' ') }

async function loadPackages() {
  if (!customerId.value) return
  try {
    const response = await getPackageByCustomer(customerId.value)
    const data = response.data || response
    packageList.value = Array.isArray(data) ? data.filter(p => p.status === '1') : []
  } catch (e) {
    console.error('加载套餐失败:', e)
  }
}

const pages = getCurrentPages()
const options = pages[pages.length - 1].options || {}
customerId.value = options.customerId || ''
customerName.value = decodeURIComponent(options.customerName || '')
storeId.value = options.storeId || ''
storeName.value = decodeURIComponent(options.storeName || '')
enterpriseId.value = options.enterpriseId || ''
enterpriseName.value = decodeURIComponent(options.enterpriseName || '')
uni.setNavigationBarTitle({ title: '选择操作项目' })

loadPackages()
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }
.op-page { min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; box-sizing: border-box; }

.customer-info { padding: 14rpx 16rpx; background: #fff; border-bottom: 1rpx solid #F2F3F5; }
.info-row { display: flex; align-items: center; gap: 10rpx; margin-bottom: 6rpx; &:last-child { margin-bottom: 0; } }
.customer-name { font-size: 30rpx; font-weight: 600; color: #1D2129; }
.store-name { font-size: 24rpx; color: #86909C; }

.tab-content { flex: 1; }
.tab-panel { padding: 12rpx 12rpx 40rpx; }

.section-header { display: flex; align-items: center; gap: 10rpx; margin-bottom: 12rpx; padding: 0 2rpx; }
.section-line { width: 4rpx; height: 28rpx; background: #3D6DF7; border-radius: 2rpx; }
.section-title { font-size: 28rpx; font-weight: 600; color: #1D2129; }
.section-subtitle { font-size: 22rpx; color: #86909C; margin-left: auto; }

.op-list-scroll { flex: 1; padding: 12rpx 8rpx; box-sizing: border-box; overflow-x: hidden; }

.pkg-list { display: flex; flex-direction: column; gap: 16rpx; }
.pkg-card { background: #fff; border-radius: 8rpx; padding: 14rpx 12rpx; border: 1rpx solid #F0F2F5; box-sizing: border-box; overflow: hidden; }
.pkg-card-head { display: flex; align-items: center; gap: 8rpx; margin-bottom: 12rpx; padding-bottom: 10rpx; border-bottom: 1rpx solid #F2F3F5;
  .pkg-name { font-size: 26rpx; font-weight: 600; color: #1D2129; }
}
.pkg-status { font-size: 20rpx; padding: 2rpx 12rpx; border-radius: 4rpx;
  &.active { color: #00B42A; background: #E8FFEA; }
  &.exhausted { color: #86909C; background: #F2F3F5; }
}
.pkg-amount { margin-left: auto; font-size: 24rpx; color: #FF6B35; font-weight: 600; }

.item-row { display: flex; align-items: center; gap: 10rpx; padding: 10rpx 12rpx; background: #F7F8FA; border-radius: 8rpx; margin-bottom: 8rpx; transition: opacity 0.15s; overflow: hidden;
  &:last-child { margin-bottom: 0; }
  &.disabled { opacity: 0.45; pointer-events: none; }
  &:active:not(.disabled) { background: #EEF2FF; }
}
.item-check { padding: 4rpx; }
.check-box { width: 32rpx; height: 32rpx; border: 2rpx solid #E5E6EB; border-radius: 6rpx; transition: all 0.15s; display: flex; align-items: center; justify-content: center;
  &::after { content: ''; width: 16rpx; height: 16rpx; border-radius: 3rpx; background: transparent; transition: all 0.15s; }
}
.item-check.checked .check-box { border-color: #3D6DF7; background: #EEF2FF;
  &::after { background: #3D6DF7; }
}
.item-name { flex: 1; font-size: 24rpx; color: #1D2129; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.item-price { font-size: 22rpx; color: #86909C; min-width: 90rpx; text-align: right; }
.item-remain { font-size: 21rpx; color: #00B42A; font-weight: 500; min-width: 70rpx; text-align: right; }
.pkg-empty { font-size: 24rpx; color: #C9CDD4; text-align: center; padding: 20rpx 0; }

.op-bottom-bar { position: fixed; left: 0; right: 0; bottom: 0; display: flex; align-items: center; padding: 10rpx 24rpx; padding-bottom: calc(10rpx + env(safe-area-inset-bottom)); background: #fff; border-top: 1rpx solid #F2F3F5; z-index: 100; }
.bar-info { display: flex; align-items: center; gap: 14rpx; flex: 1;
  .bar-count { font-size: 24rpx; color: #86909C;
    .count-num { color: #3D6DF7; font-weight: 700; font-size: 26rpx; }
  }
  .bar-total { font-size: 24rpx; color: #FF6B35; font-weight: 600; }
}
.bar-btn { margin-left: auto !important; width: 180rpx; height: 60rpx; border-radius: 8rpx; font-size: 26rpx; font-weight: 600; border: none; display: flex; align-items: center; justify-content: center; background: #E5E6EB; color: #FFF; transition: all 0.2s; flex-shrink: 0; padding: 0;
  &.active { background: #3D6DF7; }
  &[disabled] { opacity: 1; }
}

.detail-drawer { background: #F5F6FA; border-radius: 16rpx 16rpx 0 0; height: 85vh; display: flex; flex-direction: column; overflow: hidden; width: 100%; max-width: 100vw; box-sizing: border-box; }
.drawer-head { display: flex; justify-content: space-between; align-items: center; padding: 14rpx 20rpx 10rpx;
  .head-left { display: flex; align-items: center; gap: 8rpx;
    .drawer-title { font-size: 28rpx; font-weight: 700; color: #1D2129; }
  }
  .head-tag { display: flex; align-items: center; gap: 6rpx; background: #EEF2FF; padding: 4rpx 12rpx; border-radius: 12rpx;
    text { font-size: 22rpx; color: #3D6DF7; font-weight: 500; }
  }
}
.drawer-scroll { overflow-y: auto; overflow-x: hidden; width: 100%; box-sizing: border-box; min-height: 400px; }

.dd-section { margin-bottom: 10rpx; }

.section-label { margin-bottom: 8rpx; padding-left: 2rpx;
  text { font-size: 22rpx; color: #86909C; font-weight: 500; }
}

.item-card-list { display: flex; flex-direction: column; gap: 8rpx; }
.item-card { background: #fff; border-radius: 8rpx; padding: 12rpx 10rpx; border: 1rpx solid #F0F2F5; }

.ic-main-row { display: flex; align-items: center; gap: 8rpx; margin-bottom: 6rpx; flex-wrap: wrap;
  .ic-product { flex: 1; font-size: 25rpx; font-weight: 600; color: #1D2129; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
  .ic-pkg-tag { font-size: 18rpx; color: #3D6DF7; background: #EEF2FF; padding: 2rpx 8rpx; border-radius: 4rpx; white-space: nowrap; flex-shrink: 0; }
  .ic-remove { padding: 4rpx; flex-shrink: 0; &:active { opacity: 0.6; } }
}
.ic-price-row { display: flex; align-items: stretch; background: #F7F8FA; border-radius: 6rpx; overflow: hidden;
  > view { flex: 1; display: flex; align-items: center; justify-content: center; }
}
.col-price { font-size: 21rpx; color: #86909C; border-right: 1rpx solid #F0F2F5; padding: 8rpx 4rpx; }
.col-qty { border-right: 1rpx solid #F0F2F5; gap: 4rpx; padding: 6rpx 4rpx;
  .qty-btn { width: 44rpx; height: 36rpx; border-radius: 6rpx; background: #fff; border: 1rpx solid #E5E6EB; display: flex; align-items: center; justify-content: center; font-size: 26rpx; color: #3D6DF7; line-height: 1;
    &:active { background: #EEF2FF; }
  }
  .qty-val { font-size: 24rpx; font-weight: 600; color: #1D2129; min-width: 40rpx; text-align: center; }
}
.col-amount { font-size: 24rpx; font-weight: 700; color: #FF6B35; padding: 8rpx 4rpx; }

.total-card { display: flex; justify-content: space-between; align-items: center; background: #EEF2FF; border-radius: 8rpx; padding: 12rpx 16rpx; margin-top: 8rpx;
  .total-left { font-size: 24rpx; color: #86909C; font-weight: 500; }
  .total-right { font-size: 30rpx; font-weight: 800; color: #3D6DF7; }
}

.form-grid { display: flex; gap: 10rpx; width: 100%; box-sizing: border-box; min-width: 0; }
.form-cell { flex: 1; background: #fff; border-radius: 8rpx; padding: 8rpx; border: 1rpx solid #F0F2F5; min-width: 0; box-sizing: border-box;
  .fc-label { display: flex; align-items: center; gap: 4rpx; margin-bottom: 4rpx;
    text { font-size: 21rpx; color: #86909C; font-weight: 500; }
  }
  .fc-field { display: flex; align-items: center; justify-content: space-between; background: #F7F8FA; border-radius: 6rpx; padding: 8rpx 10rpx; min-height: 40rpx;
    text { font-size: 23rpx; color: #1D2129; &.placeholder { color: #C9CDD4; } }
  }
}

.satisfaction-card { background: #fff; border-radius: 8rpx; padding: 12rpx 14rpx; border: 1rpx solid #F0F2F5; display: flex; justify-content: space-between; align-items: center; }
.sat-label { display: inline-flex; align-items: center; gap: 4rpx;
  text { font-size: 21rpx; color: #86909C; font-weight: 500; }
}

.fc-label { display: flex; align-items: center; gap: 4rpx; margin-bottom: 8rpx;
  text { font-size: 21rpx; color: #86909C; font-weight: 500; }
}
.fc-textarea { width: 100%; height: 72rpx; background: #fff; border-radius: 8rpx; padding: 10rpx 12rpx; font-size: 23rpx; color: #1D2129; box-sizing: border-box; border: 1rpx solid #F0F2F5; }

.photo-grid { display: flex; gap: 10rpx; }
.photo-cell { flex: 1; background: #fff; border-radius: 8rpx; padding: 10rpx; border: 1rpx solid #F0F2F5; }
.pc-label { display: flex; align-items: center; gap: 4rpx; margin-bottom: 8rpx;
  text { font-size: 21rpx; color: #86909C; font-weight: 500; }
}
.pc-upload { display: flex; justify-content: center; }

.drawer-foot { padding: 12rpx 24rpx; padding-bottom: calc(12rpx + env(safe-area-inset-bottom)); background: #F5F6FA; flex-shrink: 0; }
.submit-btn { width: 100%; height: 68rpx; border-radius: 12rpx; font-size: 26rpx; font-weight: 700; border: none; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #3D6DF7, #5B8DEF); color: #fff;
  &[disabled] { opacity: 0.45; background: #E5E6EB; }
}

.record-list { display: flex; flex-direction: column; gap: 12rpx; }

.record-card { background: #fff; border-radius: 10rpx; padding: 16rpx 18rpx; border: 1rpx solid #F0F2F5; }

.rc-head-row { display: flex; align-items: center; gap: 8rpx; margin-bottom: 6rpx; }
.rc-product-head { flex: 1; font-size: 26rpx; font-weight: 600; color: #1D2129; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
.rc-type-tag { font-size: 20rpx; padding: 2rpx 14rpx; border-radius: 4rpx; font-weight: 500; flex-shrink: 0;
  &.type-0 { color: #FF7D00; background: #FFF7E8; }
  &.type-1 { color: #3D6DF7; background: #EEF2FF; }
  &.type-2 { color: #00B42A; background: #E8FFEA; }
}
.rc-pkg-name { font-size: 22rpx; color: #86909C; flex-shrink: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180rpx; }

.rc-price-row { display: flex; align-items: baseline; gap: 6rpx; margin-bottom: 10rpx; padding: 10rpx 14rpx; background: #F7F8FA; border-radius: 6rpx; }
.rc-unit-price { font-size: 22rpx; color: #86909C; }
.rc-price-sep, .rc-price-eq { font-size: 22rpx; color: #C9CDD4; }
.rc-qty-num { font-size: 24rpx; font-weight: 600; color: #4E5969; }
.rc-total-price { font-size: 28rpx; font-weight: 700; color: #FF6B35; margin-left: auto; }

.rc-meta-row { display: flex; align-items: center; gap: 16rpx; padding-top: 10rpx; border-top: 1rpx solid #F2F3F5; }
.rc-operator { display: flex; align-items: center; gap: 4rpx; font-size: 20rpx; color: #86909C; flex-shrink: 0; }
.rc-satisfaction { flex-shrink: 0; }
.rc-date { font-size: 20rpx; color: #C9CDD4; margin-left: auto; }

.rc-remark { display: flex; align-items: flex-start; gap: 6rpx; margin-top: 8rpx; padding-top: 8rpx; border-top: 1rpx dashed #EDEEF2; font-size: 22rpx; color: #86909C; }
</style>
