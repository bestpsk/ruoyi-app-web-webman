<script>
/**
 * @description 应用入口 - uni-app应用初始化与全局配置
 * @description 创建应用实例，加载全局配置，H5端自动检查登录状态并跳转登录页
 */
  import config from './config'
  import { getToken } from '@/utils/auth'

  export default {
    onLaunch() {
      this.initApp()
    },
    methods: {
      /** 初始化应用，加载全局配置并在H5端检查登录状态 */
      initApp() {
        this.globalData.config = config
        // #ifdef H5
        this.checkLogin()
        // #endif
      },
      /** H5端登录状态检查，未登录则跳转登录页 */
      checkLogin() {
        if (!getToken()) {
          uni.reLaunch({ url: '/pages/login' })
        }
      }
    },
    globalData: {
      config: {}
    }
  }
</script>

<style lang="scss">
  @import '@/static/scss/common.scss';
  @import 'uview-plus/theme.scss';
  @import 'uview-plus/index.scss';

  /* uview-plus 图标字体 */
  @font-face {
    font-family: 'uicon-iconfont';
    src: url('/static/uview-plus/uicon-iconfont.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: block;
  }
</style>
