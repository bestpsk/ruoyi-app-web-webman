<template>
  <view class="form-container">
    <view class="form-section">
      <u-form ref="formRef" :model="form" :rules="rules" labelPosition="top">
        <u-form-item label="客户" prop="customerName">
          <u-input v-model="form.customerName" disabled disabledColor="#fff" border="surround"></u-input>
        </u-form-item>
        <u-form-item label="门店">
          <u-input v-model="storeName" disabled disabledColor="#fff" border="surround"></u-input>
        </u-form-item>
        <u-form-item label="操作类型" prop="operationType" required>
          <u-input v-model="form.operationType" placeholder="请输入操作类型" border="surround"></u-input>
        </u-form-item>
        <u-form-item label="操作内容" prop="content" required>
          <u-textarea v-model="form.content" placeholder="请输入操作内容" count :maxlength="500"></u-textarea>
        </u-form-item>
      </u-form>
    </view>

    <view class="form-actions">
      <u-button type="info" plain text="取消" @click="goBack"></u-button>
      <u-button type="primary" text="提交" :loading="submitting" @click="submitForm"></u-button>
    </view>
  </view>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { addOperation } from '@/api/business/operationRecord'

const formRef = ref(null)
const submitting = ref(false)
const storeName = ref('')
const enterpriseName = ref('')

const form = reactive({
  customerId: '',
  customerName: '',
  storeId: '',
  operationType: '',
  content: ''
})

const rules = {
  operationType: [{ required: true, message: '请输入操作类型', trigger: 'blur' }],
  content: [{ required: true, message: '请输入操作内容', trigger: 'blur' }]
}

async function submitForm() {
  formRef.value.validate().then(async () => {
    submitting.value = true
    try {
      await addOperation({ ...form })
      uni.showToast({ title: '操作记录已提交', icon: 'success' })
      setTimeout(() => goBack(), 1500)
    } catch (e) { console.error('提交失败:', e) } finally { submitting.value = false }
  }).catch(() => {})
}

function goBack() { uni.navigateBack() }

onMounted(() => {
  const pages = getCurrentPages()
  const options = pages[pages.length - 1].options || {}
  form.customerId = options.customerId || ''
  form.customerName = decodeURIComponent(options.customerName || '')
  form.storeId = options.storeId || ''
  storeName.value = decodeURIComponent(options.storeName || '')
  enterpriseName.value = decodeURIComponent(options.enterpriseName || '')
  uni.setNavigationBarTitle({ title: '添加操作' })
})
</script>

<style lang="scss" scoped>
page { background-color: #F5F7FA; }
.form-container { min-height: 100vh; padding-bottom: 140rpx; }
.form-section { margin: 24rpx; background: #fff; border-radius: 16rpx; padding: 28rpx; box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.04); }
.form-actions { position: fixed; left: 24rpx; right: 24rpx; bottom: 40rpx; display: flex; gap: 20rpx; z-index: 100; .u-button { flex: 1; } }
</style>
