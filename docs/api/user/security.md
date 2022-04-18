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
