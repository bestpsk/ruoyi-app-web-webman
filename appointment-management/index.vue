<template>
  <div class="appointment-container">
    <el-card>
      <el-tabs v-model="activeTab">
        <el-tab-pane label="员工预约" name="employee">
          <EmployeeAppointment
            ref="employeeAppointmentRef"
            @add-appointment="handleAddAppointment"
            @edit-appointment="handleEditAppointment"
          />
        </el-tab-pane>
        <el-tab-pane label="房间预约" name="room">
          <RoomAppointment
            ref="roomAppointmentRef"
            @add-appointment="handleAddAppointment"
            @edit-appointment="handleEditAppointment"
          />
        </el-tab-pane>
        <el-tab-pane label="预约设置" name="settings">
          <AppointmentSettings />
        </el-tab-pane>
        <el-tab-pane label="修改记录" name="logs">
          <AppointmentLogs />
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 新增/编辑预约弹窗 -->
    <el-dialog
      v-model="appointmentDialogVisible"
      :title="isEdit ? '编辑预约' : '新增预约'"
      width="600px"
      destroy-on-close
    >
      <el-form
        ref="appointmentFormRef"
        :model="appointmentForm"
        :rules="appointmentRules"
        label-width="100px"
      >
        <el-form-item label="客户" prop="customerId">
          <div style="display: flex; align-items: center; gap: 8px">
            <el-select
              v-model="appointmentForm.customerId"
              filterable
              :filter-method="filterCustomer"
              placeholder="请选择客户"
              style="width: calc(100% - 90px)"
              @focus="handleCustomerFocus"
            >
              <el-option
                v-for="customer in filteredCustomerList"
                :key="customer.id"
                :label="`${customer.name} ${customer.phone || ''}`"
                :value="customer.id"
              >
                <div class="customer-option">
                  <span>{{ customer.name }}</span>
                  <span class="text-gray-400 text-xs ml-2">{{
                    customer.phone
                  }}</span>
                  <span
                    v-if="crossStoreCustomer && customer.storeName"
                    class="text-gray-400 text-xs ml-2"
                    >({{ customer.storeName }})</span
                  >
                </div>
              </el-option>
            </el-select>
            <el-checkbox
              v-model="crossStoreCustomer"
              label="跨店消费"
              style="flex-shrink: 0"
            />
          </div>
        </el-form-item>
        <el-form-item label="服务品项">
          <el-select
            v-model="appointmentForm.projectIds"
            multiple
            filterable
            :filter-method="filterProject"
            placeholder="请选择服务品项"
            style="width: 100%"
          >
            <el-option-group
              v-if="filteredCustomerCardItems.length > 0"
              label="顾客卡项"
            >
              <el-option
                v-for="item in filteredCustomerCardItems"
                :key="'card-' + item.id"
                :label="item.projectName"
                :value="'card-' + item.id"
              >
                <div class="project-option">
                  <span>{{ item.projectName }}</span>
                  <el-tag size="small" type="warning" class="ml-2">卡项</el-tag>
                  <el-tag
                    size="small"
                    :type="item.itemType === 'product' ? 'success' : 'primary'"
                    class="ml-1"
                    >{{ item.itemType === "product" ? "产品" : "项目" }}</el-tag
                  >
                  <span class="text-gray-400 text-xs ml-2"
                    >余次 {{ item.remainingCount ?? 0 }}/{{
                      item.totalCount ?? "不限"
                    }}</span
                  >
                </div>
              </el-option>
            </el-option-group>
            <el-option-group label="门店项目">
              <el-option
                v-for="item in filteredStoreProjects"
                :key="'project-' + item.id"
                :label="item.projectName"
                :value="'project-' + item.id"
              >
                <div class="project-option">
                  <span>{{ item.projectName }}</span>
                  <el-tag
                    size="small"
                    :type="
                      item.projectType === 'product' ? 'success' : 'primary'
                    "
                    class="ml-2"
                    >{{
                      item.projectType === "product" ? "产品" : "项目"
                    }}</el-tag
                  >
                  <span class="text-gray-400 text-xs ml-2"
                    >¥{{ item.price }}</span
                  >
                </div>
              </el-option>
            </el-option-group>
          </el-select>
        </el-form-item>
        <el-form-item label="服务员工" prop="employeeIds">
          <el-select
            v-model="appointmentForm.employeeIds"
            multiple
            placeholder="请选择员工（可多选）"
            style="width: 100%"
          >
            <el-option
              v-for="employee in availableEmployeeList"
              :key="employee.id"
              :label="employee.name"
              :value="employee.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="房间">
          <el-select
            v-model="appointmentForm.roomId"
            placeholder="请选择房间"
            style="width: 100%"
            clearable
          >
            <el-option
              v-for="room in filteredRoomList"
              :key="room.id"
              :label="`${room.roomName}(${room.bedCount}床)`"
              :value="room.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="预约日期" prop="appointmentDate">
          <el-date-picker
            v-model="appointmentForm.appointmentDate"
            type="date"
            placeholder="选择预约日期"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="预约时间" prop="appointmentTimeRange">
          <div
            style="display: flex; align-items: center; width: 100%; gap: 8px"
          >
            <el-select
              v-model="appointmentForm.startHour"
              placeholder="时"
              style="width: 80px"
            >
              <el-option
                v-for="h in hourOptions"
                :key="h"
                :label="h"
                :value="h"
              />
            </el-select>
            <span>:</span>
            <el-select
              v-model="appointmentForm.startMinute"
              placeholder="分"
              style="width: 80px"
            >
              <el-option
                v-for="m in minuteOptions"
                :key="m"
                :label="m"
                :value="m"
              />
            </el-select>
            <span style="margin: 0 8px">至</span>
            <el-select
              v-model="appointmentForm.endHour"
              placeholder="时"
              style="width: 80px"
            >
              <el-option
                v-for="h in hourOptions"
                :key="h"
                :label="h"
                :value="h"
              />
            </el-select>
            <span>:</span>
            <el-select
              v-model="appointmentForm.endMinute"
              placeholder="分"
              style="width: 80px"
            >
              <el-option
                v-for="m in minuteOptions"
                :key="m"
                :label="m"
                :value="m"
              />
            </el-select>
          </div>
        </el-form-item>
        <el-form-item label="状态">
          <el-segmented
            v-model="appointmentForm.status"
            :options="statusOptions"
            size="default"
          />
        </el-form-item>
        <el-form-item label="备注">
          <el-input
            v-model="appointmentForm.remark"
            type="textarea"
            :rows="2"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="appointmentDialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          :loading="saveLoading"
          @click="saveAppointment"
          >保存</el-button
        >
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from "vue";
import { ElMessage } from "element-plus";
import type { FormInstance, FormRules } from "element-plus";
import http from "@/utils/http";
import { useStoreStore } from "@/store/modules/store";

import EmployeeAppointment from "./components/EmployeeAppointment.vue";
import RoomAppointment from "./components/RoomAppointment.vue";
import AppointmentSettings from "./components/AppointmentSettings.vue";
import AppointmentLogs from "./components/AppointmentLogs.vue";

const storeStore = useStoreStore();
const activeTab = ref("employee");

const employeeAppointmentRef = ref<any>(null);
const roomAppointmentRef = ref<any>(null);

const appointmentDialogVisible = ref(false);
const isEdit = ref(false);
const saveLoading = ref(false);
const appointmentFormRef = ref<FormInstance>();

const customerList = ref<any[]>([]);
const customerSearchKeyword = ref("");
const crossStoreCustomer = ref(false);
const employeeList = ref<any[]>([]);
const roomList = ref<any[]>([]);
const busyEmployeeIds = ref<number[]>([]);
const customerCardItems = ref<any[]>([]);
const storeProjects = ref<any[]>([]);
const projectSearchKeyword = ref("");

const statusOptions = [
  { label: "已预约", value: "pending" },
  { label: "已签到", value: "checked_in" },
  { label: "服务中", value: "in_service" },
  { label: "已完成", value: "completed" },
  { label: "已取消", value: "cancelled" }
];

const currentStoreId = computed(() => storeStore.getCurrentStore?.id);

const filteredRoomList = computed(() => {
  if (!currentStoreId.value) return roomList.value;
  return roomList.value.filter(room => room.storeId === currentStoreId.value);
});

const filteredCustomerList = computed(() => {
  if (!customerSearchKeyword.value) return customerList.value;
  const keyword = customerSearchKeyword.value.toLowerCase();
  return customerList.value.filter(customer => {
    const name = (customer.name || "").toLowerCase();
    const phone = (customer.phone || "").toLowerCase();
    const memberId = (customer.memberId || "").toLowerCase();
    const pinyin = (
      customer.namePinyin ||
      customer.name_pinyin ||
      ""
    ).toLowerCase();
    return (
      name.includes(keyword) ||
      phone.includes(keyword) ||
      memberId.includes(keyword) ||
      pinyin.includes(keyword)
    );
  });
});

const availableEmployeeList = computed(() => {
  return employeeList.value.filter(employee => {
    if (busyEmployeeIds.value.includes(employee.id)) {
      return false;
    }
    if (employee.config && employee.config.isAppointmentable === false) {
      return false;
    }
    return true;
  });
});

const hourOptions = Array.from({ length: 24 }, (_, i) =>
  i.toString().padStart(2, "0")
);
const minuteOptions = ["00", "15", "30", "45"];

const appointmentForm = reactive({
  id: null as number | null,
  customerId: null as number | null,
  employeeIds: [] as number[],
  roomId: null as number | null,
  appointmentDate: new Date().toISOString().slice(0, 10),
  startHour: "",
  startMinute: "",
  endHour: "",
  endMinute: "",
  projectIds: [] as string[],
  status: "pending",
  remark: ""
});

const startTime = computed(() => {
  if (appointmentForm.startHour && appointmentForm.startMinute) {
    return `${appointmentForm.startHour}:${appointmentForm.startMinute}`;
  }
  return "";
});

const endTime = computed(() => {
  if (appointmentForm.endHour && appointmentForm.endMinute) {
    return `${appointmentForm.endHour}:${appointmentForm.endMinute}`;
  }
  return "";
});

const appointmentRules: FormRules = {
  customerId: [{ required: true, message: "请选择客户", trigger: "change" }],
  employeeIds: [
    {
      required: true,
      message: "请选择员工",
      trigger: "change",
      type: "array",
      min: 1
    }
  ],
  appointmentDate: [
    { required: true, message: "请选择预约日期", trigger: "change" }
  ],
  startHour: [{ required: true, message: "请选择开始时间", trigger: "change" }],
  endHour: [{ required: true, message: "请选择结束时间", trigger: "change" }]
};

const handleAddAppointment = async (data: any) => {
  isEdit.value = false;
  resetForm();

  if (data.employeeId) {
    appointmentForm.employeeIds = [data.employeeId];
  }
  if (data.roomId) {
    appointmentForm.roomId = data.roomId;
  }
  if (data.startTime) {
    const [hour, minute] = data.startTime.split(":");
    appointmentForm.startHour = hour;
    appointmentForm.startMinute = minute || "00";
  }
  if (data.endTime) {
    const [hour, minute] = data.endTime.split(":");
    appointmentForm.endHour = hour;
    appointmentForm.endMinute = minute || "00";
  }

  await Promise.all([
    loadCustomers(),
    loadEmployees(),
    loadRooms(),
    loadStoreProjects()
  ]);

  appointmentDialogVisible.value = true;

  if (appointmentForm.startHour) {
    setTimeout(() => {
      checkEmployeeAvailability();
    }, 100);
  }
};

const handleEditAppointment = async (row: any) => {
  isEdit.value = true;
  resetForm();

  appointmentForm.id = row.id;
  appointmentForm.customerId = row.customerId;
  appointmentForm.employeeIds = row.employeeId ? [row.employeeId] : [];
  appointmentForm.roomId = row.roomId;
  appointmentForm.appointmentDate = row.appointmentDate;

  if (row.startTime) {
    const [hour, minute] = row.startTime.slice(0, 5).split(":");
    appointmentForm.startHour = hour;
    appointmentForm.startMinute = minute || "00";
  }
  if (row.endTime) {
    const [hour, minute] = row.endTime.slice(0, 5).split(":");
    appointmentForm.endHour = hour;
    appointmentForm.endMinute = minute || "00";
  }

  appointmentForm.projectIds = row.projectIds || [];
  appointmentForm.status = row.status || "pending";
  appointmentForm.remark = row.remark || "";

  await Promise.all([
    loadCustomers(),
    loadEmployees(),
    loadRooms(),
    loadStoreProjects(),
    row.customerId ? loadCustomerCardItems(row.customerId) : Promise.resolve()
  ]);

  appointmentDialogVisible.value = true;

  setTimeout(() => {
    checkEmployeeAvailability();
  }, 100);
};

const resetForm = () => {
  appointmentForm.id = null;
  appointmentForm.customerId = null;
  appointmentForm.employeeIds = [];
  appointmentForm.roomId = null;
  appointmentForm.appointmentDate = new Date().toISOString().slice(0, 10);
  appointmentForm.startHour = "";
  appointmentForm.startMinute = "";
  appointmentForm.endHour = "";
  appointmentForm.endMinute = "";
  appointmentForm.projectIds = [];
  appointmentForm.status = "pending";
  appointmentForm.remark = "";
  customerSearchKeyword.value = "";
  busyEmployeeIds.value = [];
  customerCardItems.value = [];
};

const loadCustomerCardItems = async (customerId: number) => {
  if (!customerId) {
    customerCardItems.value = [];
    return;
  }
  try {
    const res = await http.get(
      `/api/appointment/get-customer-cards/${customerId}`
    );
    if (res.code === 200) {
      customerCardItems.value = res.data || [];
    }
  } catch (e) {
    console.error("加载顾客卡项失败", e);
  }
};

const loadStoreProjects = async () => {
  try {
    const res = await http.get("/api/appointment/get-store-projects", {
      params: { storeId: currentStoreId.value }
    });
    if (res.code === 200) {
      storeProjects.value = res.data || [];
    }
  } catch (e) {
    console.error("加载门店项目失败", e);
  }
};

const filterProject = (keyword: string) => {
  projectSearchKeyword.value = keyword;
};

const filteredCustomerCardItems = computed(() => {
  if (!projectSearchKeyword.value) return customerCardItems.value;
  const keyword = projectSearchKeyword.value.toLowerCase();
  return customerCardItems.value.filter(
    item =>
      (item.projectName || "").toLowerCase().includes(keyword) ||
      (item.projectPinyin || "").toLowerCase().includes(keyword)
  );
});

const filteredStoreProjects = computed(() => {
  if (!projectSearchKeyword.value) return storeProjects.value;
  const keyword = projectSearchKeyword.value.toLowerCase();
  return storeProjects.value.filter(
    item =>
      (item.projectName || "").toLowerCase().includes(keyword) ||
      (item.projectPinyin || "").toLowerCase().includes(keyword)
  );
});

const saveAppointment = async () => {
  if (!appointmentFormRef.value) return;

  await appointmentFormRef.value.validate(async valid => {
    if (!valid) return;

    if (!appointmentForm.customerId) {
      ElMessage.error("请选择客户");
      return;
    }

    if (!startTime.value || !endTime.value) {
      ElMessage.error("请选择预约时间");
      return;
    }

    saveLoading.value = true;
    try {
      console.log("currentStoreId:", currentStoreId.value);
      console.log("storeStore.currentStore:", storeStore.currentStore);
      
      if (!currentStoreId.value) {
        ElMessage.error("请先选择门店");
        saveLoading.value = false;
        return;
      }
      
      const data: any = {
        customerIds: [appointmentForm.customerId],
        employeeIds: appointmentForm.employeeIds,
        appointmentDate: appointmentForm.appointmentDate,
        startTime: startTime.value,
        endTime: endTime.value,
        status: appointmentForm.status,
        projectIds: appointmentForm.projectIds,
        remark: appointmentForm.remark,
        storeId: currentStoreId.value
      };

      if (appointmentForm.roomId) {
        data.roomId = appointmentForm.roomId;
      }

      console.log("Sending data:", data);

      let res;
      if (isEdit.value && appointmentForm.id) {
        res = await http.put(
          `/api/appointment/update-appointment/${appointmentForm.id}`,
          {
            data: {
              ...data,
              id: appointmentForm.id
            }
          }
        );
      } else {
        res = await http.post("/api/appointment/add-appointment", { data });
      }

      if (res.code === 200) {
        ElMessage.success(isEdit.value ? "修改成功" : "添加成功");
        appointmentDialogVisible.value = false;

        if (activeTab.value === "employee" && employeeAppointmentRef.value) {
          employeeAppointmentRef.value.loadAppointments();
        } else if (activeTab.value === "room" && roomAppointmentRef.value) {
          roomAppointmentRef.value.loadAppointments();
        }
      } else {
        ElMessage.error(res.message || "保存失败");
      }
    } catch (e) {
      ElMessage.error("保存失败");
    } finally {
      saveLoading.value = false;
    }
  });
};

const handleCustomerFocus = () => {
  if (customerList.value.length === 0) {
    loadCustomers();
  }
};

const filterCustomer = (keyword: string) => {
  customerSearchKeyword.value = keyword;
};

const loadCustomers = async () => {
  try {
    const params: any = {};
    if (currentStoreId.value) {
      params.storeId = currentStoreId.value;
      params.crossStore = crossStoreCustomer.value ? 1 : 0;
    }
    const res = await http.get("/api/appointment/get-customers", { params });
    if (res.code === 200) {
      customerList.value = res.data || [];
    }
  } catch (e) {
    console.error("加载客户失败", e);
  }
};

const loadEmployees = async () => {
  try {
    const params: any = {};
    if (currentStoreId.value) {
      params.storeId = currentStoreId.value;
    }
    const res = await http.get("/api/appointment/get-employees", { params });
    if (res.code === 200) {
      employeeList.value = res.data || [];
    }
  } catch (e) {
    console.error("加载员工失败", e);
  }
};

const loadRooms = async () => {
  try {
    const res = await http.get("/api/appointment/get-rooms");
    if (res.code === 200) {
      roomList.value = res.data || [];
    }
  } catch (e) {
    console.error("加载房间失败", e);
  }
};

const checkEmployeeAvailability = async () => {
  if (!startTime.value || !endTime.value) {
    busyEmployeeIds.value = [];
    return;
  }

  if (!appointmentForm.appointmentDate) {
    busyEmployeeIds.value = [];
    return;
  }

  try {
    const res = await http.get("/api/appointment/check-employee-availability", {
      params: {
        employeeIds: employeeList.value.map(e => e.id).join(","),
        appointmentDate: appointmentForm.appointmentDate,
        startTime: startTime.value,
        endTime: endTime.value
      }
    });

    if (res.code === 200) {
      busyEmployeeIds.value = res.data?.busyEmployeeIds || [];
    }
  } catch (e) {
    console.error("检查员工可用性失败", e);
  }
};

watch(
  [
    () => appointmentForm.appointmentDate,
    () => appointmentForm.startHour,
    () => appointmentForm.startMinute,
    () => appointmentForm.endHour,
    () => appointmentForm.endMinute
  ],
  () => {
    if (employeeList.value.length > 0) {
      checkEmployeeAvailability();
    }
  },
  { deep: true }
);

watch(
  () => appointmentForm.customerId,
  newCustomerId => {
    if (newCustomerId) {
      loadCustomerCardItems(newCustomerId);
    } else {
      customerCardItems.value = [];
    }
  }
);

watch(
  () => currentStoreId.value,
  newStoreId => {
    if (newStoreId) {
      customerList.value = [];
      appointmentForm.customerId = null;
      crossStoreCustomer.value = false;
      loadCustomers();
    }
  }
);

watch(
  () => crossStoreCustomer.value,
  () => {
    appointmentForm.customerId = null;
    loadCustomers();
  }
);

onMounted(async () => {
  await storeStore.initStores();
  loadEmployees();
  loadRooms();
  loadStoreProjects();
});
</script>

<style scoped>
.appointment-container {
  height: auto;
  display: flex;
  flex-direction: column;
  overflow: visible;
}

.appointment-container .el-card {
  height: auto;
  display: flex;
  flex-direction: column;
}

.appointment-container :deep(.el-card__body) {
  padding: 12px;
  height: auto;
  display: flex;
  flex-direction: column;
  overflow: visible;
}

.appointment-container :deep(.el-tabs) {
  height: auto;
  display: flex;
  flex-direction: column;
}

.appointment-container :deep(.el-tabs__content) {
  flex: 1;
  overflow: visible;
}

.appointment-container :deep(.el-tab-pane) {
  height: auto;
}
</style>
