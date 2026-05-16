<template>
  <view class="detail-container">
    <view class="order-info">
      <view class="info-header">
        <text class="order-no">{{ orderInfo.orderNo || ('ORD' + orderInfo.orderId) }}</text>
        <view class="status-tag" :class="'status-' + getStatusClass()">
          {{ getStatusText() }}
        </view>
      </view>

      <view class="info-body">
        <view class="info-row">
          <u-icon name="account" size="20" color="#86909C" />
          <text class="label">客户</text>
          <text class="value">{{ orderInfo.customerName || '-' }}</text>
        </view>

        <view class="info-row">
          <u-icon name="home" size="20" color="#86909C" />
          <text class="label">门店</text>
          <text class="value">
            <template v-if="orderInfo.enterpriseName">
              {{ orderInfo.enterpriseName }} · {{ orderInfo.storeName || '-' }}
            </template>
            <template v-else>
              {{ orderInfo.storeName || '-' }}
            </template>
          </text>
        </view>

        <view v-if="detailMode === 'operation'" class="info-row">
          <u-icon name="account-fill" size="20" color="#86909C" />
          <text class="label">操作人</text>
          <text class="value">{{ orderInfo.operatorName || '-' }}</text>
        </view>

        <view v-if="detailMode === 'operation' && orderInfo.satisfaction != null && orderInfo.satisfaction !== ''" class="info-row">
          <u-icon name="star" size="20" color="#86909C" />
          <text class="label">满意度</text>
          <view class="star-list">
            <u-icon v-for="i in 5" :key="i"
              :name="i <= satisfactionValue ? 'star-fill' : 'star'"
              :size="28"
              :color="i <= satisfactionValue ? '#FFB800' : '#E5E6EB'">
            </u-icon>
          </view>
        </view>

        <view class="info-row amount-row">
          <u-icon name="rmb-circle" size="20" color="#86909C" />
          <text class="label">金额</text>
          <text class="value amount">¥{{ orderInfo.dealAmount || orderInfo.deal_amount || orderInfo.totalAmount || '0.00' }}</text>
        </view>

        <view class="info-row">
          <u-icon name="clock" size="20" color="#86909C" />
          <text class="label">时间</text>
          <text class="value">{{ formatTime(orderInfo.createTime) }}</text>
        </view>

        <view v-if="detailMode === 'operation' && orderInfo.photos && orderInfo.photos.length > 0" class="info-row photo-row">
          <u-icon name="photo" size="20" color="#86909C" />
          <text class="label">照片</text>
          <scroll-view scroll-x class="photo-scroll">
            <view class="photo-list">
              <image v-for="(url, idx) in orderInfo.photos" :key="idx" :src="getImgUrl(url)" mode="aspectFill" class="photo-img" @click="previewImage(url, orderInfo.photos)" />
            </view>
          </scroll-view>
        </view>

        <view v-if="detailMode === 'operation' && orderInfo.feedback" class="info-row">
          <u-icon name="chat" size="20" color="#86909C" />
          <text class="label">反馈</text>
          <text class="value remark-text">{{ orderInfo.feedback }}</text>
        </view>

        <view v-if="!detailMode && orderInfo.remark" class="info-row">
          <u-icon name="edit-pen" size="20" color="#86909C" />
          <text class="label">备注</text>
          <text class="value remark-text">{{ orderInfo.remark }}</text>
        </view>
      </view>
    </view>

    <view class="items-section" v-if="orderItems.length > 0">
      <view class="section-header">
        <u-icon name="list" size="26" color="#86909C" />
        <text class="section-title">订单项目</text>
        <text class="item-count">{{ orderItems.length }}项</text>
      </view>

      <view v-for="(item, idx) in orderItems" :key="idx" class="item-card">
        <view class="item-header">
          <view class="item-left">
            <text class="item-index">{{ idx + 1 }}.</text>
            <text class="item-name">{{ item.productName || item.product_name || item.itemName || '-' }}</text>
          </view>
          <view class="item-right">
            <text class="item-count">{{ item.quantity || item.count || 0 }}次</text>
            <text v-if="item.isDeal === '1' || item.is_deal === '1'" class="deal-tag">已成交</text>
          </view>
        </view>

        <view class="item-body">
          <template v-if="detailMode !== 'operation'">
          <view class="info-line">
            <view class="info-left">
              <text class="info-label">方案价</text>
              <text class="info-value price">¥{{ item.planPrice || item.plan_price || item.price || '0.00' }}</text>
            </view>
            <view class="info-right">
              <text class="info-label">单价</text>
              <text class="info-value">¥{{ getUnitPrice(item) }}</text>
            </view>
          </view>

          <view class="info-line summary-line">
            <view class="info-left">
              <text class="info-label">成交价</text>
              <text class="info-value amount">¥{{ item.dealAmount || item.deal_amount || '0.00' }}</text>
            </view>
            <view class="info-right">
              <template v-if="getOwedAmount(item) > 0">
                <text class="info-label">欠款</text>
                <text class="info-value owed">¥{{ getOwedAmount(item) }}</text>
                <text class="info-gap"></text>
              </template>
              <text class="info-label">实付</text>
              <text class="info-value paid">¥{{ item.paidAmount || item.paid_amount || '0.00' }}</text>
            </view>
          </view>
          </template>

          <template v-else>
          <view class="info-line">
            <view class="info-left">
              <text class="info-label">单价</text>
              <text class="info-value price">¥{{ getUnitPrice(item) }}</text>
            </view>
            <view class="info-right">
              <text class="info-label">总消耗</text>
              <text class="info-value amount">¥{{ item.planPrice || item.plan_price || '0.00' }}</text>
            </view>
          </view>
          </template>
        </view>
      </view>
    </view>

    <view class="audit-section" v-if="canAudit && detailMode !== 'operation'">
      <view class="section-title">审核操作</view>
      <view class="audit-btns">
        <u-button v-if="(orderInfo.orderStatus || orderInfo.status) === '0'" type="primary" text="企业审核通过" @click="handleEnterpriseAudit"></u-button>
        <u-button v-if="(orderInfo.orderStatus || orderInfo.status) === '1'" type="success" text="财务审核通过" @click="handleFinanceAudit"></u-button>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * @description 订单详情页 - 订单/操作记录详情与审核
 * @description 支持两种详情模式：order（订单模式，展示品项/金额/审核）和
 * operation（操作记录模式，展示满意度/照片/反馈），支持企业审核和财务审核操作
 */
import { ref, computed, onMounted } from 'vue'
import { getSalesOrder, enterpriseAudit, financeAudit } from '@/api/business/salesOrder'
import { getOperationRecord } from '@/api/business/operationRecord'

const orderInfo = ref({})
const orderItems = ref([])
const orderId = ref(null)
/** 详情模式：order-订单 / operation-操作记录 */
const detailMode = ref('order')

/** 是否可审核：订单状态为待审核或企业已审时可操作 */
const canAudit = computed(() => {
  const status = orderInfo.value.orderStatus || orderInfo.value.status
  return status === '0' || status === '1'
})

const satisfactionValue = ref(0)

/** 订单状态编码映射为中文名称 */
function getOrderStatusName(status) {
  if (!status && status !== 0) return '未知'
  const statusMap = {
    '0': '待审核',
    '1': '企业已审',
    '2': '财务已审',
    '3': '已完成',
    '4': '已取消'
  }
  return statusMap[String(status)] || '未知'
}

function getStatusText() {
  if (detailMode.value === 'operation') {
    const opType = orderInfo.value.operationType || orderInfo.value.operation_type
    const typeMap = { '0': '持卡操作', '1': '体验操作' }
    return typeMap[String(opType)] || '操作'
  }
  return getOrderStatusName(orderInfo.value.orderStatus || orderInfo.value.status)
}

function getStatusClass() {
  if (detailMode.value === 'operation') {
    const opType = orderInfo.value.operationType || orderInfo.value.operation_type
    return String(opType) === '1' ? '4' : '1'
  }
  return String(orderInfo.value.orderStatus || orderInfo.value.status || '0')
}

function formatTime(time) { if (!time) return ''; return String(time).substring(0, 16) }

/** 计算品项单次价：方案价÷次数 */
function getUnitPrice(item) {
  const qty = Number(item.quantity || item.count || 1)
  const price = Number(item.planPrice || item.plan_price || 0)
  if (qty <= 0) return '0.00'
  return (price / qty).toFixed(2)
}

/** 计算品项欠款金额：成交金额-实付金额，最小为0 */
function getOwedAmount(item) {
  const deal = Number(item.dealAmount || item.deal_amount || 0)
  const paid = Number(item.paidAmount || item.paid_amount || 0)
  return Math.max(0, deal - paid).toFixed(2)
}

/** 根据图片路径拼接完整URL，处理相对路径和绝对路径 */
function getImgUrl(path) {
  if (!path) return ''
  if (String(path).startsWith('http')) return path
  if (String(path).startsWith('/profile/')) return path
  return '/profile/upload/' + path
}

/** 预览图片，使用uni.previewImage全屏查看 */
function previewImage(current, urls) {
  const fullUrls = urls.map(url => getImgUrl(url)).filter(Boolean)
  uni.previewImage({ current: getImgUrl(current), urls: fullUrls })
}

/** 加载订单/操作记录详情，根据detailMode区分两种模式，操作记录模式需额外映射字段 */
async function loadDetail() {
  if (!orderId.value) return
  try {
    uni.showLoading({ title: '加载中...' })

    let data
    if (detailMode.value === 'operation') {
      const response = await getOperationRecord(orderId.value)
      data = response.data || response

      const record = data.record || data
      const items = (data.items && data.items.length > 0) ? data.items : [record]

      orderInfo.value = {
        recordId: record.recordId || record.record_id,
        orderNo: record.packageNo || ('OPR' + (record.recordId || record.record_id)),
        operationType: record.operationType || record.operation_type,
        customerName: record.customerName || record.customer_name,
        enterpriseName: data.enterpriseName || data.enterprise_name || record.enterpriseName || record.enterprise_name,
        storeName: data.storeName || data.store_name || record.storeName || record.store_name,
        dealAmount: data.totalAmount || data.total_amount || record.consumeAmount || record.consume_amount || 0,
        totalAmount: data.totalAmount || data.total_amount || record.consumeAmount || record.consume_amount || 0,
        createTime: record.operationDate || record.operation_date || record.createTime || record.create_time,
        remark: record.remark,
        operatorName: record.operatorUserName || record.operator_user_name,
        satisfaction: record.satisfaction,
        feedback: record.customerFeedback || record.customer_feedback,
        photos: [record.beforePhoto || record.before_photo, record.afterPhoto || record.after_photo].filter(Boolean)
      }

      satisfactionValue.value = Number(record.satisfaction) || 0

      orderItems.value = items.map(item => ({
        product_name: item.productName || item.product_name,
        productName: item.productName || item.product_name,
        quantity: item.operationQuantity || item.operation_quantity,
        count: item.operationQuantity || item.operation_quantity,
        planPrice: Number(item.consumeAmount || item.consume_amount || item.trialPrice || item.trial_price || 0),
        dealAmount: Number(item.consumeAmount || item.consume_amount || item.trialPrice || item.trial_price || 0),
        paidAmount: Number(item.consumeAmount || item.consume_amount || item.trialPrice || item.trial_price || 0)
      }))
    } else {
      const response = await getSalesOrder(orderId.value)
      data = response.data || response
      orderInfo.value = data
      orderItems.value = data.items || []
    }
  } catch (e) { console.error('加载订单详情失败:', e); uni.showToast({ title: '加载失败', icon: 'none' }) }
  finally { uni.hideLoading() }
}

/** 企业审核，弹出确认框后调用企业审核接口，成功后刷新详情 */
async function handleEnterpriseAudit() {
  uni.showModal({ title: '提示', content: '确认企业审核通过?', success: async (res) => {
    if (res.confirm) {
      try { await enterpriseAudit(orderId.value); uni.showToast({ title: '审核成功', icon: 'success' }); loadDetail() }
      catch (e) { console.error('审核失败:', e) }
    }
  }})
}

/** 财务审核，弹出确认框后调用财务审核接口，成功后刷新详情 */
async function handleFinanceAudit() {
  uni.showModal({ title: '提示', content: '确认财务审核通过?', success: async (res) => {
    if (res.confirm) {
      try { await financeAudit(orderId.value); uni.showToast({ title: '审核成功', icon: 'success' }); loadDetail() }
      catch (e) { console.error('审核失败:', e) }
    }
  }})
}

onMounted(() => {
  const pages = getCurrentPages()
  const options = pages[pages.length - 1].options || {}
  orderId.value = options.id ? parseInt(options.id) : null
  detailMode.value = options.type || 'order'

  uni.setNavigationBarTitle({
    title: detailMode.value === 'operation' ? '操作详情' : '订单详情'
  })
  loadDetail()
})
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }
.detail-container { min-height: 100vh; padding: 24rpx; }

/* ========== 订单基本信息卡片 ========== */
.order-info {
  background: #fff;
  border-radius: 12rpx;
  padding: 28rpx;
  margin-bottom: 24rpx;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24rpx;
  padding-bottom: 20rpx;
  border-bottom: 1rpx solid #F2F3F5;
}

.order-no {
  font-size: 30rpx;
  font-weight: 600;
  color: #1D2129;
}

.status-tag {
  padding: 6rpx 18rpx;
  border-radius: 6rpx;
  font-size: 23rpx;
  font-weight: 500;

  &.status-0 { background: #FFF7E8; color: #FF7D00; }
  &.status-1 { background: #E8F0FE; color: #3D6DF7; }
  &.status-2 { background: #E8F0FE; color: #3D6DF7; }
  &.status-3 { background: #E8FFEA; color: #00B42A; }
  &.status-4 { background: #F2F3F5; color: #86909C; }
}

.info-body {
  display: flex;
  flex-direction: column;
  gap: 10rpx;
}

.info-row {
  display: flex;
  align-items: center;
  gap: 10rpx;

  .u-icon {
    flex-shrink: 0;
  }

  .star-list {
    display: flex;
    align-items: center;
    gap: 4rpx;
  }

  &.amount-row {
    margin-top: 4rpx;
    padding-top: 8rpx;
  }

  &.photo-row {
    align-items: flex-start;

    .photo-scroll {
      flex: 1;
      white-space: nowrap;
    }

    .photo-list {
      display: inline-flex;
      gap: 12rpx;
    }

    .photo-img {
      width: 120rpx;
      height: 120rpx;
      border-radius: 8rpx;
      border: 1rpx solid #EBEEF5;
    }
  }
}

.label {
  font-size: 26rpx;
  color: #86909C;
  min-width: 80rpx;
}

.value {
  font-size: 27rpx;
  color: #1D2129;
  flex: 1;

  &.amount {
    color: #1D2129;
    font-weight: 600;
    font-size: 30rpx;
  }

  &.strike-through {
    text-decoration: line-through;
    color: #86909C;
  }

  &.remark-text {
    word-break: break-all;
  }
}

/* ========== 订单项目区域（扁平化设计）========== */
.items-section {
  background: #fff;
  border-radius: 12rpx;
  padding: 28rpx;
  margin-bottom: 24rpx;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 10rpx;
  margin-bottom: 20rpx;
  padding-bottom: 16rpx;
  border-bottom: 1rpx solid #F2F3F5;

  .section-title {
    font-size: 29rpx;
    font-weight: 600;
    color: #1D2129;
  }

  .item-count {
    font-size: 22rpx;
    color: #86909C;
    background: #F5F7FA;
    padding: 2rpx 14rpx;
    border-radius: 4rpx;
  }
}

.item-card {
  padding: 20rpx 0;
  border-bottom: 1rpx solid #F2F3F5;

  &:last-child {
    border-bottom: none;
  }
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16rpx;

  .item-left {
    display: flex;
    align-items: baseline;
    gap: 12rpx;
    flex: 1;
  }

  .item-right {
    display: flex;
    align-items: center;
    gap: 12rpx;
    flex-shrink: 0;
    margin-left: auto;
  }

  .item-index {
    font-size: 26rpx;
    color: #86909C;
    font-weight: 500;
  }

  .item-name {
    font-size: 27rpx;
    color: #1D2129;
    font-weight: 500;
  }

  .item-count {
    font-size: 24rpx;
    color: #3D6DF7;
    font-weight: 600;
  }

  .deal-tag {
    font-size: 21rpx;
    color: #00B42A;
    background: transparent;
    border: 1rpx solid #00B42A;
    padding: 2rpx 12rpx;
    border-radius: 4rpx;
    white-space: nowrap;
  }
}

.item-body {
  display: flex;
  flex-direction: column;
  gap: 12rpx;
}

.info-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 25rpx;
  line-height: 1.6;

  .info-left {
    display: flex;
    align-items: center;
    gap: 8rpx;
    flex: 1;
  }

  .info-right {
    display: flex;
    align-items: center;
    gap: 8rpx;
    flex-shrink: 0;
    margin-left: auto;
  }

  .info-label {
    color: #86909C;
    white-space: nowrap;
    font-size: 24rpx;
  }

  .info-value {
    color: #4E5969;
    font-size: 25rpx;

    &.price {
      color: #1D2129;
      font-weight: 500;
    }

    &.amount {
      color: #FF6B35;
      font-weight: 600;
    }

    &.paid {
      color: #00B42A;
      font-weight: 500;
    }

    &.owed {
      color: #F56C6C;
      font-weight: 500;
    }
  }

  .info-gap {
    width: 16rpx;
  }

  &.summary-line {
    margin-top: 4rpx;
    padding-top: 6rpx;
  }
}

/* ========== 审核操作区域 ========== */
.audit-section {
  background: #fff;
  border-radius: 12rpx;
  padding: 28rpx;

  .section-title {
    font-size: 29rpx;
    font-weight: 600;
    color: #1D2129;
    margin-bottom: 20rpx;
  }
}

.audit-btns {
  display: flex;
  gap: 20rpx;

  .u-button {
    flex: 1;
  }
}
</style>
