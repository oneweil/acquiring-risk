# 命令行

ThinkPHP8支持`Console`应用，通过命令行的方式执行一些URL访问不方便或者安全性较高的操作。

我们可以在cmd命令行下面，切换到应用根目录（注意不是web根目录），然后执行`php think`，会出现下面的提示信息：

```
>php think
version 8.0.0

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -V, --version         Display this console version
  -q, --quiet           Do not output any message
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  build             Build Application Dirs
  clear             Clear runtime file
  help              Displays help for a command
  list              Lists commands
  run               PHP Built-in Server for ThinkPHP
  version           show thinkphp framework version
 make
  make:command      Create a new command class
  make:controller   Create a new resource controller class
  make:event        Create a new event class
  make:listener     Create a new listener class
  make:middleware   Create a new middleware class
  make:model        Create a new model class
  make:service      Create a new Service class
  make:subscribe    Create a new subscribe class
  make:validate     Create a validate class
 optimize
  optimize:route    Build app route cache.
  optimize:schema   Build database schema cache.
 route
  route:list        show route list.
 service
  service:discover  Discover Services for ThinkPHP
 vendor
  vendor:publish    Publish any publishable assets from vendor packages
```

`console`命令的执行格式一般为：

> ### >php think 指令 参数

下面介绍下系统自带的几个命令，包括：

|指令|描述|
|---|---|
|build|自动生成应用目录和文件|
|help|帮助|
|list|指令列表|
|clear|清除缓存指令|
|run|启动PHP内置服务器|
|version|查看当前框架版本号|
|make:controller|创建控制器类|
|make:model|创建模型类|
|make:command|创建指令类文件|
|make:validate|创建验证器类|
|make:middleware|创建中间件类|
|make:event|创建事件类|
|make:listener|创建事件监听器类|
|make:subscribe|创建事件订阅者类|
|make:service|创建系统服务类|
|optimize:schema|生成数据表字段缓存文件|
|optimize:facade|生成Facade注释|
|route:build|生成注解路由|
|route:list|查看路由定义|
|service:discover|自动注册扩展包的系统服务|
|vendor:publish|自动生成扩展的配置文件|

更多的指令可以自己扩展。

# 启动内置服务器

## 启动内置服务器

命令行切换到应用根目录后，输入：

```
>php think run
```

如果启动成功，会输出下面信息，并显示`web`目录位置。

```
ThinkPHP Development server is started On <http://0.0.0.0:8000/>
You can exit with `CTRL-C`
Document root is: D:\WWW\tp6/public
```

然后你可以直接在浏览器里面访问

```
http://127.0.0.1:8000/
```

而无须设置`Vhost`，不过需要注意，这个只有web服务器，其它的例如数据库服务的需要自己单独管理。

支持制定IP和端口访问

```
>php think run -H tp.com -p 80
```

会显示

```
ThinkPHP Development server is started On <http://tp.com:80/>
You can exit with `CTRL-C`
Document root is: D:\WWW\tp6/public
```

然后你可以直接在浏览器里面访问

```
http://tp.com/
```

# 查看版本

## 查看版本

查看当前框架版本

```
php think version
```

输出显示

```
v8.0.0
```

# 自动生成应用目录

ThinkPHP 具备自动创建功能，可以用来自动生成需要的应用及目录结构和文件等。

> 本指令需要安装多应用扩展后才支持。

## 快速生成应用

如果使用了多应用模式，可以快速生成一个应用，例如生成`demo`应用的指令如下：

```
>php think build demo
```

如果看到输出

```
Successed
```

则表示自动生成应用成功。

会自动生成`demo`应用，自动生成的应用目录包含了`controller`、`model`和`view`目录以及`common.php`、`middleware.php`、`event.php`和`provider.php`等文件。

生成成功后，我们可以直接访问`demo`应用

会显示

```
您好！这是一个[demo]示例应用
```

## 应用结构自定义

如果你希望自定义生成应用的结构，可以在app目录下增加一个`build.php`文件，内容如下：

```
return [
    // 需要自动创建的文件
    '__file__'   => [],
    // 需要自动创建的目录
    '__dir__'    => ['controller', 'model', 'view'],
    // 需要自动创建的控制器
    'controller' => ['Index'],
    // 需要自动创建的模型
    'model'      => ['User'],
    // 需要自动创建的模板
    'view'       => ['index/index'],
];
```

可以给定义需要自动生成的文件和目录，以及MVC类。

- `__dir__` 表示生成目录（支持多级目录）
- `__file__` 表示生成文件（默认会生成`common.php`、`middleware.php`、`event.php`和`provider.php`文件，无需定义）
- `controller` 表示生成控制器类
- `model`表示生成模型类
- `view`表示生成模板文件（支持子目录）

并且会自动生成应用的默认`Index`访问控制器文件用于显示应用的欢迎页面。

# 创建类库文件

## 快速生成控制器

快速创建`Blog`控制器类库文件

```
>php think make:controller Blog
```

如果是多应用模式，则需传入应用名

```
>php think make:controller index@Blog
```

默认生成的是一个资源控制器，包含了资源路由对应的操作方法，如果仅仅生成空的控制器则可以使用：

```
>php think make:controller Blog --plain
```

如果需要生成多级控制器，可以使用

```
>php think make:controller test/Blog
```

会生成一个 `app\index\controller\test\Blog` 控制器类。

可以支持 --api 参数生成用于API接口的资源控制器。

## 快速生成模型

和生成控制器类似，执行下面的指令可以生成`Blog`模型类

```
>php think make:model Blog
```

## 生成带后缀的类库

如果要生成带后缀的类库，可以直接使用：

```
>php think make:controller BlogController
```

```
>php think make:model BlogModel
```

## 快速生成中间件

可以使用下面的指令生成一个中间件类。

```
>php think make:middleware Auth
```

会自动生成一个 `app\middleware\Auth`类文件。

## 创建验证器类

可以使用

```
>php think make:validate User
```

生成一个 `app\validate\User` 验证器类，然后添加自己的验证规则和错误信息。

# 清除缓存文件

## 清除缓存文件`clear`

如果需要清除应用的缓存文件，可以使用下面的命令：

```
php think clear
```

不带任何参数调用`clear`命令的话，会清除`runtime`目录（包括模板缓存、日志文件及其子目录）下面的所有的文件，但会保留目录。

如果不需要保留空目录，可以使用

```
php think clear --dir
```

清除日志目录

```
php think clear --log
```

清除日志目录并删除空目录

```
php think clear --log --dir
```

清除数据缓存目录

```
php think clear --cache
```

清除数据缓存目录并删除空目录

```
php think clear --cache --dir
```

如果需要清除某个指定目录下面的文件，可以使用：

```
php think clear --path d:\www\tp\runtime\log\
```

# 生成数据表字段缓存

## 生成数据表字段缓存`optimize:schema`

> 字段缓存仅在部署模式下生效，并且仅适用于使用`think-orm`的情况，如果你使用了其它的ORM库，则不支持生成。

可以通过生成数据表字段信息缓存，提升数据库查询的性能，避免多余的查询。命令如下：

```
php think optimize:schema
```

如果是多应用模式，你可以使用下面的指令生成`admin`应用的字段缓存。

```
php think optimize:schema admin
```

会自动生成当前数据库配置文件中定义的数据表字段缓存，也可以指定数据库连接生成字段缓存（必须有用户权限），例如，下面指定生成`mysql`数据库连接下面的所有数据表的字段缓存信息。

```
php think optimize:schema --connection mysql
```

> 没有继承think\Model类的（抽象）模型类不会生成。如果定义了公共模型类，最好把公共模型类定义为抽象类（`abstract`）。

更新数据表字段缓存也是同样的方式，每次执行都会重新生成缓存。如果需要单独更新某个数据表的缓存，可以使用：

```
php think optimize:schema --table think_user
```

支持指定数据库名称

```
php think optimize:schema --table demo.think_user
```

# 生成路由映射缓存

## 生成路由映射缓存`optimize:route`

> 路由映射缓存用于开启路由延迟解析的情况下，支持路由反解的URL生成，如果你没有开启路由延迟解析或者没有使用URL路由反解生成则不需要生成。

生成路由映射缓存的命令：

```
php think optimize:route
```

执行后，会在`runtime`目录下面生成`route.php`文件。

如果是多应用模式的话，需要增加应用名参数调用指令

```
php think optimize:route index
```

# 生成配置缓存

## 生成配置缓存

在生产环境下面，你可以通过下面的指令生成配置缓存文件。

```
php think optimize:config
```

执行后，会在`runtime`目录下面生成`config.php`文件。

如果是多应用模式的话，需要增加应用名参数调用指令

```
php think optimize:config index
```

# 输出路由定义

## 输出并生成路由列表

假设你的路由定义文件内容为：

```
Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

Route::resource('blog', 'Blog');

Route::get('hello/:name', 'index/hello')->ext('html');
```

可以使用下面的指令查看定义的路由列表

```
php think route:list
```

如果是多应用模式的话，需要改成

```
php think route:list index
```

输出结果类似于下面的显示：

```
+----------------+-------------+--------+-------------+
| Rule           | Route       | Method | Name        | 
+----------------+-------------+--------+-------------+
| think          | <Closure>   | get    |             | 
| hello/<name>   | index/hello | get    | index/hello | 
| blog           | Blog/index  | get    | Blog/index  | 
| blog           | Blog/save   | post   | Blog/save   |  
| blog/create    | Blog/create | get    | Blog/create |  
| blog/<id>/edit | Blog/edit   | get    | Blog/edit   | 
| blog/<id>      | Blog/read   | get    | Blog/read   |
| blog/<id>      | Blog/update | put    | Blog/update | 
| blog/<id>      | Blog/delete | delete | Blog/delete | 
+----------------+-------------+--------+-------------+
```

并且同时会在runtime目录下面生成一个`route_list.php`的文件，内容和上面的输出结果一致，方便你随时查看。

> 如果你的路由定义发生改变的话， 则需要重新调用该指令，会自动更新上面生成的缓存文件。

## 输出样式

支持定义不同的样式输出，例如：

```
php think route:list box
```

输出结果变为：

```
┌────────────────┬─────────────┬────────┬─────────────┐
│ Rule           │ Route       │ Method │ Name        │ 
├────────────────┼─────────────┼────────┼─────────────┤
│ think          │ <Closure>   │ get    │             │ 
│ hello/<name>   │ index/hello │ get    │ index/hello │
│ blog           │ Blog/index  │ get    │ Blog/index  │
│ blog           │ Blog/save   │ post   │ Blog/save   │
│ blog/create    │ Blog/create │ get    │ Blog/create │
│ blog/<id>/edit │ Blog/edit   │ get    │ Blog/edit   │
│ blog/<id>      │ Blog/read   │ get    │ Blog/read   │ 
│ blog/<id>      │ Blog/update │ put    │ Blog/update │
│ blog/<id>      │ Blog/delete │ delete │ Blog/delete │
└────────────────┴─────────────┴────────┴─────────────┘
```

```
php think route:list box-double
```

输出结果变为：

```
╔════════════════╤═════════════╤════════╤═════════════╗
║ Rule           │ Route       │ Method │ Name        ║
╠────────────────╪─────────────╪────────╪─────────────╣
║ think          │ <Closure>   │ get    │             ║
║ hello/<name>   │ index/hello │ get    │ index/hello ║
║ blog           │ Blog/index  │ get    │ Blog/index  ║
║ blog           │ Blog/save   │ post   │ Blog/save   ║
║ blog/create    │ Blog/create │ get    │ Blog/create ║
║ blog/<id>/edit │ Blog/edit   │ get    │ Blog/edit   ║
║ blog/<id>      │ Blog/read   │ get    │ Blog/read   ║
║ blog/<id>      │ Blog/update │ put    │ Blog/update ║
║ blog/<id>      │ Blog/delete │ delete │ Blog/delete ║
╚════════════════╧═════════════╧════════╧═════════════╝
```

```
php think route:list markdown
```

输出结果变为：

```
| Rule           | Route       | Method | Name        |
|----------------|-------------|--------|-------------|
| think          | <Closure>   | get    |             |
| hello/<name>   | index/hello | get    | index/hello |
| blog           | Blog/index  | get    | Blog/index  |
| blog           | Blog/save   | post   | Blog/save   |
| blog/create    | Blog/create | get    | Blog/create |
| blog/<id>/edit | Blog/edit   | get    | Blog/edit   |
| blog/<id>      | Blog/read   | get    | Blog/read   | 
| blog/<id>      | Blog/update | put    | Blog/update |
| blog/<id>      | Blog/delete | delete | Blog/delete | 
```

## 排序支持

如果你希望生成的路由列表按照路由规则排序，可以使用

```
php think route:list -s rule
```

输出结果变成：

```
+----------------+-------------+--------+-------------+
| Rule           | Route       | Method | Name        | 
+----------------+-------------+--------+-------------+
| blog           | Blog/index  | get    | Blog/index  |
| blog           | Blog/save   | post   | Blog/save   | 
| blog/<id>      | Blog/read   | get    | Blog/read   |
| blog/<id>      | Blog/update | put    | Blog/update |
| blog/<id>      | Blog/delete | delete | Blog/delete | 
| blog/<id>/edit | Blog/edit   | get    | Blog/edit   | 
| blog/create    | Blog/create | get    | Blog/create |
| hello/<name>   | index/hello | get    | index/hello |
| think          | <Closure>   | get    |             |
+----------------+-------------+--------+-------------+
```

同样的，你还可以按照请求类型排序

```
php think route:list -s method
```

输出结果变为：

```
+----------------+-------------+--------+-------------+
| Rule           | Route       | Method | Name        | 
+----------------+-------------+--------+-------------+
| blog/<id>      | Blog/delete | delete | Blog/delete |
| think          | <Closure>   | get    |             |
| hello/<name>   | index/hello | get    | index/hello |
| blog           | Blog/index  | get    | Blog/index  |
| blog/create    | Blog/create | get    | Blog/create | 
| blog/<id>/edit | Blog/edit   | get    | Blog/edit   |
| blog/<id>      | Blog/read   | get    | Blog/read   |
| blog           | Blog/save   | post   | Blog/save   |
| blog/<id>      | Blog/update | put    | Blog/update | 
+----------------+-------------+--------+-------------+
```

> 支持排序的字段名包括：`rule`、`route`、`name`、`method`和`domain`（全部小写）。

## 输出详细信息

如果你希望看到更多的路由参数和变量规则，可以使用

```
php think route:list -m
```

输出结果变为：

```
+----------------+-------------+--------+-------------+--------+-------------------------+---------+
| Rule           | Route       | Method | Name        | Domain | Option                  | Pattern |
+----------------+-------------+--------+-------------+--------+-------------------------+---------+
| think          | <Closure>   | get    |             |        | []                      | []      |
| hello/<name>   | index/hello | get    | index/hello |        | {"ext":"html"}          | []      |
| blog           | Blog/index  | get    | Blog/index  |        | {"complete_match":true} | []      |
| blog           | Blog/save   | post   | Blog/save   |        | {"complete_match":true} | []      |
| blog/create    | Blog/create | get    | Blog/create |        | []                      | []      |
| blog/<id>/edit | Blog/edit   | get    | Blog/edit   |        | []                      | []      |
| blog/<id>      | Blog/read   | get    | Blog/read   |        | []                      | []      |
| blog/<id>      | Blog/update | put    | Blog/update |        | []                      | []      |
| blog/<id>      | Blog/delete | delete | Blog/delete |        | []                      | []      |
+----------------+-------------+--------+-------------+--------+-------------------------+-------
```

# 自定义指令

## 创建自定义指令

第一步，创建一个自定义命令类文件，运行指令

```
php think make:command Hello hello
```

会生成一个`app\command\Hello`命令行指令类，指令名为hello，我们修改内容如下：

```
<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Hello extends Command
{
    protected function configure()
    {
        $this->setName('hello')
        	->addArgument('name', Argument::OPTIONAL, "your name")
            ->addOption('city', null, Option::VALUE_REQUIRED, 'city name')
        	->setDescription('Say Hello');
    }

    protected function execute(Input $input, Output $output)
    {
    	$name = trim($input->getArgument('name'));
      	$name = $name ?: 'thinkphp';

		if ($input->hasOption('city')) {
        	$city = PHP_EOL . 'From ' . $input->getOption('city');
        } else {
        	$city = '';
        }
        
        $output->writeln("Hello," . $name . '!' . $city);
    }
}
```

这个文件定义了一个叫`hello`的命令，并设置了一个`name`参数和一个`city`选项。

第二步，配置`config/console.php`文件

```
<?php
return [
    'commands' => [
        'hello' => 'app\command\Hello',
    ]
];
```

第三步，测试-命令帮助-命令行下运行

```
php think
```

输出

```
Think Console version 0.1

Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -V, --version         Display this console version
  -q, --quiet           Do not output any message
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  build              Build Application Dirs
  clear              Clear runtime file
  hello              Say Hello 
  help               Displays help for a command
  list               Lists commands
 make
  make:controller    Create a new resource controller class
  make:model         Create a new model class
 optimize
  optimize:autoload  Optimizes PSR0 and PSR4 packages to be loaded with classmaps too, good for production.
  optimize:config    Build config and common file cache.
  optimize:schema    Build database schema cache.

```

第四步，运行`hello`命令

```
php think hello
```

输出

```
Hello thinkphp!
```

添加命令参数

```
php think hello kancloud
```

输出

```
Hello kancloud!
```

添加`city`选项

```
php think hello kancloud --city shanghai
```

输出

```
Hello kancloud!
From shanghai
```

> 注意看参数和选项的调用区别

如果需要生成一个指定的命名空间，可以使用：

```
php think make:command app\index\Command second
```

## 在控制器中调用命令

支持在控制器的操作方法中直接调用命令，例如：

```
<?php
namespace app\index\controller;

use think\facade\Console;

class Index
{
    public function hello($name)
    {
        $output = Console::call('hello', [$name]);

        return $output->fetch();
    }
}
```

访问该操作方法后，例如：

```
http://serverName/index/hello/name/thinkphp
```

页面会输出

```
Hello thinkphp!
```

## 命令行选项

```
use think\\console\\input\\Option;

 // 无需传值
 Option::VALUE_NONE     = 1;
 // 必须传值
 Option::VALUE_REQUIRED = 2;
 // 可选传值
 Option::VALUE_OPTIONAL = 4;
 // 传数组值
 Option::VALUE_IS_ARRAY = 8;
```

## addOption

```
/**
* 添加选项
* @param  string $name        选项名称
* @param  string $shortcut    别名
* @param  int $mode        类型
* @param  string $description 描述
* @param  mixed $default     默认值
* @return  Command
*/
 public function addOption(string  $name, string  $shortcut = null, int  $mode = null, string  $description = '', $default = null)
```

### 示例

定义如下:

```
->addOption('adminname', 'p', Option::VALUE_OPTIONAL, '管理员账号')
```

调用方式可以如下:

```
// 无需任何参数
php think install
// 使用全名参数
php think install --adminuser admin
// 使用缩写
php think install -u admin
```

指令逻辑中可以这样获取:

```
$input->getOption('adminname');
```

# Debug输出级别

默认情况下，命令行中输出的内容很少，即便是异常和报错也如此，可以通过调整输出级别，显示更多信息。

- 不显示信息(静默)

```
php think runcmd 
php think runcmd -q
php think runcmd --quiet
```

- 详细信息

```
php think runcmd -v
```

- 非常详细的信息

```
php think runcmd -vv
```

- 调试信息

```
php think runcmd -vvv
```

开发者可以判断输出级别，在命令行中进行不同的输出：

```
$output->isDebug();            // 调试模式
$output->isVeryVerbose();    // 非常详细的信息
$output->isVerbose();            // 详细信息
$output->isQuiet();                // 静默信息
$output->getVerbosity();        // 获取输出级别常量，可参考\think\\console\Output
```