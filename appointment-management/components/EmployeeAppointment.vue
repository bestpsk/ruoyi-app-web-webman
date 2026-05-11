<template>
  <div class="employee-appointment">
    <el-card shadow="hover">
      <div class="filter-section mb-4">
        <el-form :inline="true" :model="filterForm">
          <el-form-item label="日期">
            <el-date-picker
              v-model="filterForm.date"
              type="date"
              placeholder="选择日期"
              style="width: 160px"
              @change="handleDateChange"
            />
          </el-form-item>
          <el-form-item label="员工">
            <el-select
              v-model="filterForm.employeeIds"
              multiple
              placeholder="选择员工"
              style="width: 200px"
            >
              <el-option
                v-for="emp in employeeList"
                :key="emp.id"
                :label="emp.name"
                :value="emp.id"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="状态">
            <el-segmented
              v-model="filterForm.status"
              :options="statusOptions"
              size="default"
              @change="handleStatusChange"
            />
          </el-form-item>
        </el-form>
      </div>

      <div class="schedule-table">
        <div class="schedule-header-row">
          <div class="employee-column">员工/时间段</div>
          <div class="time-slots-container">
            <div
              v-for="(slot, index) in headerTimeSlots"
              :key="index"
              class="time-slot-header-merged"
            >
              {{ slot }}
            </div>
          </div>
        </div>

        <div
          v-for="employee in filteredEmployees"
          :key="employee.id"
          class="employee-row"
        >
          <div class="employee-column">
            <div class="employee-name">{{ employee.name }}</div>
            <el-tag
              v-if="employee.positionName"
              size="small"
              type="info"
              class="position-tag"
              >{{ employee.positionName }}</el-tag
            >
          </div>
          <div
            class="time-slots-container"
            @mousedown="handleMouseDown($event, employee)"
            @mouseup="handleMouseUp"
            @mouseleave="handleMouseLeave"
          >
            <div
              v-for="(slot, index) in timeSlots"
              :key="index"
              :class="['time-slot', getSlotClass(employee.id, index)]"
              :data-slot-index="index"
              :data-slot-time="slot"
              @mouseenter="handleMouseEnter($event, employee, index)"
              @click.stop="handleSlotClick(employee, slot, index)"
            />
            <el-tooltip
              v-for="appointment in getEmployeeAppointments(employee.id)"
              :key="appointment.id"
              :content="getAppointmentTooltip(appointment)"
              placement="top"
              :show-after="300"
            >
              <div
                class="appointment-block"
                :class="`status-${appointment.status}`"
                :style="getAppointmentStyle(appointment)"
                @click.stop="emit('edit-appointment', appointment)"
              >
                <div class="appointment-info">
                  <div class="customer-name">
                    {{ appointment.customerName }}
                  </div>
                  <div class="detail-row">
                    <span v-if="appointment.roomName" class="room-name">{{
                      appointment.roomName
                    }}</span>
                    <el-tag
                      size="small"
                      :type="getStatusType(appointment.status)"
                      class="status-tag"
                    >
                      {{ getStatusText(appointment.status) }}
                    </el-tag>
                  </div>
                </div>
              </div>
            </el-tooltip>
          </div>
        </div>
      </div>
    </el-card>

    <el-card shadow="hover" class="mt-4">
      <template #header>
        <div class="card-header flex justify-between items-center">
          <span>预约列表</span>
          <el-button type="primary" size="small" @click="handleAddAppointment"
            ><el-icon><Plus /></el-icon>新增预约</el-button
          >
        </div>
      </template>

      <el-table v-loading="loading" :data="appointmentList" style="width: 100%">
        <el-table-column
          prop="appointmentDate"
          label="预约日期"
          min-width="120"
        >
          <template #default="{ row }">
            {{ row.appointmentDate || '-' }}
          </template>
        </el-table-column>
        <el-table-column
          prop="appointmentNo"
          label="预约编号"
          min-width="150"
        >
          <template #default="{ row }">
            {{ row.appointmentNo || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="customerName" label="客户" min-width="100" />
        <el-table-column prop="employeeName" label="服务员工" min-width="100">
          <template #default="{ row }">
            {{ row.employeeName || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="roomName" label="房间" min-width="100">
          <template #default="{ row }">
            {{ row.roomName || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="预约时间" min-width="150">
          <template #default="{ row }">
            <span v-if="row.startTime && row.endTime">
              {{ row.startTime?.slice(0, 5) }} - {{ row.endTime?.slice(0, 5) }}
            </span>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column prop="projectName" label="预约项目" min-width="150" />
        <el-table-column prop="status" label="状态" min-width="80">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)" size="small">{{
              getStatusText(row.status)
            }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="200">
          <template #default="{ row }">
            <el-dropdown
              v-if="row.status !== 'completed' && row.status !== 'cancelled'"
              trigger="click"
              @command="(cmd: string) => handleUpdateStatus(row, cmd)"
            >
              <el-button type="success" size="small">
                更新状态<el-icon class="el-icon--right"><arrow-down /></el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item
                    v-if="row.status === 'pending'"
                    command="checked_in"
                  >
                    <el-tag type="primary" size="small">已签到</el-tag>
                  </el-dropdown-item>
                  <el-dropdown-item
                    v-if="row.status === 'pending' || row.status === 'checked_in'"
                    command="in_service"
                  >
                    <el-tag type="success" size="small">服务中</el-tag>
                  </el-dropdown-item>
                  <el-dropdown-item
                    v-if="row.status === 'in_service' || row.status === 'checked_in'"
                    command="completed"
                  >
                    <el-tag type="info" size="small">已完成</el-tag>
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
            <el-button
              type="primary"
              size="small"
              text
              @click="handleEditAppointment(row)"
              ><el-icon><Edit /></el-icon>编辑</el-button
            >
            <el-button
              type="danger"
              size="small"
              text
              @click="handleDeleteAppointment(row)"
              ><el-icon><Delete /></el-icon>删除</el-button
            >
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, watch } from "vue";
import { ElMessage, ElMessageBox } from "element-plus";
import { ArrowDown, Edit, Delete, Plus } from "@element-plus/icons-vue";
import http from "@/utils/http";
import { useStoreStore } from "@/store/modules/store";

const emit = defineEmits(["add-appointment", "edit-appointment"]);

const storeStore = useStoreStore();
const loading = ref(false);
const employeeList = ref<any[]>([]);
const appointmentList = ref<any[]>([]);

const filterForm = reactive({
  date: new Date(),
  employeeIds: [] as number[],
  status: "全部"
});

const statusOptions = ref([
  "全部",
  "已预约",
  "已签到",
  "服务中",
  "已完成",
  "已取消"
]);

const currentStoreId = computed(() => storeStore.getCurrentStore?.id);

const currentStoreBusinessHours = computed(() => {
  const store = storeStore.getCurrentStore;
  if (store && store.businessHours) {
    const [start, end] = store.businessHours.split("-");
    return { start, end };
  }
  return { start: "09:00", end: "21:00" };
});

const timeSlots = ref<string[]>([]);
const generateTimeSlots = () => {
  const slots: string[] = [];
  const { start, end } = currentStoreBusinessHours.value;

  const [startHour, startMin = 0] = start.split(":").map(Number);
  const [endHour, endMin = 0] = end.split(":").map(Number);

  const startMinutes = startHour * 60 + startMin;
  const endMinutes = endHour * 60 + endMin;

  for (let minutes = startMinutes; minutes < endMinutes; minutes += 15) {
    const hour = Math.floor(minutes / 60);
    const min = minutes % 60;
    slots.push(
      `${hour.toString().padStart(2, "0")}:${min.toString().padStart(2, "0")}`
    );
  }
  timeSlots.value = slots;
};

const shouldShowTimeLabel = (time: string) => {
  const parts = time.split(":");
  const min = parseInt(parts[1]);
  return min === 0 || min === 30;
};

const headerTimeSlots = computed(() => {
  return timeSlots.value.filter((slot, index) => {
    const min = parseInt(slot.split(":")[1]);
    return min === 0 || min === 30;
  });
});

const filteredEmployees = computed(() => {
  let employees = employeeList.value;

  employees = employees.filter(
    e => !e.config || e.config.isAppointmentable !== false
  );

  if (filterForm.employeeIds.length > 0) {
    employees = employees.filter(e => filterForm.employeeIds.includes(e.id));
  }

  return employees;
});

const timeSlotAppointments = computed(() => {
  return appointmentList.value.filter(
    a => a.employeeId && a.startTime && a.endTime
  );
});

const getSlotClass = (employeeId: number, slotIndex: number) => {
  const classes = [];

  if (isSelectedSlot(employeeId, slotIndex)) {
    classes.push("selected");
  }

  const appointment = getAppointmentAtSlot(employeeId, slotIndex);
  if (appointment) {
    classes.push("has-appointment-slot");
  }

  return classes.join(" ");
};

const getAppointmentAtSlot = (employeeId: number, slotIndex: number) => {
  const slotTime = timeSlots.value[slotIndex];
  if (!slotTime) return null;

  return timeSlotAppointments.value.find(a => {
    if (Number(a.employeeId) !== Number(employeeId)) return false;
    const startTime = formatTime(a.startTime);
    const endTime = formatTime(a.endTime);
    return slotTime >= startTime && slotTime < endTime;
  });
};

const getEmployeeAppointments = (employeeId: number) => {
  return timeSlotAppointments.value.filter(
    a => Number(a.employeeId) === Number(employeeId)
  );
};

const getAppointmentStyle = (appointment: any) => {
  const startTime = formatTime(appointment.startTime);
  const endTime = formatTime(appointment.endTime);

  let startIndex = findClosestSlotIndex(startTime);
  let endIndex = findClosestSlotIndex(endTime);

  const totalSlots = timeSlots.value.length;

  if (startIndex < 0) startIndex = 0;
  if (endIndex < 0 || endIndex > totalSlots) endIndex = totalSlots;
  if (startIndex >= endIndex) {
    startIndex = Math.max(0, endIndex - 1);
  }

  const leftPercent = (startIndex / totalSlots) * 100;
  const widthPercent = ((endIndex - startIndex) / totalSlots) * 100;

  return {
    left: `${leftPercent}%`,
    width: `${widthPercent}%`
  };
};

const findClosestSlotIndex = (time: string) => {
  const exactIndex = timeSlots.value.findIndex(slot => slot === time);
  if (exactIndex !== -1) return exactIndex;

  const targetMinutes = parseTimeToMinutes(time);
  const firstSlotMinutes = parseTimeToMinutes(timeSlots.value[0]);
  const lastSlotMinutes = parseTimeToMinutes(
    timeSlots.value[timeSlots.value.length - 1]
  );

  if (targetMinutes < firstSlotMinutes) return 0;
  if (targetMinutes > lastSlotMinutes) return timeSlots.value.length;

  for (let i = 0; i < timeSlots.value.length - 1; i++) {
    const currentMinutes = parseTimeToMinutes(timeSlots.value[i]);
    const nextMinutes = parseTimeToMinutes(timeSlots.value[i + 1]);
    if (targetMinutes >= currentMinutes && targetMinutes < nextMinutes) {
      return i;
    }
  }

  return timeSlots.value.length;
};

const formatTime = (time: string) => {
  if (!time) return "";
  const parts = time.split(":");
  return `${parts[0]}:${parts[1]}`;
};

const parseTimeToMinutes = (time: string) => {
  if (!time) return 0;
  const [hour, min] = time.split(":").map(Number);
  return hour * 60 + (min || 0);
};

const handleSlotClick = (employee: any, slot: string, index: number) => {
  const appointment = getAppointmentAtSlot(employee.id, index);
  if (appointment) {
    emit("edit-appointment", appointment);
  } else {
    emit("add-appointment", { employeeId: employee.id, startTime: slot });
  }
};

const handleAddAppointment = () => {
  emit("add-appointment", {});
};

const handleEditAppointment = (row: any) => {
  emit("edit-appointment", row);
};

const handleDeleteAppointment = async (row: any) => {
  try {
    await ElMessageBox.confirm("确定要删除该预约吗？", "提示", {
      type: "warning"
    });
    const res = await http.delete(
      `/api/appointment/delete-appointment/${row.id}`
    );
    if (res.code === 200) {
      ElMessage.success("删除成功");
      loadAppointments();
    } else {
      ElMessage.error(res.message || "删除失败");
    }
  } catch (e) {}
};

const handleUpdateStatus = async (row: any, status: string) => {
  const statusTextMap: Record<string, string> = {
    checked_in: "已签到",
    in_service: "服务中",
    completed: "已完成"
  };
  
  try {
    await ElMessageBox.confirm(
      `确定要将状态更新为"${statusTextMap[status]}"吗？`,
      "更新状态",
      { type: "info" }
    );
    
    const res = await http.put(
      `/api/appointment/update-appointment-status/${row.id}`,
      { status }
    );
    
    if (res.code === 200) {
      ElMessage.success("状态更新成功");
      loadAppointments();
    } else {
      ElMessage.error(res.message || "状态更新失败");
    }
  } catch (e) {}
};

const isDragging = ref(false);
const dragStartSlot = ref<number | null>(null);
const dragEndSlot = ref<number | null>(null);
const dragEmployee = ref<any>(null);
const selectedSlots = ref<Set<number>>(new Set());

const handleMouseDown = (event: MouseEvent, employee: any) => {
  if (event.button !== 0) return;

  const target = event.target as HTMLElement;
  const slotElement = target.closest(".time-slot");
  if (!slotElement) return;

  const slotIndex = parseInt(
    slotElement.getAttribute("data-slot-index") || "0"
  );

  if (getAppointmentAtSlot(employee.id, slotIndex)) return;

  isDragging.value = true;
  dragStartSlot.value = slotIndex;
  dragEndSlot.value = slotIndex;
  dragEmployee.value = employee;
  selectedSlots.value.clear();
  selectedSlots.value.add(slotIndex);

  event.preventDefault();
};

const handleMouseEnter = (
  event: MouseEvent,
  employee: any,
  slotIndex: number
) => {
  if (!isDragging.value || dragEmployee.value?.id !== employee.id) return;

  if (getAppointmentAtSlot(employee.id, slotIndex)) return;

  dragEndSlot.value = slotIndex;

  selectedSlots.value.clear();
  const start = Math.min(dragStartSlot.value!, slotIndex);
  const end = Math.max(dragStartSlot.value!, slotIndex);

  let hasAppointmentInRange = false;
  for (let i = start; i <= end; i++) {
    if (getAppointmentAtSlot(employee.id, i)) {
      hasAppointmentInRange = true;
      break;
    }
  }

  if (!hasAppointmentInRange) {
    for (let i = start; i <= end; i++) {
      selectedSlots.value.add(i);
    }
  }
};

const handleMouseUp = () => {
  if (isDragging.value && dragEmployee.value && selectedSlots.value.size > 1) {
    const slots = Array.from(selectedSlots.value).sort((a, b) => a - b);
    const startTime = timeSlots.value[slots[0]];
    const endIndex = slots[slots.length - 1];
    const endTime =
      endIndex < timeSlots.value.length - 1
        ? timeSlots.value[endIndex + 1]
        : getEndTime(timeSlots.value[endIndex]);

    emit("add-appointment", {
      employeeId: dragEmployee.value.id,
      employeeName: dragEmployee.value.name,
      startTime,
      endTime
    });
  }

  isDragging.value = false;
  dragStartSlot.value = null;
  dragEndSlot.value = null;
  dragEmployee.value = null;
  selectedSlots.value.clear();
};

const handleMouseLeave = () => {};

const getEndTime = (startTime: string) => {
  const [hour, min] = startTime.split(":").map(Number);
  const endMinutes = hour * 60 + min + 30;
  const endHour = Math.floor(endMinutes / 60);
  const endMin = endMinutes % 60;
  return `${endHour.toString().padStart(2, "0")}:${endMin.toString().padStart(2, "0")}`;
};

const isSelectedSlot = (employeeId: number, slotIndex: number) => {
  return (
    dragEmployee.value?.id === employeeId && selectedSlots.value.has(slotIndex)
  );
};

const handleDateChange = () => loadAppointments();

const handleStatusChange = () => loadAppointments();

const getStatusType = (status: string) => {
  const map: Record<string, string> = {
    pending: "warning",
    checked_in: "primary",
    in_service: "success",
    completed: "info",
    cancelled: "danger"
  };
  return map[status] || "";
};

const getStatusText = (status: string) => {
  const map: Record<string, string> = {
    pending: "已预约",
    checked_in: "已签到",
    in_service: "服务中",
    completed: "已完成",
    cancelled: "已取消"
  };
  return map[status] || status;
};

const getAppointmentTooltip = (appointment: any) => {
  const parts = [];
  if (appointment.projectName) {
    parts.push(`项目: ${appointment.projectName}`);
  }
  if (appointment.roomName) {
    parts.push(`房间: ${appointment.roomName}`);
  }
  if (appointment.startTime && appointment.endTime) {
    parts.push(
      `时间: ${appointment.startTime?.slice(0, 5)} - ${appointment.endTime?.slice(0, 5)}`
    );
  }
  return parts.join(" | ") || "无详细信息";
};

const loadEmployees = async () => {
  if (!currentStoreId.value) return;
  try {
    const res = await http.get("/api/appointment/get-employees", {
      params: { storeId: currentStoreId.value }
    });
    if (res.code === 200) {
      employeeList.value = res.data || [];
    }
  } catch (e) {
    console.error("加载员工失败", e);
  }
};

const loadAppointments = async () => {
  if (!currentStoreId.value) return;
  loading.value = true;
  try {
    const params: any = {
      appointmentDate: filterForm.date.toISOString().slice(0, 10),
      storeId: currentStoreId.value
    };

    if (filterForm.status !== "全部") {
      params.status = statusTextToCode(filterForm.status);
    }

    const res = await http.get("/api/appointment/get-appointments", { params });
    if (res.code === 200) {
      appointmentList.value = res.data || [];
    }
  } catch (e) {
    console.error("加载预约失败", e);
  } finally {
    loading.value = false;
  }
};

const statusTextToCode = (text: string) => {
  const map: Record<string, string> = {
    已预约: "pending",
    已签到: "checked_in",
    服务中: "in_service",
    已完成: "completed",
    已取消: "cancelled"
  };
  return map[text] || text;
};

onMounted(async () => {
  generateTimeSlots();
  if (currentStoreId.value) {
    await loadEmployees();
    await loadAppointments();
  }

  document.addEventListener("mouseup", handleMouseUp);
});

onUnmounted(() => {
  document.removeEventListener("mouseup", handleMouseUp);
});

watch(currentStoreId, () => {
  generateTimeSlots();
  loadEmployees();
  loadAppointments();
});

watch(currentStoreBusinessHours, () => {
  generateTimeSlots();
});

defineExpose({
  loadAppointments
});
</script>

<style scoped>
.employee-appointment {
  height: auto;
  display: flex;
  flex-direction: column;
}

.employee-appointment .el-card {
  height: auto;
  display: flex;
  flex-direction: column;
}

.employee-appointment :deep(.el-card__body) {
  height: auto;
  display: flex;
  flex-direction: column;
  overflow: visible;
}

.filter-section {
  flex-shrink: 0;
  margin-bottom: 12px;
}

.schedule-table {
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  overflow-x: auto;
}

.schedule-header-row {
  display: flex;
  background-color: #f5f7fa;
  border-bottom: 1px solid #e4e7ed;
  position: sticky;
  top: 0;
  z-index: 10;
  min-width: max-content;
}

.employee-row {
  display: flex;
  border-bottom: 1px solid #e4e7ed;
  min-height: 60px;
  min-width: max-content;
}

.employee-row:last-child {
  border-bottom: none;
}

.employee-column {
  width: 100px;
  min-width: 100px;
  flex-shrink: 0;
  padding: 8px 4px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  border-right: 1px solid #e4e7ed;
  background-color: #fafafa;
  z-index: 10;
  position: sticky;
  left: 0;
}

.employee-name {
  font-size: 14px;
  margin-bottom: 4px;
}

.position-tag {
  font-size: 10px;
  transform: scale(0.9);
}

.time-slots-container {
  display: flex;
  flex: 1;
  position: relative;
}

.time-slot-header {
  flex: 1;
  min-width: 25px;
  padding: 4px;
  text-align: center;
  font-size: 11px;
  border-right: 1px solid #e4e7ed;
  background-color: #f5f7fa;
}

.time-slot-header-merged {
  flex: 2;
  min-width: 50px;
  padding: 8px 4px;
  text-align: center;
  font-size: 13px;
  font-weight: 500;
  border-right: 1px solid #e4e7ed;
  background-color: #f5f7fa;
  display: flex;
  align-items: center;
  justify-content: center;
}

.time-slot {
  flex: 1;
  min-width: 25px;
  min-height: 60px;
  border-right: 1px solid #e4e7ed;
  cursor: pointer;
  user-select: none;
  transition: background-color 0.2s;
}

.time-slot:hover {
  background-color: #ecf5ff;
}

.time-slot.selected {
  background-color: #409eff;
}

.has-appointment-slot {
  background-color: transparent;
}

.appointment-block {
  position: absolute;
  top: 0;
  height: 100%;
  cursor: pointer;
  z-index: 5;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  border-radius: 2px;
}

.appointment-block:hover {
  filter: brightness(0.95);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.status-pending {
  background-color: #fdf6ec;
  border-left: 3px solid #e6a23c;
}

.status-checked_in {
  background-color: #ecf5ff;
  border-left: 3px solid #409eff;
}

.status-in_service {
  background-color: #e1f3d8;
  border-left: 3px solid #67c23a;
}

.status-completed {
  background-color: #f0f9eb;
  border-left: 3px solid #909399;
}

.status-cancelled {
  background-color: #fef0f0;
  border-left: 3px solid #f56c6c;
  opacity: 0.7;
}

.appointment-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 2px 4px;
  overflow: hidden;
}

.customer-name {
  font-size: 12px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

.detail-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  width: 100%;
}

.room-name {
  font-size: 10px;
  color: #909399;
  background-color: rgba(0, 0, 0, 0.05);
  padding: 1px 4px;
  border-radius: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 60%;
}

.status-tag {
  font-size: 10px;
  flex-shrink: 0;
}
</style>
