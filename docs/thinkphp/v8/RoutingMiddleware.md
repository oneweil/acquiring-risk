# 路由中间件

## 路由中间件

可以使用路由中间件，注册方式如下：

```
Route::rule('hello/:name','hello')
	->middleware(\app\middleware\Auth::class);
```

或者对路由分组注册中间件

```
Route::group('hello', function(){
	Route::rule('hello/:name','hello');
})->middleware(\app\middleware\Auth::class);
```

如果需要对分组中的某个路由不使用分组中间件（`V8.1.0+`版本开始支持），可以使用

```
Route::group('blog', function(){
	Route::get(':id/edit','blog/edit');
	Route::put(':id','blog/update');
	Route::delete(':id','blog/delete');
	Route::get(':id','blog/read')->withoutMiddleware(); // 无需鉴权
})->middleware(\app\middleware\Auth::class);
```

`withoutmiddleware`也支持通过数组方式传入需要排除的部分中间件。

如果需要传入额外参数给中间件，可以使用

```
Route::rule('hello/:name','hello')
	->middleware(\app\middleware\Auth::class,'admin');
```

如果需要定义多个中间件，使用数组方式

```
Route::rule('hello/:name','hello')
	->middleware([\app\middleware\Auth::class,\app\middleware\Check::class]);
```

可以统一传入同一个额外参数

```
Route::rule('hello/:name','hello')
	->middleware([\app\middleware\Auth::class, \app\middleware\Check::class], 'admin');
```

如果你希望某个路由中间件是全局执行（不管路由是否匹配），可以不需要在路由里面定义，支持直接在路由配置文件中定义，例如在`config/route.php`配置文件中添加：

```
'middleware'    =>    [
    app\middleware\Auth::class,
    app\middleware\Check::class,
],
```

这样，所有该应用下的请求都会执行`Auth`和`Check`中间件。

更多中间件的用法参考架构章节的[中间件](https://doc.thinkphp.cn/v8_0/middleware.html)内容。