<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="80px">
      <el-form-item label="出库单号" prop="stockOutNo">
        <el-input v-model="queryParams.stockOutNo" placeholder="请输入出库单号" clearable style="width: 200px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="出库类型" prop="stockOutType">
        <el-select v-model="queryParams.stockOutType" placeholder="请选择" clearable style="width: 150px">
          <el-option v-for="dict in biz_stock_out_type" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-select v-model="queryParams.status" placeholder="请选择" clearable style="width: 120px">
          <el-option v-for="dict in biz_doc_status" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="出库日期" prop="stockOutDate">
        <el-date-picker v-model="dateRange" type="daterange" range-separator="-" start-placeholder="开始" end-placeholder="结束" value-format="YYYY-MM-DD" style="width: 240px" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-row :gutter="10" class="mb8">
      <el-col :span="1.5">
        <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['wms:stockOut:add']">新增</el-button>
      </el-col>
      <el-col :span="1.5">
        <el-button type="danger" plain icon="Delete" :disabled="multiple" @click="handleDelete" v-hasPermi="['wms:stockOut:remove']">删除</el-button>
      </el-col>
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList" />
    </el-row>

    <el-table v-loading="loading" :data="stockOutList" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="55" align="center" />
      <el-table-column label="出库单号" prop="stockOutNo" width="160" />
      <el-table-column label="出库类型" prop="stockOutType" width="90" align="center">
        <template #default="scope">
          <dict-tag :options="biz_stock_out_type" :value="scope.row.stockOutType" />
        </template>
      </el-table-column>
      <el-table-column label="出库企业" prop="enterpriseName" width="80" show-overflow-tooltip />
      <el-table-column label="对接员工" prop="contactEmployeeName" width="80" align="center" />
      <el-table-column label="出库员工" prop="responsibleName" width="80" align="center" />
      <el-table-column label="总数量" width="160" align="center">
        <template #default="scope">
          <span v-if="scope.row.display_unit_type === '1' && scope.row.display_pack_qty > 1">
            {{ scope.row.display_original_qty }}{{ getUnitLabel(scope.row.display_unit) }}({{ scope.row.totalQuantity }}{{ getSpecLabel(scope.row.display_spec) }})
          </span>
          <span v-else-if="scope.row.display_spec">
            {{ scope.row.totalQuantity }}{{ getSpecLabel(scope.row.display_spec) }}
          </span>
          <span v-else>{{ scope.row.totalQuantity }}</span>
        </template>
      </el-table-column>
      <el-table-column label="总金额" prop="totalAmount" width="110" align="right" />
      <el-table-column label="出库日期" prop="stockOutDate" width="105" align="center" />
      <el-table-column label="状态" prop="status" width="85" align="center">
        <template #default="scope">
          <dict-tag :options="biz_doc_status" :value="scope.row.status" />
        </template>
      </el-table-column>
      <el-table-column label="操作" width="250" align="center">
        <template #default="scope">
          <el-button link type="primary" icon="View" @click="handleView(scope.row)">查看</el-button>
          <el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)" v-if="scope.row.status === '0'" v-hasPermi="['wms:stockOut:edit']">修改</el-button>
          <el-button link type="primary" icon="Check" @click="handleConfirm(scope.row)" v-if="scope.row.status === '0'" v-hasPermi="['wms:stockOut:confirm']">确认</el-button>
          <el-button link type="primary" icon="Delete" @click="handleDelete(scope.row)" v-if="scope.row.status === '0'" v-hasPermi="['wms:stockOut:remove']">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" v-model:page="queryParams.pageNum" v-model:limit="queryParams.pageSize" @pagination="getList" />