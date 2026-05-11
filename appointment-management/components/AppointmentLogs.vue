<template>
  <div class="appointment-logs">
    <el-card shadow="hover">
      <div class="filter-section mb-4">
        <el-form :inline="true" :model="filterForm">
          <el-form-item label="预约编号">
            <el-input
              v-model="filterForm.appointmentNo"
              placeholder="请输入预约编号"
              clearable
              style="width: 180px"
            />
          </el-form-item>
          <el-form-item label="操作类型">
            <el-select
              v-model="filterForm.operationType"
              placeholder="全部"
              clearable
              style="width: 150px"
            >
              <el-option label="创建" value="create" />
              <el-option label="修改" value="update" />
              <el-option label="取消" value="cancel" />
              <el-option label="删除" value="delete" />
            </el-select>
          </el-form-item>
          <el-form-item label="日期范围">
            <el-date-picker
              v-model="filterForm.dateRange"
              type="daterange"
              range-separator="至"
              start-placeholder="开始日期"
              end-placeholder="结束日期"
              value-format="YYYY-MM-DD"
              style="width: 260px"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">搜索</el-button>
            <el-button @click="handleReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <el-table v-loading="loading" :data="logList">
        <el-table-column
          prop="appointmentNo"
          label="预约编号"
          min-width="140"
        />
        <el-table-column prop="operationType" label="操作类型" min-width="90">
          <template #default="{ row }">
            <el-tag :type="getOperationTypeTag(row.operationType)" size="small">
              {{ getOperationTypeText(row.operationType) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="changeFields" label="变更内容" min-width="180">
          <template #default="{ row }">
            <div v-if="row.changeFields">{{ row.changeFields }}</div>
            <div v-else-if="row.operationType === 'create'">创建预约</div>
            <div v-else-if="row.operationType === 'delete'">删除预约</div>
            <div v-else>-</div>
          </template>
        </el-table-column>
        <el-table-column prop="operatorName" label="操作人" min-width="90" />
        <el-table-column prop="createdAt" label="操作时间" min-width="160">
          <template #default="{ row }">
            {{ formatDateTime(row.createdAt) }}
          </template>
        </el-table-column>
        <el-table-column label="操作" min-width="70">
          <template #default="{ row }">
            <el-button
              type="primary"
              size="small"
              text
              @click="handleViewDetail(row)"
              >详情</el-button
            >
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container mt-4">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.pageSize"
          :page-sizes="[10, 20, 50, 100]"
          :total="pagination.total"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="handleSizeChange"
          @current-change="handlePageChange"
        />
      </div>
    </el-card>

    <!-- 详情弹窗 -->
    <el-dialog v-model="detailDialogVisible" title="修改记录详情" width="700px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="预约编号">{{
          currentLog?.appointmentNo
        }}</el-descriptions-item>
        <el-descriptions-item label="操作类型">
          <el-tag
            :type="getOperationTypeTag(currentLog?.operationType)"
            size="small"
          >
            {{ getOperationTypeText(currentLog?.operationType) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="操作人">{{
          currentLog?.operatorName
        }}</el-descriptions-item>
        <el-descriptions-item label="操作时间">{{
          formatDateTime(currentLog?.createdAt)
        }}</el-descriptions-item>
        <el-descriptions-item label="变更内容" :span="2">{{
          currentLog?.changeFields || "-"
        }}</el-descriptions-item>
      </el-descriptions>

      <div v-if="currentLog?.beforeData || currentLog?.afterData" class="mt-4">
        <el-tabs>
          <el-tab-pane label="修改前数据">
            <pre class="data-preview">{{
              JSON.stringify(currentLog?.beforeData, null, 2) || "无数据"
            }}</pre>
          </el-tab-pane>
          <el-tab-pane label="修改后数据">
            <pre class="data-preview">{{
              JSON.stringify(currentLog?.afterData, null, 2) || "无数据"
            }}</pre>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import http from "@/utils/http";

const loading = ref(false);
const logList = ref<any[]>([]);

const filterForm = reactive({
  appointmentNo: "",
  operationType: "",
  dateRange: [] as string[]
});

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
});

const detailDialogVisible = ref(false);
const currentLog = ref<any>(null);

const getOperationTypeTag = (type: string) => {
  const map: Record<string, string> = {
    create: "success",
    update: "warning",
    cancel: "info",
    delete: "danger"
  };
  return map[type] || "";
};

const getOperationTypeText = (type: string) => {
  const map: Record<string, string> = {
    create: "创建",
    update: "修改",
    cancel: "取消",
    delete: "删除"
  };
  return map[type] || type;
};

const formatDateTime = (datetime: string) => {
  if (!datetime) return "-";
  return datetime.replace("T", " ").slice(0, 19);
};

const handleSearch = () => {
  pagination.page = 1;
  loadLogs();
};

const handleReset = () => {
  filterForm.appointmentNo = "";
  filterForm.operationType = "";
  filterForm.dateRange = [];
  handleSearch();
};

const handleSizeChange = () => {
  pagination.page = 1;
  loadLogs();
};

const handlePageChange = () => {
  loadLogs();
};

const handleViewDetail = (row: any) => {
  currentLog.value = row;
  detailDialogVisible.value = true;
};

const loadLogs = async () => {
  loading.value = true;
  try {
    const params: any = {
      page: pagination.page,
      pageSize: pagination.pageSize
    };

    if (filterForm.appointmentNo) {
      params.appointmentNo = filterForm.appointmentNo;
    }
    if (filterForm.operationType) {
      params.operationType = filterForm.operationType;
    }
    if (filterForm.dateRange && filterForm.dateRange.length === 2) {
      params.startDate = filterForm.dateRange[0];
      params.endDate = filterForm.dateRange[1];
    }

    const res = await http.get("/api/appointment/logs", { params });
    if (res.code === 200) {
      logList.value = res.data || [];
      pagination.total = res.total || logList.value.length;
    }
  } catch (e) {
    console.error("加载修改记录失败", e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadLogs();
});
</script>

<style scoped>
.appointment-logs {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.appointment-logs .el-card {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.appointment-logs :deep(.el-card__body) {
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.filter-section {
  flex-shrink: 0;
}

.appointment-logs :deep(.el-table) {
  flex: 1;
  min-height: 0;
}

.pagination-container {
  display: flex;
  justify-content: flex-end;
  flex-shrink: 0;
}

.data-preview {
  background-color: #f5f7fa;
  padding: 12px;
  border-radius: 4px;
  font-size: 12px;
  max-height: 300px;
  overflow: auto;
  white-space: pre-wrap;
  word-break: break-all;
}
</style>
