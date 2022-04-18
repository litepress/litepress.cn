# 安全页面接口

## 重置用户密码接口

### 认证

是

### 路径

PUT /user/wp-json/center/security/reset-passwd

### 传入字段

| 字段名        | 类型     | 必填  | 备注  |
|------------|--------|-----|-----|
| old_passwd | String | 是   | 旧密码 |
| new_passwd | String | 是   | 新密码 |

### 返回值

```json
{
  "message": "密码已成功重置",
  "status": 0
}
```

## 为账号绑定邮箱

### 认证

是

### 路径

PUT /user/wp-json/center/security/bind-email

### 传入字段

| 字段名        | 类型     | 必填  | 备注    |
|------------|--------|-----|-------|
| email      | String | 是   | 邮箱    |
| email_code | String | 是   | 邮箱验证码 |

### 返回值

```json
{
  "message": "邮箱绑定成功",
  "status": 0
}
```

## 为账号绑定手机号

此接口与手机号自动登录/注册功能所使用的接口不是同一个！

### 认证

是

### 路径

PUT /user/wp-json/center/security/bind-mobile

### 传入字段

| 字段名         | 类型     | 必填  | 备注     |
|-------------|--------|-----|--------|
| mobile      | String | 是   | 手机号    |
| mobile_code | String | 是   | 手机号验证码 |

### 返回值

```json
{
  "message": "手机号绑定成功",
  "status": 0
}
```
