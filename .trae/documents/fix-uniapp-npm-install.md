# 修复 uni-app npm install 错误

## 问题分析

**错误信息**: `npm error notarget No matching version found for @dcloudio/uni-app@^3.0.0`

**根本原因**: 
- `@dcloudio/uni-app` 的版本号格式不是标准的语义化版本（如 `3.0.0`）
- 实际版本号格式为 `3.0.0-4080720251210001` 这样的特殊格式
- `^3.0.0` 这个版本范围在 npm 上不存在

## 解决方案：修改 package.json 使用正确版本

**最新稳定版本**: `3.0.0-4080720251210001`

### 修改后的 package.json

```json
{
  "name": "馥辰国际",
  "version": "1.0.0",
  "scripts": {
    "dev:h5": "uni",
    "dev:mp-weixin": "uni -p mp-weixin",
    "build:h5": "uni build",
    "build:mp-weixin": "uni build -p mp-weixin"
  },
  "dependencies": {
    "@dcloudio/uni-app": "3.0.0-4080720251210001",
    "@dcloudio/uni-components": "3.0.0-4080720251210001",
    "@dcloudio/uni-h5": "3.0.0-4080720251210001",
    "@dcloudio/uni-mp-weixin": "3.0.0-4080720251210001",
    "@dcloudio/uni-ui": "^1.5.0",
    "vue": "^3.4.21"
  },
  "devDependencies": {
    "@dcloudio/types": "3.0.0-4080720251210001",
    "@dcloudio/uni-automator": "3.0.0-4080720251210001",
    "@dcloudio/uni-cli-shared": "3.0.0-4080720251210001",
    "@dcloudio/uni-stacktracey": "3.0.0-4080720251210001",
    "@dcloudio/vite-plugin-uni": "3.0.0-4080720251210001",
    "vite": "^5.2.8"
  }
}
```

## 实施步骤

1. 修改 `d:\fuchenpro\App\package.json` 文件
2. 删除 `node_modules` 目录和 `package-lock.json` 文件（如果存在）
3. 运行 `npm install`

## 注意事项

1. Vue3/Vite 版要求 Node.js 版本 `^14.18.0 || >=16.0.0`
2. 如果安装仍然失败，可以尝试使用 `--legacy-peer-deps` 参数：
   ```bash
   npm install --legacy-peer-deps
   ```
