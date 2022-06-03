# 图片管理相关接口

## 获取当前用户全部图片

### 认证

是

### 路径

GET /wp-json/cravatar/images

### 传入字段

无

### 返回值

```json
{
  "message": "数据获取成功",
  "data": [
    {
      "id": 36,
      "url": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/1.jpg"
    },
    {
      "id": 27,
      "url": "https://litepress.cn/lavatar/wp-content/uploads/sites/9/2021/06/未标题-1.png"
    }
  ],
  "status": 0
}
```

## 添加新图片接口

### 认证

是

### 路径

POST /wp-json/cravatar/images

### 传入字段

| 字段名        | 类型     | 必填  | 备注                     |
|------------|--------|-----|------------------------|
| image      | File   | 是   | 图片文件，需使用 FromData 方式上传 |

### 返回值

```json
{
  "message": "添加成功",
  "status": 0
}
```

## 删除头像接口

### 认证

是

### 路径

DELETE /wp-json/cravatar/images/<id>

### 传入字段

无

### 返回值

```json
{
  "message": "删除成功",
  "status": 0
}
```
