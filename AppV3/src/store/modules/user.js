/**
 * @description 用户状态管理 - 登录认证与用户信息持久化
 * @description Pinia store模块，管理用户Token、个人信息、角色和权限。
 * 用户数据同时存储在state和本地存储（storage）中，确保应用重启后状态不丢失。
 * 提供登录（调用后端接口并存储Token）、获取用户信息（含头像路径处理）、退出登录三个核心action
 */
import { defineStore } from 'pinia'
import config from '@/config'
import storage from '@/utils/storage'
import constant from '@/utils/constant'
import { login, logout, getInfo } from '@/api/login'
import { getToken, setToken, removeToken } from '@/utils/auth'
import { isHttp, isEmpty } from '@/utils/validate'

const baseUrl = config.baseUrl
const defaultAvatar = '/static/images/profile.jpg'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: getToken(),                    // JWT认证令牌，从本地存储初始化
    id: storage.get(constant.id),         // 用户ID
    name: storage.get(constant.name),     // 用户名（登录账号）
    nickName: storage.get(constant.nickName) || '',  // 用户昵称
    avatar: storage.get(constant.avatar), // 用户头像URL
    roles: storage.get(constant.roles),   // 用户角色列表，用于权限判断
    permissions: storage.get(constant.permissions)  // 用户权限标识列表，用于按钮级权限控制
  }),
  getters: {
    getToken: (state) => state.token,
    getAvatar: (state) => state.avatar,
    getId: (state) => state.id,
    getName: (state) => state.name,
    getNickName: (state) => state.nickName,
    getRoles: (state) => state.roles,
    getPermissions: (state) => state.permissions
  },
  actions: {
    /**
     * 用户登录：调用后端登录接口验证账号密码，成功后将Token存储到本地存储和state
     * @param {object} userInfo - 登录信息 { username, password, code, uuid }
     * @returns {Promise<void>}
     */
    async loginAction(userInfo) {
      const username = userInfo.username.trim()
      const password = userInfo.password
      const code = userInfo.code
      const uuid = userInfo.uuid
      const res = await login(username, password, code, uuid)
      setToken(res.token)
      this.token = res.token
    },
    /**
     * 获取当前登录用户的详细信息，包括角色、权限、头像等。
     * 首次加载或刷新页面后由页面调用，同时将用户信息持久化到本地存储。
     * 头像处理逻辑：空值使用默认头像，相对路径拼接API基础地址，外链直接使用
     * @returns {Promise<object>} 用户信息对象
     */
    async getInfoAction() {
      const res = await getInfo()
      const user = res.user
      // 头像路径处理：空值→默认头像，相对路径→拼接baseUrl，外链→直接使用
      let avatar = user.avatar || ''
      if (!isHttp(avatar) && !avatar.startsWith('https://') && !avatar.startsWith('http://')) {
        avatar = isEmpty(avatar) ? defaultAvatar : baseUrl + avatar
      }
      const userid = isEmpty(user) || isEmpty(user.userId) ? '' : user.userId
      const username = isEmpty(user) || isEmpty(user.userName) ? '' : user.userName
      const nickname = isEmpty(user) || isEmpty(user.nickName) ? '' : user.nickName
      // 角色验证：后端未返回角色时分配默认角色，避免权限判断异常
      if (res.roles && res.roles.length > 0) {
        this.roles = res.roles
        this.permissions = res.permissions
        storage.set(constant.roles, res.roles)
        storage.set(constant.permissions, res.permissions)
      } else {
        this.roles = ['ROLE_DEFAULT']
        storage.set(constant.roles, ['ROLE_DEFAULT'])
      }
      // 将用户信息同步到state和本地存储，确保应用重启后可恢复
      this.id = userid
      this.name = username
      this.nickName = nickname
      this.avatar = avatar
      storage.set(constant.id, userid)
      storage.set(constant.name, username)
      storage.set(constant.nickName, nickname)
      storage.set(constant.avatar, avatar)
    },
    /**
     * 退出登录：调用后端退出接口，清除Token和本地存储中的所有用户数据
     * @returns {Promise<void>}
     */
    async logOut() {
      await logout()
      this.token = ''
      this.roles = []
      this.permissions = []
      removeToken()
      storage.clean()
    }
  }
})
