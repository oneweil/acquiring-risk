# 域名路由

## 域名路由

ThinkPHP支持完整域名、子域名和IP部署的路由和绑定功能，同时还可以起到简化URL的作用。

可以单独给域名设置路由规则，例如给`blog`子域名注册单独的路由规则：

```
Route::domain('blog', function () {
    // 动态注册域名的路由规则
    Route::rule('new/:id', 'news/read');
    Route::rule(':user', 'user/info');
});
```

一旦定义了域名路由，该域名的访问就只会读取域名路由定义的路由规则。

> 闭包中可以使用路由的其它方法，包括路由分组，但不能再包含域名路由

支持同时对多个域名设置相同的路由规则：

```
Route::domain(['blog', 'admin'], function () {
    // 动态注册域名的路由规则
    Route::rule('new/:id', 'news/read');
    Route::rule(':user', 'user/info');
});
```

如果你需要设置一个路由跨所有域名都可以生效，可以对分组路由或者某个路由使用`crossDomainRule`方法设置：

```
Route::group( function () {
    // 动态注册域名的路由规则
    Route::rule('new/:id', 'news/read');
    Route::rule(':user', 'user/info');
})->crossDomainRule();
```

> 如果你给域名路由设置了绑定，则跨域路由可能不会生效。

## 域名绑定

域名路由可以和分组路由一样进行绑定操作，包括绑定到命名空间、控制器分级、类等。

```
Route::domain('blog', function () {
    // 动态注册域名的路由规则
    Route::rule('new/:id', 'news/read');
})->layer('blog');
```

## 绑定到Response对象

可以直接绑定某个域名到`Response`对象，例如：

```
// 绑定域名到Response对象
Route::domain('test', response()->code(404));
```

## 路由参数

域名路由本身也是一个路由分组，所以可以和路由分组一样定义公共的路由参数，例如：

```
Route::domain('blog', function () {
    // 动态注册域名的路由规则
    Route::rule('new/:id', 'news/read');
    Route::rule(':user', 'user/info');
})->ext('html')
->pattern(['id' => '\d+'])
->append(['group_id' => 1]);
```