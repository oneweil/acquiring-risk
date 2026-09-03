# 验证码

## 安装

首先使用`Composer`安装`think-captcha`扩展包：

```
composer require topthink/think-captcha
```

> 验证码库需要开启Session才能生效。

## 使用

扩展包内定义了一些常见用法方便使用，可以满足大部分常用场景，以下示例说明。

在模版内添加验证码的显示代码

```
<div>{:captcha_img()}</div>
```

或者

```
<div><img src="{:captcha_src()}" alt="captcha" /></div>
```

> 上面两种的最终效果是一样的，根据需要调用即可。

然后使用框架的内置验证功能（具体可以参考验证章节），添加`captcha`验证规则即可

```
$this->validate($data,[
    'captcha|验证码'=>'require|captcha'
]);
```

如果没有使用内置验证功能，则可以调研内置的函数手动验证

```
if(!captcha_check($captcha)){
 // 验证失败
};
```

如果是多应用模式下，你需要自己注册一个验证码的路由。

```
Route::get('captcha/[:config]','\\think\\captcha\\CaptchaController@index');
```

## 配置

`Captcha`类带有默认的配置参数，支持自定义配置。这些参数包括：

|参数|描述|默认|
|---|---|---|
|codeSet|验证码字符集合|略|
|expire|验证码过期时间（s）|1800|
|math|使用算术验证码|false|
|useZh|使用中文验证码|false|
|zhSet|中文验证码字符串|略|
|useImgBg|使用背景图片|false|
|fontSize|验证码字体大小(px)|25|
|useCurve|是否画混淆曲线|true|
|useNoise|是否添加杂点|true|
|imageH|验证码图片高度，设置为0为自动计算|0|
|imageW|验证码图片宽度，设置为0为自动计算|0|
|length|验证码位数|5|
|fontttf|验证码字体，不设置是随机获取|空|
|bg|背景颜色|[243, 251, 254]|
|reset|验证成功后是否重置|true|

直接在应用的`config`目录下面的`captcha.php`文件中进行设置即可，例如下面的配置参数用于输出4位数字验证码。

```
return [
    'length'    =>  4,
    'codeSet'   =>  '0123456789',
];
```

## 自定义验证码

如果需要自己独立生成验证码，可以调用`Captcha`类（`think\captcha\facade\Captcha`）操作。

在控制器中使用下面的代码进行验证码生成：

```
<?php
namespace app\index\controller;

use think\captcha\facade\Captcha;

class Index 
{
	public function verify()
    {
        return Captcha::create();    
    }
}
```

然后访问下面的地址就可以显示验证码：

```
http://serverName/index/index/verify
```

输出效果如图

![](https://box.kancloud.cn/dcbf30b119dc2bb7ec6f41d943b5646c_250x62.png)

通常可以给验证码地址注册路由

```
Route::get('verify','index/verify');
```

在模板中就可以使用下面的代码显示验证码图片

```
<div><img src="{:url('index/verify')}" alt="captcha" /></div>
```

可以用`Captcha`类的`check`方法检测验证码的输入是否正确，

```
// 检测输入的验证码是否正确，$value为用户输入的验证码字符串
$captcha = new Captcha();
if( !$captcha->check($value))
{
	// 验证失败
}
```

或者直接调用封装的一个验证码检测的函数`captcha_check`

```
// 检测输入的验证码是否正确，$value为用户输入的验证码字符串
if( !captcha_check($value ))
{
	// 验证失败
}
```

如果你需要生成多个不同设置的验证码，可以使用下面的配置方式：

```
<?php
return [
    'verify'=>[
        'codeSet'=>'1234567890'
    ]
];
```

使用指定的配置生成验证码:

```
return Captcha::create('verify');
```

默认情况下，验证码的字体是随机使用扩展包内 `think-captcha/assets/ttfs`目录下面的字体文件，我们可以指定验证码的字体，例如：  
修改或新建配置文件如下:

```
<?php
return [
    'verify'=>[
        'fontttf'=>'1.ttf'
    ]
];
```

```
return Captcha::create('verify');
```

> 默认的验证码字符已经剔除了易混淆的`1l0o`等字符