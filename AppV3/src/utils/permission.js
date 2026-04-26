import { useUserStore } from '@/store/modules/user'

export function checkPermi(value) {
  const userStore = useUserStore()
  const permissions = userStore.permissions
  const allPermission = '*:*:*'
  const permissionList = typeof value === 'string' ? [value] : value
  return permissions.some(permission => {
    return allPermission === permission || permissionList.includes(permission)
  })
}

export function checkRole(value) {
  const userStore = useUserStore()
  const roles = userStore.roles
  const superAdmin = 'admin'
  const roleList = typeof value === 'string' ? [value] : value
  return roles.some(role => {
    return superAdmin === role || roleList.includes(role)
  })
}
