# 验证

`think-validate`是一个基于`PHP8.0`的数据验证类库，最新版本已经从`ThinkPHP`核心独立出来单独维护更新，以优异的功能和突出的性能著称，提供了更优秀的性能和开发体验。使用`ThinkPHP`框架的话，无需额外安装会自动依赖，如需用于第三方PHP框架，可以参考[ThinkValidate开发指南](https://doc.thinkphp.cn/@think-validate/default.html)进行安装使用。

## 主要特性

- 基于PHP8和强类型实现
- 内置丰富的验证规则
- 支持验证器类、数组和链式方法定义验证规则
- 支持验证场景和验证分组
- 支持独立数据验证
- 支持枚举验证
- 支持批量验证
- 支持抛出异常

# 验证器

ThinkPHP推荐使用验证器来解决各种验证场景和需求。

## 验证器定义

为具体的验证场景或者数据表定义好验证器类，直接调用验证类的`check`方法即可完成验证，下面是一个例子：

我们定义一个`\app\validate\User`验证器类用于`User`的验证，使用下面的指令快速生成`User`验证器。

```
php think make:validate User
```

在验证器类中定义验证规则

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:25',
        'age'   => 'number|between:1,120',
        'email' => 'email',    
    ];
}
```

可以直接在验证器类中使用`message`属性定义错误提示信息，例如：

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:25',
        'age'   => 'number|between:1,120',
        'email' => 'email',    
    ];
    
    protected $message  =   [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过25个字符',
        'age.number'   => '年龄必须是数字',
        'age.between'  => '年龄只能在1-120之间',
        'email'        => '邮箱格式错误',    
    ];
    
}
```

> 如果没有定义错误提示信息，则使用系统默认的提示信息

## 数据验证

这里以控制器验证为例（这也是推荐的验证方式）进行说明验证器的用法，在需要进行`User`验证的控制器方法中，添加如下代码即可：

```
<?php
namespace app\controller;

use app\validate\User;
use think\exception\ValidateException;

class Index
{
    public function index()
    {
        try {
            validate(User::class)->check([
                'name'  => 'thinkphp',
                'email' => 'thinkphp@qq.com',
            ]);
        } catch (ValidateException $e) {
            // 验证失败 
            dump($e->getError()); // 输出错误信息
            dump($e->getKey()); // 验证错误的字段名
        }
    }
}
```

如果你没有定义验证器，也可以通过`validate`方法进行自定义验证规则

```
<?php
namespace app\controller;

use think\exception\ValidateException;

class Index
{
    public function index()
    {
        try {
            validate([
                'name'  => 'require|max:25',
                'age'   => 'number|between:1,120',
                'email' => 'email',    
            ])->message([
                'name.require' => '名称必须',
                'name.max'     => '名称最多不能超过25个字符',
                'age.number'   => '年龄必须是数字',
                'age.between'  => '年龄只能在1-120之间',
                'email'        => '邮箱格式错误',    
            ])->check([
                'name'  => 'thinkphp',
                'email' => 'thinkphp@qq.com',
            ]);
        } catch (ValidateException $e) {
            // 验证失败 
            dump($e->getError()); // 输出错误信息
            dump($e->getKey()); // 验证错误的字段名
        }
    }
}
```

# 验证规则

验证规则可以通过字符串、数组、闭包和规则类四种方式进行定义。在验证器中，只能使用前两种方式，而闭包和规则类的定义则适用于使用`rule`方法来动态定义验证规则。

## 属性定义

属性定义方式仅限于验证器，通常类似于下面的方式：

```
<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => 'require|max:25',
      'age'   => 'number|between:1,120',
      'email' => 'email',
    ];

}
```

> 系统内置了一些常用的验证规则可以满足大部分的验证需求，具体每个规则的含义参考内置规则一节。

因为一个字段可以使用多个验证规则（如上面的`age`字段定义了`number`和`between`两个验证规则），在一些特殊的情况下，为了避免混淆可以在`rule`属性中使用数组定义规则。

```
<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => ['require', 'max' => 25, 'regex' => '/^[\w|\d]\w+/'],
      'age'   => ['number', 'between' => '1,120'],
      'email' => 'email',
    ];

}
```

`V8.1.0+`版本开始，可以通过`must`属性定义必须验证的规则，无论是否使用了`require`规则。例如，下面定义了`name`和`email`为必须验证。

```
<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $must = ['name', 'email'];
    protected $rule = [
      'name'  => 'max:25',
      'age'   => 'number|between:1,120',
      'email' => 'email',
    ];

}
```

## 方法定义

> 本功能需要`V8.1.2+`版本支持

由于属性定义的局限性，因此如果需要定义一些特殊的验证规则，我们可以改成通过方法定义。

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected function rules()
    {  
        return [
            'name'  => 'require|max:25',
            'age'   => 'number|between:1,120',
            'email' => 'email',    
        ];
    }
}
```

可以在`rules`方法里面返回数组或当前对象，区别主要在于使用返回数组方式，`rule`属性定义的验证规则不再生效。而对象方式的`rules`定义规则会和`rule`属性定义的合并（存在相同的字段则会覆盖）。

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected function rules()
    {  
        return $this
            ->rule('name', 'require|max:25', ['require' => '名称必须', 'max' => '名称最多不能超过25个字符'])
            ->rule('age', 'number|between:1,120', ['number' => '年龄必须是数字', 'between' => '年龄只能在1-120之间'])
            ->rule('email', 'email', '邮箱格式错误');           
    }
}
```

如果没有使用验证器而是独立验证（即手动调用验证类进行验证）方式的话，通常使用`rule`方法进行验证规则的设置，举例说明如下。独立验证通常使用`Facade`或者自己实例化验证类。

```
$validate = \think\facade\Validate::rule('age', 'number|between:1,120')
->rule([
    'name'  => 'require|max:25',
    'email' => 'email'
]);

$data = [
    'name'  => 'thinkphp',
    'email' => 'thinkphp@qq.com'
];

if (!$validate->check($data)) {
    dump($validate->getError());
}
```

> `rule`方法传入数组表示批量设置规则。

`rule`方法还可以支持使用对象化的规则定义。

我们把上面的验证代码改为

```
use think\facade\Validate;
use think\validate\ValidateRule as Rule;

$validate = Validate::rule('age', Rule::isNumber()->between([1,120]))
->rule([
    'name'  => Rule::isRequire()->max(25),
    'email' => Rule::isEmail(),
]);

$data = [
    'name'  => 'thinkphp',
    'email' => 'thinkphp@qq.com'
];

if (!$validate->check($data)) {
    dump($validate->getError());
}
```

## 闭包定义

可以对某个字段使用闭包验证，例如：

```
$validate = Validate::rule([
    'name'  => function($value) { 
        return 'thinkphp' == strtolower($value) ? true : false;
    },
]);
```

闭包支持传入两个参数，第一个参数是当前字段的值（必须），第二个参数是所有数据（可选）。

> 如果使用了闭包进行验证，则不再支持对该字段使用多个验证规则。

闭包函数如果返回true则表示验证通过，返回false表示验证失败并使用系统的错误信息，如果返回字符串，则表示验证失败并且以返回值作为错误提示信息。

```
$validate = Validate::rule([
    'name'  => function($value) { 
        return 'thinkphp' == strtolower($value) ? true : '用户名错误';
    },
]);
```

## 规则别名

> 本功能需要`V8.1.2+`版本支持

可以给一些常见的规则定义别名，方便复用。

```
<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $alias = [
        'name'    =>    'require|alphaNum|max:25',
        'age'     =>    'number|between:1,120',
    ];
    protected $rule = [
      // 使用规则别名
      'name'  => 'name', 
      'age'   => 'age',
      'email' => 'email',
    ];
}
```

规则别名对应的规则可以是任何有效的规则定义，包括字符串和数组方式。

## 数组验证

> 本功能需要`V8.1.0+`版本支持

支持对数组元素进行验证

```
<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => 'require|max:25',
      'age'   => 'number|between:1,120',
      // info是一个索引数组 
      'info.email' => 'email',
      'info.score' => 'number',
    ];
}
```

支持多维数组验证

```
<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => 'require|max:25',
      'age'   => 'number|between:1,120',
      // info是一个二维数组 
      'info.*.email' => 'email',
      'info.*.score' => 'number',
    ];
}
```

## 验证集

> 本功能需要`V8.1.2+`版本支持

使用验证集的方式定义验证规则，可以简化数组验证规则定义。例如，

```
$validate = \think\facade\Validate::ruleSet('pay', [
    'title'  => 'require',
    'price'  => 'require|integer',
]);
```

相当于下面的定义

```
$validate = \think\facade\Validate::rule([
    'pay.*.title' => 'require',
    'pay.*.price' => 'require|integer',
]);
```

并且可以支持嵌套定义，例如：

```
$validate = \think\facade\Validate::ruleSet('pay', [
     'total'  => 'require|integer',
     'item.*' => rules([
         'title'  => 'require',
         'price'  => 'require|integer',
     ]),
]);
```

## 批量验证

默认情况下，一旦有某个数据的验证规则不符合，就会停止后续数据及规则的验证，如果希望批量进行验证，可以设置：

```
<?php
namespace app\controller;

use app\validate\User;
use think\exception\ValidateException;

class Index
{
    public function index()
    {
	try {
		validate(User::class)->batch(true)->check([
			'name'  => 'thinkphp',
			'email' => 'thinkphp@qq.com',
		    ]);
	} catch (ValidateException $e) {
            // 验证失败 输出错误信息
            // 错误信息是一个包括字段名和错误信息的数组
            dump($e->getError());
        }
    }
}
```

## 自定义验证规则

系统内置了一些常用的规则（参考后面的内置规则），如果不能满足需求，可以在验证器单独添加额外的验证方法，例如：

```
<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'name'  =>  'checkName:thinkphp',
        'email' =>  'email',
    ];
    
    protected $message = [
        'name'  =>  '用户名必须',
        'email' =>  '邮箱格式错误',
    ];
    
    // 自定义验证规则
    protected function checkName($value, $rule, $data=[])
    {
        return $rule == $value ? true : '名称错误';
    }
}
```

验证方法可以传入的参数共有`5`个（后面三个根据情况选用），依次为：

- 验证数据
- 验证规则
- 全部数据（数组）
- 字段名
- 字段描述

> 自定义的验证规则方法名不能和已有的规则冲突，并且必须使用驼峰命名规范。

## 独立验证

可以对数据进行相关规则的验证，不需要使用验证器。

```
use think\facade\Validate;

Validate::checkRule('thinkphp@qq.com', 'email');
```

`checkRule`方法返回布尔值，验证成功返回true，否则false，可以支持传入多个验证规则

## 全局扩展

你可以在扩展包或者应用里面全局注册验证规则，使用方法

```
Validate::maker(function($validate) {
    $validate->extend('extra', 'extra_validate_callback');
});
```

全局验证规则优先检测，因此你也可以通过全局验证覆盖内置的验证规则。

# 错误信息

验证规则的错误提示信息有三种方式可以定义，如下：

## 使用默认的错误提示信息

如果没有定义任何的验证提示信息，系统会显示默认的错误信息，例如：

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => 'require|max:25',
      'age'   => 'number|between:1,120',
      'email' => 'email',
    ];

}
```

```
$data = [
    'name'  => 'thinkphp',
    'age'   => 121,
    'email' => 'thinkphp@qq.com',
];

$validate = new \app\validate\User;
$result = $validate->check($data);

if(!$result){
    echo $validate->getError();
}
```

会输出 `age只能在 1 - 120 之间`。

可以给`age`字段设置中文名，例如：

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => 'require|max:25',
      'age|年龄'   => 'number|between:1,120',
      'email' => 'email',
    ];

}
```

会输出 `年龄只能在 1 - 120 之间`。

## 单独定义提示信息

如果要输出自定义的错误信息，可以定义`message`属性：

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => 'require|max:25',
      'age'   => 'number|between:1,120',
      'email' => 'email',
    ];

    protected $message = [
      'name.require' => '名称必须',
      'name.max'     => '名称最多不能超过25个字符',
      'age.number'   => '年龄必须是数字',
      'age.between'  => '年龄必须在1~120之间',
      'email'        => '邮箱格式错误',
    ];
}
```

```
$data = [
    'name'  => 'thinkphp',
    'age'   => 121,
    'email' => 'thinkphp@qq.com',
];

$validate = new \app\validate\User;
$result = $validate->check($data);

if(!$result){
    echo $validate->getError();
}
```

会输出 `年龄必须在1~120之间`。

错误信息可以支持数组定义，并且通过JSON方式传给前端。

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => 'require|max:25',
      'age'   => 'number|between:1,120',
      'email' => 'email',
    ];

    protected $message = [
      'name.require' => ['code' => 1001, 'msg' => '名称必须'],
      'name.max'     => ['code' => 1002, 'msg' => '名称最多不能超过25个字符'],
      'age.number'   => ['code' => 1003, 'msg' => '年龄必须是数字'],
      'age.between'  => ['code' => 1004, 'msg' => '年龄必须在1~120之间'],
      'email'        => ['code' => 1005, 'msg' =>'邮箱格式错误'],
    ];
}
```

## 使用多语言

验证信息提示支持多语言功能，你只需要给相关错误提示信息定义语言包，例如：

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
      'name'  => 'require|max:25',
      'age'   => 'number|between:1,120',
      'email' => 'email',
    ];

    protected $message = [
      'name.require' => 'name_require',
      'name.max'     => 'name_max',
      'age.number'   => 'age_number',
      'age.between'  => 'age_between',
      'email'        => 'email_error',
    ];
}
```

你可以在语言包文件中添加下列定义：

```
'name_require '	=>	'姓名必须',
'name_max' 		=>	'姓名最大长度不超过25个字符',
'age_between'	=>	'年龄必须在1~120之间',
'age_number'	=>	'年龄必须是数字',
'email_error'	=>	'邮箱格式错误',
```

> 系统内置的验证错误提示均支持多语言（参考框架目录下的`lang/zh-cn.php`语言定义文件）。

# 验证场景

## 验证场景

> 验证场景仅针对验证器有效，独立验证不存在验证场景的概念

验证器支持定义场景，并且验证不同场景的数据，例如：

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:25',
        'age'   => 'number|between:1,120',
        'email' => 'email',    
    ];
    
    protected $message  =   [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过25个字符',
        'age.number'   => '年龄必须是数字',
        'age.between'  => '年龄只能在1-120之间',
        'email'        => '邮箱格式错误',    
    ];
    
    protected $scene = [
        'edit'  =>  ['name','age'],
    ];    
}
```

然后可以在验证方法中指定验证的场景

```
$data = [
    'name'  => 'thinkphp',
    'age'   => 10,
    'email' => 'thinkphp@qq.com',
];

try {
    validate(app\validate\User::class)
        ->scene('edit')
        ->check($data);
} catch (ValidateException $e) {
    // 验证失败 输出错误信息
    dump($e->getError());
}
```

可以单独为某个场景定义方法（方法的命名规范是`scene`+场景名），并且对某些字段的规则重新设置，例如：

- 注意：场景名不区分大小写，且在调用的时候不能将驼峰写法转为下划线

```
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:25',
        'age'   => 'number|between:1,120',
        'email' => 'email',    
    ];
    
    protected $message  =   [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过25个字符',
        'age.number'   => '年龄必须是数字',
        'age.between'  => '年龄只能在1-120之间',
        'email'        => '邮箱格式错误',    
    ];
    
    // edit 验证场景定义
    public function sceneEdit()
    {
    	return $this->only(['name','age'])
        	->append('name', 'min:5')
            ->remove('age', 'between')
            ->append('age', 'require|max:100');
    }    
}
```

主要方法说明如下：

|方法名|描述|
|---|---|
|only|场景需要验证的字段|
|remove|移除场景中的字段的部分验证规则|
|append|给场景中的字段需要追加验证规则|

如果对同一个字段进行多次规则补充（包括移除和追加），必须使用下面的方式：

```
remove('field', ['rule1','rule2'])
// 或者
remove('field', 'rule1|rule2')
```

下面的方式会导致rule1规则remove不成功

```
remove('field', 'rule1')
->remove('field', 'rule2')
```

# 验证分组

## 验证分组

> 本功能需要`V8.1.2+`版本支持

可以给验证规则定义分组，每个分组的规则彼此独立，但可以共用别名规则和错误信息，相当于把多个验证器类合并成一个验证类。

```
namespace app\validate;

use think\Validate;

class Project extends Validate
{
    protected $message  =   [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过25个字符',
        'age.number'   => '年龄必须是数字',
        'age.between'  => '年龄只能在1-120之间',
        'email'        => '邮箱格式错误',    
    ];
    
    protected $group = [
        'user'  =>  [
            'name'  => 'require|max:25',
            'age'   => 'number|between:1,120',
            'email' => 'email',    
        ],
    ];    
}
```

然后可以在验证方法中指定验证的分组

```
$data = [
    'name'  => 'thinkphp',
    'age'   => 10,
    'email' => 'thinkphp@qq.com',
];

try {
    validate(app\validate\Project::class)
        ->check($data, 'user');
} catch (ValidateException $e) {
    // 验证失败 输出错误信息
    var_dump($e->getError());
}
```

可以单独为某个分组定义方法（方法的命名规范是`rules`+分组名），并且通过数组或方法设置分组规则，例如：

```
namespace app\validate;

use think\Validate;

class Project extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:25',
        'age'   => 'number|between:1,120',
        'email' => 'email',    
    ];
    
    protected $message  =   [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过25个字符',
        'age.number'   => '年龄必须是数字',
        'age.between'  => '年龄只能在1-120之间',
        'email'        => '邮箱格式错误',    
    ];
    
    // 分组规则定义
    public function rulesUser()
    {
    	return [
            'name'  => 'require|max:25',
            'age'   => 'number|between:1,120',
            'email' => 'email',  
        ];
    }    
}
```

可以使用方法定义分组，这个时候必须传入一个`validate`参数，该参数是一个`Validate`对象。

```
namespace app\validate;

use think\Validate;

class Project extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:25',
        'age'   => 'number|between:1,120',
        'email' => 'email',    
    ];
    
    protected $message  =   [
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过25个字符',
        'age.number'   => '年龄必须是数字',
        'age.between'  => '年龄只能在1-120之间',
        'email'        => '邮箱格式错误',    
    ];
    
    // 分组规则定义
    public function rulesUser(Validate $validate)
    {
    	return $validate->rule([
            'name'  => 'require|max:25',
            'age'   => 'number|between:1,120',
            'email' => 'email',  
        ]);
    }    
}
```

# 路由验证

可以在路由规则定义的时候调用`validate`方法指定验证器类对请求的数据进行验证。

例如下面的例子表示对请求数据使用验证器类`app\validate\User`进行自动验证，并且使用`edit`验证场景：

```
Route::post('hello/:id', 'index/hello')
	->validate(\app\validate\User::class,'edit');
```

或者不使用验证器而直接传入验证规则

```
Route::post('hello/:id', 'index/hello')
    ->validate([
        'name'	=>	'require|min:5|max:50',
        'email'	=>	'email',
    ]);
```

`validate`方法也支持传入错误信息

```
Route::post('hello/:id', 'index/hello')
    ->validate([
        'name'	=>	'require|min:5|max:50',
        'email'	=>	'email',
    ], message: [
      'name.require' => '名称必须',
      'name.min'     => '名称最少需要5个字符',
      'name.max'     => '名称最多不能超过50个字符',
      'email'        => '邮箱格式错误',
    ]);
```

也支持使用对象化规则定义

```
Route::post('hello/:id', 'index/hello')
    ->validate([
        'name'	=>	ValidateRule::min(5)->max(50),
        'email'	=>	ValidateRule::isEmail(),
    ]);
```

# 内置规则

系统内置了一些常用的验证规则，可以完成大部分场景的验证需求，包括：

> 验证规则严格区分大小写

## 格式验证类

格式验证类的验证规则如果在使用静态方法调用的时候需要加上`is`（以`number`验证为例，需要使用 `isNumber()`）。

> ### require

验证某个字段必须，例如：

```
'name'=>'require'
```

> 如果验证规则没有添加`require`就表示没有值的话不进行验证

> 由于`require`属于PHP保留字，所以在使用方法验证的时候必须使用`isRequire`或者`must`方法调用。

> ### number

验证某个字段的值是否为纯数字（采用`ctype_digit`验证，不包含负数和小数点），例如：

```
'num'=>'number'
```

> ### integer

验证某个字段的值是否为整数（采用`filter_var`验证），例如：

```
'num'=>'integer'
```

> ### float

验证某个字段的值是否为浮点数字（采用`filter_var`验证），例如：

```
'num'=>'float'
```

> ### boolean 或者 bool

验证某个字段的值是否为布尔值（采用`filter_var`验证），例如：

```
'num'=>'boolean'
```

> ### email

验证某个字段的值是否为email地址（采用`filter_var`验证），例如：

```
'email'=>'email'
```

> ### array

验证某个字段的值是否为数组，例如：

```
'info'=>'array'
```

支持验证数组必须包含某些键名（多个用逗号分割），例如：

```
'info'=>'array:name,email'
```

> ### enum:枚举类名

验证某个字段的值是否在允许的枚举值范围（支持普通枚举、回退枚举和自定义枚举类），例如：

```
'status' => 'enum:' . StatusEnum::class
```

或者简化为

```
'status' => StatusEnum::class
```

使用简化定义的方式只能是普通枚举和回退枚举，或者实现了`think\contract\Enumable`接口的自定义枚举类。

> ### string

验证某个字段的值是否为字符串，例如：

```
'info'=>'string'
```

> ### accepted

待验证的字段必须是 `「yes」` 、`「on」` 、`1`、`「1」`、`true` 或 `「true」`。这对于验证「服务条款」接受或类似字段非常有用。例如：

```
'accept'=>'accepted'
```

> ### acceptedIf:field,value

如果`field`字段的值等于指定`value`值，则验证字段必须为 `「yes」` 、`「on」` 、`1`、`「1」`、`true` 或 `「true」`。这对于验证「服务条款」接受或类似字段非常有用。

> ### declined

验证某个字段的值必须是 `「no」`，`「off」`，`0`，`「0」`，`false` 或者 `「false」` 。

> ### declinedIf:field,value

如果`field`字段的值等于指定`value`值，则验证字段的值必须为`「no」`，`「off」`，`0`，`「0」`，`false` 或者 `「false」` 。

> ### multipleOf:value

验证某个字段的值必须是某个值的倍数，例如：

```
'number'=>'multipleOf:10'
```

> ### date

验证值是否为有效的日期，例如：

```
'date'=>'date'
```

会对日期值进行`strtotime`后进行判断。

> ### alpha

验证某个字段的值是否为纯字母，例如：

```
'name'=>'alpha'
```

> ### alphaNum

验证某个字段的值是否为字母和数字，例如：

```
'name'=>'alphaNum'
```

> ### alphaDash

验证某个字段的值是否为字母和数字，下划线`_`及破折号`-`，例如：

```
'name'=>'alphaDash'
```

> ### chs

验证某个字段的值只能是汉字，例如：

```
'name'=>'chs'
```

> ### chsAlpha

验证某个字段的值只能是汉字、字母，例如：

```
'name'=>'chsAlpha'
```

> ### chsAlphaNum

验证某个字段的值只能是汉字、字母和数字，例如：

```
'name'=>'chsAlphaNum'
```

> ### chsDash

验证某个字段的值只能是汉字、字母、数字和下划线_及破折号-，例如：

```
'name'=>'chsDash'
```

> ### cntrl

验证某个字段的值只能是控制字符（换行、缩进、空格），例如：

```
'name'=>'cntrl'
```

> ### graph

验证某个字段的值只能是可打印字符（空格除外），例如：

```
'name'=>'graph'
```

> ### print

验证某个字段的值只能是可打印字符（包括空格），例如：

```
'name'=>'print'
```

> ### lower

验证某个字段的值只能是小写字符，例如：

```
'name'=>'lower'
```

> ### upper

验证某个字段的值只能是大写字符，例如：

```
'name'=>'upper'
```

> ### space

验证某个字段的值只能是空白字符（包括缩进，垂直制表符，换行符，回车和换页字符），例如：

```
'name'=>'space'
```

> ### xdigit

验证某个字段的值只能是十六进制字符串，例如：

```
'name'=>'xdigit'
```

> ### activeUrl

验证某个字段的值是否为有效的域名或者IP，例如：

```
'host'=>'activeUrl'
```

> ### url

验证某个字段的值是否为有效的URL地址（采用`filter_var`验证），例如：

```
'url'=>'url'
```

> ### ip

验证某个字段的值是否为有效的IP地址（采用`filter_var`验证），例如：

```
'ip'=>'ip'
```

支持验证ipv4和ipv6格式的IP地址。

> ### dateFormat:format

验证某个字段的值是否为指定格式的日期，例如：

```
'create_time'=>'dateFormat:y-m-d'
```

> ### mobile

验证某个字段的值是否为有效的手机，例如：

```
'mobile'=>'mobile'
```

> ### idCard

验证某个字段的值是否为有效的身份证格式，例如：

```
'id_card'=>'idCard'
```

> ### macAddr

验证某个字段的值是否为有效的MAC地址，例如：

```
'mac'=>'macAddr'
```

> ### zip

验证某个字段的值是否为有效的邮政编码，例如：

```
'zip'=>'zip'
```

## 长度和区间验证类

> ### in

验证某个字段的值是否在某个范围，例如：

```
'num'=>'in:1,2,3'
```

> ### notIn

验证某个字段的值不在某个范围，例如：

```
'num'=>'notIn:1,2,3'
```

> ### between

验证某个字段的值是否在某个区间，例如：

```
'num'=>'between:1,10'
```

> ### notBetween

验证某个字段的值不在某个范围，例如：

```
'num'=>'notBetween:1,10'
```

> ### length:num1,num2

验证某个字段的值的长度是否在某个范围，例如：

```
'name'=>'length:4,25'
```

或者指定长度

```
'name'=>'length:4'
```

> 如果验证的数据是数组，则判断数组的长度。  
> 如果验证的数据是File对象，则判断文件的大小。

> ### max:number

验证某个字段的值的最大长度，例如：

```
'name'=>'max:25'
```

> 如果验证的数据是数组，则判断数组的长度。  
> 如果验证的数据是File对象，则判断文件的大小。

> ### min:number

验证某个字段的值的最小长度，例如：

```
'name'=>'min:5'
```

> 如果验证的数据是数组，则判断数组的长度。  
> 如果验证的数据是File对象，则判断文件的大小。

> ### after:日期

验证某个字段的值是否在某个日期之后，例如：

```
'begin_time' => 'after:2016-3-18',
```

> ### before:日期

验证某个字段的值是否在某个日期之前，例如：

```
'end_time'   => 'before:2016-10-01',
```

> ### expire:开始时间,结束时间

验证当前操作（注意不是某个值）是否在某个有效日期之内，例如：

```
'expire_time'   => 'expire:2016-2-1,2016-10-01',
```

> ### allowIp:allow1,allow2,...

验证当前请求的IP是否在某个范围，例如：

```
'name'   => 'allowIp:114.45.4.55',
```

该规则可以用于某个后台的访问权限，多个IP用逗号分隔

> ### denyIp:allow1,allow2,...

验证当前请求的IP是否禁止访问，例如：

```
'name'   => 'denyIp:114.45.4.55',
```

多个IP用逗号分隔

## 字段比较类

> ### confirm:field

验证某个字段是否和另外一个字段的值一致，例如：

```
'repassword'=>'require|confirm:password'
```

支持字段自动匹配验证规则，如`password`和`password_confirm`是自动相互验证的，只需要使用

```
'password'=>'require|confirm'
```

会自动验证和`password_confirm`进行字段比较是否一致，反之亦然。

> ### different:field

验证某个字段是否和另外一个字段的值不一致，例如：

```
'name'=>'require|different:account'
```

> ### startWith:str

验证某个字段是否以某个字符串开头，例如：

```
'name'=>'require|startWith:think'
```

> ### endWith:str

验证某个字段是否以某个字符串结尾，例如：

```
'name'=>'require|endWith:think'
```

> ### contain:str

验证某个字段是否以包含某个字符串，例如：

```
'name'=>'require|contain:think'
```

> ### eq 或者 = 或者 same

验证是否等于某个值，例如：

```
'score'=>'eq:100'
'num'=>'=:100'
'num'=>'same:100'
```

> ### egt 或者 >=

验证是否大于等于某个值，例如：

```
'score'=>'egt:60'
'num'=>'>=:100'
```

> ### gt 或者 >

验证是否大于某个值，例如：

```
'score'=>'gt:60'
'num'=>'>:100'
```

> ### elt 或者 <=

验证是否小于等于某个值，例如：

```
'score'=>'elt:100'
'num'=>'<=:100'
```

> ### lt 或者 <

验证是否小于某个值，例如：

```
'score'=>'lt:100'
'num'=>'<:100'
```

> ### 字段比较

验证对比其他字段大小（数值大小对比），例如：

```
'price'=>'lt:market_price'
'price'=>'<:market_price'
```

## filter验证

支持使用`filter_var`进行验证，例如：

```
'ip'=>'filter:validate_ip'
```

具体支持规则可以参考`filter_list`函数的返回列表。

## 正则验证

支持直接使用正则验证，例如：

```
'zip'=>'\d{6}',
// 或者
'zip'=>'regex:\d{6}',
```

如果你的正则表达式中包含有`|`符号的话，必须使用数组方式定义。

```
'accepted'=>['regex'=>'/^(yes|on|1)$/i'],
```

也可以实现预定义正则表达式后直接调用，例如在验证器类中定义regex属性

```
namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    protected $regex = [ 'zip' => '\d{6}'];
    
    protected $rule = [
        'name'  =>  'require|max:25',
        'email' =>  'email',
    ];

}
```

然后就可以使用

```
'zip'	=>	'regex:zip',
```

## 上传验证

> ### file

验证是否是一个上传文件

> ### image:width,height,type

验证是否是一个图像文件，width height和type都是可选，width和height必须同时定义。

> ### fileExt:允许的文件后缀

验证上传文件后缀

> ### fileMime:允许的文件类型

验证上传文件类型

> ### fileSize:允许的文件字节大小

验证上传文件大小

## 其它验证

> ### token:表单令牌名称

表单令牌验证

> ### unique:table,field,except,pk

验证当前请求的字段值是否为唯一的，例如：

```
// 表示验证name字段的值是否在user表（不包含前缀）中唯一
'name'   => 'unique:user',
// 验证其他字段
'name'   => 'unique:user,account',
// 排除某个主键值
'name'   => 'unique:user,account,10',
// 指定某个主键值排除
'name'   => 'unique:user,account,10,user_id',
```

如果需要对复杂的条件验证唯一，可以使用下面的方式：

```
// 多个字段验证唯一验证条件
'name'   => 'unique:user,status^account',
// 复杂验证条件
'name'   => 'unique:user,status=1&account='.$data['account'],
```

> ### requireIf:field,value

验证某个字段的值等于某个值的时候必须，例如：

```
// 当account的值等于1的时候 password必须
'password'=>'requireIf:account,1'
```

> ### requireWith:field

验证某个字段有值的时候必须，例如：

```
// 当account有值的时候password字段必须
'password'=>'requireWith:account'
```

> ### requireWithout:field

验证某个字段没有值的时候必须，例如：

```
// mobile和phone必须输入一个
'mobile' => 'requireWithout:phone',
'phone'  => 'requireWithout:mobile'
```

> ### requireCallback:callable

验证当某个callable为真的时候字段必须，例如：

```
// 使用check_require方法检查是否需要验证age字段必须
'age'=>'requireCallback:check_require|number'
```

用于检查是否需要验证的方法支持两个参数，第一个参数是当前字段的值，第二个参数则是所有的数据。

```
function check_require($value, $data){
    if(empty($data['birthday'])){
    	return true;
    }
}
```

只有check_require函数返回true的时候age字段是必须的，并且会进行后续的其它验证。

# 表单令牌

## 添加令牌`Token`验证

验证规则支持对表单的令牌验证，首先需要在你的表单里面增加下面隐藏域：

```
<input type="hidden" name="__token__" value="{:token()}" />
```

也可以直接使用

```
{:token_field()}
```

默认的令牌Token名称是`__token__`，如果需要自定义名称及令牌生成规则可以使用

```
{:token_field('__hash__', 'md5')}
```

第二个参数表示token的生成规则，也可以使用闭包。

如果你没有使用默认的模板引擎，则需要自己生成表单隐藏域

```
namespace app\controller;

use think\Request;
use think\facade\View;

class Index
{
    public function index(Request $request)
    {
        $token = $request->buildToken('__token__', 'sha1');
        View::assign('token', $token);
        return View::fetch();
    }
}
```

然后在模板表单中使用：

```
<input type="hidden" name="__token__" value="{$token}" />
```

## AJAX提交

如果是AJAX提交的表单，可以将`token`设置在`meta`中

```
<meta name="csrf-token" content="{:token()}">
```

或者直接使用

```
{:token_meta()}
```

然后在全局Ajax中使用这种方式设置`X-CSRF-Token`请求头并提交：

```
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

## 路由验证

然后在路由规则定义中，使用

```
Route::post('blog/save','blog/save')->token();
```

如果自定义了`token`名称，需要改成

```
Route::post('blog/save','blog/save')->token('__hash__');
```

令牌检测如果不通过，会抛出`think\exception\ValidateException`异常。

## 控制器验证

如果没有使用路由定义，可以在控制器里面手动进行令牌验证

```
namespace app\controller;

use think\exception\ValidateException;
use think\Request;

class Index
{
    public function index(Request $request)
    {
        $check = $request->checkToken('__token__');
        
        if(false === $check) {
            throw new ValidateException('invalid token');
        }

        // ...
    }
}
```

提交数据默认获取`post`数据，支持指定数据进行`Token`验证。

```
namespace app\controller;

use think\exception\ValidateException;
use think\Request;

class Index
{
    public function index(Request $request)
    {
        $check = $request->checkToken('__token__', $request->param());
        
        if(false === $check) {
            throw new ValidateException('invalid token');
        }

        // ...
    }
}
```

## 使用验证器验证

在你的验证规则中，添加`token`验证规则即可，例如，如果使用的是验证器的话，可以改为：

```
protected $rule = [
        'name'  =>  'require|max:25|token',
        'email' =>  'email',
    ];
```

如果你的令牌名称不是`__token__`（假设是`__hash__`)，验证器中需要改为：

```
protected $rule = [
        'name'  =>  'require|max:25|token:__hash__',
        'email' =>  'email',
    ];
```

