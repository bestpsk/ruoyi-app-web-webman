/**
 * @description 用户状态管理 - 登录认证与用户信息
 * @description Pinia store模块，管理用户Token、个人信息、角色和权限。
 * 提供登录（调用后端接口并存储Token）、获取用户信息（含初始/过期密码提示）、退出登录三个核心action
 */
import router from '@/router'
import cache from '@/plugins/cache'
import { ElMessageBox, } from 'element-plus'
import { login, logout, getInfo } from '@/api/login'
import { getToken, setToken, removeToken } from '@/utils/auth'
import { isHttp, isEmpty } from "@/utils/validate"
import useLockStore from '@/store/modules/lock'
import defAva from '@/assets/images/profile.jpg'

const useUserStore = defineStore(
  'user',
  {
    state: () => ({
      token: getToken(),    // JWT认证令牌，从Cookie初始化
      id: '',               // 用户ID
      name: '',             // 用户名（登录账号）
      realName: '',         // 用户真实姓名
      avatar: '',           // 用户头像URL
      roles: [],            // 用户角色列表，用于权限判断
      permissions: []       // 用户权限标识列表，用于按钮级权限控制
    }),
    actions: {
      /**
       * 用户登录：调用后端登录接口验证账号密码，成功后将Token存储到Cookie和state，
       * 同时解除屏幕锁定状态
       * @param {object} userInfo - 登录信息 { username, password, code, uuid }
       * @returns {Promise<void>}
       */
      login(userInfo) {
        const username = userInfo.username.trim()
        const password = userInfo.password
        const code = userInfo.code
        const uuid = userInfo.uuid
        return new Promise((resolve, reject) => {
          login(username, password, code, uuid).then(res => {
            setToken(res.token)
            this.token = res.token
            useLockStore().unlockScreen()
            resolve()
          }).catch(error => {
            reject(error)
          })
        })
      },
      /**
       * 获取当前登录用户的详细信息，包括角色、权限、头像等。
       * 首次加载或刷新页面后由路由守卫调用，同时检测初始密码和过期密码并弹窗提示
       * @returns {Promise<object>} 用户信息对象
       */
      getInfo() {
        return new Promise((resolve, reject) => {
          getInfo().then(res => {
            const user = res.user
            // 头像处理：空值使用默认头像，相对路径拼接API基础地址，外链直接使用
            let avatar = user.avatar || ""
            if (!isHttp(avatar)) {
              avatar = (isEmpty(avatar)) ? defAva : import.meta.env.VITE_APP_BASE_API + avatar
            }
            // 角色验证：后端未返回角色时分配默认角色，避免权限判断异常
            if (res.roles && res.roles.length > 0) {
              this.roles = res.roles
              this.permissions = res.permissions
            } else {
              this.roles = ['ROLE_DEFAULT']
            }
            this.id = user.userId
            this.name = user.userName
            this.realName = user.realName
            this.avatar = avatar
            // 缓存密码强度类型，用于修改密码时的规则校验
            cache.session.set('pwrChrtype', res.pwdChrtype)
            // 初始密码安全提示：首次登录未修改密码时提醒用户修改
            if(res.isDefaultModifyPwd) {
              ElMessageBox.confirm('您的密码还是初始密码，请修改密码！',  '安全提示', {  confirmButtonText: '确定',  cancelButtonText: '取消',  type: 'warning' }).then(() => {
                router.push({ name: 'Profile', params: { activeTab: 'resetPwd' } })
              }).catch(() => {})
            }
            // 密码过期提示：密码超过有效期时提醒用户尽快修改
            if(!res.isDefaultModifyPwd && res.isPasswordExpired) {
              ElMessageBox.confirm('您的密码已过期，请尽快修改密码！',  '安全提示', {  confirmButtonText: '确定',  cancelButtonText: '取消',  type: 'warning' }).then(() => {
                router.push({ name: 'Profile', params: { activeTab: 'resetPwd' } })
              }).catch(() => {})
            }
            resolve(res)
          }).catch(error => {
            reject(error)
          })
        })
      },
      /**
       * 退出登录：调用后端退出接口，清除本地Token、角色和权限数据
       * @returns {Promise<void>}
       */
      logOut() {
        return new Promise((resolve, reject) => {
          logout(this.token).then(() => {
            this.token = ''
            this.roles = []
            this.permissions = []
            removeToken()
            resolve()
          }).catch(error => {
            reject(error)
          })
        })
      }
    }
  })

export default useUserStore
