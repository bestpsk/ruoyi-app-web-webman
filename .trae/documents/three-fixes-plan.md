# 打卡功能三个问题修复计划

## 问题汇总

| # | 问题 | 原因 | 优先级 |
|---|------|------|--------|
| 1 | QQ/Trae浏览器不能定位 | 浏览器Geolocation API兼容性差异 | 中 |
| 2 | 只显示坐标无地名 | 缺少逆地理编码（坐标→地址） | 高 |
| 3 | 打卡失败500：`Unknown column 'clock_type'` | 数据库表缺少新字段，未执行ALTER TABLE | 高 |

---

## 问题1：部分浏览器无法定位

**现象**：Edge能定位，QQ浏览器和Trae内置浏览器不能定位

**原因**：
- QQ浏览器和Trae内置浏览器的Geolocation API实现可能不同
- 部分浏览器在非HTTPS环境下会静默拒绝定位
- 降级方案（`navigator.geolocation`）在这些浏览器中也可能不触发回调

**方案**：
- 已有8秒超时+手动输入地址兜底，基本可用
- 进一步优化：超时后立即停止loading并显示手动输入框（当前代码可能还有条件编译问题导致降级未执行）

## 问题2：只显示坐标无地名（需新增）

**现象**：定位成功后只显示 `31.044241, 121.466440` 这样的坐标，没有可读的地址

**原因**：
- `uni.getLocation` 的 `res.address` 在H5端依赖高德SDK逆地理编码
- 降级到 `navigator.geolocation` 后只有坐标，没有地址信息
- 需要主动调用逆地理编码API将坐标转为地址文字

**方案**：在后端或前端增加逆地理编码调用
- **推荐方案（前端）**：打卡成功后，前端用高德Web服务API做逆地理编码
- **备选方案（后端）**：后端接收坐标后调用高德API获取地址存入数据库

选择**前端方案**：使用高德REST API `https://restapi.amap.com/v3/geocode/regeo`，用Key直接请求，无需加载SDK。

## 问题3：打卡500错误 `Unknown column 'clock_type'`

**现象**：点击打卡按钮报错 `PDOException: Column not found: 1054 Unknown column 'clock_type' in 'field list'`

**原因**：
- 之前修改了SQL脚本（建表语句含新字段），但**实际数据库没有执行过ALTER TABLE**
- `biz_attendance_record` 表中不存在 `clock_type` 和 `outside_reason` 两列

**方案**：
- 需要在MySQL中执行两条ALTER TABLE语句添加缺失字段
- 或提供一键执行的SQL片段

---

## 实施步骤

### 步骤1：修复 attendance/index.vue — 移除条件编译 + 添加逆地理编码
- 重写 `getLocation()` / `doGetLocation()` / `fallbackGetLocation()`，移除所有 `#ifdef` 条件编译
- 新增 `reverseGeocode(lat, lng)` 函数，使用高德REST API将坐标转为地名
- 定位成功后自动调用逆地理编码，更新 `location.value.address`
- 确保所有路径都最终重置 `locationLoading = false`

### 步骤2：执行数据库ALTER TABLE
- 执行SQL添加 `clock_type` 和 `outside_reason` 字段到已有表

---

## 文件变更清单

| 文件 | 修改内容 |
|------|----------|
| `AppV3/src/pages/attendance/index.vue` | 移除条件编译 + 新增逆地理编码函数 |
| 数据库 | 执行ALTER TABLE添加字段 |

## 逆地理编码实现细节

```javascript
async function reverseGeocode(lat, lng) {
  const key = 'fa588d6bc9fbc9dce1f0c379e40f9faa'
  const url = `https://restapi.amap.com/v3/georegeo?location=${lng},${lat}&key=${key}&extensions=base&output=JSON`
  try {
    const res = await new Promise((resolve, reject) => {
      uni.request({ url, method: 'GET', success: resolve, fail: reject })
    })
    if (res.data?.regeocode?.formatted_address) {
      location.value.address = res.data.regeocode.formatted_address
    }
  } catch(e) {
    console.warn('逆地理编码失败', e)
  }
}
```

在 `doGetLocation` success 回调和 `fallbackGetLocation` success 回调中都调用此函数。
