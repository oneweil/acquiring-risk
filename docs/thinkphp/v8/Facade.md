# 门面

## 门面（`Facade`）

门面为容器中的（动态）类提供了一个静态调用接口，相比于传统的静态方法调用， 带来了更好的可测试性和扩展性，你可以为任何的非静态类库定义一个`facade`类。

> 系统已经为大部分核心类库定义了`Facade`，所以你可以通过`Facade`来访问这些系统类，当然也可以为你的应用类库添加静态代理。

下面是一个示例，假如我们定义了一个`app\common\Test`类，里面有一个`hello`动态方法。

```
<?php
namespace app\common;

class Test
{
    public function hello($name)
    {
        return 'hello,' . $name;
    }
}
```

调用hello方法的代码应该类似于：

```
$test = new \app\common\Test;
echo $test->hello('thinkphp'); // 输出 hello，thinkphp
```

接下来，我们给这个类定义一个静态代理类`app\facade\Test`（这个类名不一定要和`Test`类一致，但通常为了便于管理，建议保持名称统一）。

```
<?php
namespace app\facade;

use think\Facade;

class Test extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'app\common\Test';
    }
}
```

只要这个类库继承`think\Facade`，就可以使用静态方式调用动态类`app\common\Test`的动态方法，例如上面的代码就可以改成：

```
// 无需进行实例化 直接以静态方法方式调用hello
echo \app\facade\Test::hello('thinkphp');
```

结果也会输出 `hello，thinkphp`。

> 说的直白一点，Facade功能可以让类无需实例化而直接进行静态方式调用。

## 核心`Facade`类库

系统给内置的常用类库定义了`Facade`类库，包括：

|（动态）类库|Facade类|
|---|---|
|think\App|think\facade\App|
|think\Cache|think\facade\Cache|
|think\Config|think\facade\Config|
|think\Cookie|think\facade\Cookie|
|think\Db|think\facade\Db|
|think\Env|think\facade\Env|
|think\Event|think\facade\Event|
|think\Filesystem|think\facade\Filesystem|
|think\Lang|think\facade\Lang|
|think\Log|think\facade\Log|
|think\Middleware|think\facade\Middleware|
|think\Request|think\facade\Request|
|think\Route|think\facade\Route|
|think\Session|think\facade\Session|
|think\Validate|think\facade\Validate|
|think\View|think\facade\View|

所以你无需进行实例化就可以很方便的进行方法调用，例如：

```
use think\facade\Cache;

Cache::set('name','value');
echo Cache::get('name');
```

在进行依赖注入的时候，请不要使用`Facade`类作为类型约束，而是建议使用原来的动态类，下面是错误的用法：

```
<?php
namespace app\index\controller;

use think\facade\App;

class Index
{
    public function index(App $app)
    {
    }
}
```

应当使用下面的方式：

```
<?php
namespace app\index\controller;

use think\App;

class Index
{
    public function index(App $app)
    {
    }
}
```

事实上，依赖注入和使用`Facade`代理的效果大多数情况下是一样的，都是从容器中获取对象实例。例如：

```
<?php
namespace app\index\controller;

use think\Request;

class Index
{
    public function index(Request $request)
    {
        echo $request->controller();
    }
}
```

和下面的作用是一样的

```
<?php
namespace app\index\controller;

use think\facade\Request;

class Index
{
    public function index()
    {
        echo Request::controller();
    }
}
```

依赖注入的优势是支持接口的注入，而`Facade`则无法完成。

> 一定要注意两种方式的`use`引入类库的区别