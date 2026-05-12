# 添加行程 - 状态选项重复显示修复计划

## 问题现象
添加行程对话框中的"状态"选项（已预约、服务中、已完成、已取消）被重复显示了多次，形成网格状布局。

## 问题原因

查看 [index.vue](file:///d:/fuchenpro/front/src/views/business/schedule/index.vue#L311-L315) 的状态部分代码：

```vue
<el-form-item label="状态" prop="status">
  <el-radio-group v-model="form.status">
    <el-radio-button v-for="dict in biz_schedule_status" :key="dict.value" :value="dict.value">
      {{ dict.label }}
    </el-radio-button>
  </el-radio-group>
</el-form-item>
```

**根本原因**：`biz_schedule_status` 字典数据源包含重复数据。
- 正常应该只有4个选项：已预约(1)、服务中(2)、已完成(3)、已取消(4)
- 但实际可能返回了20+条重复记录

## 解决方案

### 方案：对字典数据进行去重处理

在 `useDict` 返回的字典数据基础上，创建一个计算属性进行去重：

```javascript
// 添加计算属性 - 去重后的状态列表
const uniqueScheduleStatus = computed(() => {
  if (!biz_schedule_status.value) return []
  const seen = new Map()
  return biz_schedule_status.value.filter(dict => {
    if (seen.has(dict.value)) return false
    seen.set(dict.value, true)
    return true
  })
})
```

然后模板中使用去重后的数据：

```vue
<el-radio-button v-for="dict in uniqueScheduleStatus" :key="dict.value" :value="dict.value">
  {{ dict.label }}
</el-radio-button>
```

同样的问题可能也存在于 **下店目的** (`biz_schedule_purpose`)，建议一并检查和修复。

---

## 修改文件
| 文件 | 修改内容 |
|------|----------|
| `front/src/views/business/schedule/index.vue` | 添加去重计算属性，修改状态和目的的v-for数据源 |

## 修改位置
1. **第382行附近** - 添加 `uniqueScheduleStatus` 和 `uniqueSchedulePurpose` 计算属性
2. **第303-307行** - 下店目的改用 `uniqueSchedulePurpose`
3. **第312-314行** - 状态改用 `uniqueScheduleStatus`
