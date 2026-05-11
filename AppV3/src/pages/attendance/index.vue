<template>
  <view class="attendance-container" :class="{ 'page-ready': pageReady }">
    <view class="header-section">
      <text class="date-text">{{ currentDate }}</text>
      <text class="greeting-text">{{ greeting }}</text>
    </view>

    <view class="type-tabs">
      <view class="type-tab" :class="{ 'tab-active': clockType === '0' }" @click="clockType = '0'">
        <u-icon name="home" :size="16" :color="clockType === '0' ? '#fff' : '#86909C'" />
        <text class="tab-text" :class="{ 'tab-text-active': clockType === '0' }">坐班打卡</text>
      </view>
      <view class="type-tab" :class="{ 'tab-active': clockType === '1' }" @click="clockType = '1'">
        <u-icon name="map" :size="16" :color="clockType === '1' ? '#fff' : '#86909C'" />
        <text class="tab-text" :class="{ 'tab-text-active': clockType === '1' }">外勤打卡</text>
      </view>
    </view>

    <view class="location-card" v-if="clockType === '0'" :class="{ 'loc-card-success': !locationError && location.address && !isCoordinateOnly(location.address), 'loc-card-loading': locationLoading, 'loc-card-error': locationError }">
      <view v-if="locationLoading" class="location-info">
        <view class="loading-dots">
          <view class="dot"></view>
          <view class="dot"></view>
          <view class="dot"></view>
        </view>
        <text class="location-text location-unknown">正在获取位置...</text>
      </view>

      <view v-else-if="!locationError && location.address && location.address !== '定位失败' && !isCoordinateOnly(location.address)" class="location-success">
        <view class="loc-indicator"></view>
        <view class="loc-main">
          <u-icon name="map-fill" size="20" color="#52c41a" />
          <view class="loc-detail">
            <text class="loc-address">{{ formatShortAddress(location.address, location.poi) }}</text>
          </view>
        </view>
        <view class="loc-coord-row">
          <text class="loc-distance">
            <u-icon name="navigation" size="12" color="#52c41a" />
            {{ formatDistance(distanceToWorkplace) ? `距考勤点${formatDistance(distanceToWorkplace)}` : '' }}
          </text>
          <view class="loc-refresh" @click="getLocation">
            <u-icon name="reload" size="13" color="#86909C" />
          </view>
        </view>
      </view>

      <view v-else-if="!locationError && location.address && isCoordinateOnly(location.address)" class="location-pending">
        <view class="loc-indicator loc-indicator-pending"></view>
        <view class="loc-main">
          <u-icon name="navigation" size="18" :color="geocodingLoading ? '#3D6DF7' : '#faad14'" />
          <view class="loc-detail">
            <text class="loc-address loc-pending-text">{{ geocodingLoading ? '正在解析地址...' : '地址解析中' }}</text>
            <text class="loc-coord-inline">{{ location.address }}</text>
          </view>
        </view>
      </view>

      <view v-else class="location-fail">
        <view class="location-info">
          <u-icon name="close-circle" size="18" color="#f5222d" />
          <text class="location-text" style="color: #f5222d; font-weight: 500;">定位失败</text>
        </view>
        <text class="fail-hint">无法自动获取位置，请选择以下方式</text>
        <view class="fail-actions">
          <view class="fail-btn fail-btn-primary" @click="getLocation">
            <u-icon name="reload" size="14" color="#fff" />
            <text>重新定位</text>
          </view>
          <view class="fail-btn fail-btn-secondary" @click="showManualInput = true">
            <u-icon name="edit-pen" size="14" color="#3D6DF7" />
            <text style="color: #3D6DF7;">手动输入</text>
          </view>
        </view>
        <view class="manual-address" v-if="showManualInput">
          <input
            class="manual-input"
            type="text"
            placeholder="请输入您的当前位置"
            v-model="manualAddress"
          />
          <view class="manual-confirm" @click="confirmManualAddress" v-if="manualAddress.trim()">
            <text>确认使用此地址</text>
          </view>
        </view>
      </view>
    </view>

    <view class="outside-card" v-if="clockType === '1'">
      <view class="outside-label">外勤事由</view>
      <textarea
        class="outside-input"
        v-model="outsideReason"
        placeholder="请输入外勤事由（必填）"
        maxlength="200"
      />
    </view>

    <view class="clock-section">
      <view class="clock-ring" v-if="!todayRecord?.clockOutTime"></view>
      <view
        class="clock-btn"
        :class="clockBtnClass"
        @click="handleClockClick"
      >
        <text class="clock-btn-text">{{ clockBtnText }}</text>
        <text class="clock-btn-time">{{ currentTime }}</text>
      </view>
    </view>

    <view class="today-card" v-if="todayRecord">
      <view class="card-header">
        <text class="card-title">今日打卡</text>
        <view class="status-tag" :class="statusClass">
          <text class="status-text">{{ statusLabel }}</text>
        </view>
      </view>
      
      <view class="clock-count-info">
        <text>已打卡 {{ todayRecord.clockCount || 0 }} 次</text>
      </view>
      
      <view class="clock-list" v-if="clockList.length > 0">
        <view v-for="(clock, index) in clockList" :key="clock.clockId || index" class="clock-item">
          <view class="clock-item-left">
            <text class="clock-time">{{ formatTime(clock.clockTime) }}</text>
            <text class="clock-type-tag" :class="getClockTagClass(index)">
              {{ getClockTagText(index) }}
            </text>
          </view>
          <text class="clock-address">{{ formatShortAddress(clock.address) || '未记录位置' }}</text>
        </view>
      </view>
      
      <view class="timeline" v-if="false">
        <view class="timeline-item" :class="{ 'timeline-first': !todayRecord.clockInTime, 'timeline-last': todayRecord.clockOutTime }">
          <view class="timeline-dot" :class="{ 'dot-done': todayRecord.clockInTime, 'dot-late': todayRecord.attendanceStatus === '1' || todayRecord.attendanceStatus === '3' }">
            <u-icon v-if="todayRecord.clockInTime" name="checkmark" size="10" color="#fff" />
          </view>
          <view class="timeline-content">
            <text class="timeline-time">{{ formatTime(todayRecord.clockInTime) }}</text>
            <text class="timeline-label">上班打卡</text>
            <view v-if="todayRecord.clockInTime" class="timeline-status" :class="{ 'status-late': todayRecord.attendanceStatus === '1' || todayRecord.attendanceStatus === '3' }">
              <text>{{ (todayRecord.attendanceStatus === '1' || todayRecord.attendanceStatus === '3') ? '迟到' : '正常' }}</text>
            </view>
          </view>
        </view>
        <view class="timeline-line" v-if="!todayRecord.clockOutTime || !todayRecord.clockInTime"></view>
        <view class="timeline-item timeline-last" v-if="todayRecord.clockOutTime">
          <view class="timeline-dot" :class="{ 'dot-done': true, 'dot-early': todayRecord.attendanceStatus === '2' || todayRecord.attendanceStatus === '3' }">
            <u-icon name="checkmark" size="10" color="#fff" />
          </view>
          <view class="timeline-content">
            <text class="timeline-time">{{ formatTime(todayRecord.clockOutTime) }}</text>
            <text class="timeline-label">下班打卡</text>
            <view class="timeline-status" :class="{ 'status-early': todayRecord.attendanceStatus === '2' || todayRecord.attendanceStatus === '3' }">
              <text>{{ (todayRecord.attendanceStatus === '2' || todayRecord.attendanceStatus === '3') ? '早退' : '正常' }}</text>
            </view>
          </view>
        </view>
      </view>
    </view>

    <view class="photo-section" v-if="showPhotoArea">
      <view class="photo-label">打卡照片{{ clockType === '1' ? '（必填）' : '' }}</view>
      <view class="photo-area" @click="takePhoto" v-if="!photoUrl">
        <u-icon name="camera" size="36" color="#c0c4cc" />
        <text class="photo-hint">点击拍照</text>
      </view>
      <view class="photo-preview" v-else>
        <image :src="photoUrl" mode="aspectFill" class="photo-img" @click="takePhoto" />
        <view class="photo-overlay" @click="takePhoto">
          <u-icon name="reload" size="14" color="#fff" />
          <text class="overlay-text">重新拍摄</text>
        </view>
      </view>
    </view>

    <view class="footer-link" @click="goToRecord">
      <text class="footer-text">查看打卡记录</text>
      <u-icon name="arrow-right" size="14" color="#86909C" />
    </view>
  </view>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { getTodayRecord, clock, getTodayClockList, uploadAttendancePhoto, getUserAttendanceRule } from '@/api/attendance'
import config from '@/config'

const AMAP_WEB_SERVICE_KEY = config.amap.webServiceKey

const currentDate = ref('')
const currentTime = ref('')
const greeting = ref('')
const todayRecord = ref(null)
const location = ref({ latitude: null, longitude: null, address: '', poi: '', district: '' })
const locationLoading = ref(false)
const locationError = ref(false)
const geocodingLoading = ref(false)
const manualAddress = ref('')
const showManualInput = ref(false)
const pageReady = ref(false)
const photoUrl = ref('')
const photoUploadedUrl = ref('')
const clockType = ref('0')
const outsideReason = ref('')
const clockList = ref([])
const recordMap = ref({})
const clockListMap = ref({})
const selectedDate = ref(null)
const shortAddress = ref('')
const distanceToWorkplace = ref(null)
const userRule = ref(null)
const ruleLoading = ref(false)

let timer = null
let locationTimer = null

const showPhotoArea = computed(() => {
  return true
})

const canClock = computed(() => {
  if (clockType.value === '0') {
    return !!(location.value.latitude || manualAddress.value.trim())
  }

  if (clockType.value === '1') {
    return !!(outsideReason.value.trim() && photoUploadedUrl.value)
  }

  return false
})

const clockBtnText = computed(() => {
  if (!todayRecord.value || todayRecord.value.clockCount === 0) {
    return '上班打卡'
  }
  return '打卡'
})

const clockBtnClass = computed(() => {
  if (!canClock.value) {
    return 'btn-disabled'
  }

  return 'btn-clock-in'
})

const statusLabel = computed(() => {
  if (!todayRecord.value) return ''
  const map = { '0': '正常', '1': '迟到', '2': '早退', '3': '迟到+早退', '4': '缺勤' }
  return map[todayRecord.value.attendanceStatus] || ''
})

const statusClass = computed(() => {
  if (!todayRecord.value) return ''
  const map = { '0': 'tag-normal', '1': 'tag-late', '2': 'tag-early', '3': 'tag-danger', '4': 'tag-danger' }
  return map[todayRecord.value.attendanceStatus] || ''
})

function isCoordinateOnly(str) {
  if (!str) return false
  return /^\s*[-+]?\d+\.\d+,\s*[-+]?\d+\.\d+\s*$/.test(str)
}

function formatShortAddress(fullAddress, poi) {
  if (poi && poi.length > 0) {
    return poi
  }

  if (!fullAddress || isCoordinateOnly(fullAddress)) {
    return fullAddress || ''
  }

  const address = fullAddress.trim()

  if (address.includes('(')) {
    const match = address.match(/\(([^)]+)\)/)
    if (match && match[1] && match[1].length >= 2) {
      return match[1]
    }
  }

  if (address.length <= 20) {
    return address
  }

  const meaningfulPatterns = [
    /(?:省|市|自治区)[^区县镇路]{0,10}(?:区|县|镇|乡)/,
    /(?:区|县|镇|路|街|道|巷|弄|号)[^区县镇路]{2,15}/,
    /[^\s区县镇路街道巷弄号()（）]{2,18}(?:区|县|镇|路|街)?$/
  ]

  for (const pattern of meaningfulPatterns) {
    const match = address.match(pattern)
    if (match && match[0] && match[0].length >= 2) {
      let result = match[0].trim()
      if (result.length > 20) {
        result = result.substring(0, 18) + '...'
      }
      return result
    }
  }

  const separators = ['区', '县', '镇', '路', '街', '道', '巷']
  for (let i = address.length - 1; i >= Math.max(0, address.length - 25); i--) {
    if (separators.includes(address[i])) {
      const candidate = address.substring(i + 1).trim()
      if (candidate.length >= 2) {
        return candidate.length > 20 ? candidate.substring(0, 18) + '...' : candidate
      }
    }
  }

  if (address.length > 20) {
    return address.substring(0, 18) + '...'
  }

  return address.length >= 2 ? address : (address + '...')
}

function calculateDistance(lat1, lng1, lat2, lng2) {
  const R = 6371000
  const dLat = (lat2 - lat1) * Math.PI / 180
  const dLng = (lng2 - lng1) * Math.PI / 180
  const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2)
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  return Math.round(R * c)
}

function formatDistance(meters) {
  if (!meters && meters !== 0) return ''
  if (meters < 1000) {
    return `${meters}米`
  }
  return `${(meters / 1000).toFixed(1)}公里`
}

function formatTime(time) {
  if (!time) return '--:--'
  return time.substring(11, 16)
}

function updateDateTime() {
  const now = new Date()
  const weekDays = ['日', '一', '二', '三', '四', '五', '六']
  currentDate.value = `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日 星期${weekDays[now.getDay()]}`
  currentTime.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`
  const hour = now.getHours()
  if (hour < 6) greeting.value = '凌晨好'
  else if (hour < 9) greeting.value = '早上好'
  else if (hour < 12) greeting.value = '上午好'
  else if (hour < 14) greeting.value = '中午好'
  else if (hour < 18) greeting.value = '下午好'
  else greeting.value = '晚上好'
}

function getLocation() {
  locationLoading.value = true
  locationError.value = false
  geocodingLoading.value = false
  doGetLocation()
}

function handleLocationSuccess(lat, lng) {
  location.value.latitude = lat
  location.value.longitude = lng
  location.value.address = `${lat.toFixed(6)}, ${lng.toFixed(6)}`
  locationLoading.value = false
  locationError.value = false

  if (userRule.value && userRule.value.work_latitude && userRule.value.work_longitude) {
    distanceToWorkplace.value = calculateDistance(
      lat, lng,
      userRule.value.work_latitude,
      userRule.value.work_longitude
    )
  } else {
    distanceToWorkplace.value = null
  }

  reverseGeocode(lat, lng)
}

async function ipGeolocation() {
  try {
    const key = AMAP_WEB_SERVICE_KEY
    const url = `https://restapi.amap.com/v3/ip?key=${key}`
    const res = await new Promise((resolve, reject) => {
      uni.request({ url, method: 'GET', timeout: 5000, success: resolve, fail: reject })
    })
    if (res.data?.city || res.data?.province) {
      const ipLocation = `${res.data.province || ''}${res.data.city || ''}`.trim()
      if (ipLocation) {
        location.value.address = ipLocation
        location.value.district = ipLocation
      }
      return true
    }
  } catch (e) {
    console.warn('IP定位失败', e)
  }
  return false
}

function showLocationError(error) {
  locationLoading.value = false
  locationError.value = true
  location.value.address = '定位失败'

  console.warn('定位失败:', error?.message || '未知错误')
}

function confirmManualAddress() {
  if (manualAddress.value.trim()) {
    location.value.address = manualAddress.value.trim()
    locationError.value = false
    showManualInput.value = false
    shortAddress.value = manualAddress.value.trim().length > 20
      ? manualAddress.value.trim().substring(0, 18) + '...'
      : manualAddress.value.trim()
    uni.showToast({ title: '已使用手动输入的地址', icon: 'success' })
  }
}

function doGetLocation() {
  clearTimeout(locationTimer)
  locationTimer = setTimeout(() => {
    console.warn('定位超时，使用降级方案')
    fallbackGetLocation()
  }, 5000)

  uni.getLocation({
    type: 'gcj02',
    success: (res) => {
      clearTimeout(locationTimer)
      const lat = res.latitude
      const lng = res.longitude
      handleLocationSuccess(lat, lng)
    },
    fail: (err) => {
      clearTimeout(locationTimer)
      console.warn('uni.getLocation 失败:', err?.errMsg)
      fallbackGetLocation()
    }
  })
}

async function fallbackGetLocation() {
  if (typeof navigator !== 'undefined' && navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude
        const lng = position.coords.longitude
        handleLocationSuccess(lat, lng)
      },
      (error) => {
        console.warn('navigator.geolocation 失败:', error.message)
        showLocationError(error)
      },
      { timeout: 8000, enableHighAccuracy: false, maximumAge: 60000 }
    )
  } else {
    console.warn('浏览器不支持 geolocation，尝试IP定位')
    const ipSuccess = await ipGeolocation()
    if (!ipSuccess) {
      locationLoading.value = false
      locationError.value = true
      location.value.address = '定位失败'
    }
  }
}

async function reverseGeocode(lat, lng) {
  geocodingLoading.value = true
  try {
    const key = AMAP_WEB_SERVICE_KEY
    const url = `https://restapi.amap.com/v3/geocode/regeo?location=${lng},${lat}&key=${key}&extensions=all&output=JSON`
    const res = await new Promise((resolve, reject) => {
      uni.request({ url, method: 'GET', timeout: 8000, success: resolve, fail: reject })
    })
    if (res.data?.regeocode) {
      const regeocode = res.data.regeocode
      location.value.address = regeocode.formatted_address || location.value.address

      if (regeocode.pois && regeocode.pois.length > 0) {
        location.value.poi = regeocode.pois[0].name
      }

      if (regeocode.addressComponent) {
        const addr = regeocode.addressComponent
        location.value.district = `${addr.province || ''}${addr.city || ''}${addr.district || ''}`.trim()
      }

      shortAddress.value = formatShortAddress(location.value.address, location.value.poi)
    }
  } catch (e) {
    console.warn('逆地理编码失败', e)
  } finally {
    geocodingLoading.value = false
  }
}

async function loadTodayRecord() {
  try {
    const res = await getTodayRecord()
    todayRecord.value = res.data || null
    if (todayRecord.value?.clockType) {
      clockType.value = todayRecord.value.clockType
    }
  } catch (e) {
    console.error('获取今日记录失败', e)
  }
}

async function loadTodayClockList() {
  try {
    const res = await getTodayClockList()
    clockList.value = res.data || []
  } catch (e) {
    console.error('获取打卡列表失败', e)
  }
}

async function loadUserRule() {
  try {
    ruleLoading.value = true
    const res = await getUserAttendanceRule()
    userRule.value = res.data || null
    if (userRule.value) {
      console.log('获取考勤规则成功:', userRule.value)
    }
  } catch (e) {
    console.error('获取考勤规则失败', e)
    userRule.value = null
  } finally {
    ruleLoading.value = false
  }
}

function takePhoto() {
  uni.chooseImage({
    count: 1,
    sourceType: ['camera'],
    success: async (res) => {
      const tempPath = res.tempFilePaths[0]
      photoUrl.value = tempPath
      try {
        const uploadRes = await uploadAttendancePhoto({ filePath: tempPath })
        photoUploadedUrl.value = uploadRes.url || ''
      } catch (e) {
        uni.showToast({ title: '照片上传失败', icon: 'none' })
      }
    }
  })
}

async function handleClock() {
  if (clockType.value === '0') {
    if (!location.value.latitude && !manualAddress.value) {
      uni.showToast({ title: '请先获取定位或手动输入地址', icon: 'none' })
      return
    }
  }

  if (clockType.value === '1') {
    if (!outsideReason.value.trim()) {
      uni.showToast({ title: '请填写外勤事由', icon: 'none' })
      return
    }
    if (!photoUploadedUrl.value) {
      uni.showToast({ title: '外勤打卡必须拍照', icon: 'none' })
      return
    }
  }

  const params = {
    clockType: clockType.value,
    outsideReason: outsideReason.value,
    latitude: location.value.latitude,
    longitude: location.value.longitude,
    address: shortAddress.value || location.value.address || manualAddress.value,
    photo: photoUploadedUrl.value
  }

  try {
    uni.showLoading({ title: '打卡中...', mask: true })

    const res = await clock(params)

    uni.hideLoading()

    uni.vibrateShort({
      type: 'medium',
      success: () => {
        console.log('震动反馈成功')
      },
      fail: () => {
        console.log('设备不支持震动')
      }
    })

    uni.showToast({
      title: '打卡成功',
      icon: 'success',
      duration: 1500,
      mask: true
    })

    photoUrl.value = ''
    photoUploadedUrl.value = ''
    outsideReason.value = ''
    manualAddress.value = ''
    showManualInput.value = false

    setTimeout(async () => {
      await loadTodayRecord()
      await loadTodayClockList()
    }, 500)
  } catch (e) {
    uni.hideLoading()
    console.error('打卡失败', e)

    uni.showToast({
      title: e.message || '打卡失败，请重试',
      icon: 'none',
      duration: 2000
    })

    uni.vibrateShort({
      type: 'heavy'
    })
  }
}

function goToRecord() {
  uni.navigateTo({ url: '/pages/attendance/record' })
}

function getClockTagText(index) {
  if (index === 0) return '上班'
  if (index === 1) return '下班'
  return '补卡'
}

function getClockTagClass(index) {
  if (index === 0) return 'type-in'
  if (index === 1) return 'type-out'
  return 'type-supplement'
}

function handleClockClick() {
  if (!canClock.value) {
    if (clockType.value === '0') {
      uni.showToast({
        title: '请先获取定位或手动输入地址',
        icon: 'none',
        duration: 2000
      })
    } else if (clockType.value === '1') {
      if (!outsideReason.value.trim()) {
        uni.showToast({ title: '请填写外勤事由', icon: 'none' })
      } else if (!photoUploadedUrl.value) {
        uni.showToast({ title: '外勤打卡必须拍照', icon: 'none' })
      }
    }
    return
  }

  handleClock()
}

onMounted(() => {
  updateDateTime()
  timer = setInterval(updateDateTime, 1000)

  setTimeout(() => {
    pageReady.value = true
  }, 100)

  loadUserRule()
  getLocation()
  loadTodayRecord()
  loadTodayClockList()
})

onUnmounted(() => {
  clearTimeout(locationTimer)
  if (timer) clearInterval(timer)
})
</script>

<style lang="scss" scoped>
page {
  background-color: #F5F7FA;
}

.attendance-container {
  min-height: 100vh;
  padding: 0 24rpx 40rpx;
  opacity: 0;
  transform: translateY(20rpx);
  transition: opacity 0.5s ease, transform 0.5s ease;

  &.page-ready {
    opacity: 1;
    transform: translateY(0);
  }
}

.header-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10rpx;
  padding: 56rpx 24rpx 40rpx;
  background: linear-gradient(180deg, #5B8FF9 0%, #3D6DF7 100%);
  margin: -24rpx -24rpx 28rpx;
  border-radius: 0 0 36rpx 36rpx;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400rpx;
    height: 400rpx;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
  }

  &::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -15%;
    width: 300rpx;
    height: 300rpx;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
    border-radius: 50%;
  }
}

.date-text {
  font-size: 30rpx;
  color: #fff;
  font-weight: 700;
  letter-spacing: 1rpx;
}

.greeting-text {
  font-size: 26rpx;
  color: rgba(255, 255, 255, 0.85);
}

.type-tabs {
  display: flex;
  background: #fff;
  border-radius: 20rpx;
  padding: 8rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 4rpx 20rpx rgba(102, 126, 234, 0.08);
}

.type-tab {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8rpx;
  padding: 22rpx 0;
  border-radius: 16rpx;
  transition: all 0.25s ease;
}

.tab-active {
  background: linear-gradient(180deg, #5B8FF9 0%, #3D6DF7 100%);
  box-shadow: 0 4rpx 12rpx rgba(61, 109, 247, 0.25);
}

.tab-text {
  font-size: 26rpx;
  color: #86909C;
  font-weight: 500;
}

.tab-text-active {
  color: #fff;
  font-weight: 600;
}

.location-card {
  background: rgba(255, 255, 255, 0.98);
  border-radius: 20rpx;
  padding: 28rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 4rpx 24rpx rgba(0, 0, 0, 0.06);
  border: 1rpx solid rgba(0, 0, 0, 0.04);
  overflow: hidden;
  transition: all 0.3s ease;

  &.loc-card-success {
    border-color: rgba(82, 196, 26, 0.15);
    box-shadow: 0 8rpx 32rpx rgba(82, 196, 26, 0.1);
  }

  &.loc-card-loading {
    border-color: rgba(61, 109, 247, 0.15);
    box-shadow: 0 8rpx 32rpx rgba(61, 109, 247, 0.08);
  }

  &.loc-card-error {
    border-color: rgba(245, 34, 45, 0.15);
    box-shadow: 0 8rpx 32rpx rgba(245, 34, 45, 0.08);
  }
}

.location-info {
  display: flex;
  align-items: center;
  gap: 14rpx;
}

.loading-dots {
  display: flex;
  gap: 8rpx;
  align-items: center;
}

.loading-dots .dot {
  width: 12rpx;
  height: 12rpx;
  border-radius: 50%;
  background: #3D6DF7;
  animation: dot-pulse 1.4s ease-in-out infinite;
}

.loading-dots .dot:nth-child(1) {
  animation-delay: 0s;
}

.loading-dots .dot:nth-child(2) {
  animation-delay: 0.2s;
}

.loading-dots .dot:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes dot-pulse {
  0%, 80%, 100% {
    transform: scale(0.6);
    opacity: 0.4;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}

.location-text {
  font-size: 26rpx;
  color: #1D2129;
  flex: 1;
}

.location-unknown {
  color: #86909C;
}

.location-success {
  position: relative;
  padding-left: 16rpx;
  border-left: 4rpx solid #52c41a;
}

.loc-indicator {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4rpx;
  height: 40rpx;
  border-radius: 2rpx;
  background: linear-gradient(180deg, #52c41a, #73d13d);
}

.loc-indicator-pending {
  background: linear-gradient(180deg, #3D6DF7, #5B8FF9);
  animation: pulse-bar 1.5s ease-in-out infinite;
}

.loc-main {
  display: flex;
  align-items: flex-start;
  gap: 12rpx;
  flex: 1;
}

.loc-detail {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6rpx;
}

.loc-address {
  font-size: 27rpx;
  color: #1D2129;
  font-weight: 500;
  line-height: 1.4;
}

.loc-pending-text {
  color: #3D6DF7 !important;
  animation: pulse-text 1.5s ease-in-out infinite;
}

@keyframes pulse-text {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.loc-poi {
  display: inline-flex;
  align-items: center;
  gap: 6rpx;
  font-size: 23rpx;
  color: #3D6DF7;
  background: rgba(61, 109, 247, 0.06);
  padding: 4rpx 12rpx;
  border-radius: 8rpx;
  width: fit-content;
  margin-top: 4rpx;
}

.loc-coord {
  display: block;
  font-size: 22rpx;
  color: #bfbfbf;
  margin-top: 6rpx;
  margin-left: 34rpx;
  font-family: 'Monaco', 'Menlo', monospace;
}

.loc-distance {
  display: inline-flex;
  align-items: center;
  gap: 4rpx;
  font-size: 22rpx;
  color: #52c41a;
  font-weight: 500;
}

.loc-coord-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 10rpx;
  margin-left: 34rpx;
  padding-top: 10rpx;
  border-top: 1rpx solid #F0F0F0;
}

.loc-coord-inline {
  display: block;
  font-size: 22rpx;
  color: #bfbfbf;
  margin-top: 4rpx;
  font-family: 'Monaco', 'Menlo', monospace;
}

.loc-refresh {
  width: 48rpx;
  height: 48rpx;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;

  &:active {
    background: rgba(61, 109, 247, 0.08);
    transform: rotate(180deg);
  }
}

.location-pending {
  position: relative;
  padding-left: 16rpx;
  border-left: 4rpx solid #faad14;
}

.location-fail {
  display: flex;
  flex-direction: column;
  gap: 18rpx;
}

.fail-hint {
  font-size: 24rpx;
  color: #86909C;
  padding-left: 4rpx;
}

.fail-actions {
  display: flex;
  gap: 16rpx;
}

.fail-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8rpx;
  padding: 18rpx 0;
  border-radius: 12rpx;
  font-size: 26rpx;
  font-weight: 500;
  transition: all 0.25s ease;

  &:active {
    transform: scale(0.96);
    opacity: 0.85;
  }
}

.fail-btn-primary {
  background: linear-gradient(180deg, #5B8FF9 0%, #3D6DF7 100%);
  color: #fff;
  box-shadow: 0 4rpx 16rpx rgba(61, 109, 247, 0.3);
}

.fail-btn-secondary {
  background: #F7F8FA;
  border: 1rpx solid #E5E6EB;
}

.manual-address {
  margin-top: 12rpx;
  animation: slide-down 0.3s ease-out;
}

@keyframes slide-down {
  from {
    opacity: 0;
    transform: translateY(-10rpx);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.manual-input {
  width: 100%;
  height: 72rpx;
  background: #F7F8FA;
  border-radius: 12rpx;
  padding: 0 20rpx;
  font-size: 25rpx;
  color: #1D2129;
  border: 2rpx solid #E5E6EB;
  transition: all 0.2s ease;

  &:focus {
    border-color: #3D6DF7;
    background: #fff;
    box-shadow: 0 0 0 4rpx rgba(61, 109, 247, 0.08);
  }
}

.manual-confirm {
  margin-top: 12rpx;
  padding: 16rpx 0;
  background: linear-gradient(135deg, #52c41a 0%, #73d13d 100%);
  border-radius: 10rpx;
  text-align: center;

  text {
    color: #fff;
    font-size: 26rpx;
    font-weight: 600;
  }

  &:active {
    opacity: 0.9;
    transform: scale(0.98);
  }
}

.outside-card {
  background: #fff;
  border-radius: 20rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.04);
}

.outside-label {
  font-size: 28rpx;
  font-weight: 600;
  color: #1D2129;
  margin-bottom: 16rpx;
}

.outside-input {
  width: 100%;
  height: 160rpx;
  background: #F7F8FA;
  border-radius: 14rpx;
  padding: 20rpx;
  font-size: 26rpx;
  color: #1D2129;
  line-height: 1.5;
}

.clock-section {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 32rpx 0 36rpx;
  position: relative;
}

.clock-ring {
  position: absolute;
  width: 360rpx;
  height: 360rpx;
  border-radius: 50%;
  border: 3rpx solid rgba(82, 196, 26, 0.15);
  animation: spin-slow 3s linear infinite;

  &.ring-out {
    width: 380rpx;
    height: 380rpx;
    border-color: rgba(61, 109, 247, 0.15);
  }
}

@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes pulse-bar {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

@keyframes pulse-btn {
  0%, 100% { transform: scale(1); box-shadow: 0 8rpx 32rpx rgba(82, 196, 26, 0.35); }
  50% { transform: scale(1.03); box-shadow: 0 12rpx 40rpx rgba(82, 196, 26, 0.45); }
}

@keyframes fade-up {
  from { opacity: 0; transform: translateY(16rpx); }
  to { opacity: 1; transform: translateY(0); }
}

.clock-btn {
  width: 340rpx;
  height: 340rpx;
  border-radius: 50%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14rpx;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 1;

  &::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    background: rgba(255, 255, 255, 0.3);
    transition: transform 0.6s ease-out;
  }

  &:active {
    transform: scale(0.95);

    &::before {
      transform: translate(-50%, -50%) scale(2);
      opacity: 0;
    }
  }
}

.btn-clock-in {
  background: linear-gradient(145deg, #43e97b 0%, #38f9d7 100%);
  box-shadow: 0 10rpx 32rpx rgba(56, 233, 122, 0.35);
  animation: pulse-btn 2s ease-in-out infinite;
}

.btn-clock-out {
  background: linear-gradient(145deg, #5B8FF9 0%, #3D6DF7 100%);
  box-shadow: 0 10rpx 32rpx rgba(61, 109, 247, 0.35);
  animation: pulse-btn 2s ease-in-out infinite;
}

.btn-disabled {
  background: linear-gradient(145deg, #d9d9d9 0%, #bfbfbf 100%);
  box-shadow: 0 10rpx 32rpx rgba(0, 0, 0, 0.08);
  animation: none;
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-disabled .clock-btn-text {
  color: #8c8c8c;
}

.btn-disabled .clock-btn-time {
  color: #8c8c8c;
}

.btn-disabled:active {
  transform: none;
}

.btn-done {
  background: linear-gradient(145deg, #dfe4ea 0%, #eef2f6 100%);
  box-shadow: none;
  animation: none;
}

.clock-btn-text {
  font-size: 34rpx;
  font-weight: 700;
  color: #fff;
  letter-spacing: 2rpx;
}

.btn-done .clock-btn-text {
  color: #86909C;
  letter-spacing: 0;
}

.clock-btn-time {
  font-size: 24rpx;
  color: rgba(255, 255, 255, 0.88);
  font-weight: 400;
}

.btn-done .clock-btn-time {
  color: #86909C;
}

.today-card {
  background: #fff;
  border-radius: 20rpx;
  padding: 28rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.04);
  animation: fade-up 0.4s ease-out;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24rpx;
}

.card-title {
  font-size: 29rpx;
  font-weight: 700;
  color: #1D2129;
}

.status-tag {
  padding: 6rpx 18rpx;
  border-radius: 20rpx;
}

.status-text {
  font-size: 22rpx;
  font-weight: 600;
}

.tag-normal {
  background: #f6ffed;
  .status-text { color: #52c41a; }
}

.tag-late {
  background: #fff7e6;
  .status-text { color: #fa8c16; }
}

.tag-early {
  background: #fff7e6;
  .status-text { color: #fa8c16; }
}

.tag-danger {
  background: #fff1f0;
  .status-text { color: #cf1322; }
}

.timeline {
  padding: 4rpx 0;
}

.timeline-item {
  display: flex;
  gap: 20rpx;
  padding: 12rpx 0;
  position: relative;
}

.timeline-first {
  padding-top: 4rpx;
}

.timeline-last {
  padding-bottom: 4rpx;
}

.timeline-dot {
  width: 36rpx;
  height: 36rpx;
  border-radius: 50%;
  background: #E5E6EB;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2rpx;
}

.dot-done {
  background: linear-gradient(135deg, #52c41a, #73d13d);
}

.dot-late {
  background: linear-gradient(135deg, #faad14, #fa8c16);
}

.dot-early {
  background: linear-gradient(135deg, #faad14, #fa8c16);
}

.timeline-line {
  width: 2rpx;
  height: 32rpx;
  background: linear-gradient(180deg, #E5E6EB, transparent);
  margin-left: 17rpx;
}

.timeline-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6rpx;
}

.timeline-time {
  font-size: 28rpx;
  font-weight: 700;
  color: #1D2129;
}

.timeline-label {
  font-size: 23rpx;
  color: #86909C;
}

.timeline-status {
  display: inline-block;
  font-size: 21rpx;
  color: #52c41a;
  padding: 2rpx 10rpx;
  background: #f6ffed;
  border-radius: 8rpx;
  width: fit-content;
}

.clock-count-info {
  padding: 16rpx 0;
  border-bottom: 1rpx solid #F0F0F0;
  margin-bottom: 16rpx;
  
  text {
    font-size: 26rpx;
    color: #86909C;
  }
}

.clock-list {
  display: flex;
  flex-direction: column;
  gap: 12rpx;
}

.clock-item {
  display: flex;
  flex-direction: column;
  gap: 6rpx;
  padding: 14rpx 16rpx;
  background: #F7F8FA;
  border-radius: 10rpx;
}

.clock-item-left {
  display: flex;
  align-items: center;
  gap: 12rpx;
}

.clock-time {
  font-size: 28rpx;
  font-weight: 600;
  color: #1D2129;
}

.clock-type-tag {
  padding: 2rpx 10rpx;
  border-radius: 6rpx;
  font-size: 22rpx;
  font-weight: 500;
}

.type-in {
  background: #e6f7ff;
  color: #1890ff;
}

.type-out {
  background: #f6ffed;
  color: #52c41a;
}

.type-supplement {
  background: #fff7e6;
  color: #fa8c16;
}

.clock-address {
  font-size: 24rpx;
  color: #86909C;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.status-late {
  color: #fa8c16;
  background: #fff7e6;
}

.status-early {
  color: #fa8c16;
  background: #fff7e6;
}

.photo-section {
  background: #fff;
  border-radius: 20rpx;
  padding: 24rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.04);
  animation: fade-up 0.5s ease-out;
}

.photo-label {
  font-size: 28rpx;
  font-weight: 600;
  color: #1D2129;
  margin-bottom: 20rpx;
}

.photo-area {
  width: 240rpx;
  height: 240rpx;
  border-radius: 16rpx;
  background: #FAFBFC;
  border: 2rpx dashed #DCDEE0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12rpx;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: -2rpx;
    left: -2rpx;
    right: -2rpx;
    bottom: -2rpx;
    border-radius: 16rpx;
    background: linear-gradient(90deg, transparent, rgba(61, 109, 247, 0.3), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  &:active {
    border-color: #3D6DF7;
    background: #F0F5FF;
    transform: scale(0.97);

    &::before {
      opacity: 1;
      animation: border-flow 1.5s linear infinite;
    }
  }
}

@keyframes border-flow {
  from { transform: translateX(-100%); }
  to { transform: translateX(100%); }
}

.photo-hint {
  font-size: 24rpx;
  color: #c0c4cc;
}

.photo-preview {
  position: relative;
  width: 240rpx;
  height: 240rpx;
  border-radius: 16rpx;
  overflow: hidden;
}

.photo-img {
  width: 100%;
  height: 100%;
}

.photo-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 60rpx;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.45));
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8rpx;
}

.overlay-text {
  font-size: 22rpx;
  color: #fff;
  font-weight: 500;
}

.footer-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8rpx;
  padding: 32rpx 0;
  position: relative;

  &::before {
    content: '';
    position: absolute;
    top: 12rpx;
    left: 50%;
    transform: translateX(-50%);
    width: 120rpx;
    height: 1rpx;
    background: linear-gradient(90deg, transparent, #E5E6EB, transparent);
  }
}

.footer-text {
  font-size: 26rpx;
  color: #86909C;
  transition: color 0.2s ease;
}

.footer-link:active .footer-text {
  color: #3D6DF7;
}

.footer-link:active {
  transform: translateX(4rpx);
}
</style>
