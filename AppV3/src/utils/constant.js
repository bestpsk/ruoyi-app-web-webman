/**
 * @description 存储键名常量 - 用户数据本地存储的键名映射
 * @description 定义用户相关数据在聚合存储中使用的键名常量，
 * 与storage.js配合使用，确保键名一致性和可维护性
 */
const constant = {
  /** 用户头像URL的存储键 */
  avatar: 'user_avatar',
  /** 用户ID的存储键 */
  id: 'user_id',
  /** 用户名（登录账号）的存储键 */
  name: 'user_name',
  /** 用户昵称的存储键 */
  nickName: 'user_nick_name',
  /** 用户角色列表的存储键 */
  roles: 'user_roles',
  /** 用户权限标识列表的存储键 */
  permissions: 'user_permissions'
}

export default constant
