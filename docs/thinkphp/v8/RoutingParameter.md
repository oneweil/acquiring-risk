# 路由参数

## 路由参数

路由分组及规则定义支持指定路由参数，这些参数主要完成路由匹配检测以及后续行为。

> 路由参数可以在定义路由规则的时候直接传入（批量），推荐使用方法配置更加清晰。

|参数|说明|方法名|
|---|---|---|
|ext|URL后缀检测，支持匹配多个后缀|ext|
|deny_ext|URL禁止后缀检测，支持匹配多个后缀|denyExt|
|https|检测是否https请求|https|
|domain|域名检测|domain|
|complete_match|是否完整匹配路由|completeMatch|
|model|绑定模型|model|
|cache|请求缓存|cache|
|ajax|Ajax检测|ajax|
|pjax|Pjax检测|pjax|
|json|JSON检测|json|
|validate|绑定验证器类进行数据验证|validate|
|append|追加额外的参数|append|
|middleware|注册路由中间件|middleware|
|filter|请求变量过滤|filter|
|match|路由闭包检测|match|
|var_rule|路由变量检测验证（`V8.1.0+`）|when|
|default|路由可选变量的默认值（`V8.1.0+`）|default|
|header|Header检测（`V8.1.3+`）|header|
|version|路由版本检测（`V8.1.3+`）|version|

用法举例：

```
Route::get('new/:id', 'News/read')
    ->ext('html')
    ->https();
```

> 这些路由参数可以混合使用，只要有任何一条参数检查不通过，当前路由就不会生效，继续检测后面的路由规则。

如果你需要批量设置路由参数，也可以使用`option`方法。

```
Route::get('new/:id', 'News/read')
    ->option([
        'ext'   => 'html',
        'https' => true
    ]);
```

### URL后缀

> URL后缀如果是全局统一的话，可以在路由配置文件中设置`url_html_suffix`参数，如果当前访问的URL地址中的URL后缀是允许的伪静态后缀，那么后缀本身是不会被作为参数值传入的。

不同参数设置的区别如下：

|配置值|描述|
|---|---|
|`false`|禁止伪静态访问|
|空字符串|允许任意伪静态后缀|
|`html`|只允许设置的伪静态后缀|
|`html\|htm`|允许多个伪静态后缀|

```
// 定义GET请求路由规则 并设置URL后缀为html的时候有效
Route::get('new/:id', 'News/read')
    ->ext('html');
```

支持匹配多个后缀，例如：

```
Route::get('new/:id', 'News/read')
    ->ext('shtml|html');
```

> 如果`ext`方法不传入任何值，表示不允许使用任何后缀访问。

可以设置禁止访问的URL后缀，例如：

```
// 定义GET请求路由规则 并设置禁止URL后缀为png、jpg和gif的访问
Route::get('new/:id', 'News/read')
    ->denyExt('jpg|png|gif');
```

> 如果`denyExt`方法不传入任何值，表示必须使用后缀访问。

### 域名检测

支持使用完整域名或者子域名进行检测，例如：

```
// 完整域名检测 只在news.thinkphp.cn访问时路由有效
Route::get('new/:id', 'News/read')
    ->domain('news.thinkphp.cn');
// 子域名检测
Route::get('new/:id', 'News/read')
    ->domain('news');
```

> 如果需要给子域名定义批量的路由规则，建议使用`domain`方法进行路由定义。

### `HTTPS`检测

支持检测当前是否`HTTPS`访问

```
// 必须使用HTTPS访问
Route::get('new/:id', 'News/read')
    ->https();
```

### `AJAX`/`PJAX`/`JSON`检测

可以检测当前是否为`AJAX`/`PJAX`/`JSON`请求。

```
// 必须是JSON请求访问
Route::get('new/:id', 'News/read')
    ->json();
```

### `Header`信息检测（`V8.1.3+`）

可以检测当前请求的Header信息是否满足特定要求。

```
// 检测是否为JSON请求
Route::get('new/:id', 'News/read')
    ->header(['Content-Type' => 'application/json']);
```

### 版本检测（`V8.1.3+`）

系统内置了一个用于路由版本检测的`version`方法，如果你的路由需要区分不同的版本，可以使用下面的方式定义：

```
// 检测是否为2.0版本的路由
Route::get('new/:id', 'News/get')->version('2.0');
// 默认版本的路由
Route::get('new/:id', 'News/read');
```

版本检测使用`header`信息检测方式，默认的路由版本的`header`变量名是`Api-Version`，你可以在路由配置文件中修改`api_version`配置参数的值来变更。

### 请求变量检测

可以在匹配路由地址之外，额外检查请求变量是否匹配，只有指定的请求变量也一致的情况下才能匹配该路由。

```
// 检查type和status变量是否满足
Route::post('new/:id', 'News/save')
    ->filter([ 'type' => 1,'status'=> 1 ]);       
```

使用`filter`方法对请求变量的值进行验证，无论是否有值都会进行验证。

如果需要检测变量是否满足某个验证规则，而不是值，可以使用

```
Route::post('new/:id', 'News/save')
    ->when('type', TypeEnum::class)
    ->when('id', 'number');       
```

`when`方法支持对路由变量和请求变量进行验证（只有当有值的情况才会验证），如果验证不通过，则不匹配该路由。

### 闭包检测

可以通过闭包来检测当前路由或分组是否匹配

```
// 闭包检测
Route::get('new/:id', 'News/read')
    ->match(function(Rule $rule,Request $request) {
        // 如果返回false 则视为不匹配
        return false;
    });
```

### 追加额外参数

可以在定义路由的时候隐式追加额外的参数，这些参数不会出现在URL地址中。

```
Route::get('blog/:id', 'Blog/read')
    ->append(['app_id' => 1, 'status' => 1]);
```

在路由请求的时候会同时传入`app_id`和`status`两个参数。

### 路由绑定模型

路由规则和分组支持绑定模型数据，例如：

```
Route::get('hello/:id', 'index/hello')
    ->model('id', '\app\index\model\User');
```

会自动给当前路由绑定 `id`为 当前路由变量值的`User`模型数据。

如果你的模型绑定使用的是`id`作为查询条件的话，还可以简化成下面的方式

```
Route::get('hello/:id', 'index/hello')
    ->model('\app\index\model\User');
```

默认情况下，如果没有查询到模型数据，则会抛出异常，如果不希望抛出异常，可以使用

```
Route::rule('hello/:id', 'index/hello')
    ->model('id', '\app\index\model\User', false);
```

可以定义模型数据的查询条件，例如：

```
Route::rule('hello/:name/:id', 'index/hello')
    ->model('id&name', '\app\index\model\User');
```

表示查询`id`和`name`的值等于当前路由变量的模型数据。

也可以使用闭包来自定义返回需要的模型对象

```
Route::rule('hello/:id', 'index/hello')
    ->model(function ($id) {
        $model = new \app\index\model\User;
        return $model->where('id', $id)->find();
    });
```

闭包函数的参数就是当前请求的URL变量信息。

> 绑定的模型可以直接在控制器的架构方法或者操作方法中自动注入，具体可以参考请求章节的依赖注入。

## 请求缓存

可以对当前的路由请求进行请求缓存处理，例如：

```
Route::get('new/:name$', 'News/read')
    ->cache(3600);
```

表示对当前路由请求缓存`3600`秒，更多内容可以参考请求缓存一节。

## 动态参数

如果你需要额外自定义一些路由参数，可以使用下面的方式：

```
Route::get('new/:name$', 'News/read')
    ->option('rule','admin');
```

或者使用动态方法

```
Route::get('new/:name$', 'News/read')
    ->rule('admin');
```

在后续的路由行为后可以调用该路由的`rule`参数来进行权限检查。