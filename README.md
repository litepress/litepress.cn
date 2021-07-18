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

本项目基于GPL v3协议开源，任何人都可以自由的复制、分发、重构本项目的所有代码，并将其用于任何目的，而不用承担任何法律风险。

数据库部分因为涉及用户隐私，且当前平台开发中，表结构不断变化亦不便于进行脱敏处理。将会在项目架构稳定并上线后将其开源。

## 运行环境

 - PHP 8.0 及以上版本
 - MySQL 8.0 及以上版本
 - Nginx 21 及以上版本

## 依赖的外部环境

 1. 平台整体依赖于 [Cavalcade](https://github.com/humanmade/Cavalcade) 项目用于处理Cron队列
 2. 翻译平台的翻译记忆库的实现依赖于 [Pontoon](https://github.com/mozilla/pontoon)

## 一些话

虽然litepress.cn的构建完全基于GPL协议授权的代码，但因为这是服务性质的私有化平台，而非公开发行的代码，所以我们本来是没义务对其开源的。

但是这个项目在开发过程中接收了大量赞助，已经可以说并非是我们一家之力的成果了，所以对项目产出进行垄断显然是不合适的。

于是决定将所有代码都公开放出来，算是给国内WordPress圈子留下一点财富，如果将来我们做不下去了，其他人可以接过来继续，而不是从头构建这一切。
