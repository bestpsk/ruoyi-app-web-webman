<template>
  <div class="appointment-settings">
    <el-tabs v-model="activeTab">
      <el-tab-pane label="员工配置" name="employee">
        <el-card shadow="hover">
          <div class="filter-section mb-4">
            <el-form :inline="true">
              <el-form-item label="员工">
                <el-input
                  v-model="employeeFilter.keyword"
                  placeholder="搜索员工姓名"
                  clearable
                  style="width: 200px"
                  @input="filterEmployees"
                />
              </el-form-item>
              <el-form-item>
                <el-button
                  type="primary"
                  :disabled="selectedEmployees.length === 0"
                  @click="handleBatchEmployeeConfig"
                  >批量配置 ({{ selectedEmployees.length }})</el-button
                >
              </el-form-item>
            </el-form>
          </div>

          <el-table
            v-loading="employeeLoading"
            :data="filteredEmployeeList"
            @selection-change="handleEmployeeSelectionChange"
          >
            <el-table-column type="selection" width="50" />
            <el-table-column prop="name" label="员工姓名" min-width="100" />
            <el-table-column prop="positionName" label="职位" min-width="100">
              <template #default="{ row }">
                {{ row.positionName || "-" }}
              </template>
            </el-table-column>
            <el-table-column
              prop="departmentName"
              label="部门"
              min-width="100"
            />
            <el-table-column label="工作时间" min-width="140">
              <template #default="{ row }">
                {{ row.config?.workStartTime || "09:00" }} -
                {{ row.config?.workEndTime || "21:00" }}
              </template>
            </el-table-column>
            <el-table-column label="每日最大预约数" min-width="120">
              <template #default="{ row }">
                {{ row.config?.maxAppointmentsPerDay || 20 }}
              </template>
            </el-table-column>
            <el-table-column label="是否可预约" min-width="100">
              <template #default="{ row }">
                <el-switch
                  :model-value="row.config?.isAppointmentable !== false"
                  @change="
                    (val: boolean) =>
                      handleEmployeeAppointmentableChange(row, val)
                  "
                />
              </template>
            </el-table-column>
            <el-table-column label="操作" min-width="80">
              <template #default="{ row }">
                <el-button
                  type="primary"
                  size="small"
                  text
                  @click="handleEditEmployeeConfig(row)"
                  >配置</el-button
                >
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>

      <el-tab-pane label="房间配置" name="room">
        <el-card shadow="hover">
          <div class="filter-section mb-4">
            <el-form :inline="true">
              <el-form-item>
                <el-button
                  type="primary"
                  :disabled="selectedRooms.length === 0"
                  @click="handleBatchRoomConfig"
                  >批量配置 ({{ selectedRooms.length }})</el-button
                >
              </el-form-item>
            </el-form>
          </div>

          <el-table
            v-loading="roomLoading"
            :data="roomList"
            @selection-change="handleRoomSelectionChange"
          >
            <el-table-column type="selection" width="50" />
            <el-table-column prop="roomName" label="房间名称" min-width="120" />
            <el-table-column prop="bedCount" label="床位数量" min-width="100" />
            <el-table-column label="可用时间" min-width="140">
              <template #default="{ row }">
                {{ row.config?.availableStartTime || "09:00" }} -
                {{ row.config?.availableEndTime || "21:00" }}
              </template>
            </el-table-column>
            <el-table-column label="最大容纳人数" min-width="110">
              <template #default="{ row }">
                {{ row.config?.maxOccupancy || 1 }}
              </template>
            </el-table-column>
            <el-table-column label="是否可用" min-width="90">
              <template #default="{ row }">
                <el-switch
                  :model-value="row.config?.isAvailable !== false"
                  @change="
                    (val: boolean) => handleRoomAvailableChange(row, val)
                  "
                />
              </template>
            </el-table-column>
            <el-table-column label="操作" min-width="80">
              <template #default="{ row }">
                <el-button
                  type="primary"
                  size="small"
                  text
                  @click="handleEditRoomConfig(row)"
                  >配置</el-button
                >
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-tab-pane>
    </el-tabs>

    <!-- 员工配置弹窗 -->
    <el-dialog
      v-model="employeeConfigDialogVisible"
      :title="isBatchEmployee ? '批量配置员工预约' : '员工预约配置'"
      width="550px"
    >
      <el-form :model="employeeConfigForm" label-width="100px">
        <el-form-item v-if="!isBatchEmployee" label="员工姓名">
          <el-input :value="currentEmployee?.name" disabled />
        </el-form-item>
        <el-form-item v-if="isBatchEmployee" label="已选员工">
          <el-tag
            v-for="emp in selectedEmployees"
            :key="emp.id"
            class="mr-1 mb-1"
            >{{ emp.name }}</el-tag
          >
        </el-form-item>
        <el-form-item label="最大预约数/日">
          <el-input-number
            v-model="employeeConfigForm.maxAppointmentsPerDay"
            :min="1"
            :max="100"
          />
        </el-form-item>
        <el-form-item label="工作时间">
          <el-time-picker
            v-model="employeeConfigForm.workTimeRange"
            is-range
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            format="HH:mm"
            value-format="HH:mm"
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="休息时间">
          <el-time-picker
            v-model="employeeConfigForm.restTimeRange"
            is-range
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            format="HH:mm"
            value-format="HH:mm"
            clearable
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="工作日">
          <el-checkbox-group v-model="employeeConfigForm.workDaysArray">
            <el-checkbox
              v-for="day in weekDays"
              :key="day.value"
              :label="day.value"
              >{{ day.label }}</el-checkbox
            >
          </el-checkbox-group>
        </el-form-item>
        <el-form-item label="休假日期">
          <el-date-picker
            v-model="employeeConfigForm.holidayDates"
            type="dates"
            placeholder="选择休假日期（可多选）"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
            clearable
            style="width: 100%"
          />
        </el-form-item>
        <el-form-item label="是否可预约">
          <el-switch v-model="employeeConfigForm.isAppointmentable" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input
            v-model="employeeConfigForm.remark"
            type="textarea"
            :rows="2"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="employeeConfigDialogVisible = false">取消</el-button>
        <el-button
          type="primary"
          :loading="saveLoading"
          @click="saveEmployeeConfig"
          >保存</el-button
        >
      </template>
    </el-dialog>

    <!-- 房间配置弹窗 -->
    <el-dialog
      v-model="roomConfigDialogVisible"
      :title="isBatchRoom ? '批量配置房间预约' : '房间预约配置'"
      width="500px"
    >
      <el-form :model="roomConfigForm" label-width="120px">
        <el-form-item v-if="!isBatchRoom" label="房间名称">
          <el-input :value="currentRoom?.roomName" disabled />
        </el-form-item>
        <el-form-item v-if="isBatchRoom" label="已选房间">
          <el-tag
            v-for="room in selectedRooms"
            :key="room.id"
            class="mr-1 mb-1"
            >{{ room.roomName }}</el-tag
          >
        </el-form-item>
        <el-form-item label="最大容纳人数">
          <el-input-number
            v-model="roomConfigForm.maxOccupancy"
            :min="1"
            :max="20"
          />
        </el-form-item>
        <el-form-item label="可用开始时间">
          <el-time-select
            v-model="roomConfigForm.availableStartTime"
            start="06:00"
            step="00:30"
            end="23:00"
          />
        </el-form-item>
        <el-form-item label="可用结束时间">
          <el-time-select
            v-model="roomConfigForm.availableEndTime"
            start="06:00"
            step="00:30"
            end="23:00"
          />
        </el-form-item>
        <el-form-item label="是否可用">
          <el-switch v-model="roomConfigForm.isAvailable" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="roomConfigForm.remark" type="textarea" :rows="2" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="roomConfigDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="saveLoading" @click="saveRoomConfig"
          >保存</el-button
        >
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from "vue";
import { ElMessage } from "element-plus";
import http from "@/utils/http";
import { useStoreStore } from "@/store/modules/store";

const activeTab = ref("employee");
const saveLoading = ref(false);
const storeStore = useStoreStore();

const employeeLoading = ref(false);
const employeeList = ref<any[]>([]);
const employeeFilter = reactive({
  keyword: ""
});

const roomLoading = ref(false);
const roomList = ref<any[]>([]);

const currentStoreId = computed(() => storeStore.getCurrentStore?.id);

const selectedEmployees = ref<any[]>([]);
const selectedRooms = ref<any[]>([]);

const employeeConfigDialogVisible = ref(false);
const currentEmployee = ref<any>(null);
const isBatchEmployee = ref(false);
const employeeConfigForm = reactive({
  maxAppointmentsPerDay: 20,
  workTimeRange: ["09:00", "21:00"] as string[],
  restTimeRange: [] as string[],
  workDaysArray: [1, 2, 3, 4, 5, 6, 7] as number[],
  holidayDates: [] as string[],
  isAppointmentable: true,
  remark: ""
});

const roomConfigDialogVisible = ref(false);
const currentRoom = ref<any>(null);
const isBatchRoom = ref(false);
const roomConfigForm = reactive({
  maxOccupancy: 1,
  availableStartTime: "09:00",
  availableEndTime: "21:00",
  isAvailable: true,
  remark: ""
});

const weekDays = [
  { value: 1, label: "周一" },
  { value: 2, label: "周二" },
  { value: 3, label: "周三" },
  { value: 4, label: "周四" },
  { value: 5, label: "周五" },
  { value: 6, label: "周六" },
  { value: 7, label: "周日" }
];

const filteredEmployeeList = computed(() => {
  if (!employeeFilter.keyword) return employeeList.value;
  return employeeList.value.filter(e =>
    e.name.includes(employeeFilter.keyword)
  );
});

const filterEmployees = () => {};

const handleEmployeeSelectionChange = (selection: any[]) => {
  selectedEmployees.value = selection;
};

const handleRoomSelectionChange = (selection: any[]) => {
  selectedRooms.value = selection;
};

const resetEmployeeConfigForm = () => {
  employeeConfigForm.maxAppointmentsPerDay = 20;
  employeeConfigForm.workTimeRange = ["09:00", "21:00"];
  employeeConfigForm.restTimeRange = [];
  employeeConfigForm.workDaysArray = [1, 2, 3, 4, 5, 6, 7];
  employeeConfigForm.holidayDates = [];
  employeeConfigForm.isAppointmentable = true;
  employeeConfigForm.remark = "";
};

const resetRoomConfigForm = () => {
  roomConfigForm.maxOccupancy = 1;
  roomConfigForm.availableStartTime = "09:00";
  roomConfigForm.availableEndTime = "21:00";
  roomConfigForm.isAvailable = true;
  roomConfigForm.remark = "";
};

const handleEditEmployeeConfig = async (row: any) => {
  currentEmployee.value = row;
  isBatchEmployee.value = false;
  resetEmployeeConfigForm();

  try {
    const res = await http.get(`/api/appointment/employee-config/${row.id}`);
    if (res.code === 200 && res.data) {
      const config = res.data;
      employeeConfigForm.maxAppointmentsPerDay =
        config.maxAppointmentsPerDay || 20;
      employeeConfigForm.workTimeRange = [
        config.workStartTime || "09:00",
        config.workEndTime || "21:00"
      ];
      employeeConfigForm.restTimeRange =
        config.restStartTime && config.restEndTime
          ? [config.restStartTime, config.restEndTime]
          : [];
      employeeConfigForm.workDaysArray = config.workDays
        ? config.workDays.split(",").map(Number)
        : [1, 2, 3, 4, 5, 6, 7];
      employeeConfigForm.holidayDates = config.holidayDates
        ? config.holidayDates.split(",")
        : [];
      employeeConfigForm.isAppointmentable = config.isAppointmentable !== false;
      employeeConfigForm.remark = config.remark || "";
    }
  } catch (e) {
    console.error("加载员工配置失败", e);
  }

  employeeConfigDialogVisible.value = true;
};

const handleBatchEmployeeConfig = () => {
  if (selectedEmployees.value.length === 0) {
    ElMessage.warning("请选择要配置的员工");
    return;
  }
  isBatchEmployee.value = true;
  currentEmployee.value = null;
  resetEmployeeConfigForm();
  employeeConfigDialogVisible.value = true;
};

const saveEmployeeConfig = async () => {
  saveLoading.value = true;
  try {
    const data = {
      maxAppointmentsPerDay: employeeConfigForm.maxAppointmentsPerDay,
      workStartTime: employeeConfigForm.workTimeRange?.[0] || "09:00",
      workEndTime: employeeConfigForm.workTimeRange?.[1] || "21:00",
      restStartTime: employeeConfigForm.restTimeRange?.[0] || null,
      restEndTime: employeeConfigForm.restTimeRange?.[1] || null,
      workDays: employeeConfigForm.workDaysArray.join(","),
      holidayDates: employeeConfigForm.holidayDates?.join(",") || "",
      isAppointmentable: employeeConfigForm.isAppointmentable,
      remark: employeeConfigForm.remark
    };

    let res;
    if (isBatchEmployee.value) {
      const batchData = {
        employeeIds: selectedEmployees.value.map(e => e.id),
        ...data
      };
      res = await http.post("/api/appointment/batch-employee-config", {
        data: batchData
      });
    } else {
      if (!currentEmployee.value) return;
      res = await http.put(
        `/api/appointment/employee-config/${currentEmployee.value.id}`,
        { data }
      );
    }

    if (res.code === 200) {
      ElMessage.success(
        isBatchEmployee.value
          ? `批量配置成功，共${res.data?.count || selectedEmployees.value.length}个员工`
          : "保存成功"
      );
      employeeConfigDialogVisible.value = false;
      selectedEmployees.value = [];
      loadEmployees();
    } else {
      ElMessage.error(res.message || "保存失败");
    }
  } catch (e) {
    ElMessage.error("保存失败");
  } finally {
    saveLoading.value = false;
  }
};

const handleEditRoomConfig = async (row: any) => {
  currentRoom.value = row;
  isBatchRoom.value = false;
  resetRoomConfigForm();

  try {
    const res = await http.get(`/api/appointment/room-config/${row.id}`);
    if (res.code === 200 && res.data) {
      const config = res.data;
      roomConfigForm.maxOccupancy = config.maxOccupancy || 1;
      roomConfigForm.availableStartTime = config.availableStartTime || "09:00";
      roomConfigForm.availableEndTime = config.availableEndTime || "21:00";
      roomConfigForm.isAvailable = config.isAvailable !== false;
      roomConfigForm.remark = config.remark || "";
    }
  } catch (e) {
    console.error("加载房间配置失败", e);
  }

  roomConfigDialogVisible.value = true;
};

const handleBatchRoomConfig = () => {
  if (selectedRooms.value.length === 0) {
    ElMessage.warning("请选择要配置的房间");
    return;
  }
  isBatchRoom.value = true;
  currentRoom.value = null;
  resetRoomConfigForm();
  roomConfigDialogVisible.value = true;
};

const saveRoomConfig = async () => {
  saveLoading.value = true;
  try {
    let res;
    if (isBatchRoom.value) {
      const batchData = {
        roomIds: selectedRooms.value.map(r => r.id),
        ...roomConfigForm
      };
      res = await http.post("/api/appointment/batch-room-config", {
        data: batchData
      });
    } else {
      if (!currentRoom.value) return;
      res = await http.put(
        `/api/appointment/room-config/${currentRoom.value.id}`,
        { data: roomConfigForm }
      );
    }

    if (res.code === 200) {
      ElMessage.success(
        isBatchRoom.value
          ? `批量配置成功，共${res.data?.count || selectedRooms.value.length}个房间`
          : "保存成功"
      );
      roomConfigDialogVisible.value = false;
      selectedRooms.value = [];
      loadRooms();
    } else {
      ElMessage.error(res.message || "保存失败");
    }
  } catch (e) {
    ElMessage.error("保存失败");
  } finally {
    saveLoading.value = false;
  }
};

const handleEmployeeAppointmentableChange = async (row: any, val: boolean) => {
  try {
    const res = await http.put(`/api/appointment/employee-config/${row.id}`, {
      data: { isAppointmentable: val }
    });
    if (res.code === 200) {
      ElMessage.success(val ? "已开启预约" : "已关闭预约");
      loadEmployees();
    } else {
      ElMessage.error(res.message || "操作失败");
    }
  } catch (e) {
    ElMessage.error("操作失败");
  }
};

const handleRoomAvailableChange = async (row: any, val: boolean) => {
  try {
    const res = await http.put(`/api/appointment/room-config/${row.id}`, {
      data: { isAvailable: val }
    });
    if (res.code === 200) {
      ElMessage.success(val ? "已开启房间" : "已关闭房间");
      loadRooms();
    } else {
      ElMessage.error(res.message || "操作失败");
    }
  } catch (e) {
    ElMessage.error("操作失败");
  }
};

const loadEmployees = async () => {
  if (!currentStoreId.value) return;
  employeeLoading.value = true;
  try {
    const res = await http.get("/api/appointment/get-employees", {
      params: { storeId: currentStoreId.value }
    });
    if (res.code === 200) {
      employeeList.value = res.data || [];
    }
  } catch (e) {
    console.error("加载员工失败", e);
  } finally {
    employeeLoading.value = false;
  }
};

const loadRooms = async () => {
  if (!currentStoreId.value) return;
  roomLoading.value = true;
  try {
    const res = await http.get("/api/appointment/get-rooms", {
      params: { storeId: currentStoreId.value }
    });
    if (res.code === 200) {
      roomList.value = res.data || [];
    }
  } catch (e) {
    console.error("加载房间失败", e);
  } finally {
    roomLoading.value = false;
  }
};

onMounted(async () => {
  if (currentStoreId.value) {
    await loadEmployees();
    await loadRooms();
  }
});

watch(currentStoreId, () => {
  loadEmployees();
  loadRooms();
});
</script>

<style scoped>
.appointment-settings {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.appointment-settings :deep(.el-tabs) {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.appointment-settings :deep(.el-tabs__content) {
  flex: 1;
  overflow: auto;
}

.appointment-settings :deep(.el-tab-pane) {
  height: 100%;
}

.appointment-settings :deep(.el-card) {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.appointment-settings :deep(.el-card__body) {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.appointment-settings :deep(.el-table) {
  flex: 1;
  min-height: 0;
}

.filter-section {
  flex-shrink: 0;
}

.mr-1 {
  margin-right: 4px;
}

.mb-1 {
  margin-bottom: 4px;
}
</style>
