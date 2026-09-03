# 路由地址

## 路由地址

路由地址表示定义的路由表达式最终需要路由到的实际地址（或者响应对象）以及一些需要的额外参数，支持下面几种方式定义：

## 路由到控制器/操作

这是最常用的一种路由方式，把满足条件的路由规则路由到相关的控制器和操作，然后由系统调度执行相关的操作，格式为：

> ### 模块/控制器/操作

解析规则是从操作开始解析，然后解析控制器，例如：

```
// 路由到blog控制器
Route::get('blog/:id','Blog/read');
```

Blog类定义如下：

```
<?php
namespace app\index\controller;

class Blog
{
    public function read($id)
    {
        return 'read:' . $id;
    }
}
```

路由地址中支持多级控制器，使用下面的方式进行设置：

```
Route::get('blog/:id','group.Blog/read');
```

表示路由到下面的控制器类，

```
index/controller/group/Blog
```

还可以支持路由到动态的应用、控制器或者操作，例如：

```
// action变量的值作为操作方法传入
Route::get(':action/blog/:id', 'Blog/:action');
```

## 路由到类的方法

这种方式的路由可以支持执行任何类的方法，而不局限于执行控制器的操作方法。

路由地址的格式为（动态方法）：

> ### \完整类名@方法名 或 [ 完整类名, 方法名 ]

或者（静态方法）

> ### \完整类名::方法名

例如：

```
Route::get('blog/:id','\app\index\service\Blog@read');
```

执行的是 `\app\index\service\Blog`类的`read`方法。  
也支持执行某个静态方法，例如：

```
Route::get('blog/:id','\app\index\service\Blog::read');
```

## 重定向路由

可以直接使用`redirect`方法注册一个重定向路由

```
Route::redirect('blog/:id', 'http://blog.thinkphp.cn/read/:id', 302);
```

## 路由到模板

支持路由直接渲染模板输出。

```
// 路由到模板文件
Route::view('hello/:name', 'index/hello');
```

表示该路由会渲染当前应用下面的`view/index/hello.html`模板文件输出。

模板文件中可以直接输出当前请求的`param`变量，如果需要增加额外的模板变量，可以使用：

```
Route::view('hello/:name', 'index/hello', ['city'=>'shanghai']);
```

在模板中可以输出`name`和`city`两个变量。

```
Hello,{$name}--{$city}！
```

## 路由到闭包

我们可以使用闭包的方式定义一些特殊需求的路由，而不需要执行控制器的操作方法了，例如：

```
Route::get('hello', function () {
    return 'hello,world!';
});
```

可以通过闭包的方式支持路由自定义响应输出，例如：

```
Route::get('hello/:name', function () {
    response()->data('Hello,ThinkPHP')
    ->code(200)
    ->contentType('text/plain');
});
```

### 参数传递

闭包定义的时候支持参数传递，例如：

```
Route::get('hello/:name', function ($name) {
    return 'Hello,' . $name;
});
```

规则路由中定义的动态变量的名称 就是闭包函数中的参数名称，不分次序。

因此，如果我们访问的URL地址是：

```
http://serverName/hello/thinkphp
```

则浏览器输出的结果是：

```
Hello,thinkphp
```

### 依赖注入

可以在闭包中使用依赖注入，例如下面的闭包注入了当前请求对象：

```
Route::rule('hello/:name', function (Request $request, $name) {
    $method = $request->method();
    return '[' . $method . '] Hello,' . $name;
});
```

## 路由到调度对象

支持路由到一个自定义的路由调度对象。

```
// 路由到自定义调度对象
Route::get('blog/:id',\app\route\BlogDispatch::class);
```

```
namespace app\route;

use think\route\Dispatch;
use think\route\Rule;
use think\Request;

class BlogDispatch extends Dispatch
{
    public function exec()
    {
        // 自定义路由调度
    }
}
```

具体调度类的实现可以参考内置的几个调度类的实现。