---
layout: default
title: 数据库文档
nav_order: 3
---

# 数据库文档

## user_meta表

| Key | 类型 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- |
| wprpv_real_name | string | 真实姓名 | 王二麻 |
| wprpv_cert_no | string | 身份证号码 | 513436200004189056 |
| wprpv_enterprise_name | string | 企业名| 王家屯有限责任公司 |
| wprpv_enterprise_code | string | 企业社会信用代码 | 91330106673959654P |
| wprpv_enterprise_license_img | string | 企业营业执照图片URL | https://wp-china-yes.com/store/wp-content/uploads/real-person-verify/2021-04-21-19-04-40-32cb0cd23ac4b751b36872d34795f775.jpg |
| wprpv_verify_limit | string | 当前账户剩余可扫脸实名次数(企业实名API因为调用资费便宜，为了减少代码复杂度，只进行了IP及调用频率限制) | 3 |

