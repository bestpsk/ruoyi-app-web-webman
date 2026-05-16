/**
 * @description 全局配置 - 应用基础信息和第三方服务配置
 * @description 定义后端API基础路径、应用名称/版本/Logo等元信息、隐私政策和用户协议链接，
 * 以及高德地图Web服务Key等第三方服务密钥
 */
export default {
  /** 后端API基础路径，与vite.config.js中的代理配置对应 */
  baseUrl: '/prod-api',
  /** 应用基本信息，用于关于页面、登录页等处展示 */
  appInfo: {
    name: "馥辰国际",
    version: "1.0.0",
    logo: "/static/logo.png",
    site_url: "https://fuchenpro.com",
    /** 法律合规文档链接，登录/注册页展示 */
    agreements: [
      { title: "隐私政策", url: "https://fuchenpro.com/privacy.html" },
      { title: "用户服务协议", url: "https://fuchenpro.com/service.html" }
    ]
  },
  /** 高德地图Web服务Key，用于考勤定位、门店地图等功能 */
  amap: {
    webServiceKey: 'd184e115457658cbcf3f92ed8e3a1772'
  }
}
