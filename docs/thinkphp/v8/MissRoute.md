# MISS路由

## 全局MISS路由

如果希望在没有匹配到所有的路由规则后执行一条设定的路由，可以注册一个单独的`MISS`路由：

```
Route::miss('public/miss');
```

或者使用闭包定义

```
Route::miss(function() {
    return '404 Not Found!';
});
```

> 一旦设置了MISS路由，相当于开启了强制路由模式

当所有已经定义的路由规则都不匹配的话，会路由到`miss`方法定义的路由地址。

你可以限制`MISS`路由的请求类型

```
// 只有GET请求下MISS路由有效
Route::miss('public/miss', 'get');
```

## 分组MISS路由

分组支持独立的`MISS`路由，例如如下定义：

```
Route::group('blog', function () {
    Route::rule(':id', 'blog/read');
    Route::rule(':name', 'blog/read');
    Route::miss('blog/miss');
})->ext('html')
  ->pattern(['id' => '\d+', 'name' => '\w+']);
```

## 域名MISS路由

支持给某个域名设置单独的`MISS`路由
AnnotationRouting
```
Route::domain('blog', function () {
    // 动态注册域名的路由规则
    Route::rule('new/:id', 'news/read');
    Route::rule(':user', 'user/info');
    Route::miss('blog/miss');
});
```