# 免费分享｜高颜值网站建设中 / Coming Soon 页面源码，自带邮箱订阅 + 微信推送通知

## 前言

最近给自己的站做了一个"网站建设中"页面，效果还不错，源码分享给大家。

纯前端 + PHP 后端，不依赖任何框架和第三方库，拿来就能用。适合新站上线前挂着当建设中页面，顺便收集用户邮箱。

## 预览

暗色系星空主题，左右分栏布局，自带粒子动画和鼠标跟随光晕，视觉效果拉满。

## 功能亮点

**前端部分：**
- 星空粒子背景 + 粒子连线动画（Canvas 实现，100 个粒子）
- 鼠标跟随紫色光晕效果
- 30 天自动倒计时，数字翻转动画
- 建设进度条展示
- 邮箱订阅表单 + 滑块验证码（防机器人）
- 微信公众号二维码展示
- 完整响应式适配（PC / 平板 / 手机）
- 入场动画（fadeIn、shimmer 等）

**后端部分（subscribe.php）：**
- 邮箱格式验证（前端 HTML5 + 后端 filter_var 双重校验）
- 邮箱去重，防止重复订阅
- JSON 文件存储，文件锁防并发（不需要数据库）
- 订阅成功自动发送 HTML 格式确认邮件（紫色渐变主题，很好看）
- ShowDoc 微信推送通知管理员（有人订阅立刻收到微信消息）
- 原生 PHP Socket SMTP 发信，零依赖

**安全机制：**
- 滑块验证码防止恶意提交
- 前端 localStorage 本地备份，服务器挂了数据也不丢
- CORS 跨域支持
- 邮箱格式前后端双重验证

## 技术栈

| 技术 | 说明 |
|------|------|
| HTML5 + CSS3 | 页面结构和样式，纯手写无框架 |
| Vanilla JavaScript | 粒子动画、倒计时、滑块验证、表单提交 |
| PHP 7.0+ | 后端接口，原生 Socket SMTP |
| JSON | 数据存储，无需数据库 |

## 文件结构

```
├── index.html          # 前端页面（所有样式和脚本内联）
├── subscribe.php       # 后端订阅接口
├── subscribers.json    # 自动生成的订阅数据
└── qrcode_xxx.jpg      # 微信公众号二维码（替换成你自己的）
```

## 部署方法

1. 把文件上传到你的虚拟主机或服务器
2. 修改 `subscribe.php` 顶部的配置：

```php
define('SMTP_HOST', 'smtp.qq.com');        // SMTP 服务器
define('SMTP_PORT', 465);                   // 端口
define('SMTP_USER', 'your_email@qq.com');   // 你的邮箱
define('SMTP_PASS', 'your_auth_code');      // SMTP 授权码
define('SMTP_FROM', 'your_email@qq.com');   // 发件人
define('ADMIN_EMAIL', 'admin@qq.com');      // 管理员邮箱
define('SHOWDOC_PUSH_URL', 'your_url');     // ShowDoc 推送地址（可选）
```

3. 确保 PHP 有写入权限（用于自动创建 subscribers.json）
4. 替换公众号二维码图片
5. 访问你的域名，搞定

## 自定义说明

- 品牌名称：搜索 `AMUCHEN` 和 `沐辰网络` 替换成你自己的
- 倒计时天数：JS 里 `launchDate.setDate(launchDate.getDate() + 30)` 改数字
- 进度百分比：CSS 里 `progressGrow` 动画和 HTML 里 `35%` 改成你要的
- 配色：主色 `#a78bfa`（紫色），全局搜索替换即可
- 邮件模板：subscribe.php 里的 HTML 邮件内容可以自行修改

## 邮件效果

订阅成功后用户会收到一封精美的 HTML 邮件，紫色渐变头部 + 卡片式布局，包含订阅邮箱和时间信息。

同时管理员会通过 ShowDoc 收到微信推送通知，包含订阅者邮箱、IP、时间和总订阅人数。

## 兼容性

- Chrome / Edge / Firefox / Safari 最新版
- iOS Safari / Android Chrome
- 响应式断点：860px / 380px

## 开源地址

GitHub：https://github.com/mcwlgzs/amuchen-coming-soon

觉得不错的话给个 Star 支持一下，有问题欢迎提 Issue。

---

> 免费开源，随意使用和修改。如果用在了你的项目里，欢迎留言分享。
