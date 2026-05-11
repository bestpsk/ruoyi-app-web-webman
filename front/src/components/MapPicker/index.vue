<template>
  <el-dialog title="选择考勤地点" v-model="visible" width="700px" append-to-body @open="initMap" @close="destroyMap">
    <div class="map-picker">
      <div class="map-search">
        <el-input
          v-model="searchKeyword"
          placeholder="搜索地址"
          clearable
          @keyup.enter="handleSearch"
        >
          <template #append>
            <el-button icon="Search" @click="handleSearch" />
          </template>
        </el-input>
      </div>
      <div class="map-container" ref="mapContainer"></div>
      <div class="map-info" v-if="selectedPoint.address">
        <el-icon><Location /></el-icon>
        <span>{{ selectedPoint.address }}</span>
      </div>
    </div>
    <template #footer>
      <div class="dialog-footer">
        <el-button @click="visible = false">取 消</el-button>
        <el-button type="primary" @click="handleConfirm">确 定</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive } from 'vue'
import AMapLoader from '@amap/amap-jsapi-loader'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  latitude: { type: [Number, String], default: null },
  longitude: { type: [Number, String], default: null }
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const visible = ref(false)
const searchKeyword = ref('')
const mapContainer = ref(null)
const selectedPoint = reactive({ latitude: null, longitude: null, address: '' })

let map = null
let marker = null
let placeSearch = null
let geocoder = null

const AMAP_KEY = import.meta.env.VITE_AMAP_KEY
const AMAP_SECURITY_CODE = import.meta.env.VITE_AMAP_SECURITY_CODE

window._AMapSecurityConfig = {
  securityJsCode: AMAP_SECURITY_CODE
}

function initMap() {
  AMapLoader.load({
    key: AMAP_KEY,
    version: '2.0',
    plugins: ['AMap.PlaceSearch', 'AMap.Geocoder', 'AMap.AutoComplete'],
    securityJsCode: AMAP_SECURITY_CODE
  }).then((AMap) => {
    const center = (props.latitude && props.longitude)
      ? [parseFloat(props.longitude), parseFloat(props.latitude)]
      : [116.397428, 39.90923]

    map = new AMap.Map(mapContainer.value, {
      zoom: 15,
      center: center
    })

    if (props.latitude && props.longitude) {
      addMarker(center[0], center[1])
    }

    geocoder = new AMap.Geocoder()
    placeSearch = new AMap.PlaceSearch({ 
      pageSize: 10, 
      pageIndex: 1,
      city: '全国',
      citylimit: false
    })

    map.on('click', (e) => {
      const lng = e.lnglat.getLng()
      const lat = e.lnglat.getLat()
      addMarker(lng, lat)
      reverseGeocode(lat, lng)
    })
  }).catch((e) => {
    console.error('地图加载失败', e)
  })
}

function addMarker(lng, lat) {
  if (marker) {
    marker.setPosition([lng, lat])
  } else if (map) {
    marker = new AMap.Marker({
      position: [lng, lat],
      draggable: true
    })
    marker.on('dragend', (e) => {
      const pos = marker.getPosition()
      reverseGeocode(pos.lat, pos.lng)
    })
    map.add(marker)
  }
  selectedPoint.latitude = lat
  selectedPoint.longitude = lng
  map?.setCenter([lng, lat])
}

function reverseGeocode(lat, lng) {
  if (!geocoder) return
  geocoder.getAddress([lng, lat], (status, result) => {
    if (status === 'complete' && result.info === 'OK') {
      selectedPoint.address = result.regeocode.formattedAddress
      selectedPoint.latitude = lat
      selectedPoint.longitude = lng
    }
  })
}

function handleSearch() {
  if (!searchKeyword.value.trim()) {
    return
  }
  if (!placeSearch) {
    console.error('placeSearch未初始化')
    return
  }
  placeSearch.search(searchKeyword.value, (status, result) => {
    if (status === 'complete' && result.poiList && result.poiList.pois && result.poiList.pois.length) {
      const poi = result.poiList.pois[0]
      const lng = poi.location.lng
      const lat = poi.location.lat
      addMarker(lng, lat)
      reverseGeocode(lat, lng)
    } else if (status === 'no_data') {
      console.warn('搜索无结果')
    } else {
      console.error('搜索失败', status, result)
    }
  })
}

function handleConfirm() {
  emit('confirm', {
    latitude: selectedPoint.latitude,
    longitude: selectedPoint.longitude,
    address: selectedPoint.address
  })
  visible.value = false
}

function destroyMap() {
  if (map) {
    map.destroy()
    map = null
    marker = null
  }
}

function open() {
  visible.value = true
}

defineExpose({ open })
</script>

<style scoped>
.map-picker {
  width: 100%;
}

.map-search {
  margin-bottom: 12px;
}

.map-container {
  width: 100%;
  height: 400px;
  border-radius: 8px;
  overflow: hidden;
}

.map-info {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 12px;
  padding: 8px 12px;
  background: #f5f7fa;
  border-radius: 6px;
  font-size: 13px;
  color: #606266;
}
</style>
