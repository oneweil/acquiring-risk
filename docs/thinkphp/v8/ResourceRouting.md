# 资源路由

## 资源路由

支持设置`RESTFul`请求的资源路由，方式如下：

```
// 注册一个blog资源路由 指向Blog控制器
Route::resource('blog', 'Blog');
```

可以通过执行命令行的指令查看当前注册的路由

```
php think route:list
```

表示注册了一个名称为`blog`的资源路由到`Blog`控制器，系统会自动注册7个路由规则，如下：

|标识|请求类型|生成路由规则|对应操作方法（默认）|
|---|---|---|---|
|index|GET|`blog`|index|
|create|GET|`blog/create`|create|
|save|POST|`blog`|save|
|read|GET|`blog/:id`|read|
|edit|GET|`blog/:id/edit`|edit|
|update|PUT|`blog/:id`|update|
|delete|DELETE|`blog/:id`|delete|

具体指向的控制器由路由地址决定，你只需要为`Blog`控制器创建以上对应的操作方法就可以支持下面的URL访问：

```
http://serverName/blog/
http://serverName/blog/128
http://serverName/blog/28/edit
```

Blog控制器中的对应方法如下：

```
<?php
namespace app\controller;

class Blog
{
    public function index()
    {
    }

    public function read($id)
    {
    }

    public function edit($id)
    {
    }
}
```

可以通过命令行快速创建一个资源控制器类（参考后面的控制器章节的[资源控制器](https://doc.thinkphp.cn/v8_0/resource_controller.html)一节）。

可以改变默认的id参数名，例如：

```
Route::resource('blog', 'Blog')
    ->vars(['blog' => 'blog_id']);
```

控制器的方法定义需要调整如下：

```
<?php
namespace app\controller;

class Blog
{
    public function index()
    {
    }

    public function read($blog_id)
    {
    }

    public function edit($blog_id)
    {
    }
}
```

为了遵循变量规范，控制器的变量可以改成驼峰命名仍然有效

```
<?php
namespace app\controller;

class Blog
{
    public function index()
    {
    }

    public function read($blogId)
    {
    }

    public function edit($blogId)
    {
    }
}
```

也可以在定义资源路由的时候限定执行的方法（标识），例如：

```
// 只允许index read edit update 四个操作
Route::resource('blog', 'Blog')
    ->only(['index', 'read', 'edit', 'update']);
    
// 排除index和delete操作
Route::resource('blog', 'Blog')
    ->except(['index', 'delete']);
```

资源路由的标识不可更改，但生成的路由规则和对应操作方法可以修改。

如果需要更改某个资源路由标识的对应操作，可以使用下面方法：

```
Route::rest('create',['GET', '/add','add']);
```

设置之后，URL访问变为：

```
http://serverName/blog/create
变成
http://serverName/blog/add
```

创建blog页面的对应的操作方法也变成了add。

支持批量更改，如下：

```
Route::rest([
    'save'   => ['POST', '', 'store'],
    'update' => ['PUT', '/:id', 'save'],
    'delete' => ['DELETE', '/:id', 'destory'],
]);
```

## 资源嵌套

支持资源路由的嵌套，例如：

```
Route::resource('blog', 'Blog');
Route::resource('blog.comment','Comment');
```

就可以访问如下地址：

```
http://serverName/blog/128/comment/32
http://serverName/blog/128/comment/32/edit
```

对应的路由规则分别是：

```
blog/:blog_id/comment/:id
blog/:blog_id/comment/:id/edit
```

Comment控制器对应的操作方法如下：

```
<?php

namespace app\controller;

class Comment
{
    public function edit($id, $blog_id)
    {
    }
}
```

edit方法中的参数顺序可以随意，但参数名称必须满足定义要求。

如果需要改变其中的变量名，可以使用：

```
// 更改嵌套资源路由的blog资源的资源变量名为blogId
Route::resource('blog.comment', 'index/comment')
    ->vars(['blog' => 'blogId']);
```

Comment控制器对应的操作方法改变为：

```
<?php
namespace app\controller;

class Comment
{
    public function edit($id, $blogId)
    {
    }
}
```

## 资源设置

资源路由可以使用路由相关设置方法对所有资源路由有效，也可以给某个单独的资源类型设置中间件、变量规则、绑定模型和数据验证。

```
Route::resource('blog', 'Blog')
    ->withModel('update', ['id','app\model\Blog'])
    ->withMiddleware('update', 'checkAuth');
```

## 扩展路由

可以在资源路由的默认规则之外，额外注册路由，比如使用下面的路由定义：

```
// 注册资源路由
Route::resource('space.bot', 'bot/index');
// 在资源路径下追加注册相关路由
Route::post('space/:space_id/bot/:id/chat', 'bot.chat/index');
Route::post('space/:space_id/bot/:id/chat/upload', 'bot.chat/upload');
Route::post('space/:space_id/bot/:id/chat/suggestion', 'bot.chat/suggestion');
Route::post('space/:space_id/bot/:id/chat/speech', 'bot.chat/speech');
```

`V8.1.0+`版本开始，我们可以使用资源路由的扩展路由定义方法来简化注册（用法类似于分组路由的定义，仅支持使用闭包定义）。

```
Route::resource('space.bot', 'bot/index', function() {
    Route::post('chat', 'bot.chat/index');
    Route::post('chat/upload', 'bot.chat/upload');
    Route::post('chat/suggestion', 'bot.chat/suggestion');
    Route::post('chat/speech', 'bot.chat/speech');
});
```

或者使用`extend`方法注册，效果一致。

```
Route::resource('space.bot', 'bot/index')->extend(function() {
    Route::post('chat', 'bot.chat/index');
    Route::post('chat/upload', 'bot.chat/upload');
    Route::post('chat/suggestion', 'bot.chat/suggestion');
    Route::post('chat/speech', 'bot.chat/speech');
});
```

并且一样可以支持分组，例如可以进一步简化成

```
Route::resource('space.bot', 'bot/index', function() {
    Route::group('chat', function() {
        Route::post('/', 'bot.chat/index');
        Route::post('upload', 'bot.chat/upload');
        Route::post('suggestion', 'bot.chat/suggestion');
        Route::post('speech', 'bot.chat/speech');
    });
});
```

通过资源路由的扩展路由注册的优势是在路由匹配上效率更高（未匹配资源路由的话不会进行扩展路由的检测匹配），另外资源路由采用完整匹配模式，所以下面的扩展路由也会自动采用完整匹配模式。