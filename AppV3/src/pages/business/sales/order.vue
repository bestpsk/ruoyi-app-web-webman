<template>
  <view class="order-container">
    <view class="customer-info" v-if="customerInfo">
      <view class="info-row">
        <u-icon name="account-fill" size="18" color="#3D6DF7"></u-icon>
        <text class="customer-name">{{ customerInfo.customerName }}</text>
        <text class="customer-phone" @click="callPhone(customerInfo.phone)">{{ customerInfo.phone }}</text>
      </view>
      <view class="info-row">
        <u-icon name="map" size="14" color="#86909C"></u-icon>
        <text class="store-name">{{ enterpriseName }} · {{ storeName }}</text>
      </view>
    </view>

    <u-tabs :list="tabList" :current="currentTab" @click="onTabChange" :activeStyle="{ color: '#3D6DF7', fontWeight: 'bold' }" :lineColor="'#3D6DF7'" :scrollable="false"></u-tabs>

    <scroll-view scroll-y class="tab-content" :style="{ height: scrollHeight + 'px' }">
      <view v-if="currentTab === 0" class="tab-panel">
        <view class="package-name-section">
          <view class="section-title-row">
            <u-icon name="gift-fill" size="16" color="#3D6DF7"></u-icon>
            <text class="section-label">套餐名称</text>
          </view>
          <input class="package-name-input" type="text" v-model="orderPackageName" placeholder="请输入套餐名称" />
        </view>

        <view class="items-section">
          <view class="section-header">
            <view class="section-title-row">
              <u-icon name="list" size="16" color="#3D6DF7"></u-icon>
              <text class="section-label">品项列表</text>
            </view>
            <view class="add-item-btn" @click="addOrderItemRow">
              <u-icon name="plus-circle-fill" size="15" color="#3D6DF7"></u-icon>
              <text class="add-item-text">添加品项</text>
            </view>
          </view>

          <view v-for="(item, index) in orderItems" :key="index" class="item-card">
            <view class="item-card-header">
              <view class="item-index-wrap">
                <u-icon name="edit-pen" size="13" color="#3D6DF7"></u-icon>
                <text class="item-index">品项 {{ index + 1 }}</text>
              </view>
              <view class="item-delete" @click="removeOrderItem(index)" v-if="orderItems.length > 1">
                <u-icon name="trash" size="14" color="#F56C6C"></u-icon>
                <text class="delete-text">删除</text>
              </view>
            </view>
            <view class="item-form">
              <view class="form-row">
                <text class="form-label">名称</text>
                <input class="form-input" type="text" v-model="item.productName" placeholder="品项名称" />
              </view>
              <view class="form-row">
                <text class="form-label">次数</text>
                <input class="form-input" type="number" v-model="item.quantity" placeholder="1" @input="calcItemAuto(index)" />
              </view>
              <view class="form-row">
                <text class="form-label">成交金额</text>
                <input class="form-input" type="digit" v-model="item.dealAmount" placeholder="0.00" @input="calcItemAuto(index)" />
              </view>
              <view class="form-row readonly">
                <text class="form-label">单次价</text>
                <text class="form-value auto-hint">¥{{ calcUnitPrice(item) }}</text>
              </view>
              <view class="form-row">
                <text class="form-label">实付金额</text>
                <input class="form-input" type="digit" v-model="item.paidAmount" placeholder="0.00" @input="calcItemAuto(index)" />
              </view>
              <view class="form-row readonly">
                <text class="form-label">欠款金额</text>
                <text class="form-value owed">¥{{ calcOwedAmount(item) }}</text>
              </view>
            </view>
          </view>
        </view>

        <view class="summary-section">
          <view class="summary-title-row">
            <u-icon name="red-packet-fill" size="16" color="#FF6B35"></u-icon>
            <text class="summary-title">费用合计</text>
          </view>
          <view class="summary-body">
            <view class="summary-item">
              <text class="summary-label">成交</text>
              <text class="summary-value">¥{{ totalDealAmount.toFixed(2) }}</text>
            </view>
            <view class="summary-divider"></view>
            <view class="summary-item">
              <text class="summary-label">实付</text>
              <text class="summary-value paid">¥{{ totalPaidAmount.toFixed(2) }}</text>
            </view>
            <view class="summary-divider" v-if="totalOwedAmount > 0"></view>
            <view class="summary-item" v-if="totalOwedAmount > 0">
              <text class="summary-label">欠款</text>
              <text class="summary-value owed">¥{{ totalOwedAmount.toFixed(2) }}</text>
            </view>
          </view>
        </view>

        <view class="remark-section">
          <view class="section-title-row">
            <u-icon name="chat" size="16" color="#86909C"></u-icon>
            <text class="section-label">备注</text>
          </view>
          <u-textarea v-model="orderRemark" placeholder="请输入备注（选填）" count :maxlength="500" height="80" :customStyle="{ background: '#F7F8FA', borderRadius: '8rpx', fontSize: '26rpx' }"></u-textarea>
        </view>

        <view class="submit-bar">
          <u-button type="primary" text="提交订单" :loading="submitting" @click="submitOrder" :customStyle="{ borderRadius: '12rpx', height: '84rpx' }"></u-button>
        </view>
      </view>

      <view v-if="currentTab === 1" class="tab-panel">
        <view v-if="orderList.length > 0" class="record-list">
          <view v-for="item in orderList" :key="item.orderId" class="record-card">
            <view class="record-header">
              <text class="record-no">{{ item.orderNo || ('ORD' + item.orderId) }}</text>
              <text class="record-status" :class="'status-' + item.orderStatus">{{ getOrderStatusName(item.orderStatus) }}</text>
            </view>
            <view class="record-body">
              <view class="record-amounts">
                <text class="record-amount">¥{{ Number(item.dealAmount || 0).toFixed(2) }}</text>
                <text class="record-paid" v-if="Number(item.paidAmount || 0) > 0">实付¥{{ Number(item.paidAmount || 0).toFixed(2) }}</text>
              </view>
              <text class="record-time">{{ formatTime(item.createTime) }}</text>
            </view>
            <view class="record-remark" v-if="item.remark">
              <text class="remark-text">{{ item.remark }}</text>
            </view>
          </view>
        </view>
        <u-empty v-else mode="data" text="暂无开单记录" :marginTop="40"></u-empty>
      </view>

      <view v-if="currentTab === 2" class="tab-panel">
        <view v-if="owedPackageList.length > 0" class="record-list">
          <view v-for="pkg in owedPackageList" :key="pkg.packageId" class="record-card owed-card">
            <view class="record-header">
              <text class="record-no">{{ pkg.packageName || pkg.packageNo }}</text>
              <text class="owed-amount">欠款: ¥{{ Number(pkg.owedAmount || 0).toFixed(2) }}</text>
            </view>
            <view class="record-body">
              <view class="owed-info">
                <text class="owed-label">成交金额: ¥{{ Number(pkg.totalAmount || 0).toFixed(2) }}</text>
                <text class="owed-label">已付金额: ¥{{ Number(pkg.paidAmount || 0).toFixed(2) }}</text>
              </view>
              <view class="repay-btn" @click="openRepayPopup(pkg)">
                <text>还款</text>
              </view>
            </view>
            <view class="record-time-row">
              <text class="record-time">{{ formatTime(pkg.createTime) }}</text>
            </view>
          </view>
        </view>
        <u-empty v-else mode="data" text="暂无欠款记录" :marginTop="40"></u-empty>
      </view>
    </scroll-view>

    <u-popup :show="showRepayPopup" mode="bottom" round="16" @close="closeRepayPopup">
      <view class="repay-popup">
        <view class="popup-header">
          <text class="popup-title">还款</text>
          <view class="popup-close" @click="closeRepayPopup">
            <u-icon name="close" size="20" color="#86909C"></u-icon>
          </view>
        </view>
        <view class="popup-body">
          <view class="repay-info-row">
            <text class="repay-label">套餐名称</text>
            <text class="repay-value">{{ selectedPackage?.packageName || '-' }}</text>
          </view>
          <view class="repay-info-row">
            <text class="repay-label">欠款金额</text>
            <text class="repay-value owed">¥{{ Number(selectedPackage?.owedAmount || 0).toFixed(2) }}</text>
          </view>
          <view class="repay-info-row">
            <text class="repay-label">还款金额</text>
            <view class="repay-input-wrap">
              <text class="currency">¥</text>
              <input class="repay-input" type="digit" v-model="repayAmount" placeholder="请输入还款金额" />
            </view>
          </view>
          <view class="repay-info-row">
            <text class="repay-label">支付方式</text>
            <view class="payment-methods">
              <view v-for="method in paymentMethods" :key="method.value" class="method-tag" :class="{ active: selectedPaymentMethod === method.value }" @click="selectedPaymentMethod = method.value">
                <text>{{ method.label }}</text>
              </view>
            </view>
          </view>
          <view class="repay-info-row">
            <text class="repay-label">备注</text>
            <u-textarea v-model="repayRemark" placeholder="请输入备注" :maxlength="200" height="60"></u-textarea>
          </view>
        </view>
        <view class="popup-actions">
          <u-button type="primary" text="确认还款" :loading="repaySubmitting" @click="submitRepay"></u-button>
        </view>
      </view>
    </u-popup>
  </view>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getCustomer } from '@/api/business/customer'
import { addSalesOrder, listSalesOrder } from '@/api/business/salesOrder'
// import { listOperation } from '@/api/business/operationRecord'
import { getOwedPackages, addRepayment } from '@/api/business/repayment'

const currentTab = ref(0)
const tabList = ref([{ name: '开单' }, { name: '开单记录' }, { name: '还欠款' }])
const customerInfo = ref(null)
const orderList = ref([])
// const operationList = ref([])
const owedPackageList = ref([])
const submitting = ref(false)
const scrollHeight = ref(500)
const customerId = ref('')
const storeId = ref('')
const storeName = ref('')
const enterpriseName = ref('')

const orderPackageName = ref('')
const orderItems = ref([
  { productName: '', quantity: 1, dealAmount: 0, paidAmount: 0 }
])
const orderRemark = ref('')

const totalDealAmount = computed(() => orderItems.value.reduce((sum, item) => sum + (parseFloat(item.dealAmount) || 0), 0))
const totalPaidAmount = computed(() => orderItems.value.reduce((sum, item) => sum + (parseFloat(item.paidAmount) || 0), 0))
const totalOwedAmount = computed(() => totalDealAmount.value - totalPaidAmount.value)

function calcUnitPrice(item) {
  const qty = parseInt(item.quantity) || 0
  const deal = parseFloat(item.dealAmount) || 0
  if (qty <= 0) return '0.00'
  return (deal / qty).toFixed(2)
}

function calcOwedAmount(item) {
  const deal = parseFloat(item.dealAmount) || 0
  const paid = parseFloat(item.paidAmount) || 0
  return Math.max(0, deal - paid).toFixed(2)
}

function calcItemAuto(index) {
  // 触发响应式更新，computed 会自动重新计算
}

function addOrderItemRow() {
  orderItems.value.push({ productName: '', quantity: 1, dealAmount: 0, paidAmount: 0 })
}

function removeOrderItem(index) {
  orderItems.value.splice(index, 1)
}

const paymentMethods = ref([
  { label: '现金', value: 'cash' },
  { label: '微信', value: 'wechat' },
  { label: '支付宝', value: 'alipay' },
  { label: '银行卡', value: 'bank' }
])
const selectedPaymentMethod = ref('cash')

const showRepayPopup = ref(false)
const selectedPackage = ref(null)
const repayAmount = ref('')
const repayRemark = ref('')
const repaySubmitting = ref(false)

function onTabChange(e) {
  currentTab.value = e.index
  if (e.index === 1) loadOrders()
  if (e.index === 2) loadOwedPackages()
}

async function loadCustomer() {
  if (!customerId.value) return
  try {
    const response = await getCustomer(customerId.value)
    customerInfo.value = response.data || response
  } catch (e) { console.error('加载客户失败:', e) }
}

async function loadOrders() {
  if (!customerId.value) return
  try {
    const response = await listSalesOrder({ customerId: customerId.value, pageNum: 1, pageSize: 50 })
    const data = response.data || response
    orderList.value = data.rows || []
  } catch (e) { console.error('加载订单失败:', e) }
}

// async function loadOperations() {
//   if (!customerId.value) return
//   try {
//     const response = await listOperation({ customerId: customerId.value, pageNum: 1, pageSize: 50 })
//     const data = response.data || response
//     operationList.value = data.rows || []
//   } catch (e) { console.error('加载操作记录失败:', e) }
// }

async function loadOwedPackages() {
  if (!customerId.value) return
  try {
    const response = await getOwedPackages(customerId.value)
    const data = response.data || response
    owedPackageList.value = Array.isArray(data) ? data : []
  } catch (e) { console.error('加载欠款列表失败:', e) }
}

function openRepayPopup(pkg) {
  selectedPackage.value = pkg
  repayAmount.value = ''
  selectedPaymentMethod.value = 'cash'
  repayRemark.value = ''
  showRepayPopup.value = true
}

function closeRepayPopup() {
  showRepayPopup.value = false
  selectedPackage.value = null
}

async function submitRepay() {
  if (!selectedPackage.value) return
  const amount = parseFloat(repayAmount.value)
  if (!amount || amount <= 0) {
    uni.showToast({ title: '请输入有效的还款金额', icon: 'none' })
    return
  }
  if (amount > Number(selectedPackage.value.owedAmount || 0)) {
    uni.showToast({ title: '还款金额不能超过欠款金额', icon: 'none' })
    return
  }

  repaySubmitting.value = true
  try {
    await addRepayment({
      customerId: customerId.value,
      customerName: customerInfo.value?.customerName || '',
      packageId: selectedPackage.value.packageId,
      packageNo: selectedPackage.value.packageNo,
      packageName: selectedPackage.value.packageName,
      orderId: selectedPackage.value.orderId,
      orderNo: selectedPackage.value.orderNo,
      repaymentAmount: amount,
      repaymentType: '1',
      paymentMethod: selectedPaymentMethod.value,
      remark: repayRemark.value,
      enterpriseId: selectedPackage.value.enterpriseId,
      enterpriseName: enterpriseName.value,
      storeId: storeId.value,
      storeName: storeName.value
    })
    uni.showToast({ title: '还款成功', icon: 'success' })
    closeRepayPopup()
    loadOwedPackages()
  } catch (e) {
    console.error('还款失败:', e)
    uni.showToast({ title: '还款失败', icon: 'none' })
  } finally {
    repaySubmitting.value = false
  }
}

async function submitOrder() {
  if (!orderPackageName.value) {
    uni.showToast({ title: '请输入套餐名称', icon: 'none' })
    return
  }

  const validItems = orderItems.value.filter(i => i.productName)
  if (validItems.length === 0) {
    uni.showToast({ title: '请添加品项并填写名称', icon: 'none' })
    return
  }

  const hasInvalidAmount = validItems.some(i => (parseFloat(i.dealAmount) || 0) <= 0)
  if (hasInvalidAmount) {
    uni.showToast({ title: '请填写成交金额', icon: 'none' })
    return
  }

  submitting.value = true
  try {
    await addSalesOrder({
      customerId: customerId.value,
      customerName: customerInfo.value?.customerName || '',
      storeId: storeId.value,
      storeName: storeName.value,
      enterpriseId: customerInfo.value?.enterpriseId || '',
      enterpriseName: enterpriseName.value,
      orderStatus: '1',
      packageName: orderPackageName.value,
      remark: orderRemark.value,
      items: validItems.map(i => ({
        productName: i.productName,
        quantity: parseInt(i.quantity) || 1,
        dealAmount: parseFloat(i.dealAmount) || 0,
        paidAmount: parseFloat(i.paidAmount) || 0
      }))
    })

    const owed = totalOwedAmount.value
    if (owed > 0) {
      uni.showToast({ title: `开单成功，欠款¥${owed.toFixed(2)}`, icon: 'success', duration: 2000 })
    } else {
      uni.showToast({ title: '开单成功', icon: 'success' })
    }

    orderPackageName.value = ''
    orderItems.value = [{ productName: '', quantity: 1, dealAmount: 0, paidAmount: 0 }]
    orderRemark.value = ''
  } catch (e) {
    console.error('开单失败:', e)
    uni.showToast({ title: '开单失败: ' + (e.message || '未知错误'), icon: 'none' })
  } finally {
    submitting.value = false
  }
}

function getOrderStatusName(status) {
  const map = { '0': '未成交', '1': '已成交', '2': '已用完', '3': '还款', '4': '已取消' }
  return map[status] || '未知'
}

function formatTime(time) { if (!time) return ''; return time.substring(0, 16) }
function callPhone(phone) { if (!phone) return; uni.makePhoneCall({ phoneNumber: phone }) }

function calcScrollHeight() {
  const systemInfo = uni.getSystemInfoSync()
  const safeBottom = systemInfo.safeAreaInsets?.bottom || 0
  const navBarHeight = systemInfo.statusBarHeight + 44
  const tabBarHeight = 44
  const customerInfoHeight = 70
  scrollHeight.value = systemInfo.windowHeight - navBarHeight - tabBarHeight - customerInfoHeight - safeBottom - 20
}

onMounted(() => {
  const pages = getCurrentPages()
  const options = pages[pages.length - 1].options || {}
  customerId.value = options.customerId || ''
  storeId.value = options.storeId || ''
  storeName.value = decodeURIComponent(options.storeName || '')
  enterpriseName.value = decodeURIComponent(options.enterpriseName || '')
  calcScrollHeight()
  loadCustomer()
})
</script>

<style lang="scss" scoped>
page { background-color: #F5F6F8; }
.order-container { height: 100vh; display: flex; flex-direction: column; }

.customer-info { padding: 16rpx 24rpx; background: #fff; border-bottom: 1rpx solid #F2F3F5; }
.info-row { display: flex; align-items: center; gap: 10rpx; margin-bottom: 6rpx; &:last-child { margin-bottom: 0; } }
.customer-name { font-size: 30rpx; font-weight: 600; color: #1D2129; }
.customer-phone { font-size: 26rpx; color: #3D6DF7; margin-left: auto; }
.store-name { font-size: 24rpx; color: #86909C; }

.tab-content { flex: 1; }
.tab-panel { padding: 12rpx 32rpx 40rpx; }

.package-name-section { background: #fff; border-radius: 10rpx; padding: 18rpx 20rpx; margin-bottom: 14rpx; border: 1rpx solid #EDEEF2; }
.section-title-row { display: flex; align-items: center; gap: 8rpx; margin-bottom: 10rpx; }
.section-label { font-size: 26rpx; font-weight: 600; color: #1D2129; }
.package-name-input { width: 100%; height: 60rpx; background: #F7F8FA; border-radius: 8rpx; padding: 0 18rpx; font-size: 27rpx; color: #1D2129; box-sizing: border-box; }

.items-section { margin-bottom: 14rpx; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12rpx; }
.add-item-btn { display: flex; align-items: center; gap: 6rpx; padding: 8rpx 16rpx; background: #EEF2FF; border-radius: 8rpx; }
.add-item-text { font-size: 24rpx; color: #3D6DF7; font-weight: 500; }

.item-card { background: #fff; border-radius: 10rpx; padding: 16rpx 18rpx; margin-bottom: 12rpx; border: 1rpx solid #EDEEF2; }
.item-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10rpx; padding-bottom: 8rpx; border-bottom: 1rpx solid #F5F6F7; }
.item-index-wrap { display: flex; align-items: center; gap: 6rpx; }
.item-index { font-size: 25rpx; font-weight: 600; color: #1D2129; }
.item-delete { display: flex; align-items: center; gap: 4rpx; padding: 6rpx 12rpx; background: #FEF2F2; border-radius: 6rpx; }
.delete-text { font-size: 22rpx; color: #F56C6C; }

.item-form { display: flex; flex-direction: column; gap: 8rpx; }
.form-row { display: flex; align-items: center; gap: 12rpx; min-height: 56rpx; background: #FAFBFC; border-radius: 6rpx; padding: 0 12rpx; }
.form-row.readonly { }
.form-label { font-size: 24rpx; color: #86909C; width: 120rpx; min-width: 120rpx; white-space: nowrap; }
.form-input { flex: 1; height: 56rpx; background: #F7F8FA; border-radius: 6rpx; padding: 0 16rpx; font-size: 26rpx; color: #1D2129; text-align: right; box-sizing: border-box; }
.form-value { flex: 1; font-size: 26rpx; color: #1D2129; font-weight: 500; text-align: right; display: flex; align-items: center; justify-content: flex-end; gap: 4rpx; }
.auto-hint { color: #4E5969 !important; font-weight: 400; }

.summary-section { background: #fff; border-radius: 10rpx; padding: 16rpx 20rpx; margin-bottom: 14rpx; border: 1rpx solid #EDEEF2; }
.summary-title-row { display: flex; align-items: center; gap: 8rpx; margin-bottom: 12rpx; }
.summary-title { font-size: 26rpx; font-weight: 600; color: #1D2129; }
.summary-body { display: flex; align-items: center; gap: 0; }
.summary-item { flex: 1; text-align: center; padding: 8rpx 0; }
.summary-divider { width: 1rpx; height: 36rpx; background: #F2F3F5; }
.summary-label { font-size: 22rpx; color: #86909C; display: block; margin-bottom: 4rpx; }
.summary-value { font-size: 28rpx; font-weight: 700; color: #1D2129; display: block; }
.summary-value.paid { color: #00B42A; }
.summary-value.owed { color: #F53F3F; }

.remark-section { background: #fff; border-radius: 10rpx; padding: 16rpx 20rpx; margin-bottom: 14rpx; border: 1rpx solid #EDEEF2; }
.submit-bar { margin-top: 16rpx; padding-bottom: env(safe-area-inset-bottom); }

.record-list { display: flex; flex-direction: column; gap: 12rpx; }
.record-card { background: #fff; border-radius: 10rpx; padding: 20rpx; border: 1rpx solid #EDEEF2; }
.record-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10rpx; }
.record-no { font-size: 25rpx; color: #1D2129; font-weight: 500; }
.record-status { font-size: 22rpx; padding: 4rpx 10rpx; border-radius: 4rpx;
  &.status-0 { background: #FFF7E8; color: #FF7D00; }
  &.status-1 { background: #E8FFEA; color: #00B42A; }
  &.status-2 { background: #F2F3F5; color: #86909C; }
}
.record-body { display: flex; justify-content: space-between; align-items: center; }
.record-amounts { display: flex; align-items: center; gap: 10rpx; }
.record-amount { font-size: 28rpx; font-weight: 600; color: #FF6B35; }
.record-paid { font-size: 22rpx; color: #00B42A; }
.record-time { font-size: 21rpx; color: #C9CDD4; }
.record-remark { margin-top: 6rpx; }
.remark-text { font-size: 23rpx; color: #909399; }
.record-type { font-size: 24rpx; color: #3D6DF7; font-weight: 500; }
.record-content { font-size: 25rpx; color: #4E5969; }

.owed-card { border-left: 4rpx solid #F53F3F; }
.owed-amount { font-size: 26rpx; color: #F53F3F; font-weight: 600; }
.owed-info { display: flex; flex-direction: column; gap: 6rpx; }
.owed-label { font-size: 23rpx; color: #86909C; }
.repay-btn { padding: 10rpx 28rpx; background: linear-gradient(135deg, #3D6DF7 0%, #5B8DEF 100%); border-radius: 8rpx;
  text { font-size: 24rpx; color: #fff; font-weight: 500; }
}
.record-time-row { margin-top: 10rpx; padding-top: 10rpx; border-top: 1rpx solid #F5F6F7; }

.repay-popup { background: #fff; border-radius: 20rpx 20rpx 0 0; max-height: 80vh; }
.popup-header { display: flex; justify-content: space-between; align-items: center; padding: 28rpx; border-bottom: 1rpx solid #F2F3F5; }
.popup-title { font-size: 30rpx; font-weight: 600; color: #1D2129; }
.popup-close { padding: 8rpx; }
.popup-body { padding: 20rpx 28rpx; max-height: 50vh; overflow-y: auto; }
.repay-info-row { margin-bottom: 18rpx; }
.repay-label { font-size: 26rpx; color: #86909C; margin-bottom: 8rpx; display: block; }
.repay-value { font-size: 28rpx; color: #1D2129; font-weight: 500;
  &.owed { color: #F53F3F; }
}
.repay-input-wrap { display: flex; align-items: center; background: #F7F8FA; border-radius: 10rpx; padding: 0 20rpx; height: 80rpx; box-sizing: border-box; }
.currency { font-size: 30rpx; color: #1D2129; font-weight: 600; margin-right: 6rpx; }
.repay-input { flex: 1; font-size: 30rpx; color: #1D2129; height: 80rpx; }
.payment-methods { display: flex; flex-wrap: wrap; gap: 12rpx; }
.method-tag { padding: 14rpx 28rpx; background: #F7F8FA; border-radius: 8rpx; border: 1rpx solid transparent;
  text { font-size: 24rpx; color: #4E5969; }
  &.active { background: #EEF2FF; border-color: #3D6DF7;
    text { color: #3D6DF7; }
  }
}
.popup-actions { padding: 20rpx 28rpx calc(20rpx + env(safe-area-inset-bottom)); border-top: 1rpx solid #F2F3F5; }
</style>
