# 销售开单页面布局优化计划

## 问题现象
1. **对比照（图片上传）缩略图太大** - image-upload 组件没有限制显示大小
2. **左侧客户列表宽度太宽** - 当前占 25% (span=6)，需要变窄

## 修改方案

### 1. 左侧客户列表宽度调整
- 当前: `<el-col :span="6">` → 占页面宽度的 25%
- 调整为: `<el-col :span="5">` → 占页面宽度的 ~20.8%
- 右侧面板对应调整为: `<el-col :span="19">`

### 2. 对比照图片上传组件缩小
当前代码:
```vue
<image-upload v-model="scope.row.beforePhoto" :limit="2" :fileSize="5" />
```

修改为添加尺寸限制:
```vue
<image-upload v-model="scope.row.beforePhoto" :limit="2" :fileSize="5" width="60" height="60" />
```
- 设置 `width="60" height="60"` 使缩略图显示为 60x60px

### 3. 操作Tab中的图片上传同样处理
操作表单中的前后对比照也需要同样缩小。

---

## 修改文件
| 文件 | 修改内容 |
|------|----------|
| `front/src/views/business/sales/index.vue` | 布局比例 + 图片尺寸 |

## 具体修改位置

1. **第22行**: `el-col span="6"` → `el-col span="5"`
2. **第44行**: `el-col span="18"` → `el-col span="19"`
3. **第92行**: 开单表格中 beforePhoto 的 image-upload 添加 `width="60" height="60"`
4. **操作Tab中的 image-upload** (约146/149行): 同样添加尺寸限制
