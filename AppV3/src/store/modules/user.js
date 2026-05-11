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
    token: getToken(),
    id: storage.get(constant.id),
    name: storage.get(constant.name),
    nickName: storage.get(constant.nickName) || '',
    avatar: storage.get(constant.avatar),
    roles: storage.get(constant.roles),
    permissions: storage.get(constant.permissions)
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
    async loginAction(userInfo) {
      const username = userInfo.username.trim()
      const password = userInfo.password
      const code = userInfo.code
      const uuid = userInfo.uuid
      const res = await login(username, password, code, uuid)
      setToken(res.token)
      this.token = res.token
    },
    async getInfoAction() {
      const res = await getInfo()
      const user = res.user
      let avatar = user.avatar || ''
      if (!isHttp(avatar)) {
        avatar = isEmpty(avatar) ? defaultAvatar : baseUrl + avatar
      }
      const userid = isEmpty(user) || isEmpty(user.userId) ? '' : user.userId
      const username = isEmpty(user) || isEmpty(user.userName) ? '' : user.userName
      const nickname = isEmpty(user) || isEmpty(user.nickName) ? '' : user.nickName
      if (res.roles && res.roles.length > 0) {
        this.roles = res.roles
        this.permissions = res.permissions
        storage.set(constant.roles, res.roles)
        storage.set(constant.permissions, res.permissions)
      } else {
        this.roles = ['ROLE_DEFAULT']
        storage.set(constant.roles, ['ROLE_DEFAULT'])
      }
      this.id = userid
      this.name = username
      this.nickName = nickname
      this.avatar = avatar
      storage.set(constant.id, userid)
      storage.set(constant.name, username)
      storage.set(constant.nickName, nickname)
      storage.set(constant.avatar, avatar)
    },
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
