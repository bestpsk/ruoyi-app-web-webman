/**
 * @description 权限校验工具 - 前端按钮级权限控制
 * @description 提供权限标识和角色的校验方法，用于v-if或代码中判断用户是否拥有某项操作权限。
 * 超级权限标识'*:*:*'拥有所有权限，admin角色拥有所有角色权限
 */
import { useUserStore } from '@/store/modules/user'

/**
 * 检查当前用户是否拥有指定权限标识，支持字符串或数组参数（OR逻辑，包含任一即通过）
 * @param {string|string[]} value - 权限标识，如'system:user:add'或['system:user:add','system:user:edit']
 * @returns {boolean} 拥有指定权限返回true
 */
export function checkPermi(value) {
  const userStore = useUserStore()
  const permissions = userStore.permissions
  // 超级权限标识，拥有此标识的用户不受权限限制
  const allPermission = '*:*:*'
  const permissionList = typeof value === 'string' ? [value] : value
  return permissions.some(permission => {
    return allPermission === permission || permissionList.includes(permission)
  })
}

/**
 * 检查当前用户是否拥有指定角色，支持字符串或数组参数（OR逻辑，包含任一即通过）
 * @param {string|string[]} value - 角色标识，如'admin'或['admin','editor']
 * @returns {boolean} 拥有指定角色返回true
 */
export function checkRole(value) {
  const userStore = useUserStore()
  const roles = userStore.roles
  // admin角色拥有所有权限，等同于超级管理员
  const superAdmin = 'admin'
  const roleList = typeof value === 'string' ? [value] : value
  return roles.some(role => {
    return superAdmin === role || roleList.includes(role)
  })
}
