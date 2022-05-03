# 公共接口

## 发送邮件验证码

### 认证

否

### 路径

POST /user/wp-json/common/send-email-code

### 传入字段

| 字段名            | 类型     | 必填  | 备注         |
|----------------|--------|-----|------------|
| email          | String | 是   | 邮箱地址       |

### 返回值

```json
{
  "message": "发送成功，有效期 5 分钟",
  "status": 0
}
```

## 发送手机验证码

### 认证

否

### 滑块验证

是

### 路径

POST /user/wp-json/common/send-sms-code

### 传入字段

| 字段名    | 类型     | 必填  | 备注  |
|--------|--------|-----|-----|
| mobile | String | 是   | 手机号 |

### 返回值

```json
{
  "message": "发送成功，有效期 5 分钟",
  "status": 0
}
```


