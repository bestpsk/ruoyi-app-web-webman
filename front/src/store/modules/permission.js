/**
 * @description 权限路由状态管理 - 动态路由加载与权限过滤
 * @description Pinia store模块，负责从后端获取路由数据、将路由字符串转换为Vue组件对象、
 * 动态注册路由到vue-router，以及基于角色/权限过滤前端定义的动态路由。
 * 核心流程：请求后端路由 → 转换组件路径 → 注册到router → 存储到state供侧边栏渲染
 */
import auth from '@/plugins/auth'
import router, { constantRoutes, dynamicRoutes } from '@/router'
import { getRouters } from '@/api/menu'
import Layout from '@/layout/index'
import ParentView from '@/components/ParentView'
import InnerLink from '@/layout/components/InnerLink'

// 使用Vite的import.meta.glob预加载所有views目录下的.vue文件，用于动态路由的组件匹配
const modules = import.meta.glob('./../../views/**/*.vue')

const usePermissionStore = defineStore(
  'permission',
  {
    state: () => ({
      routes: [],            // 完整路由列表（静态+动态），用于菜单渲染
      addRoutes: [],         // 动态添加的路由列表
      defaultRoutes: [],     // 默认路由列表
      topbarRouters: [],     // 顶部导航栏路由列表
      sidebarRouters: []     // 侧边栏路由列表
    }),
    actions: {
      /**
       * 设置动态路由列表，同时合并静态路由生成完整路由表
       * @param {Array} routes - 动态路由数组
       */
      setRoutes(routes) {
        this.addRoutes = routes
        this.routes = constantRoutes.concat(routes)
      },
      /**
       * 设置默认路由列表（静态路由+动态路由）
       * @param {Array} routes - 动态路由数组
       */
      setDefaultRoutes(routes) {
        this.defaultRoutes = constantRoutes.concat(routes)
      },
      /**
       * 设置顶部导航栏路由列表
       * @param {Array} routes - 路由数组
       */
      setTopbarRoutes(routes) {
        this.topbarRouters = routes
      },
      /**
       * 设置侧边栏路由列表
       * @param {Array} routes - 路由数组
       */
      setSidebarRouters(routes) {
        this.sidebarRouters = routes
      },
      /**
       * 从后端获取路由数据并生成可访问的动态路由，同时注册到vue-router。
       * 对后端返回的路由数据进行三份深拷贝处理，分别用于侧边栏渲染、路由重写和默认路由
       * @returns {Promise<Array>} 重写后的动态路由数组（用于router.addRoute）
       */
      generateRoutes(roles) {
        return new Promise(resolve => {
          getRouters().then(res => {
            // 三份深拷贝避免引用污染：侧边栏路由、重写路由、默认路由各自独立处理
            const sdata = JSON.parse(JSON.stringify(res.data))
            const rdata = JSON.parse(JSON.stringify(res.data))
            const defaultData = JSON.parse(JSON.stringify(res.data))
            const sidebarRoutes = filterAsyncRouter(sdata)
            const rewriteRoutes = filterAsyncRouter(rdata, false, true)
            const defaultRoutes = filterAsyncRouter(defaultData)
            // 过滤前端定义的动态路由（基于角色/权限）
            const asyncRoutes = filterDynamicRoutes(dynamicRoutes)
            asyncRoutes.forEach(route => { router.addRoute(route) })
            this.setRoutes(rewriteRoutes)
            this.setSidebarRouters(constantRoutes.concat(sidebarRoutes))
            this.setDefaultRoutes(sidebarRoutes)
            this.setTopbarRoutes(defaultRoutes)
            resolve(rewriteRoutes)
          })
        })
      }
    }
  })

/**
 * 递归遍历后端返回的路由数据，将组件路径字符串转换为实际的Vue组件对象。
 * 处理三种特殊组件：Layout（布局容器）、ParentView（父级视图占位）、InnerLink（内嵌iframe），
 * 其余组件通过loadView动态匹配views目录下的.vue文件
 * @param {Array} asyncRouterMap - 后端返回的路由配置数组
 * @param {boolean} [lastRouter=false] - 父级路由（filterChildren使用）
 * @param {boolean} [type=false] - 是否为重写模式，重写模式下会展开ParentView嵌套层级
 * @returns {Array} 转换后的路由配置数组
 */
function filterAsyncRouter(asyncRouterMap, lastRouter = false, type = false) {
  return asyncRouterMap.filter(route => {
    // 重写模式下，将ParentView嵌套的子路由提升到同一层级，避免多级菜单路由嵌套问题
    if (type && route.children) {
      route.children = filterChildren(route.children)
    }
    if (route.component) {
      // Layout：页面布局容器，作为一级菜单的组件
      if (route.component === 'Layout') {
        route.component = Layout
      } else if (route.component === 'ParentView') {
        // ParentView：二级菜单占位组件，本身不渲染内容，仅作为路由层级容器
        route.component = ParentView
      } else if (route.component === 'InnerLink') {
        // InnerLink：内嵌iframe组件，用于在系统内展示外部网页
        route.component = InnerLink
      } else {
        // 业务页面组件：根据路径字符串动态匹配views目录下的.vue文件
        route.component = loadView(route.component)
      }
    }
    if (route.children != null && route.children && route.children.length) {
      route.children = filterAsyncRouter(route.children, route, type)
    } else {
      // 叶子节点清除children和redirect，避免路由警告
      delete route['children']
      delete route['redirect']
    }
    return true
  })
}

/**
 * 递归展开ParentView嵌套的子路由，将多级嵌套的路由平铺为一级子路由。
 * 解决vue-router嵌套路由导致侧边栏多级菜单显示异常的问题
 * @param {Array} childrenMap - 子路由数组
 * @param {object} [lastRouter=false] - 上级路由对象，用于拼接完整路径
 * @returns {Array} 展平后的子路由数组
 */
function filterChildren(childrenMap, lastRouter = false) {
  var children = []
  childrenMap.forEach(el => {
    // 拼接完整路径：父路径/子路径
    el.path = lastRouter ? lastRouter.path + '/' + el.path : el.path
    if (el.children && el.children.length && el.component === 'ParentView') {
      // ParentView的子路由继续递归展开
      children = children.concat(filterChildren(el.children, el))
    } else {
      children.push(el)
    }
  })
  return children
}

/**
 * 根据当前用户的角色和权限过滤前端定义的动态路由。
 * 路由配置中的permissions字段使用OR逻辑（包含任一权限即通过），
 * roles字段同样使用OR逻辑（包含任一角色即通过）
 * @param {Array} routes - 前端定义的动态路由数组
 * @returns {Array} 过滤后当前用户有权限访问的路由数组
 */
export function filterDynamicRoutes(routes) {
  const res = []
  routes.forEach(route => {
    if (route.permissions) {
      if (auth.hasPermiOr(route.permissions)) {
        res.push(route)
      }
    } else if (route.roles) {
      if (auth.hasRoleOr(route.roles)) {
        res.push(route)
      }
    }
  })
  return res
}

/**
 * 根据组件路径字符串动态加载views目录下对应的.vue文件。
 * 使用Vite的import.meta.glob实现按需加载，路径匹配规则为views/目录下的所有.vue文件
 * @param {string} view - 组件路径（相对于views目录，如'system/user/index'）
 * @returns {Function|undefined} 返回动态import函数，未匹配到则返回undefined
 */
export const loadView = (view) => {
  let res
  for (const path in modules) {
    const dir = path.split('views/')[1].split('.vue')[0]
    if (dir === view) {
      res = () => modules[path]()
    }
  }
  return res
}

export default usePermissionStore
