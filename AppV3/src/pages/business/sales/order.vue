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

    <view class="tab-content">
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

        <view class="dealer-section">
          <view class="section-title-row">
            <u-icon name="account" size="16" color="#3D6DF7"></u-icon>
            <text class="section-label">门店成交人</text>
          </view>
          <input class="dealer-input" type="text" v-model="orderStoreDealer" placeholder="请输入门店成交人（选填）" />
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
            <view class="rc-header">
              <view class="rc-header-left">
                <u-icon name="file-text" size="28" color="#3D6DF7"></u-icon>
                <text class="rc-no">{{ item.orderNo || ('ORD' + item.orderId) }}</text>
              </view>
              <text class="rc-status" :class="'st-' + item.orderStatus">{{ getOrderStatusName(item.orderStatus) }}</text>
            </view>

            <view class="rc-items" v-if="item.items && item.items.length">
              <view v-for="(it, idx) in item.items" :key="idx" class="rc-item-row">
                <u-icon name="checkbox-mark" size="24" color="#3D6DF7"></u-icon>
                <text class="rc-item-name">{{ it.productName || '未命名品项' }}</text>
                <text class="rc-item-qty">×{{ it.quantity || 1 }}</text>
                <text class="rc-item-price">¥{{ Number(it.dealAmount || 0).toFixed(2) }}</text>
              </view>
            </view>

            <view class="rc-divider" v-if="item.items && item.items.length"></view>

            <view class="rc-amounts">
              <view class="rc-amount-group">
                <text class="rc-amt-label">成交</text>
                <text class="rc-amt-deal">¥{{ Number(item.dealAmount || 0).toFixed(2) }}</text>
              </view>
              <view class="rc-amount-group">
                <text class="rc-amt-label">实付</text>
                <text class="rc-amt-paid">¥{{ Number(item.paidAmount || 0).toFixed(2) }}</text>
              </view>
              <view class="rc-amount-group" v-if="Number(item.owedAmount || 0) > 0">
                <text class="rc-amt-label">欠款</text>
                <text class="rc-amt-owed">¥{{ Number(item.owedAmount || 0).toFixed(2) }}</text>
              </view>
            </view>

            <view class="rc-footer">
              <view class="rc-meta-row" v-if="item.storeDealer || item.creatorUserName">
                <view class="rc-meta-item">
                  <u-icon name="account" size="18" color="#86909C"></u-icon>
                  <text class="rc-meta-val">{{ item.storeDealer || '-' }}</text>
                </view>
                <text class="rc-meta-sep">|</text>
                <view class="rc-meta-item">
                  <u-icon name="man-add" size="18" color="#86909C"></u-icon>
                  <text class="rc-meta-val">{{ item.creatorUserName || '-' }}</text>
                </view>
              </view>
              <text class="rc-time">{{ formatTimeShort(item.createTime) }}</text>
            </view>

            <view class="rc-remark" v-if="item.remark">
              <u-icon name="edit-pen" size="18" color="#C9CDD4"></u-icon>
              <text class="rc-remark-text">{{ item.remark }}</text>
            </view>
          </view>
        </view>
        <u-empty v-else mode="data" text="暂无开单记录" :marginTop="40"></u-empty>
      </view>

      <view v-if="currentTab === 2" class="tab-panel">
        <view v-if="owedPackageList.length > 0" class="record-list">
          <view v-for="pkg in owedPackageList" :key="pkg.packageId" class="record-card rc-owed-card">
            <view class="rc-header">
              <view class="rc-header-left">
                <u-icon name="file-text" size="28" color="#F53F3F"></u-icon>
                <text class="rc-no">{{ pkg.packageName || pkg.packageNo }}</text>
              </view>
              <view class="rc-owed-badge">欠¥{{ Number(pkg.owedAmount || 0).toFixed(2) }}</view>
            </view>

            <view class="rc-items rc-owed-info">
              <view class="rc-item-row" style="padding: 8rpx 16rpx;">
                <u-icon name="rmb-circle" size="22" color="#86909C"></u-icon>
                <text class="rc-item-name">成交 ¥{{ Number(pkg.totalAmount || 0).toFixed(2) }}</text>
              </view>
              <view class="rc-item-row" style="padding: 8rpx 16rpx;">
                <u-icon name="checkmark-circle" size="22" color="#00B42A"></u-icon>
                <text class="rc-item-name">已付 ¥{{ Number(pkg.paidAmount || 0).toFixed(2) }}</text>
              </view>
            </view>

            <view class="rc-divider"></view>

            <view class="rc-action-row">
              <view class="rc-repay-btn" @click="openRepayPopup(pkg)">
                <u-icon name="red-packet" size="24" color="#fff"></u-icon>
                <text class="rc-repay-text">还款</text>
              </view>
            </view>

            <view class="rc-footer">
              <u-icon name="clock" size="18" color="#C9CDD4"></u-icon>
              <text class="rc-time">{{ formatTimeShort(pkg.createTime) }}</text>
            </view>
          </view>
        </view>
        <u-empty v-else mode="data" text="暂无欠款记录" :marginTop="40"></u-empty>
      </view>
    </view>

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
/**
 * @description 销售订单页 - 开单/开单记录/还款
 * @description 支持为客户创建销售订单（含品项、金额计算）、查看历史订单、
 * 欠款套餐还款（支持多种支付方式），自动计算单次价和欠款金额
 */
import { ref, computed, onMounted } from 'vue'
import { getCustomer } from '@/api/business/customer'
import { addSalesOrder, listSalesOrder } from '@/api/business/salesOrder'
// import { listOperation } from '@/api/business/operationRecord'
import { getOwedPackages, addRepayment } from '@/api/business/repayment'

const currentTab = ref(0)
/** Tab列表，有欠款时动态添加"还欠款"标签 */
const tabList = computed(() => {
  const tabs = [{ name: '开单' }, { name: '开单记录' }]
  if (owedPackageList.value.length > 0) tabs.push({ name: '还欠款' })
  return tabs
})
const customerInfo = ref(null)
const orderList = ref([])
// const operationList = ref([])
const owedPackageList = ref([])
const submitting = ref(false)
const customerId = ref('')
const storeId = ref('')
const storeName = ref('')
const enterpriseName = ref('')

const orderPackageName = ref('')
const orderItems = ref([
  { productName: '', quantity: 1, dealAmount: 0, paidAmount: 0 }
])
const orderRemark = ref('')
const orderStoreDealer = ref('')

/** 所有品项成交金额合计 */
const totalDealAmount = computed(() => orderItems.value.reduce((sum, item) => sum + (parseFloat(item.dealAmount) || 0), 0))
/** 所有品项实付金额合计 */
const totalPaidAmount = computed(() => orderItems.value.reduce((sum, item) => sum + (parseFloat(item.paidAmount) || 0), 0))
/** 所有品项欠款金额合计（成交-实付） */
const totalOwedAmount = computed(() => totalDealAmount.value - totalPaidAmount.value)

/** 计算品项单次价：成交金额÷次数 */
function calcUnitPrice(item) {
  const qty = parseInt(item.quantity) || 0
  const deal = parseFloat(item.dealAmount) || 0
  if (qty <= 0) return '0.00'
  return (deal / qty).toFixed(2)
}

/** 计算品项欠款金额：成交金额-实付金额，最小为0 */
function calcOwedAmount(item) {
  const deal = parseFloat(item.dealAmount) || 0
  const paid = parseFloat(item.paidAmount) || 0
  return Math.max(0, deal - paid).toFixed(2)
}

/** 触发品项金额响应式更新（computed自动重算） */
function calcItemAuto(index) {
}

/** 添加一个空白品项行 */
function addOrderItemRow() {
  orderItems.value.push({ productName: '', quantity: 1, dealAmount: 0, paidAmount: 0 })
}

/** 删除指定品项行 */
function removeOrderItem(index) {
  orderItems.value.splice(index, 1)
}

/** 支付方式选项 */
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

/** Tab切换处理，切换到记录或还款时加载对应数据 */
function onTabChange(e) {
  currentTab.value = e.index
  if (e.index === 1) loadOrders()
  if (e.index === 2) loadOwedPackages()
}

/** 加载客户信息，成功后自动加载欠款列表 */
async function loadCustomer() {
  if (!customerId.value) return
  try {
    const response = await getCustomer(customerId.value)
    customerInfo.value = response.data || response
    loadOwedPackages()
  } catch (e) { console.error('加载客户失败:', e) }
}

/** 加载客户历史订单列表 */
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

/** 加载客户欠款套餐列表，无欠款时自动切回开单Tab */
async function loadOwedPackages() {
  if (!customerId.value) return
  try {
    const response = await getOwedPackages(customerId.value)
    const data = response.data || response
    owedPackageList.value = Array.isArray(data) ? data : []
    if (owedPackageList.value.length === 0 && currentTab.value === 2) {
      currentTab.value = 0
    }
  } catch (e) { console.error('加载欠款列表失败:', e) }
}

/** 打开还款弹窗，初始化还款表单 */
function openRepayPopup(pkg) {
  selectedPackage.value = pkg
  repayAmount.value = ''
  selectedPaymentMethod.value = 'cash'
  repayRemark.value = ''
  showRepayPopup.value = true
}

/** 关闭还款弹窗并清空选中套餐 */
function closeRepayPopup() {
  showRepayPopup.value = false
  selectedPackage.value = null
}

/** 提交还款，校验金额有效性后调用接口，成功后刷新欠款列表 */
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
      storeName: storeName.value,
      autoAudit: true
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

/** 提交销售订单，校验套餐名称和品项后调用接口，成功后提示欠款金额并重置表单 */
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
      storeDealer: orderStoreDealer.value,
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
    orderStoreDealer.value = ''
  } catch (e) {
    console.error('开单失败:', e)
    uni.showToast({ title: '开单失败: ' + (e.message || '未知错误'), icon: 'none' })
  } finally {
    submitting.value = false
  }
}

/** 订单状态码映射为中文名称 */
function getOrderStatusName(status) {
  const map = { '0': '未成交', '1': '已成交', '2': '已用完', '3': '还款', '4': '已取消' }
  return map[status] || '未知'
}

/** 格式化时间为YYYY-MM-DD HH:mm */
function formatTime(time) { if (!time) return ''; return time.substring(0, 16) }
/** 格式化为MM-DD HH:mm简短格式 */
function formatTimeShort(time) { if (!time) return ''; return time.substring(5, 16).replace('-', '-').replace(' ', ' ') }
/** 拨打客户电话 */
function callPhone(phone) { if (!phone) return; uni.makePhoneCall({ phoneNumber: phone }) }

onMounted(() => {
  const pages = getCurrentPages()
  const options = pages[pages.length - 1].options || {}
  customerId.value = options.customerId || ''
  storeId.value = options.storeId || ''
  storeName.value = decodeURIComponent(options.storeName || '')
  enterpriseName.value = decodeURIComponent(options.enterpriseName || '')
  loadCustomer()
})
</script>

<style lang="scss" scoped>
page { background-color: #F5F6F8; }
.order-container { display: flex; flex-direction: column; }

.customer-info { padding: 16rpx 24rpx; background: #fff; border-bottom: 1rpx solid #F2F3F5; }
.info-row { display: flex; align-items: center; gap: 10rpx; margin-bottom: 6rpx; &:last-child { margin-bottom: 0; } }
.customer-name { font-size: 30rpx; font-weight: 600; color: #1D2129; }
.customer-phone { font-size: 26rpx; color: #3D6DF7; margin-left: auto; }
.store-name { font-size: 24rpx; color: #86909C; }

.tab-content { flex: 1; }
.tab-panel { padding: 16rpx 24rpx 40rpx; }

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

.dealer-section { background: #fff; border-radius: 10rpx; padding: 16rpx 20rpx; margin-bottom: 14rpx; border: 1rpx solid #EDEEF2; }
.dealer-input { width: 100%; height: 60rpx; background: #F7F8FA; border-radius: 8rpx; padding: 0 18rpx; font-size: 27rpx; color: #1D2129; box-sizing: border-box; }
.remark-section { background: #fff; border-radius: 10rpx; padding: 16rpx 20rpx; margin-bottom: 14rpx; border: 1rpx solid #EDEEF2; }
.submit-bar { margin-top: 16rpx; padding-bottom: env(safe-area-inset-bottom); }

.record-list { display: flex; flex-direction: column; gap: 16rpx; }

.record-card {
  background: #fff;
  border-radius: 16rpx;
  padding: 24rpx;
  box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.04);
}

.rc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16rpx; }
.rc-header-left { display: flex; align-items: center; gap: 8rpx; }
.rc-no { font-size: 26rpx; font-weight: 600; color: #1D2129; letter-spacing: 0.5rpx; }
.rc-status { font-size: 20rpx; padding: 4rpx 14rpx; border-radius: 20rpx; font-weight: 500;
  &.st-0 { background: #FFF7E8; color: #FF7D00; }
  &.st-1 { background: #E8FFEA; color: #00B42A; }
  &.st-2 { background: #F2F3F5; color: #86909C; }
  &.st-3 { background: #EEF2FF; color: #3D6DF7; }
  &.st-4 { background: #F2F3F5; color: #C9CDD4; }
}

.rc-items { display: flex; flex-direction: column; gap: 10rpx; margin-bottom: 4rpx; }
.rc-item-row { display: flex; align-items: center; gap: 12rpx; padding: 10rpx 16rpx; background: #FAFBFC; border-radius: 10rpx; }
.rc-item-name { flex: 1; font-size: 25rpx; color: #1D2129; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rc-item-qty { font-size: 23rpx; color: #86909C; flex-shrink: 0; }
.rc-item-price { font-size: 25rpx; color: #1D2129; font-weight: 600; flex-shrink: 0; }

.rc-divider { height: 1rpx; background: linear-gradient(90deg, transparent, #E5E6EB, transparent); margin: 16rpx 0; }

.rc-amounts { display: flex; align-items: center; gap: 24rpx; padding: 16rpx 20rpx; background: linear-gradient(135deg, #F7F8FA, #FDFEFF); border-radius: 12rpx; margin-bottom: 12rpx; }
.rc-amount-group { display: flex; align-items: baseline; gap: 6rpx; }
.rc-amt-label { font-size: 21rpx; color: #86909C; }
.rc-amt-deal { font-size: 28rpx; font-weight: 700; color: #FF6B35; }
.rc-amt-paid { font-size: 28rpx; font-weight: 700; color: #00B42A; }
.rc-amt-owed { font-size: 26rpx; font-weight: 700; color: #F53F3F; }

.rc-footer { display: flex; justify-content: space-between; align-items: center; }
.rc-meta-row { display: flex; align-items: center; gap: 8rpx; }
.rc-meta-item { display: flex; align-items: center; gap: 4rpx; white-space: nowrap; flex-shrink: 0; }
.rc-meta-val { font-size: 22rpx; color: #86909C; white-space: nowrap; }
.rc-meta-sep { font-size: 18rpx; color: #E5E6EB; margin: 0 4rpx; }
.rc-time { font-size: 21rpx; color: #C9CDD4; }

.rc-remark { display: flex; align-items: flex-start; gap: 6rpx; margin-top: 12rpx; padding-top: 12rpx; border-top: 1rpx dashed #EDEEF2; }
.rc-remark-text { font-size: 23rpx; color: #86909C; line-height: 1.5; }
.record-type { font-size: 24rpx; color: #3D6DF7; font-weight: 500; }
.record-content { font-size: 25rpx; color: #4E5969; }

.rc-owed-card { border-left: 6rpx solid #F53F3F; }
.rc-owed-badge { font-size: 24rpx; font-weight: 700; color: #F53F3F; background: #FEF2F2; padding: 4rpx 16rpx; border-radius: 20rpx; }
.rc-owed-info { gap: 4rpx; }
.rc-action-row { display: flex; justify-content: flex-end; padding: 8rpx 0; }
.rc-repay-btn { display: flex; align-items: center; gap: 8rpx; padding: 14rpx 40rpx; background: linear-gradient(135deg, #F53F3F 0%, #FF7875 100%); border-radius: 30rpx;
  .rc-repay-text { font-size: 26rpx; color: #fff; font-weight: 600; }
}

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
