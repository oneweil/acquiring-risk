# 异常处理

和PHP默认的异常处理不同，ThinkPHP抛出的不是单纯的错误信息，而是一个人性化的错误页面。

## 异常显示

新版的异常页面显示会自动判断当前的请求是否为Json请求，如果是JSON请求则采用JSON格式输出异常信息，否则按照HTML格式输出。

在调试模式下，系统默认展示的异常页面

> 只有在调试模式下面才能显示具体的错误信息，如果在部署模式下面，你可能看到的是一个简单的提示文字.

`V8.0.4+`版本，你可以通过给异常类添加注解（通常是业务中的异常类），不管是否调试模式始终会输出错误信息。

```
<?php

namespace app\exception;
use think\exception\AlwaysErrorMsg;

#[AlwaysErrorMsg]
class QuotaException extends \Exception
{

}
```

你可以通过设置`exception_tmpl`配置参数来自定义你的异常页面模板，默认的异常模板位于：

```
thinkphp/tpl/think_exception.tpl
```

你可以在应用配置文件`app.php`中更改异常模板

```
// 自定义异常页面的模板文件
'exception_tmpl'         => \think\facade\App::getAppPath() . 'template/exception.tpl',
```

默认的异常页面会返回`500`状态码，如果是一个`HttpException`异常则会返回HTTP的错误状态码。

## 异常处理接管

> 本着严谨的原则，框架会对任何错误（包括警告错误）抛出异常。系统产生的异常和错误都是程序的隐患，要尽早排除和解决，而不是掩盖。对于应用自己抛出的异常则做出相应的捕获处理。

框架支持异常处理由开发者自定义类进行接管，需要在`app`目录下面的`provider.php`文件中绑定异常处理类，例如：

```
    // 绑定自定义异常处理handle类
    'think\exception\Handle'       => '\\app\\exception\\Http',
```

自定义类需要继承`think\exception\Handle`并且实现`render`方法，可以参考如下代码：

```
<?php
namespace app\common\exception;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

class Http extends Handle
{
    public function render($request, Throwable $e): Response
    {
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return json($e->getError(), 422);
        }

        // 请求异常
        if ($e instanceof HttpException && $request->isAjax()) {
            return response($e->getMessage(), $e->getStatusCode());
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }

}
```

自定义异常处理的主要作用是根据不同的异常类型发送不同的状态码和响应输出格式。

> 事实上，默认安装应用后，已经帮你内置了一个`app\ExceptionHandle`异常处理类，直接修改该类的相关方法即可完成应用的自定义异常处理机制。

> 需要注意的是，如果自定义异常处理类没有再次调用系统`render`方法的话，配置`http_exception_template`就不再生效，具体可以参考`Handle`类内实现的功能。

## 手动抛出和捕获异常

ThinkPHP大部分情况异常都是自动抛出和捕获的，你也可以手动使用`throw`来抛出一个异常，例如：

```
// 使用think自带异常类抛出异常
throw new \think\Exception('异常消息', 10006);
```

手动捕获异常方式是使用`try-catch`，例如：

```
try {
    // 这里是主体代码
} catch (ValidateException $e) {
    // 这是进行验证异常捕获
    return json($e->getError());
} catch (\Exception $e) {
    // 这是进行异常捕获
    return json($e->getMessage());
}
```

> 支持使用`try-catch-finally`结构捕获异常。

## HTTP 异常

可以使用`\think\exception\HttpException`类来抛出HTTP异常

```
<?php
namespace app\index\controller;
use think\exception\HttpException;

class Index
{
    public function index()
    {
      // 抛出 HTTP 异常
      throw new HttpException(404, '异常消息');
    }
}
```

系统提供了助手函数`abort`简化HTTP异常的处理，例如：

```
<?php
namespace app\index\controller;


class Index
{
    public function index()
    {
        // 抛出404异常
        abort(404, '页面异常');
    }
}
```

如果你的应用是API接口，那么请注意在客户端首先判断HTTP状态码是否正常，然后再进行数据处理，当遇到错误的状态码的话，应该根据状态码自行给出错误提示，或者采用下面的方法进行自定义异常处理。

**部署模式**下一旦抛出了`HttpException`异常，可以定义单独的异常页面模板，只需要在`app.php`配置文件中增加：

```
'http_exception_template'    =>  [
    // 定义404错误的模板文件地址
    404 =>  \think\facade\App::getAppPath() . '404.html',
    // 还可以定义其它的HTTP status
    401 =>  \think\facade\App::getAppPath() . '401.html',
]
```

模板文件支持模板引擎中的标签。

> `http_exception_template`配置仅在部署模式下面生效。