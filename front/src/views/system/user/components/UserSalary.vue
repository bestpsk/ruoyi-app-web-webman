<template>
  <div>
    <el-row :gutter="10" class="mb8">
      <el-col :span="1.5">
        <el-button type="primary" plain icon="Plus" @click="handleAdd">新增</el-button>
      </el-col>
    </el-row>

    <el-table :data="salaryList" border>
      <el-table-column label="薪资类型" min-width="120">
        <template #default="scope">
          {{ scope.row.salaryType?.typeName || '-' }}
        </template>
      </el-table-column>
      <el-table-column label="基础金额" prop="baseAmount" min-width="100">
        <template #default="scope">
          {{ formatMoney(scope.row.baseAmount) }}
        </template>
      </el-table-column>
      <el-table-column label="提成比例" min-width="100">
        <template #default="scope">
          {{ scope.row.commissionRate ? (scope.row.commissionRate * 100).toFixed(2) + '%' : '-' }}
        </template>
      </el-table-column>
      <el-table-column label="生效日期" min-width="120">
        <template #default="scope">
          {{ formatDate(scope.row.effectiveDate) }}
        </template>
      </el-table-column>
      <el-table-column label="失效日期" min-width="120">
        <template #default="scope">
          {{ formatDate(scope.row.expireDate) }}
        </template>
      </el-table-column>
      <el-table-column label="状态" prop="status" min-width="80">
        <template #default="scope">
          <el-tag :type="scope.row.status === '0' ? 'success' : 'danger'">
            {{ scope.row.status === '0' ? '正常' : '停用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" min-width="150">
        <template #default="scope">
          <el-button link type="primary" icon="Edit" @click="handleEdit(scope.row)">修改</el-button>
          <el-button link type="danger" icon="Delete" @click="handleDelete(scope.row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog :title="title" v-model="open" width="750px" append-to-body>
      <el-form ref="salaryRef" :model="form" :rules="rules" label-width="110px">
        <el-row>
          <el-col :span="24">
            <el-form-item label="薪资类型" prop="typeId">
              <el-select v-model="form.typeId" placeholder="请选择薪资类型" style="width: 100%" @change="handleTypeChange">
                <el-option
                  v-for="item in salaryTypes"
                  :key="item.typeId"
                  :label="item.typeName"
                  :value="item.typeId"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item prop="baseAmount">
              <template #label>
                <span>基础金额</span>
                <el-tooltip v-if="currentTypeCode && currentTypeCode !== 'BASE_SALARY'" :content="baseAmountTip" placement="top">
                  <el-icon class="tooltip-icon"><QuestionFilled /></el-icon>
                </el-tooltip>
              </template>
              <el-input-number v-model="form.baseAmount" :min="0" :precision="2" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12" v-if="showCommissionRate">
            <el-form-item label="提成比例" prop="commissionRate" label-width="80px">
              <el-input
                v-model="commissionRateDisplay"
                placeholder="请输入提成比例"
                @input="handleCommissionRateInput"
              >
                <template #suffix>%</template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row v-if="showTierConfig">
          <el-col :span="24">
            <el-form-item label="阶梯配置">
              <div class="tier-config">
                <div v-for="(tier, index) in form.tiers" :key="index" class="tier-card">
                  <div class="tier-header">第 {{ index + 1 }} 级阶梯</div>
                  <div class="tier-body">
                    <div class="tier-field">
                      <label>起始金额（≥）</label>
                      <el-input-number v-model="tier.minAmount" :min="0" :precision="2" controls-position="right" style="width: 100%" />
                    </div>
                    <div class="tier-field">
                      <label>截止金额（&lt;）</label>
                      <el-input-number v-model="tier.maxAmount" :min="0" :precision="2" controls-position="right" style="width: 100%" placeholder="留空=无上限" />
                    </div>
                    <div class="tier-field">
                      <label>提成比例</label>
                      <el-input
                        v-model="tier.rateDisplay"
                        placeholder="比例"
                        @input="(val) => handleTierRateInput(index, val)"
                      >
                        <template #suffix>%</template>
                      </el-input>
                    </div>
                    <div class="tier-delete">
                      <el-button type="danger" icon="Delete" circle size="small" @click="removeTier(index)" />
                    </div>
                  </div>
                </div>
                <el-button type="primary" plain icon="Plus" @click="addTier" style="margin-top: 10px; width: 100%;">添加阶梯</el-button>
              </div>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="生效日期" prop="effectiveDate">
              <el-date-picker
                v-model="form.effectiveDate"
                type="date"
                placeholder="选择生效日期"
                value-format="YYYY-MM-DD"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="失效日期" prop="expireDate">
              <el-date-picker
                v-model="form.expireDate"
                type="date"
                placeholder="选择失效日期"
                value-format="YYYY-MM-DD"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="状态" prop="status">
              <el-select v-model="form.status" placeholder="请选择状态" style="width: 100%">
                <el-option label="正常" value="0" />
                <el-option label="停用" value="1" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="form.remark" type="textarea" placeholder="请输入备注" maxlength="500" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button type="primary" @click="submitForm">确 定</el-button>
          <el-button @click="open = false">取 消</el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { listSalaryType, listUserSalary, addSalary, updateSalary, delSalary } from "@/api/system/user"
import { QuestionFilled } from '@element-plus/icons-vue'

const props = defineProps({
  userId: {
    type: [Number, String],
    default: null
  }
})

const { proxy } = getCurrentInstance()
const salaryList = ref([])
const salaryTypes = ref([])
const open = ref(false)
const title = ref("")
const form = ref({})
const commissionRateDisplay = ref("")
const rules = ref({
  typeId: [{ required: true, message: "请选择薪资类型", trigger: "change" }],
  baseAmount: [{ required: true, message: "请输入基础金额", trigger: "blur" }]
})

const tieredTypeCodes = ['TIERED_SALES', 'TIERED_PAYMENT']
const commissionTypeCodes = ['SALES_COMMISSION', 'PAYMENT_COMMISSION', 'PROFIT_COMMISSION', 'TIERED_SALES', 'TIERED_PAYMENT']

const showCommissionRate = computed(() => {
  const type = salaryTypes.value.find(t => t.typeId === form.value.typeId)
  return type && commissionTypeCodes.includes(type.typeCode)
})

const showTierConfig = computed(() => {
  const type = salaryTypes.value.find(t => t.typeId === form.value.typeId)
  return type && tieredTypeCodes.includes(type.typeCode)
})

const currentTypeCode = computed(() => {
  const type = salaryTypes.value.find(t => t.typeId === form.value.typeId)
  return type?.typeCode || null
})

const baseAmountTip = computed(() => {
  const tips = {
    'SALES_COMMISSION': '销售业绩提成：填保底工资（无业绩时发放），如无保底填0',
    'PAYMENT_COMMISSION': '回款业绩提成：填保底工资（无回款时发放），如无保底填0',
    'PROFIT_COMMISSION': '利润提成：填保底工资（无利润时发放），如无保底填0',
    'TIERED_SALES': '阶梯销售提成：填保底工资，未达第一级时发放',
    'TIERED_PAYMENT': '阶梯回款提成：填保底工资，未达第一级时发放'
  }
  return tips[currentTypeCode.value] || ''
})

const formatMoney = (value) => {
  if (value === null || value === undefined) return '-'
  return '¥' + parseFloat(value).toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (value) => {
  if (!value) return '-'
  if (typeof value === 'string' && value.length > 10) {
    return value.substring(0, 10)
  }
  return value
}

const reset = () => {
  form.value = {
    salaryId: null,
    userId: props.userId,
    typeId: null,
    baseAmount: 0,
    commissionRate: 0,
    tiers: [],
    effectiveDate: null,
    expireDate: null,
    status: '0',
    remark: ''
  }
  commissionRateDisplay.value = ""
}

const getSalaryTypes = async () => {
  const res = await listSalaryType()
  salaryTypes.value = res.data || []
}

const getSalaryList = async () => {
  if (!props.userId) return
  const res = await listUserSalary(props.userId)
  salaryList.value = res.data || []
}

const handleTypeChange = () => {
  form.value.commissionRate = 0
  form.value.tiers = []
  commissionRateDisplay.value = ""
}

const handleCommissionRateInput = (val) => {
  const numVal = parseFloat(val) || 0
  form.value.commissionRate = numVal / 100
}

const handleTierRateInput = (index, val) => {
  const numVal = parseFloat(val) || 0
  form.value.tiers[index].commissionRate = numVal / 100
}

const addTier = () => {
  form.value.tiers.push({
    minAmount: 0,
    maxAmount: null,
    commissionRate: 0,
    rateDisplay: ""
  })
}

const removeTier = (index) => {
  form.value.tiers.splice(index, 1)
}

const handleAdd = () => {
  reset()
  open.value = true
  title.value = "新增薪资配置"
}

const handleEdit = (row) => {
  reset()
  form.value = {
    salaryId: row.salaryId,
    userId: row.userId,
    typeId: row.typeId,
    baseAmount: row.baseAmount,
    commissionRate: row.commissionRate || 0,
    tiers: row.tiers ? row.tiers.map(t => ({
      minAmount: t.minAmount,
      maxAmount: t.maxAmount,
      commissionRate: t.commissionRate || 0,
      rateDisplay: t.commissionRate ? (t.commissionRate * 100).toString() : ""
    })) : [],
    effectiveDate: formatDate(row.effectiveDate),
    expireDate: formatDate(row.expireDate),
    status: row.status,
    remark: row.remark
  }
  commissionRateDisplay.value = row.commissionRate ? (row.commissionRate * 100).toString() : ""
  open.value = true
  title.value = "修改薪资配置"
}

const handleDelete = async (row) => {
  await proxy.$modal.confirm('是否确认删除该薪资配置？')
  await delSalary(row.salaryId)
  proxy.$modal.msgSuccess("删除成功")
  getSalaryList()
}

const submitForm = async () => {
  await proxy.$refs.salaryRef.validate()
  const data = {
    ...form.value,
    userId: props.userId
  }
  delete data.tiers
  if (form.value.tiers && form.value.tiers.length > 0) {
    data.tiers = form.value.tiers.map(tier => ({
      minAmount: tier.minAmount,
      maxAmount: tier.maxAmount,
      commissionRate: tier.commissionRate
    }))
  }
  if (form.value.salaryId) {
    await updateSalary(data)
    proxy.$modal.msgSuccess("修改成功")
  } else {
    await addSalary(data)
    proxy.$modal.msgSuccess("新增成功")
  }
  open.value = false
  getSalaryList()
}

watch(() => props.userId, (val) => {
  if (val) {
    getSalaryList()
  }
}, { immediate: true })

onMounted(() => {
  getSalaryTypes()
})
</script>

<style scoped>
.tier-config {
  width: 100%;
}
.tier-card {
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  margin-bottom: 10px;
  background-color: #fafafa;
}
.tier-header {
  padding: 8px 15px;
  background-color: #f0f2f5;
  font-weight: bold;
  font-size: 14px;
  color: #303133;
  border-bottom: 1px solid #e4e7ed;
  border-radius: 6px 6px 0 0;
}
.tier-body {
  padding: 15px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
}
.tier-field {
  flex: 1;
  min-width: 0;
}
.tier-field label {
  display: block;
  font-size: 12px;
  color: #909399;
  margin-bottom: 5px;
}
.tier-delete {
  padding-top: 22px;
}
.tooltip-icon {
  vertical-align: middle;
  margin-left: 4px;
  color: #909399;
  cursor: help;
  font-size: 14px;
}
.tooltip-icon:hover {
  color: #409eff;
}
</style>
