# 头像管理相关接口

## 获取当前用户全部头像

### 认证

是

### 路径

GET /wp-json/cravatar/avatars

### 传入字段

无

### 返回值

```json
{
  "message": "数据获取成功",
  "data": {
    "loveqq@outlook.com": "https://cravatar.cn/avatar/0ffca5301a215c555d6587373fdb7ad5?s=400&r=G&d=mp",
    "977869645@qq.com": "https://cravatar.cn/avatar/526d5c35b2092765ce9865d807612f33?s=400&r=G&d=mp"
  },
  "status": 0
}
```

## 添加新头像接口

### 认证

是

### 路径

POST /wp-json/cravatar/avatars

### 传入字段

| 字段名        | 类型     | 必填  | 备注    |
|------------|--------|-----|-------|
| email      | String | 是   | 邮箱    |
| email_code | String | 是   | 邮箱验证码 |
| image_id   | Int    | 否   | 图像 ID |

### 返回值

```json
{
  "message": "添加成功",
  "status": 0
}
```

## 修改头像接口

### 认证

是

### 路径

PUT /wp-json/cravatar/avatars

### 传入字段

| 字段名        | 类型     | 必填  | 备注    |
|------------|--------|-----|-------|
| email      | String | 是   | 邮箱    |
| image_id   | Int    | 是   | 图像 ID |

### 返回值

```json
{
  "message": "修改成功",
  "status": 0
}
```

## 删除头像接口

### 认证

是

### 路径

PUT /wp-json/cravatar/avatars

### 传入字段

| 字段名        | 类型     | 必填  | 备注    |
|------------|--------|-----|-------|
| email      | String | 是   | 邮箱    |

### 返回值

```json
{
  "message": "删除成功",
  "status": 0
}
```
