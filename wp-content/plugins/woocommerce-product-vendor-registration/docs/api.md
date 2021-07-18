---
layout: default
title: API文档
nav_order: 2
---

# API文档

## 公共区域

### 接口URL

http[s]://你的域名/wp-json/wprpv/v1/API的Slug

### 状态码

这个应用比较简单，所以只使用了200和500两种状态码。

200代表请求成功；500代表异常，比如说某个必填参数漏掉了之类的情况会返回500。

### 返回值结构

无论接口请求还是失败，都会返回message字段，data字段是可选的且只在接口请求成功时会返回，用于承载返回的数据。

成功：
```json
{
    "message": "认证未通过",
    "data": {
        "passed": false
    }
}
```

失败：
```json
{
    "message": "meta_info为必填字段"
}
```

## 初始化本地个人实人认证任务

之所以不把初始化本地实名任务和初始化阿里云实名任务的API合并，是因为阿里云的API需要传递一个环境信息，因为是要用户扫码在手机上进行扫脸，所以这个获取环境信息的JS需要在用户手机上执行才可以。

为了标识这个手机扫脸网页属于哪个任务，于是在创建手机扫脸页面前生成了一个本地任务ID，而后只有在手机页面上获取了用户的环境信息，这时候才可以去调用阿里云的API来返回实际的扫脸URL，再用JS自动跳转过去。

### Slug

`init-local-face-verify-task`

### 请求方式

POST

### 请求参数

| 名称 | 类型 | 必填 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- | ----- |
| name | string | 是 | 真实姓名 | 王二麻 |
| cert_no | string | 是 | 身份证号码 | 513436200004189056 |

### 返回参数

| 名称 | 类型 | 必填 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- | ----- |
| task_id | string | 是 | 任务ID | 4124bc0a9335c27f086f24ba207a4912 |
| url | string | 是 | 进行人脸认证的页面的URL，前端生成跳转二维码时记得将task_id作为query参数拼接上，以此标识任务 | https://你的域名/real-person-verify/job-face-verify |

## 初始化阿里云实人认证任务

### Slug

`init-aliyun-face-verify-task`

### 请求方式

POST

### 请求参数

| 名称 | 类型 | 必填 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- | ----- |
| task_id | string | 是 | 任务ID | 4124bc0a9335c27f086f24ba207a4912 |
| meta_info | string | 是 | 环境信息，该环境信息的获取方法参考https://help.aliyun.com/document_detail/173782.html?spm=a2c4g.11186623.6.585.13e37562LF4bSv | {"zimVer":"3.0.0","appVersion": "1","bioMetaInfo": "4.1.0:11501568,0","appName": "com.aliyun.antcloudauth","deviceType": "ios","osVersion": "iOS 10.3.2","apdidToken": "","deviceModel": "iPhone9,1"} |

### 返回参数

| 名称 | 类型 | 必填 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- | ----- |
| certify_url | string | 是 | 阿里云实人认证URL，前端通过JS自动跳转过去就可以开始扫脸认证了 | aliyun.com |

## 查询个人实人认证结果

### Slug

`describe-face-verify-task`

### 请求方式

GET

### 请求参数

| 名称 | 类型 | 必填 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- | ----- |
| task_id | string | 是 | 任务ID | 4124bc0a9335c27f086f24ba207a4912 |

### 返回参数

| 名称 | 类型 | 必填 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- | ----- |
| passed | bool | 是 | true为通过，false为未通过 | true |

## 进行企业实名认证

### Slug

`enterprise-verify`

### 请求方式

POST

### 请求参数

| 名称 | 类型 | 必填 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- | ----- |
| code | string | 是 | 社会信用代码 | 91330106673959654P |
| name | string | 是 | 企业名称 | 王家屯科技有限公司 |
| license_img | file | 是 | 营业执照图片 | 无 |

### 返回参数

| 名称 | 类型 | 必填 | 描述 | 示例值 |
| ----- | ----- | ----- | ----- | ----- |
| passed | bool | 是 | true为通过，false为未通过 | true |
