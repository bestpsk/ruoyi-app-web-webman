<template>
  <view class="sales-container">
    <view class="search-section">
      <view class="selector-row">
        <view class="selector-item" @click="showEnterprisePicker = true">
          <text class="selector-label">企业</text>
          <text class="selector-value">{{ currentEnterpriseName || '请选择' }}</text>
          <u-icon name="arrow-down" size="12" color="#fff"></u-icon>
        </view>
        <view class="selector-item" @click="showStorePicker = true" :style="{ opacity: currentEnterpriseId ? 1 : 0.5 }">
          <text class="selector-label">门店</text>
          <text class="selector-value">{{ currentStoreName || '请选择' }}</text>
          <u-icon name="arrow-down" size="12" color="#fff"></u-icon>
        </view>
      </view>
      <view class="search-box" v-if="currentStoreId">
        <u-icon name="search" size="16" color="#86909C"></u-icon>
        <input class="search-input" type="text" v-model="customerKeyword" placeholder="搜索客户" placeholder-class="search-placeholder" @input="onCustomerSearch" />
      </view>
    </view>

    <u-picker :show="showEnterprisePicker" :columns="[enterpriseColumns]" keyName="enterpriseName" title="选择企业" @confirm="onEnterpriseConfirm" @cancel="showEnterprisePicker = false" @close="showEnterprisePicker = false"></u-picker>
    <u-picker :show="showStorePicker" :columns="[storeColumns]" keyName="storeName" title="选择门店" @confirm="onStoreConfirm" @cancel="showStorePicker = false" @close="showStorePicker = false"></u-picker>

    <scroll-view scroll-y class="list-scroll" :style="{ height: scrollHeight + 'px' }" v-if="currentStoreId">
      <view v-if="customerList.length > 0" class="card-list">
        <view v-for="item in customerList" :key="item.customerId" class="customer-card" @click="goCustomerDetail(item)">
          <view class="card-header">
            <view class="customer-name">
              <u-icon :name="item.gender === '1' ? 'woman' : 'man'" :size="18" :color="item.gender === '1' ? '#FF6B9D' : '#3D6DF7'"></u-icon>
              <text class="name-text">{{ item.customerName }}</text>
              <text class="gender-text" :class="item.gender === '1' ? 'female' : 'male'">{{ item.gender === '1' ? '女' : '男' }}</text>
              <text class="age-text" v-if="item.age">{{ item.age }}岁</text>
            </view>
          </view>
          <view class="card-body">
            <view class="info-row">
              <view class="tag-list" v-if="item.tag">
                <text class="customer-tag" v-for="(tag, idx) in item.tag.split(',')" :key="idx">{{ tag }}</text>
              </view>
              <text class="no-tag" v-else>暂无标签</text>
            </view>
          </view>
          <view class="card-actions">
            <view class="action-btn order" @click.stop="goCreateOrder(item)"><u-icon name="edit-pen" size="14"></u-icon><text>开单</text></view>
            <view class="action-btn op" @click.stop="goCreateOperation(item)"><u-icon name="grid" size="14"></u-icon><text>操作</text></view>
          </view>
        </view>
      </view>
      <u-empty v-else mode="data" text="暂无客户数据" :marginTop="100"></u-empty>
    </scroll-view>

    <view v-else class="empty-store">
      <u-icon name="shop" size="60" color="#C9CDD4"></u-icon>
      <text class="empty-text">请先选择企业和门店</text>
    </view>

    <view class="fab-btn" @click="goAddCustomer" v-if="currentStoreId">
      <u-icon name="plus" size="24" color="#fff"></u-icon>
    </view>
  </view>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { listEnterprise } from '@/api/business/enterprise'
import { searchStore } from '@/api/business/store'
import { searchCustomer, addCustomer } from '@/api/business/customer'

const enterpriseColumns = ref([])
const storeColumns = ref([])
const customerList = ref([])
const currentEnterpriseId = ref('')
const currentEnterpriseName = ref('')
const currentStoreId = ref('')
const currentStoreName = ref('')
const customerKeyword = ref('')
const showEnterprisePicker = ref(false)
const showStorePicker = ref(false)
const scrollHeight = ref(600)

let searchTimer = null

async function loadEnterpriseOptions() {
  try {
    const response = await listEnterprise({ pageNum: 1, pageSize: 1000, status: '0' })
    const data = response.data || response
    enterpriseColumns.value = data.rows || []
  } catch (e) { console.error('加载企业列表失败:', e) }
}

async function onEnterpriseConfirm(e) {
  const item = e.value[0]
  currentEnterpriseId.value = item.enterpriseId
  currentEnterpriseName.value = item.enterpriseName
  currentStoreId.value = ''
  currentStoreName.value = ''
  storeColumns.value = []
  customerList.value = []
  showEnterprisePicker.value = false
  try {
    const response = await searchStore('', item.enterpriseId)
    const data = response.data || response
    storeColumns.value = data.rows || data || []
  } catch (e) { console.error('加载门店列表失败:', e) }
}

async function onStoreConfirm(e) {
  const item = e.value[0]
  currentStoreId.value = item.storeId
  currentStoreName.value = item.storeName
  showStorePicker.value = false
  loadCustomerList()
}

async function loadCustomerList() {
  if (!currentStoreId.value) return
  try {
    const response = await searchCustomer(customerKeyword.value, currentEnterpriseId.value, currentStoreId.value)
    const data = response.data || response
    customerList.value = data.rows || data || []
  } catch (e) { console.error('加载客户列表失败:', e) }
}

function onCustomerSearch() {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => loadCustomerList(), 500)
}

function goCustomerDetail(item) {
  uni.navigateTo({ url: `/pages/business/sales/order?customerId=${item.customerId}&storeName=${currentStoreName.value}&enterpriseName=${currentEnterpriseName.value}` })
}

function goCreateOrder(item) {
  uni.navigateTo({ url: `/pages/business/sales/order?customerId=${item.customerId}&storeId=${currentStoreId.value}&storeName=${currentStoreName.value}&enterpriseName=${currentEnterpriseName.value}` })
}

function goCreateOperation(item) {
  uni.navigateTo({ url: `/pages/business/sales/operation?customerId=${item.customerId}&customerName=${item.customerName}&storeId=${currentStoreId.value}&storeName=${currentStoreName.value}&enterpriseName=${currentEnterpriseName.value}` })
}

function goAddCustomer() {
  uni.showModal({
    title: '新增客户', editable: true, placeholderText: '请输入客户姓名',
    success: async (res) => {
      if (res.confirm && res.content) {
        try {
          await addCustomer({ customerName: res.content, enterpriseId: currentEnterpriseId.value, storeId: currentStoreId.value, status: '0' })
          uni.showToast({ title: '新增成功', icon: 'success' })
          loadCustomerList()
        } catch (e) { console.error('新增客户失败:', e) }
      }
    }
  })
}

function calcScrollHeight() {
  const systemInfo = uni.getSystemInfoSync()
  scrollHeight.value = systemInfo.windowHeight - 200
}

onMounted(() => { calcScrollHeight(); loadEnterpriseOptions() })
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }
.sales-container { min-height: 100vh; padding: 0 24rpx; padding-bottom: 120rpx; }

.search-section { padding: 20rpx 24rpx; margin-left: -24rpx; margin-right: -24rpx; background: linear-gradient(180deg, #3D6DF7 0%, #4A7AEF 100%); }
.selector-row { display: flex; gap: 16rpx; margin-bottom: 16rpx; }
.selector-item { flex: 1; display: flex; align-items: center; gap: 8rpx; background: rgba(255,255,255,0.15); border-radius: 12rpx; padding: 16rpx 20rpx; }
.selector-label { font-size: 24rpx; color: rgba(255,255,255,0.7); white-space: nowrap; }
.selector-value { flex: 1; font-size: 28rpx; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.search-box { display: flex; align-items: center; background: #fff; border-radius: 36rpx; padding: 0 24rpx; height: 72rpx; gap: 12rpx; }
.search-input { flex: 1; font-size: 28rpx; color: #1D2129; height: 72rpx; }
.search-placeholder { color: #86909C; font-size: 28rpx; }

.list-scroll { padding: 20rpx 0; }
.card-list { display: flex; flex-direction: column; gap: 20rpx; }

.customer-card { background: #fff; border-radius: 16rpx; padding: 28rpx; box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.04);
  &:active { transform: scale(0.98); opacity: 0.9; }
}
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16rpx; }
.customer-name { display: flex; align-items: center; gap: 12rpx;
  .name-text { font-size: 30rpx; font-weight: 600; color: #1D2129; }
}
.gender-text { font-size: 22rpx; padding: 2rpx 10rpx; border-radius: 4rpx;
  &.male { color: #3D6DF7; background: #E8F0FE; }
  &.female { color: #FF6B9D; background: #FFF0F5; }
}
.age-text { font-size: 24rpx; color: #86909C; }
.tag-list { display: flex; gap: 8rpx; flex-wrap: wrap; }
.customer-tag { padding: 4rpx 12rpx; background: #E8F0FE; color: #3D6DF7; border-radius: 4rpx; font-size: 22rpx; }
.no-tag { font-size: 24rpx; color: #C9CDD4; }

.card-body { padding: 16rpx 0; border-top: 1rpx solid #F2F3F5; }
.info-row { display: flex; }
.info-item { flex: 1; display: flex; align-items: center; gap: 12rpx;
  .label { font-size: 24rpx; color: #86909C; min-width: 60rpx; }
  .value { font-size: 26rpx; color: #1D2129; }
}

.card-actions { display: flex; gap: 20rpx; margin-top: 16rpx; padding-top: 16rpx; border-top: 1rpx solid #F2F3F5; }
.action-btn { flex: 1; display: flex; align-items: center; justify-content: center; gap: 8rpx; padding: 16rpx; border-radius: 12rpx; font-size: 28rpx; font-weight: 500;
  &.order { color: #3D6DF7; background: #E8F0FE; }
  &.op { color: #FF6B35; background: #FFF3ED; }
}

.empty-store { display: flex; flex-direction: column; align-items: center; justify-content: center; padding-top: 200rpx; }
.empty-text { font-size: 28rpx; color: #86909C; margin-top: 20rpx; }

.fab-btn { position: fixed; right: 32rpx; bottom: 120rpx; width: 100rpx; height: 100rpx; border-radius: 50%; background: linear-gradient(135deg, #FF6B35, #FF8F5E); display: flex; align-items: center; justify-content: center; box-shadow: 0 8rpx 24rpx rgba(255,107,53,0.4);
  &:active { transform: scale(0.95); opacity: 0.9; }
}
</style>
