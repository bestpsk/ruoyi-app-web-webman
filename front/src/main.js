/**
 * @description 应用入口 - Vue应用初始化与全局注册
 * @description 创建Vue应用实例，注册ElementPlus组件库、全局组件、全局方法、自定义指令和插件，
 * 并根据Cookie中保存的UI尺寸偏好设置ElementPlus的默认尺寸
 */
import { createApp } from 'vue'

import Cookies from 'js-cookie'

import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import 'element-plus/theme-chalk/dark/css-vars.css'
import locale from 'element-plus/es/locale/lang/zh-cn'

import '@/assets/styles/index.scss'

import App from './App'
import store from './store'
import router from './router'
import directive from './directive'

import plugins from './plugins'
import { download } from '@/utils/request'

import 'virtual:svg-icons-register'
import SvgIcon from '@/components/SvgIcon'
import elementIcons from '@/components/SvgIcon/svgicon'

import './permission'

import { useDict } from '@/utils/dict'
import { getConfigKey } from "@/api/system/config"
import { parseTime, resetForm, addDateRange, handleTree, selectDictLabel, selectDictLabels } from '@/utils/ruoyi'

import Pagination from '@/components/Pagination'
import RightToolbar from '@/components/RightToolbar'
import Editor from "@/components/Editor"
import FileUpload from "@/components/FileUpload"
import ImageUpload from "@/components/ImageUpload"
import ImagePreview from "@/components/ImagePreview"
import DictTag from '@/components/DictTag'

const app = createApp(App)

// 挂载全局方法，使模板中可通过 this.xxx 直接调用
app.config.globalProperties.useDict = useDict
app.config.globalProperties.download = download
app.config.globalProperties.parseTime = parseTime
app.config.globalProperties.resetForm = resetForm
app.config.globalProperties.handleTree = handleTree
app.config.globalProperties.addDateRange = addDateRange
app.config.globalProperties.getConfigKey = getConfigKey
app.config.globalProperties.selectDictLabel = selectDictLabel
app.config.globalProperties.selectDictLabels = selectDictLabels

// 注册全局业务组件，无需在每个页面单独import即可直接使用
app.component('DictTag', DictTag)
app.component('Pagination', Pagination)
app.component('FileUpload', FileUpload)
app.component('ImageUpload', ImageUpload)
app.component('ImagePreview', ImagePreview)
app.component('RightToolbar', RightToolbar)
app.component('Editor', Editor)

app.use(router)
app.use(store)
app.use(plugins)
app.use(elementIcons)
app.component('svg-icon', SvgIcon)

directive(app)

// 注册ElementPlus并设置中文语言包和全局UI尺寸（从Cookie读取用户偏好，默认default）
app.use(ElementPlus, {
  locale: locale,
  size: Cookies.get('size') || 'default'
})

app.mount('#app')
