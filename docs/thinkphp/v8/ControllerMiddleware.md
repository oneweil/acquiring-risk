# 控制器中间件

## 控制器中间件

支持为控制器定义中间件，你只需要在你的控制器中定义`middleware`属性，例如：

```
<?php
namespace app\controller;

use app\middleware\Auth;

class Index 
{
    protected $middleware = [Auth::class];

    public function index()
    {
        return 'index';
    }

    public function hello()
    {
        return 'hello';
    }
}
```

当执行`index`控制器的时候就会调用`Auth`中间件，一样支持使用完整的命名空间定义。

如果需要设置控制器中间的生效操作，可以如下定义：

```
<?php
namespace app\index\controller;

class Index 
{
    protected $middleware = [ 
    	Auth::class . ':admin' 	=> ['except' 	=> ['hello'] ],
        'Hello' => ['only' 		=> ['hello'] ],
    ];

    public function index()
    {
        return 'index';
    }

    public function hello()
    {
        return 'hello';
    }
}
```

## 中间件传参

如果需要在中间件传参，可以通过给请求对象赋值的方式传参给控制器（或者其它地方），例如

```
<?php

namespace app\http\middleware;

class Hello
{
    public function handle($request, \Closure $next)
    {
        // 传参需要在前置中间件完成
        $request->hello = 'ThinkPHP';
        
        return $next($request);
    }
}
```

然后在控制器的方法里面可以直接使用

```
public function index(Request $request)
{
	return $request->hello; // ThinkPHP
}
```