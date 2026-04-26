# uView Plus 图标不显示 + TabBar 图标缺失修复计划

## 问题分析

### 问题1：u-icon 图标不显示（空圆圈）

**根因：uview-plus 使用远程 CDN 字体加载图标**

`config.js` 第41行：
```js
iconUrl: 'https://at.alicdn.com/t/font_2225171_8kdcwk4po24.ttf'
```

`util.js` 通过 `uni.loadFontFace()` 加载该字体。如果网络无法访问阿里 CDN，字体加载失败，图标就显示为空圆圈。

### 问题2：TabBar 图标 404

**根因：H5 模式下静态资源路径问题**

`pages.json` 中 tabBar 配置：
```json
"iconPath": "static/images/tabbar/home.png"
"selectedIconPath": "static/images/tabbar/home_.png"
```

H5 模式下需要以 `/` 开头的绝对路径才能正确解析。

## 解决方案

### 步骤1：下载 uview-plus 图标字体到本地

从阿里 CDN 下载字体文件到项目本地：
- 保存为 `d:\fuchenpro\AppV3\src\static\uview-plus\uicon-iconfont.ttf`

### 步骤2：配置 uview-plus 使用本地字体

在 `main.js` 中配置 uview-plus 的 `iconUrl` 为本地路径：

```js
import uviewPlus from 'uview-plus'

// 配置 uview-plus 使用本地图标字体
uviewPlus.config.iconUrl = '/static/uview-plus/uicon-iconfont.ttf'

app.use(uviewPlus)
```

### 步骤3：修复 TabBar 图标路径

修改 `pages.json` 中 tabBar 的 iconPath，添加前导 `/`：

```json
"iconPath": "/static/images/tabbar/home.png",
"selectedIconPath": "/static/images/tabbar/home_.png"
```

## 修改文件清单

| 文件 | 操作 |
|------|------|
| `src/static/uview-plus/uicon-iconfont.ttf` | 新建 - 本地字体文件 |
| `src/main.js` | 修改 - 配置本地字体路径 |
| `src/pages.json` | 修改 - 修复 TabBar 路径 |

## 预期结果

修复后：
1. u-icon 图标正常显示
2. TabBar 底部导航图标正常显示
3. 所有页面图标统一美观
