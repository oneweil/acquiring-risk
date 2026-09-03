# Cookie

## 概述

ThinkPHP采用`think\facade\Cookie`类提供Cookie支持。

## 配置

配置文件位于配置目录下的`cookie.php`文件，无需手动初始化，系统会在调用之前自动进行`Cookie`初始化工作。

支持的参数及默认值如下：

```
// cookie 保存时间
'expire'    => 0,
// cookie 保存路径
'path'      => '/',
// cookie 有效域名
'domain'    => '',
//  cookie 启用安全传输
'secure'    => false,
// httponly设置
'httponly'  => '',
// samesite 设置，支持 'strict' 'lax'
 'samesite' => '',
```

## 基本操作

### 设置

```
// 设置Cookie 有效期为 3600秒
Cookie::set('name', 'value', 3600);
```

`Cookie`数据不支持数组，如果需要请自行序列化后存入。

### 永久保存

```
// 永久保存Cookie
Cookie::forever('name', 'value');
```

### 删除

```
//删除cookie
Cookie::delete('name');
```

### 读取

```
// 读取某个cookie数据
Cookie::get('name');
// 获取全部cookie数据
Cookie::get();
```

## 助手函数

系统提供了`cookie`助手函数用于基本的`cookie`操作，例如：

```
// 设置
cookie('name', 'value', 3600);

// 获取
echo cookie('name');

// 删除
cookie('name', null);
```

> 可以通过同时设置`samesite=none`,`secure=true`的方式,实现跨域传递cookie