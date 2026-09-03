# 资源控制器

## 资源控制器

资源控制器可以让你轻松的创建`RESTFul`资源控制器，可以通过命令行生成需要的资源控制器，例如生成Blog资源控制器使用：

```
php think make:controller Blog
```

或者使用完整的命名空间生成

```
php think make:controller app\controller\Blog
```

如果只是用于接口开发，可以使用

```
php think make:controller Blog --api
```

然后你只需要为资源控制器注册一个资源路由：

```
Route::resource('blog', 'Blog');
```

设置后会自动注册7个路由规则，对应资源控制器的7个方法，更多内容请参考资源路由章节。