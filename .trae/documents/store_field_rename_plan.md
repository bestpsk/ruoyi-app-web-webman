# 门店管理模块修改计划

## 需求概述
1. 创建人和状态换个位置
2. 创建人显示用户姓名而不是账号
3. 把后台数据库昵称字段统一改为姓名字段

---

## 修改步骤

### 步骤1：修改前端门店页面 - 调整字段位置和创建人显示
**文件**: `front/src/views/business/store/index.vue`

#### 1.1 表单中创建人和状态换位置
将创建人移到状态前面，与状态同行显示

#### 1.2 创建人显示用户姓名
将 `userStore.name`（账号）改为 `userStore.nickName`（用户姓名）

### 步骤2：数据库字段重命名
**影响范围**：将 `nick_name` 字段重命名为 `real_name`

#### 2.1 需要修改的数据库表
- `sys_user` 表：`nick_name` → `real_name`

#### 2.2 需要修改的后端文件
| 文件 | 修改内容 |
|------|----------|
| `webman/app/model/SysUser.php` | fillable字段名修改 |
| `webman/app/service/SysUserService.php` | 字段引用修改 |
| `webman/app/service/BizScheduleService.php` | 字段引用修改 |
| `webman/app/service/SysNoticeService.php` | 字段引用修改 |
| `webman/app/controller/business/BizAttendanceController.php` | 字段引用修改 |
| `webman/app/controller/wms/BizStockCheckController.php` | 字段引用修改 |
| `webman/app/controller/wms/BizStockInController.php` | 字段引用修改 |

#### 2.3 需要修改的前端文件
| 文件 | 修改内容 |
|------|----------|
| `front/src/store/modules/user.js` | nickName → realName |
| `front/src/views/system/user/index.vue` | nickName → realName |
| `front/src/views/business/store/index.vue` | nickName → realName |
| `front/src/views/business/schedule/index.vue` | nickName → realName |
| 其他使用nickName的文件 | 同步修改 |

---

## 详细修改内容

### 1. SQL修改语句
```sql
ALTER TABLE `sys_user` CHANGE COLUMN `nick_name` `real_name` varchar(30) DEFAULT '' COMMENT '用户姓名';
```

### 2. 前端字段映射修改
- `nickName` → `realName`
- 标签保持为"用户姓名"或"姓名"

### 3. 门店表单布局调整
```vue
<el-row>
  <el-col :span="12">
    <el-form-item label="服务员工" prop="serverUserId">
      <!-- 服务员工选择 -->
    </el-form-item>
  </el-col>
  <el-col :span="12">
    <el-form-item label="创建人">
      <el-input v-model="form.creatorName" disabled />
    </el-form-item>
  </el-col>
</el-row>
<el-row>
  <el-col :span="12">
    <el-form-item label="状态" prop="status">
      <!-- 状态选择 -->
    </el-form-item>
  </el-col>
</el-row>
```

---

## 文件修改清单

### 后端文件
| 文件路径 | 操作 |
|----------|------|
| `webman/sql/biz_store.sql` | 添加ALTER语句 |
| `webman/app/model/SysUser.php` | 修改字段名 |
| `webman/app/service/SysUserService.php` | 修改字段引用 |
| `webman/app/service/BizScheduleService.php` | 修改字段引用 |
| `webman/app/service/SysNoticeService.php` | 修改字段引用 |
| `webman/app/controller/business/BizAttendanceController.php` | 修改字段引用 |
| `webman/app/controller/wms/BizStockCheckController.php` | 修改字段引用 |
| `webman/app/controller/wms/BizStockInController.php` | 修改字段引用 |

### 前端文件
| 文件路径 | 操作 |
|----------|------|
| `front/src/views/business/store/index.vue` | 修改布局和字段名 |
| `front/src/store/modules/user.js` | 修改字段名 |
| `front/src/views/system/user/index.vue` | 修改字段名 |
| `front/src/views/business/schedule/index.vue` | 修改字段名 |
| 其他相关文件 | 同步修改 |
