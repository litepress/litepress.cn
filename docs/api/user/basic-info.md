# 基本用户信息管理相关 API

## 获取用户基本信息

### 认证

是

### 路径

GET /user/wp-json/center/basic-info

### 返回值

```json
{
  "message": "用户信息更新成功",
  "data": {
    "ID": "1",
    "user_login": "ibadboy",
    "user_nicename": "ibadboy",
    "user_email": "sxy@ibadboy.net",
    "user_url": "https://www.ibadboy.net",
    "user_registered": "2020-06-08 15:39:56",
    "user_status": "0",
    "display_name": "孙锡源",
    "spam": "0",
    "deleted": "0",
    "nameplate_text": "",
    "nameplate_url": "",
    "gender": "",
    "description": ""
  },
  "status": 0
}
```

## 更新用户基本信息

### 认证

是

### 路径

POST /user/wp-json/center/basic-info

### 传入字段

| 字段名            | 类型     | 必填  | 备注         |
|----------------|--------|-----|------------|
| display_name   | String | 是   | 昵称         |
| nameplate_text | String | 是   | 名牌标识       |
| nameplate_url  | String | 是   | 铭牌网址       |
| gender         | Int    | 是   | 性别 0:男,1:女 |
| description    | String | 是   | 简介         |

### 返回值

```json
{
  "message": "更新信息更新成功",
  "status": 0
}
```
