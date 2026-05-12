# AppV3 满意度显示 + 字段顺序调整计划（v2）

## 问题分析

### 满意度不显示 - 根因
根据 uView Plus 官方示例：
```html
<up-rate v-model="score" />
<script>
export default {
  data() { return { score: 3.5 } }  // ✅ 必须是数字类型
}
</script>
```

**当前代码的问题**：
```javascript
// ❌ 使用 computed get/set 模式，v-model + readonly 组合下可能无法正确触发更新
const satisfactionValue = computed({
  get: () => Number(orderInfo.value.satisfaction) || 0,
  set: () => {}
})
```

**修复方案**：改用 **ref** 存储满意度值，在 `loadDetail()` 中**显式赋值**

---

## 修复方案

### 步骤1：修改 satisfactionValue 为 ref

**文件**: [detail.vue:169-173](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L169-L173)

```javascript
// ❌ 删除 computed 方式
const satisfactionValue = computed({
  get: () => Number(orderInfo.value.satisfaction) || 0,
  set: () => {}
})

// ✅ 改为 ref
const satisfactionValue = ref(0)
```

### 步骤2：在 loadDetail() 中显式赋值

**文件**: [detail.vue loadDetail 函数](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue) 的操作模式分支末尾

```javascript
// 在 orderInfo.value = {...} 赋值之后添加：
satisfactionValue.value = Number(record.satisfaction) || 0
```

### 步骤3：调整字段顺序（客户→门店→操作人→满意度）

**文件**: [detail.vue:11-22](file:///d:/fuchenpro/AppV3/src/pages/business/order/detail.vue#L11-L22)

重新排列 info-body 内的 view 元素顺序：

```
原顺序：                          新顺序：
1. 操作人 (v-if operation)    →  1. 客户
2. 满意度 (v-if operation)    →  2. 门店  
3. 客户                       →  3. 操作人 (v-if operation)
4. 门店                       →  4. 满意度 (v-if operation)
5. 金额                       →  5. 金额
6. 时间                       →  6. 时间
7. 照片                       →  7. 照片
8. 反馈                       →  8. 反馈
```

---

## 实施步骤

| 序号 | 任务 | 文件 | 说明 |
|------|------|------|------|
| 1 | satisfactionValue 改为 ref(0) | detail.vue script | 替换 computed |
| 2 | loadDetail 中赋值 satisfactionValue | detail.vue script | 操作模式分支 |
| 3 | 调整模板字段顺序 | detail.vue template | 客户→门店→操作人→满意度 |

## 预期效果

```
基础信息区域：

客户     客户1
门店     逆龄奢·宜川店
操作人   admin
满意度   ⭐⭐⭐⭐⭐          ← u-rate 正常渲染
金额     ¥796
时间     2026-05-10
照片     [图片]
反馈     惹我热污染
```

## 风险评估
- **风险等级**：极低 🟢
- **回滚方案**：恢复原始代码即可
