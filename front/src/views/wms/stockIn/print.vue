<template>
  <div class="print-container">
    <div class="print-header">
      <h1>入 库 单</h1>
    </div>
    <div class="print-info">
      <div class="info-row">
        <div class="info-item">
          <span class="label">入库单号：</span>
          <span class="value">{{ data.stockInNo }}</span>
        </div>
        <div class="info-item">
          <span class="label">入库日期：</span>
          <span class="value">{{ data.stockInDate }}</span>
        </div>
      </div>
      <div class="info-row">
        <div class="info-item">
          <span class="label">入库类型：</span>
          <span class="value">{{ getStockInTypeLabel(data.stockInType) }}</span>
        </div>
        <div class="info-item">
          <span class="label">操作人：</span>
          <span class="value">{{ data.operatorName || '-' }}</span>
        </div>
      </div>
      <div class="info-row" v-if="data.remark">
        <div class="info-item full">
          <span class="label">备注：</span>
          <span class="value">{{ data.remark }}</span>
        </div>
      </div>
    </div>
    <table class="print-table">
      <thead>
        <tr>
          <th width="50">序号</th>
          <th>货品名称</th>
          <th width="60">规格</th>
          <th width="60">单位</th>
          <th width="80">数量</th>
          <th width="100">单价</th>
          <th width="100">金额</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in data.items" :key="index">
          <td align="center">{{ index + 1 }}</td>
          <td>{{ item.productName }}</td>
          <td align="center">{{ getSpecLabel(item.spec) }}</td>
          <td align="center">{{ item.unitType === '1' ? getUnitLabel(item.unit) : getSpecLabel(item.spec) }}</td>
          <td align="center">{{ item.quantity }}</td>
          <td align="right">{{ item.purchasePrice }}</td>
          <td align="right">{{ item.amount }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4" align="right"><strong>合计：</strong></td>
          <td align="center"><strong>{{ data.totalQuantity }}</strong></td>
          <td></td>
          <td align="right"><strong>{{ data.totalAmount }}</strong></td>
        </tr>
      </tfoot>
    </table>
    <div class="print-footer">
      <div class="footer-row">
        <div class="footer-item">
          <span class="label">制单人：</span>
          <span class="value">{{ data.operatorName || '' }}</span>
        </div>
        <div class="footer-item">
          <span class="label">经手人：</span>
          <span class="value underline"></span>
        </div>
        <div class="footer-item">
          <span class="label">日期：</span>
          <span class="value">{{ data.stockInDate }}</span>
        </div>
      </div>
      <div class="footer-row sign-row">
        <div class="footer-item">
          <span class="label">签字：</span>
          <span class="value underline-long"></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  },
  dict: {
    type: Object,
    default: () => ({})
  }
})

function getStockInTypeLabel(value) {
  if (!props.dict.biz_stock_in_type) return value
  const item = props.dict.biz_stock_in_type.find(d => d.value === value)
  return item ? item.label : value
}

function getUnitLabel(value) {
  if (!value) return ''
  if (!props.dict.biz_product_unit) return value
  const item = props.dict.biz_product_unit.find(d => d.value === value)
  return item ? item.label : value
}

function getSpecLabel(value) {
  if (!value) return ''
  if (!props.dict.biz_product_spec) return value
  const item = props.dict.biz_product_spec.find(d => d.value === value)
  return item ? item.label : value
}
</script>

<style scoped>
.print-container {
  width: 190mm;
  padding: 10mm;
  margin: 0 auto;
  font-family: SimSun, serif;
  font-size: 12pt;
  color: #000;
  background: #fff;
}

.print-header {
  text-align: center;
  margin-bottom: 20px;
}

.print-header h1 {
  font-size: 18pt;
  font-weight: bold;
  margin: 0;
  letter-spacing: 10px;
}

.print-info {
  margin-bottom: 15px;
}

.info-row {
  display: flex;
  margin-bottom: 8px;
}

.info-item {
  flex: 1;
}

.info-item.full {
  flex: 2;
}

.info-item .label {
  color: #333;
}

.info-item .value {
  color: #000;
}

.print-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 15px;
}

.print-table th,
.print-table td {
  border: 1px solid #000;
  padding: 6px 8px;
}

.print-table th {
  background: #f5f5f5;
  font-weight: bold;
}

.print-table tfoot td {
  background: #f9f9f9;
}

.print-footer {
  margin-top: 30px;
}

.footer-row {
  display: flex;
  margin-bottom: 15px;
}

.footer-item {
  flex: 1;
}

.sign-row {
  margin-top: 40px;
}

.underline {
  display: inline-block;
  width: 60px;
  border-bottom: 1px solid #000;
}

.underline-long {
  display: inline-block;
  width: 150px;
  border-bottom: 1px solid #000;
}

@media print {
  body {
    margin: 0;
    padding: 0;
  }
  
  .print-container {
    width: 100%;
    padding: 0;
    margin: 0;
  }
}
</style>
