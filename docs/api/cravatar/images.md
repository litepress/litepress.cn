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
  "data": {
    "83": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2022/01/woocommerce-placeholder.png",
    "61": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/iro.png",
    "52": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/00000000000000000000000000000000.png",
    "50": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/default.png",
    "41": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/6.png",
    "40": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/5.png",
    "39": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/4.png",
    "38": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/3.png",
    "37": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/2.png",
    "36": "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/1.jpg",
    "27": "https://litepress.cn/lavatar/wp-content/uploads/sites/9/2021/06/未标题-1.png"
  },
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
