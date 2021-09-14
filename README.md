# LitePress.cn 平台源码

这是一个站群平台，包含以下子平台：

|  网址   | 描述  |
|  ----  | ----  |
| https://litepress.cn  | 社区论坛 |
| https://litepress.cn/store  | 应用市场 |
| https://litepress.cn/translate  | 翻译平台 |
| https://litepress.cn/docs  | 文档平台 |
| https://api.litepress.cn  | 对外API接口 |
| https://cravatar.cn  | 公共头像服务 |

本项目基于 GPL v3 协议开源，任何人都可以自由的复制、分发、重构本项目的所有代码，并将其用于任何目的，而不用承担任何法律风险。

数据库部分因为涉及用户隐私，且当前平台开发中，表结构不断变化亦不便于进行脱敏处理。将会在项目架构稳定并上线后将其开源。

## 运行环境

 - PHP 8.0 及以上版本
 - MySQL 8.0 及以上版本
 - Nginx 21 及以上版本

## 依赖的外部环境

 1. WP-Cli - 大量功能依赖此软件处理
 2. zip - 平台涉及 Zip 打包的逻辑需要调用操作系统的 zip 命令
 3. Cavalcade - 用于处理所有 Cron 队列
 4. Elasticsearch - 用于实现翻译记忆库及增强应用市场的产品检索
 5. 阿里云金融实名API - 应用市场的用户实名依赖（后面考虑更换为支付宝实名）
 6. 天眼查企业三要素接口 - 应用市场的企业实名认证依赖
 7. 又拍云CDN - 公共头像服务的CDN刷新以及webp格式适配功能依赖于此
 8. msgfmt - 用于翻译平台生成mo文件
 9. py-googletrans - 依赖该库的命令行形式来为翻译平台提供机器翻译支持

## 技术支持

如果你在分叉过程中遇到问题可以在 litepress.cn 论坛发帖交流，但我们仅能提供架构及程序流程咨询，如需定制开发请自行聘请工程师。
