# uview-plus 图标最终修复 - 添加 easycom 配置

## 问题根因

**`pages.json` 的 `easycom` 缺少 uview-plus 组件映射！**

当前配置只有 uni-ui：
```json
"easycom": {
    "autoscan": true,
    "custom": {
      "^uni-(.*)": "@dcloudio/uni-ui/lib/uni-$1/uni-$1.vue"
    }
}
```

没有 `^up-(.*)` 和 `^u-(.*)` 的映射，所以 `<up-icon>` 和 `<u-icon>` 都无法被识别！

## 解决方案

在 `pages.json` 的 `easycom.custom` 中添加 uview-plus 组件映射：

```json
"easycom": {
    "autoscan": true,
    "custom": {
      "^uni-(.*)": "@dcloudio/uni-ui/lib/uni-$1/uni-$1.vue",
      "^up-(.*)": "uview-plus/components/up-$1/up-$1.vue",
      "^u-(.*)": "uview-plus/components/u-$1/u-$1.vue"
    }
}
```

## 修改文件

| 文件 | 操作 |
|------|------|
| `src/pages.json` | 在 easycom.custom 中添加 up-* 和 u-* 映射 |

## 预期结果

添加后，uni-app 会自动识别 `<up-icon>` 并从 `uview-plus/components/up-icon/up-icon.vue` 引入组件，图标正常显示。
