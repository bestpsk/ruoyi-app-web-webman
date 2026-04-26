<script>
  import config from './config'
  import { getToken } from '@/utils/auth'

  export default {
    onLaunch() {
      this.initApp()
    },
    methods: {
      initApp() {
        this.globalData.config = config
        // #ifdef H5
        this.checkLogin()
        // #endif
      },
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
