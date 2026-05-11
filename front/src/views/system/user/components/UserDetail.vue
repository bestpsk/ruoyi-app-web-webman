<template>
  <el-form ref="detailRef" :model="form" :rules="rules" label-width="100px">
    <el-row>
      <el-col :span="12">
        <el-form-item label="微信号" prop="wechat">
          <el-input v-model="form.wechat" placeholder="请输入微信号" maxlength="50" />
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item label="生日" prop="birthday">
          <el-date-picker
            v-model="form.birthday"
            type="date"
            placeholder="选择生日"
            value-format="YYYY-MM-DD"
            style="width: 100%"
          />
        </el-form-item>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="12">
        <el-form-item label="身份证号" prop="idCard">
          <el-input v-model="form.idCard" placeholder="请输入身份证号" maxlength="18" />
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item label="入职日期" prop="hireDate">
          <el-date-picker
            v-model="form.hireDate"
            type="date"
            placeholder="选择入职日期"
            value-format="YYYY-MM-DD"
            style="width: 100%"
          />
        </el-form-item>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="12">
        <el-form-item label="在职状态" prop="employmentStatus">
          <el-select v-model="form.employmentStatus" placeholder="请选择在职状态" style="width: 100%">
            <el-option label="在职" value="0" />
            <el-option label="离职" value="1" />
          </el-select>
        </el-form-item>
      </el-col>
      <el-col :span="12">
        <el-form-item label="离职日期" prop="resignDate" v-if="form.employmentStatus === '1'">
          <el-date-picker
            v-model="form.resignDate"
            type="date"
            placeholder="选择离职日期"
            value-format="YYYY-MM-DD"
            style="width: 100%"
          />
        </el-form-item>
      </el-col>
    </el-row>
    <el-row>
      <el-col :span="24">
        <el-form-item label="住址" prop="address">
          <el-input v-model="form.address" placeholder="请输入住址" maxlength="200" />
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
</template>

<script setup>
import { getUserDetail, addUserDetail, updateUserDetail } from "@/api/system/user"

const props = defineProps({
  userId: {
    type: [Number, String],
    default: null
  }
})

const { proxy } = getCurrentInstance()
const form = ref({})
const rules = ref({
  idCard: [
    { pattern: /^[1-9]\d{5}(18|19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{3}[\dXx]$/, message: "请输入正确的身份证号", trigger: "blur" }
  ]
})

const reset = () => {
  form.value = {
    detailId: null,
    userId: props.userId,
    wechat: '',
    birthday: null,
    idCard: '',
    address: '',
    hireDate: null,
    employmentStatus: '0',
    resignDate: null,
    remark: ''
  }
}

const getDetail = async () => {
  if (!props.userId) return
  try {
    const res = await getUserDetail(props.userId)
    if (res.data) {
      form.value = {
        detailId: res.data.detailId,
        userId: res.data.userId,
        wechat: res.data.wechat || '',
        birthday: res.data.birthday || null,
        idCard: res.data.idCard || '',
        address: res.data.address || '',
        hireDate: res.data.hireDate || null,
        employmentStatus: res.data.employmentStatus || '0',
        resignDate: res.data.resignDate || null,
        remark: res.data.remark || ''
      }
    } else {
      reset()
    }
  } catch (e) {
    reset()
  }
}

const submit = async () => {
  await proxy.$refs.detailRef.validate()
  const data = {
    ...form.value,
    userId: props.userId
  }
  if (form.value.detailId) {
    await updateUserDetail(data)
    proxy.$modal.msgSuccess("修改成功")
  } else {
    await addUserDetail(data)
    proxy.$modal.msgSuccess("新增成功")
  }
  getDetail()
}

watch(() => props.userId, (val) => {
  if (val) {
    getDetail()
  }
}, { immediate: true })

defineExpose({
  submit
})
</script>
