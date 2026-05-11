<template>
  <div class="app-container">
    <el-form :model="queryParams" ref="queryRef" :inline="true" v-show="showSearch" label-width="68px">
      <el-form-item label="企业名称" prop="enterpriseName">
        <el-input v-model="queryParams.enterpriseName" placeholder="请输入企业名称" clearable style="width: 160px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="老板姓名" prop="bossName">
        <el-input v-model="queryParams.bossName" placeholder="请输入老板姓名" clearable style="width: 160px" @keyup.enter="handleQuery" />
      </el-form-item>
      <el-form-item label="企业类型" prop="enterpriseType">
        <el-select v-model="queryParams.enterpriseType" placeholder="请选择企业类型" clearable style="width: 160px">
          <el-option v-for="dict in biz_enterprise_type" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="企业级别" prop="enterpriseLevel">
        <el-select v-model="queryParams.enterpriseLevel" placeholder="请选择企业级别" clearable style="width: 160px">
          <el-option v-for="dict in biz_enterprise_level" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-select v-model="queryParams.status" placeholder="企业状态" clearable style="width: 160px">
          <el-option v-for="dict in sys_normal_disable" :key="dict.value" :label="dict.label" :value="dict.value" />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="Search" @click="handleQuery">搜索</el-button>
        <el-button icon="Refresh" @click="resetQuery">重置</el-button>
      </el-form-item>
    </el-form>

    <el-row :gutter="10" class="mb8">
      <el-col :span="1.5">
        <el-button type="primary" plain icon="Plus" @click="handleAdd" v-hasPermi="['business:enterprise:add']">新增企业</el-button>
      </el-col>
      <right-toolbar v-model:showSearch="showSearch" @queryTable="getList"></right-toolbar>
    </el-row>

    <el-table v-loading="loading" :data="enterpriseList" @selection-change="handleSelectionChange" @row-click="handleRowClick" style="width: 100%" class="pointer">
      <el-table-column type="selection" width="50" align="center" />
      <el-table-column label="企业名称" align="center" prop="enterpriseName" :show-overflow-tooltip="true" min-width="90" />
      <el-table-column label="老板姓名" align="center" prop="bossName" min-width="70" />
      <el-table-column label="联系电话" align="center" prop="phone" min-width="110" />
      <el-table-column label="企业类型" align="center" prop="enterpriseType" min-width="80">
        <template #default="scope">
          <dict-tag :options="biz_enterprise_type" :value="scope.row.enterpriseType" />
        </template>
      </el-table-column>
      <el-table-column label="门店数量" align="center" prop="storeCount" min-width="70" />
      <el-table-column label="企业级别" align="center" prop="enterpriseLevel" min-width="80">
        <template #default="scope">
          <dict-tag :options="biz_enterprise_level" :value="scope.row.enterpriseLevel" />
        </template>
      </el-table-column>
      <el-table-column label="服务人" align="center" prop="serverUserName" min-width="70" />
      <el-table-column label="开始合作" align="center" prop="cooperationStartDate" min-width="90" />
      <el-table-column label="结束合作" align="center" prop="cooperationEndDate" min-width="90" />
      <el-table-column label="状态" align="center" prop="status" min-width="60">
        <template #default="scope">
          <el-switch v-model="scope.row.status" active-value="0" inactive-value="1" @change="(val) => handleStatusChange(scope.row, val)" />
        </template>
      </el-table-column>
      <el-table-column label="方案数量" align="center" min-width="70">
        <template #default="scope">
          <el-button link type="primary" @click="handleViewPlans(scope.row)">{{ scope.row.planCount || 0 }}</el-button>
        </template>
      </el-table-column>
      <el-table-column label="操作" align="center" width="280" class-name="small-padding fixed-width">
        <template #default="scope">
          <el-button link type="primary" icon="View" @click="handleViewPlans(scope.row)">方案</el-button>
          <el-button link type="primary" icon="Plus" @click="handleAddPlan(scope.row)" v-hasPermi="['business:plan:add']">开方案</el-button>
          <el-button link type="primary" icon="Edit" @click="handleUpdate(scope.row)" v-hasPermi="['business:enterprise:edit']">修改</el-button>
          <el-button link type="primary" icon="Delete" @click="handleDelete(scope.row)" v-hasPermi="['business:enterprise:remove']">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" v-model:page="queryParams.pageNum" v-model:limit="queryParams.pageSize" @pagination="getList" />

    <el-dialog :title="title" v-model="open" width="600px" append-to-body>
      <el-form ref="enterpriseRef" :model="form" :rules="rules" label-width="100px">
        <el-row>
          <el-col :span="12">
            <el-form-item label="企业名称" prop="enterpriseName">
              <el-input v-model="form.enterpriseName" placeholder="请输入企业名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="老板姓名" prop="bossName">
              <el-input v-model="form.bossName" placeholder="请输入老板姓名" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="联系电话" prop="phone">
              <el-input v-model="form.phone" placeholder="请输入联系电话" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="企业类型" prop="enterpriseType">
              <el-select v-model="form.enterpriseType" placeholder="请选择企业类型">
                <el-option v-for="dict in biz_enterprise_type" :key="dict.value" :label="dict.label" :value="dict.value" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="地址" prop="address">
              <el-input v-model="form.address" placeholder="请输入地址" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="门店数量" prop="storeCount">
              <el-input-number v-model="form.storeCount" controls-position="right" :min="0" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="年业绩" prop="annualPerformance">
              <el-input-number v-model="form.annualPerformance" controls-position="right" :min="0" :precision="2" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="企业级别" prop="enterpriseLevel">
              <el-select v-model="form.enterpriseLevel" placeholder="请选择企业级别">
                <el-option v-for="dict in biz_enterprise_level" :key="dict.value" :label="dict.label" :value="dict.value" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="服务人" prop="serverUserName">
              <el-select v-model="form.serverUserId" placeholder="请选择服务人" filterable remote :remote-method="searchServerUser" @focus="() => searchServerUser('')" @change="onServerUserChange" style="width: 100%" clearable>
                <el-option v-for="u in serverUserOptions" :key="u.userId" :label="u.nickName" :value="u.userId" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="开始合作" prop="cooperationStartDate">
              <el-date-picker v-model="form.cooperationStartDate" type="date" value-format="YYYY-MM-DD" placeholder="选择开始合作日期" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="结束合作" prop="cooperationEndDate">
              <el-date-picker v-model="form.cooperationEndDate" type="date" value-format="YYYY-MM-DD" placeholder="选择结束合作日期" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="状态" prop="status">
              <el-radio-group v-model="form.status">
                <el-radio v-for="dict in sys_normal_disable" :key="dict.value" :value="dict.value">{{ dict.label }}</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="24">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="form.remark" type="textarea" placeholder="请输入内容" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <div class="dialog-footer">
          <el-button type="primary" @click="submitForm">确 定</el-button>
          <el-button @click="cancel">取 消</el-button>
        </div>
      </template>
    </el-dialog>

    <el-dialog title="方案列表" v-model="planListOpen" width="1300px" append-to-body>
      <el-table :data="planList" v-loading="planListLoading">
        <el-table-column label="方案编号" prop="planNo" show-overflow-tooltip />
        <el-table-column label="方案名称" prop="planName" show-overflow-tooltip />
        <el-table-column label="回款比例" prop="commissionRate" align="center">
          <template #default="scope">{{ scope.row.commissionRate }}%</template>
        </el-table-column>
        <el-table-column label="方案金额" prop="planAmount" align="right" />
        <el-table-column label="配赠金额" prop="giftAmount" align="right" />
        <el-table-column label="剩余金额" prop="remainingAmount" align="right" />
        <el-table-column label="审核状态" prop="auditStatus" align="center">
          <template #default="scope">
            <el-tag :type="auditStatusType(scope.row.auditStatus)">{{ auditStatusLabel(scope.row.auditStatus) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="启用" prop="status" align="center">
          <template #default="scope">
            <el-switch v-model="scope.row.status" active-value="0" inactive-value="1" @change="(val) => handlePlanStatusChange(scope.row, val)" />
          </template>
        </el-table-column>
        <el-table-column label="操作" align="center" width="260">
          <template #default="scope">
            <el-button link type="primary" icon="View" @click="handleViewPlanDetail(scope.row)">详情</el-button>
            <el-button link type="primary" icon="Edit" @click="handleEditPlan(scope.row)" v-hasPermi="['business:plan:edit']" v-if="scope.row.auditStatus === '0' || scope.row.auditStatus === '4'">编辑</el-button>
            <el-button link type="primary" icon="Check" @click="handleSubmitAudit(scope.row)" v-hasPermi="['business:plan:submitAudit']" v-if="scope.row.auditStatus === '0' || scope.row.auditStatus === '4'">提交审核</el-button>
            <el-button link type="primary" icon="Select" @click="handleAuditPlan(scope.row, true)" v-hasPermi="['business:plan:audit']" v-if="scope.row.auditStatus === '1'">通过</el-button>
            <el-button link type="danger" icon="CloseBold" @click="handleAuditPlan(scope.row, false)" v-hasPermi="['business:plan:audit']" v-if="scope.row.auditStatus === '1'">驳回</el-button>
            <el-button link type="primary" icon="Van" @click="handleCreateShipment(scope.row)" v-hasPermi="['business:shipment:add']" v-if="scope.row.auditStatus === '2'">出货</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>

    <el-dialog :title="planTitle" v-model="planOpen" width="1200px" append-to-body>
      <el-form ref="planRef" :model="planForm" :rules="planRules" label-width="100px">
        <el-row>
          <el-col :span="12">
            <el-form-item label="方案名称" prop="planName">
              <el-input v-model="planForm.planName" placeholder="请输入方案名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="分成比例(%)" prop="commissionRate">
              <el-input-number v-model="planForm.commissionRate" :precision="2" :min="0" :max="100" controls-position="right" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="方案金额" prop="planAmount">
              <el-input-number v-model="planForm.planAmount" :precision="2" :min="0" controls-position="right" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="配赠金额" prop="giftAmount">
              <el-input-number v-model="planForm.giftAmount" :precision="2" :min="0" controls-position="right" style="width: 100%" @change="onGiftAmountChange" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="生效日期" prop="effectiveDate">
              <el-date-picker v-model="planForm.effectiveDate" type="date" value-format="YYYY-MM-DD" placeholder="选择生效日期" style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="失效日期" prop="expiryDate">
              <el-date-picker v-model="planForm.expiryDate" type="date" value-format="YYYY-MM-DD" placeholder="选择失效日期" style="width: 100%" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="12">
            <el-form-item label="剩余金额">
              <el-input-number v-model="planForm.remainingAmount" :precision="2" :min="0" controls-position="right" disabled style="width: 100%" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="planForm.remark" placeholder="请输入备注" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-divider content-position="left">配赠明细</el-divider>
        <el-table :data="planForm.items" border style="width: 100%" size="small">
          <el-table-column label="货品名称" min-width="150">
            <template #default="scope">
              <el-select v-model="scope.row.productId" placeholder="选择货品" filterable remote :remote-method="searchProduct" @focus="() => searchProduct('')" @change="(val) => onProductSelect(scope.$index, val)" style="width: 100%">
                <el-option v-for="p in productOptions" :key="p.productId" :label="p.productName" :value="p.productId" />
              </el-select>
            </template>
          </el-table-column>
          <el-table-column label="供货商">
            <template #default="scope">
              <span>{{ scope.row.supplierName || '-' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="单位类型" min-width="120">
            <template #default="scope">
              <el-select v-model="scope.row.unitType" @change="onUnitTypeChange(scope.$index)" style="width: 100%">
                <el-option label="主单位-整" value="1" />
                <el-option label="副单位-拆" value="2" />
              </el-select>
            </template>
          </el-table-column>
          <el-table-column label="换算">
            <template #default="scope">
              <span v-if="scope.row.packQty > 1">1{{ scope.row.unitLabel }}={{ scope.row.packQty }}{{ scope.row.specLabel }}</span>
              <span v-else>-</span>
            </template>
          </el-table-column>
          <el-table-column label="数量">
            <template #default="scope">
              <el-input-number v-model="scope.row.quantity" :min="1" controls-position="right" @change="onItemQuantityChange(scope.$index)" style="width: 100%" />
            </template>
          </el-table-column>
          <el-table-column label="规格" width="60" align="center">
            <template #default="scope">
              <span>{{ scope.row.spec || '-' }}</span>
            </template>
          </el-table-column>
          <el-table-column label="单价" align="right">
            <template #default="scope">
              <span>{{ scope.row.salePrice || 0 }}</span>
            </template>
          </el-table-column>
          <el-table-column label="总金额" align="right">
            <template #default="scope">
              <span>{{ scope.row.amount || 0 }}</span>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="60" align="center">
            <template #default="scope">
              <el-button link type="danger" icon="Delete" @click="removePlanItem(scope.$index)" />
            </template>
          </el-table-column>
        </el-table>
        <el-button type="primary" link icon="Plus" @click="addPlanItem" style="margin-top: 10px">添加明细</el-button>
      </el-form>
      <template #footer>
        <el-button type="primary" @click="submitPlanForm">确 定</el-button>
        <el-button @click="planOpen = false">取 消</el-button>
      </template>
    </el-dialog>

    <el-dialog title="方案详情" v-model="planDetailOpen" width="900px" append-to-body>
      <el-descriptions :column="3" border>
        <el-descriptions-item label="方案编号">{{ currentPlan.planNo }}</el-descriptions-item>
        <el-descriptions-item label="方案名称">{{ currentPlan.planName }}</el-descriptions-item>
        <el-descriptions-item label="分成比例">{{ currentPlan.commissionRate }}%</el-descriptions-item>
        <el-descriptions-item label="方案金额">{{ currentPlan.planAmount }}</el-descriptions-item>
        <el-descriptions-item label="配赠金额">{{ currentPlan.giftAmount }}</el-descriptions-item>
        <el-descriptions-item label="剩余金额">{{ currentPlan.remainingAmount }}</el-descriptions-item>
        <el-descriptions-item label="生效日期">{{ currentPlan.effectiveDate }}</el-descriptions-item>
        <el-descriptions-item label="失效日期">{{ currentPlan.expiryDate }}</el-descriptions-item>
        <el-descriptions-item label="审核状态">
          <el-tag :type="auditStatusType(currentPlan.auditStatus)">{{ auditStatusLabel(currentPlan.auditStatus) }}</el-tag>
        </el-descriptions-item>
      </el-descriptions>
      <el-divider content-position="left">操作记录</el-divider>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="创建人">{{ currentPlan.createBy || '-' }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ currentPlan.createTime || '-' }}</el-descriptions-item>
        <el-descriptions-item label="提交审核人">{{ currentPlan.submitBy || '-' }}</el-descriptions-item>
        <el-descriptions-item label="提交审核时间">{{ currentPlan.submitTime || '-' }}</el-descriptions-item>
        <el-descriptions-item label="审核人">{{ currentPlan.auditBy || '-' }}</el-descriptions-item>
        <el-descriptions-item label="审核时间">{{ currentPlan.auditTime || '-' }}</el-descriptions-item>
        <el-descriptions-item label="审核备注" :span="2">{{ currentPlan.auditRemark || '-' }}</el-descriptions-item>
      </el-descriptions>
      <el-divider content-position="left">配赠明细</el-divider>
      <el-table :data="currentPlan.items || []" border size="small">
        <el-table-column label="货品名称" prop="productName" />
        <el-table-column label="供货商" prop="supplierName" />
        <el-table-column label="单位类型" width="80" align="center">
          <template #default="scope">{{ scope.row.unitType === '1' ? '主单位整' : '副单位拆' }}</template>
        </el-table-column>
        <el-table-column label="数量" prop="quantity" width="80" align="center" />
        <el-table-column label="规格" prop="spec" width="60" align="center" />
        <el-table-column label="单价" prop="salePrice" width="90" align="right" />
        <el-table-column label="总金额" prop="amount" width="100" align="right" />
        <el-table-column label="已出数量" prop="shippedQuantity" width="80" align="center" />
        <el-table-column label="剩余数量" prop="remainingQuantity" width="80" align="center" />
      </el-table>
      <el-divider content-position="left">出货记录</el-divider>
      <el-table :data="currentPlan.shipments || []" border size="small">
        <el-table-column label="出货单号" prop="shipmentNo" width="130" />
        <el-table-column label="总数量" prop="totalQuantity" width="80" align="center" />
        <el-table-column label="总金额" prop="totalAmount" width="100" align="right" />
        <el-table-column label="出货状态" width="80" align="center">
          <template #default="scope">
            <el-tag :type="shipmentStatusType(scope.row.shipmentStatus)" size="small">{{ shipmentStatusLabel(scope.row.shipmentStatus) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" prop="createTime" width="160" />
      </el-table>
    </el-dialog>

    <el-dialog title="新建出货单" v-model="shipmentOpen" width="950px" append-to-body>
      <el-form ref="shipmentRef" :model="shipmentForm" :rules="shipmentRules" label-width="100px">
        <el-row>
          <el-col :span="12">
            <el-form-item label="关联方案">
              <el-input v-model="shipmentForm.planName" disabled />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="企业名称">
              <el-input v-model="shipmentForm.enterpriseName" disabled />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            <el-form-item label="收货人" prop="contactPerson">
              <el-input v-model="shipmentForm.contactPerson" placeholder="收货人" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="收货电话" prop="contactPhone">
              <el-input v-model="shipmentForm.contactPhone" placeholder="收货电话" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="收货地址" prop="shippingAddress">
              <el-input v-model="shipmentForm.shippingAddress" placeholder="收货地址" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-divider content-position="left">出货明细</el-divider>
        <el-table :data="shipmentForm.items" border style="width: 100%" size="small">
          <el-table-column label="货品名称" width="150">
            <template #default="scope">{{ scope.row.productName }}</template>
          </el-table-column>
          <el-table-column label="供货商" width="120">
            <template #default="scope">{{ scope.row.supplierName || '-' }}</template>
          </el-table-column>
          <el-table-column label="单位类型" width="80" align="center">
            <template #default="scope">{{ scope.row.unitType === '1' ? '主单位整' : '副单位拆' }}</template>
          </el-table-column>
          <el-table-column label="换算" width="90">
            <template #default="scope">
              <span v-if="scope.row.packQty > 1">1{{ scope.row.unitLabel }}={{ scope.row.packQty }}{{ scope.row.specLabel }}</span>
              <span v-else>-</span>
            </template>
          </el-table-column>
          <el-table-column label="数量" width="80">
            <template #default="scope">
              <el-input-number v-model="scope.row.quantity" :min="1" :max="scope.row.maxQuantity" controls-position="right" size="small" @change="onShipmentItemChange(scope.$index)" style="width: 100%" />
            </template>
          </el-table-column>
          <el-table-column label="规格" width="60">
            <template #default="scope">{{ scope.row.specLabel || '-' }}</template>
          </el-table-column>
          <el-table-column label="单价" width="80" align="right">
            <template #default="scope">{{ scope.row.salePrice }}</template>
          </el-table-column>
          <el-table-column label="折扣单价" width="90">
            <template #default="scope">
              <el-input-number v-model="scope.row.discountPrice" :precision="2" :min="0" controls-position="right" size="small" @change="onShipmentItemChange(scope.$index)" style="width: 100%" />
            </template>
          </el-table-column>
          <el-table-column label="总金额" width="90" align="right">
            <template #default="scope">{{ scope.row.amount }}</template>
          </el-table-column>
          <el-table-column label="操作" width="60" align="center">
            <template #default="scope">
              <el-button link type="danger" icon="Delete" @click="removeShipmentItem(scope.$index)" />
            </template>
          </el-table-column>
        </el-table>
        <div style="margin-top: 10px; display: flex; justify-content: space-between">
          <span>总金额: {{ shipmentTotalAmount }}</span>
          <span>方案剩余金额: {{ shipmentForm.remainingAmount }}</span>
        </div>
        <el-row style="margin-top: 10px">
          <el-col :span="24">
            <el-form-item label="备注" prop="remark">
              <el-input v-model="shipmentForm.remark" type="textarea" placeholder="请输入备注" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <template #footer>
        <el-button type="primary" @click="submitShipmentForm">确 定</el-button>
        <el-button @click="shipmentOpen = false">取 消</el-button>
      </template>
    </el-dialog>

    <el-dialog title="企业详情" v-model="detailOpen" width="700px" append-to-body>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="企业名称">{{ detailForm.enterpriseName }}</el-descriptions-item>
        <el-descriptions-item label="老板姓名">{{ detailForm.bossName }}</el-descriptions-item>
        <el-descriptions-item label="联系电话">{{ detailForm.phone }}</el-descriptions-item>
        <el-descriptions-item label="企业类型">
          <dict-tag :options="biz_enterprise_type" :value="detailForm.enterpriseType" />
        </el-descriptions-item>
        <el-descriptions-item label="地址" :span="2">{{ detailForm.address || '-' }}</el-descriptions-item>
        <el-descriptions-item label="门店数量">{{ detailForm.storeCount }}</el-descriptions-item>
        <el-descriptions-item label="年业绩">{{ detailForm.annualPerformance }}</el-descriptions-item>
        <el-descriptions-item label="企业级别">
          <dict-tag :options="biz_enterprise_level" :value="detailForm.enterpriseLevel" />
        </el-descriptions-item>
        <el-descriptions-item label="服务人">{{ detailForm.serverUserName || '-' }}</el-descriptions-item>
        <el-descriptions-item label="开始合作">{{ detailForm.cooperationStartDate || '-' }}</el-descriptions-item>
        <el-descriptions-item label="结束合作">{{ detailForm.cooperationEndDate || '-' }}</el-descriptions-item>
        <el-descriptions-item label="状态">
          <dict-tag :options="sys_normal_disable" :value="detailForm.status" />
        </el-descriptions-item>
        <el-descriptions-item label="方案数量">{{ detailForm.planCount || 0 }}</el-descriptions-item>
        <el-descriptions-item label="备注" :span="2">{{ detailForm.remark || '-' }}</el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <el-button @click="detailOpen = false">关 闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup name="Enterprise">
import { listEnterprise, getEnterprise, delEnterprise, addEnterprise, updateEnterprise, changeEnterpriseStatus } from "@/api/business/enterprise"
import { listPlan, getPlan, addPlan, updatePlan, submitAuditPlan, auditPlan, changePlanStatus } from "@/api/business/plan"
import { addShipment } from "@/api/business/shipment"
import { listProduct } from "@/api/wms/product"
import { listUser } from "@/api/system/user"

const { proxy } = getCurrentInstance()
const { sys_normal_disable, biz_enterprise_type, biz_enterprise_level } = useDict("sys_normal_disable", "biz_enterprise_type", "biz_enterprise_level")

const enterpriseList = ref([])
const open = ref(false)
const loading = ref(true)
const showSearch = ref(true)
const ids = ref([])
const single = ref(true)
const multiple = ref(true)
const total = ref(0)
const title = ref("")
const planListOpen = ref(false)
const planList = ref([])
const planListLoading = ref(false)
const planOpen = ref(false)
const planTitle = ref("")
const planDetailOpen = ref(false)
const currentPlan = ref({})
const currentEnterprise = ref({})
const shipmentOpen = ref(false)
const detailOpen = ref(false)
const detailForm = ref({})
const productOptions = ref([])
const serverUserOptions = ref([])

const data = reactive({
  form: {},
  queryParams: {
    pageNum: 1, pageSize: 10,
    enterpriseName: undefined, bossName: undefined, phone: undefined,
    enterpriseType: undefined, enterpriseLevel: undefined, status: undefined
  },
  planForm: {},
  shipmentForm: {},
  rules: {
    enterpriseName: [{ required: true, message: "企业名称不能为空", trigger: "blur" }],
    bossName: [{ required: true, message: "老板姓名不能为空", trigger: "blur" }],
    phone: [{ required: true, message: "联系电话不能为空", trigger: "blur" }],
    enterpriseType: [{ required: true, message: "企业类型不能为空", trigger: "change" }],
    enterpriseLevel: [{ required: true, message: "企业级别不能为空", trigger: "change" }]
  },
  planRules: {
    planName: [{ required: true, message: "方案名称不能为空", trigger: "blur" }],
    giftAmount: [{ required: true, message: "配赠金额不能为空", trigger: "blur" }]
  },
  shipmentRules: {
    contactPerson: [{ required: true, message: "收货人不能为空", trigger: "blur" }]
  }
})

const { queryParams, form, rules, planForm, shipmentForm, planRules, shipmentRules } = toRefs(data)

const shipmentTotalAmount = computed(() => {
  return (shipmentForm.value.items || []).reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0).toFixed(2)
})

function auditStatusType(status) {
  const map = { '0': 'info', '1': 'warning', '2': 'success', '3': '', '4': 'danger' }
  return map[status] || 'info'
}

function auditStatusLabel(status) {
  const map = { '0': '草稿', '1': '待审核', '2': '已审核', '3': '已完成', '4': '已驳回' }
  return map[status] || '未知'
}

function shipmentStatusType(status) {
  const map = { '0': 'warning', '1': '', '2': 'info', '3': 'success', '4': 'danger' }
  return map[status] || 'info'
}

function shipmentStatusLabel(status) {
  const map = { '0': '待审核', '1': '已审核', '2': '已发货', '3': '已收货', '4': '已驳回' }
  return map[status] || '未知'
}

function getList() {
  loading.value = true
  listEnterprise(queryParams.value).then(response => {
    enterpriseList.value = response.rows
    total.value = response.total
    loading.value = false
  })
}

function searchServerUser(query) {
  listUser({ nickName: query || '', status: '0', pageNum: 1, pageSize: 20 }).then(res => {
    serverUserOptions.value = res.rows || []
  })
}

function onServerUserChange(userId) {
  if (userId) {
    const user = serverUserOptions.value.find(u => u.userId === userId)
    form.value.serverUserName = user?.nickName || ''
  } else {
    form.value.serverUserName = undefined
  }
}

function cancel() {
  open.value = false
  reset()
}

function reset() {
  form.value = {
    enterpriseId: undefined, enterpriseName: undefined, bossName: undefined, phone: undefined,
    address: undefined, enterpriseType: "1", storeCount: 0, annualPerformance: 0,
    enterpriseLevel: "3", serverUserId: undefined, serverUserName: undefined,
    cooperationStartDate: undefined, cooperationEndDate: undefined, status: "0", remark: undefined
  }
  proxy.resetForm("enterpriseRef")
}

function handleQuery() {
  queryParams.value.pageNum = 1
  getList()
}

function resetQuery() {
  proxy.resetForm("queryRef")
  handleQuery()
}

function handleSelectionChange(selection) {
  ids.value = selection.map(item => item.enterpriseId)
  single.value = selection.length != 1
  multiple.value = !selection.length
}

function handleAdd() {
  reset()
  open.value = true
  title.value = "添加企业"
}

function handleUpdate(row) {
  reset()
  const enterpriseId = row.enterpriseId || ids.value
  getEnterprise(enterpriseId).then(response => {
    form.value = response.data
    if (form.value.serverUserId) {
      searchServerUser('')
    }
    open.value = true
    title.value = "修改企业"
  })
}

function handleStatusChange(row, val) {
  const text = val === "0" ? "启用" : "停用"
  proxy.$modal.confirm('确认要"' + text + '""' + row.enterpriseName + '"吗？').then(() => {
    return changeEnterpriseStatus(row.enterpriseId, val)
  }).then(() => {
    proxy.$modal.msgSuccess(text + "成功")
  }).catch(() => {
    row.status = val === "0" ? "1" : "0"
  })
}

function submitForm() {
  proxy.$refs["enterpriseRef"].validate(valid => {
    if (valid) {
      if (form.value.enterpriseId != undefined) {
        updateEnterprise(form.value).then(() => {
          proxy.$modal.msgSuccess("修改成功")
          open.value = false
          getList()
        })
      } else {
        addEnterprise(form.value).then(() => {
          proxy.$modal.msgSuccess("新增成功")
          open.value = false
          getList()
        })
      }
    }
  })
}

function handleDelete(row) {
  const enterpriseIds = row.enterpriseId || ids.value
  proxy.$modal.confirm('是否确认删除企业编号为"' + enterpriseIds + '"的数据项？').then(function() {
    return delEnterprise(enterpriseIds)
  }).then(() => {
    getList()
    proxy.$modal.msgSuccess("删除成功")
  }).catch(() => {})
}

function handleViewPlans(row) {
  currentEnterprise.value = row
  planListOpen.value = true
  planListLoading.value = true
  listPlan({ enterpriseId: row.enterpriseId, pageNum: 1, pageSize: 100 }).then(res => {
    planList.value = res.rows
    planListLoading.value = false
  })
}

function handleViewDetail(row) {
  getEnterprise(row.enterpriseId).then(response => {
    detailForm.value = response.data
    detailOpen.value = true
  })
}

function handleRowClick(row) {
  handleViewDetail(row)
}

function handleAddPlan(row) {
  resetPlanForm()
  currentEnterprise.value = row
  planForm.value.enterpriseId = row.enterpriseId
  planForm.value.planName = row.enterpriseName + ' ' + '0%方案'
  planOpen.value = true
  planTitle.value = "开方案 - " + row.enterpriseName
}

function handleEditPlan(row) {
  getPlan(row.planId).then(res => {
    planForm.value = { ...res.data, items: (res.data.items || []).map(item => ({ ...item })) }
    planOpen.value = true
    planTitle.value = "修改方案"
  })
}

function resetPlanForm() {
  planForm.value = {
    planId: undefined, enterpriseId: undefined, planName: undefined,
    commissionRate: 0, planAmount: 0, giftAmount: 0, remainingAmount: 0,
    effectiveDate: undefined, expiryDate: undefined, remark: undefined, items: []
  }
  proxy.resetForm("planRef")
}

function onGiftAmountChange() {
  planForm.value.remainingAmount = planForm.value.giftAmount
}

function addPlanItem() {
  planForm.value.items.push({
    productId: undefined, productName: '', supplierId: undefined, supplierName: '',
    unitType: '1', packQty: 1, quantity: 1, spec: '', salePrice: 0, amount: 0,
    unitLabel: '', specLabel: '', _mainPrice: null
  })
}

function removePlanItem(index) { planForm.value.items.splice(index, 1) }

function searchProduct(query) {
  listProduct({ productName: query || '', status: '0', pageNum: 1, pageSize: 20 }).then(res => {
    productOptions.value = res.rows || []
  })
}

function onProductSelect(index, productId) {
  const product = productOptions.value.find(p => p.productId === productId)
  if (product) {
    const item = planForm.value.items[index]
    item.productId = product.productId
    item.productName = product.productName
    item.supplierId = product.supplierId
    item.supplierName = product.supplierName || ''
    item.packQty = product.packQty || 1
    item.unitType = '1'
    item._mainPrice = product.salePrice || 0
    item.salePrice = product.salePrice || 0
    const unitMap = { '1': '箱', '2': '件', '3': '套', '4': '罐', '5': '盒', '6': '袋', '7': '包' }
    const specMap = { '1': '支', '2': '瓶', '3': '件', '4': '套', '5': '片', '6': '个' }
    item.unitLabel = unitMap[product.unit] || ''
    item.specLabel = specMap[product.spec] || ''
    onUnitTypeChange(index)
  }
}

function onUnitTypeChange(index) {
  const item = planForm.value.items[index]
  const packQty = item.packQty || 1
  if (item.unitType === '1') {
    if (item._mainPrice) item.salePrice = item._mainPrice
    item.spec = item.unitLabel || ''
  } else {
    if (!item._mainPrice && item.salePrice) item._mainPrice = item.salePrice
    if (item._mainPrice && packQty > 0) item.salePrice = Math.round((item._mainPrice / packQty) * 100) / 100
    item.spec = item.specLabel || ''
  }
  onItemQuantityChange(index)
}

function onItemQuantityChange(index) {
  const item = planForm.value.items[index]
  item.amount = (parseFloat(item.salePrice) || 0) * (parseInt(item.quantity) || 0)
}

function submitPlanForm() {
  proxy.$refs["planRef"].validate(valid => {
    if (valid) {
      if (planForm.value.planId != undefined) {
        updatePlan(planForm.value).then(() => { proxy.$modal.msgSuccess("修改成功"); planOpen.value = false; handleViewPlans(currentEnterprise.value); getList() })
      } else {
        addPlan(planForm.value).then(() => { proxy.$modal.msgSuccess("新增成功"); planOpen.value = false; handleViewPlans(currentEnterprise.value); getList() })
      }
    }
  })
}

function handleSubmitAudit(row) {
  proxy.$modal.confirm('确认提交审核？').then(() => {
    return submitAuditPlan(row.planId)
  }).then(() => { proxy.$modal.msgSuccess("提交成功"); handleViewPlans(currentEnterprise.value) }).catch(() => {})
}

function handleAuditPlan(row, passed) {
  const text = passed ? '通过' : '驳回'
  proxy.$modal.confirm('确认' + text + '？').then(() => {
    return auditPlan({ planId: row.planId, passed })
  }).then(() => { proxy.$modal.msgSuccess(text + "成功"); handleViewPlans(currentEnterprise.value) }).catch(() => {})
}

function handlePlanStatusChange(row, val) {
  const text = val === "0" ? "启用" : "停用"
  proxy.$modal.confirm('确认"' + text + '"该方案？').then(() => {
    return changePlanStatus(row.planId, val)
  }).then(() => { proxy.$modal.msgSuccess(text + "成功") }).catch(() => { row.status = val === "0" ? "1" : "0" })
}

function handleViewPlanDetail(row) {
  getPlan(row.planId).then(res => {
    currentPlan.value = res.data
    planDetailOpen.value = true
  })
}

function handleCreateShipment(row) {
  getPlan(row.planId).then(res => {
    const plan = res.data
    shipmentForm.value = {
      planId: plan.planId, planName: plan.planName, enterpriseId: plan.enterpriseId,
      enterpriseName: plan.enterprise?.enterpriseName || '',
      contactPerson: plan.enterprise?.bossName || '', contactPhone: plan.enterprise?.phone || '',
      shippingAddress: plan.enterprise?.address || '', remainingAmount: plan.remainingAmount, remark: undefined,
      items: (plan.items || []).filter(item => item.remainingQuantity > 0).map(item => ({
        planItemId: item.itemId, productId: item.productId, productName: item.productName,
        supplierId: item.supplierId, supplierName: item.supplierName, unitType: item.unitType,
        packQty: item.packQty, quantity: item.remainingQuantity, maxQuantity: item.remainingQuantity,
        spec: item.spec, salePrice: item.salePrice, discountPrice: item.salePrice,
        amount: (parseFloat(item.salePrice) || 0) * item.remainingQuantity,
        unitLabel: item.unitType === '1' ? '' : '', specLabel: item.spec || ''
      }))
    }
    shipmentOpen.value = true
  })
}

function onShipmentItemChange(index) {
  const item = shipmentForm.value.items[index]
  item.amount = (parseFloat(item.discountPrice) || 0) * (parseInt(item.quantity) || 0)
}

function removeShipmentItem(index) { shipmentForm.value.items.splice(index, 1) }

function submitShipmentForm() {
  if (!shipmentForm.value.items || shipmentForm.value.items.length === 0) {
    proxy.$modal.msgWarning("请至少添加一条出货明细")
    return
  }
  if (parseFloat(shipmentTotalAmount.value) > parseFloat(shipmentForm.value.remainingAmount)) {
    proxy.$modal.msgWarning("出货总金额不能大于方案剩余金额")
    return
  }
  proxy.$refs["shipmentRef"].validate(valid => {
    if (valid) {
      addShipment(shipmentForm.value).then(res => {
        if (res.code === 200) {
          proxy.$modal.msgSuccess("新建出货单成功")
          shipmentOpen.value = false
          handleViewPlans(currentEnterprise.value)
        } else {
          proxy.$modal.msgError(res.msg || "新建出货单失败")
        }
      })
    }
  })
}

getList()
</script>

<style scoped>
.pointer :deep(.el-table__body tr) {
  cursor: pointer;
}
</style>
