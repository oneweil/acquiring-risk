# 注解路由

## 注解路由

支持使用PHP8的注解方式定义路由（也称为注解路由），如果需要使用注解路由需要安装额外的扩展：

```
composer require topthink/think-annotation
```

然后只需要直接在控制器类的方法注释中定义，例如：

```
<?php
namespace app\controller;

use think\annotation\route\Route;

class Index
{
    /**
     * @param  string $name 数据名称
     * @return mixed
     */
    #[Route("GET", "hello/:name")]
    public function hello($name)
    {
    	return 'hello,'.$name;
    }
}
```

注解Route的第一个参数支持 GET/POST/PUT/DELETE/PATCH/OPTIONS/HEAD/*（注意必须大写），也可以直接使用请求名简化定义：

```
<?php
namespace app\controller;

use think\annotation\route\Get;

class Index
{
    /**
     * @param  string $name 数据名称
     * @return mixed
     */
    #[Get("hello/:name")]
    public function hello($name)
    {
    	return 'hello,'.$name;
    }
}
```

请务必注意注解的定义规范，不能在注解路由里面使用单引号，否则可能导致注解路由解析失败，可以利用IDE生成规范的注释。如果你使用`PHPStorm`的话，建议安装`PHP Annotations`插件：[https://plugins.jetbrains.com/plugin/7320-php-annotations](https://plugins.jetbrains.com/plugin/7320-php-annotations) ，可以支持注解的自动完成。

> 该方式定义的路由在调试模式下面实时生效，部署模式则在第一次访问的时候生成注解缓存。

然后就使用下面的URL地址访问：

```
http://tp.com/hello/thinkphp
```

页面输出

```
hello,thinkphp
```

如果有路由参数需要定义，可以在后面传入路由参数，例如：

```
<?php
namespace app\controller;

use think\annotation\route\Route;

class Index
{
    /**
     * @param string $name 数据名称
     * @return mixed
     */
    [#Route("GET", "hello/:name", ["https"=>1, "ext"=>"html"])]
    public function hello($name)
    {
    	return 'hello,'.$name;
    }
}
```

支持在类的注释里面定义资源路由，例如：

```
<?php
namespace app\controller;

use think\annotation\route\Resource;

#[Resource("blog")]
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

如果需要定义路由分组，可以使用

```
<?php
namespace app\controller;

use think\annotation\route\Group;
use think\annotation\route\Route;

#[Group("blog")]
class Blog
{
    /**
     * @param  string $name 数据名称
     * @return mixed
    */
    #[Route("GET","hello/:name")]
    public function hello($name)
    {
    	return 'hello,'.$name;
    }
}
```

当前控制器中的注解路由会自动加入`blog`分组下面，最终，会注册一个`blog/hello/:name`的路由规则。你一样可以对该路由分组设置公共的参数以及添加中间件定义，例如：

```
<?php
namespace app\controller;

use think\annotation\route\Middleware;
use think\annotation\route\Group;
use think\annotation\route\Route;
use think\middleware\SessionInit;

#[Group("blog", ["ext" => "html"])]
#[Middleware([SessionInit::class])]
class Blog
{
    /**
     * @param  string $name 数据名称
     * @return mixed
     */
    [#Route("GET", "hello/:name")]
    public function hello($name)
    {
    	return 'hello,'.$name;
    }
}
```