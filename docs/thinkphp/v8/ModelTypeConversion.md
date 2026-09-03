# 类型转换

支持给字段设置类型自动转换，会在写入和读取的时候自动进行类型转换处理，例如：

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    protected $type = [
        'status'    =>  'integer',
        'score'     =>  'float',
        'birthday'  =>  'datetime',
        'info'      =>  'array',
    ];
}
```

下面是一个类型自动转换的示例：

```
$user = new User;
$user->status = '1';
$user->score = '90.50';
$user->birthday = '2015/5/1';
$user->info = ['a'=>1,'b'=>2];
$user->save();
var_dump($user->status); // int 1
var_dump($user->score); // float 90.5;
var_dump($user->birthday); // string '2015-05-01 00:00:00'
var_dump($user->info);// array (size=2) 'a' => int 1  'b' => int 2
```

数据库查询默认取出来的数据都是字符串类型，如果需要转换为其他的类型，需要设置，支持的类型包括如下类型：

### `integer`

设置为integer（整型）后，该字段写入和输出的时候都会自动转换为整型。

### `float`

该字段的值写入和输出的时候自动转换为浮点型。

### `boolean`

该字段的值写入和输出的时候自动转换为布尔型。

### `array`

如果设置为强制转换为`array`类型，系统会自动把数组编码为json格式字符串写入数据库，取出来的时候会自动解码。

### `object`

该字段的值在写入的时候会自动编码为json字符串，输出的时候会自动转换为`stdclass`对象。

### `serialize`

指定为序列化类型的话，数据会自动序列化写入，并且在读取的时候自动反序列化。

### `json`

指定为`json`类型的话，数据会自动`json_encode`写入，并且在读取的时候自动`json_decode`处理。

### `timestamp`

指定为时间戳字段类型的话，该字段的值在写入时候会自动使用`strtotime`生成对应的时间戳，输出的时候会自动转换为`dateFormat`属性定义的时间字符串格式，默认的格式为`Y-m-d H:i:s`，如果希望改变其他格式，可以定义如下：

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    protected $dateFormat = 'Y/m/d';
    protected $type = [
        'status'    =>  'integer',
        'score'     =>  'float',
        'birthday'  =>  'timestamp',
    ];
}
```

或者在类型转换定义的时候使用：

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    protected $type = [
        'status'    =>  'integer',
        'score'     =>  'float',
        'birthday'  =>  'timestamp:Y/m/d',
    ];
}
```

然后就可以

```
$user = User::find(1);
echo $user->birthday; // 2015/5/1
```

### `datetime`

和`timestamp`类似，区别在于写入和读取数据的时候都会自动处理成时间字符串`Y-m-d H:i:s`的格式。

## 枚举类型（PHP`8.1+`版本支持）

你可以给字段定义枚举类型（仅支持回退枚举）

```
<?php
namespace app\enum;

Enum Status : int
{
	case Normal = 1;
	case Disabled = 0;
	case Pending  = 2;
}
```

然后，在模型里面定义类型为枚举

```
<?php
namespace app\model;

use app\enum\Status;
use think\Model;

class User extends Model 
{
    protected $type = [
        'status'    =>  Status::class,
    ];
}
```

写入的时候会自动获取枚举的`value`值写入数据库，读取的时候会自动转换为枚举实例（方便调用枚举自定义方法）。

```
$user         = new User;
$user->name   = 'thinkphp';
$user->status = Status::Normal; // 实际写入数据值为 1
$user->save();

$user = User::where('status', Status::Normal)
    ->where('name', 'thinkphp')
    ->find(1);
dump($user->status); // 输出Status枚举对象
```

如果希望读取的时候自动转换为枚举实例的`name`数据，可以开启`enumReadName`属性。

```
<?php
namespace app\model;

use app\enum\Status;
use think\Model;

class User extends Model 
{
    protected $type = [
        'status'    =>  Status::class,
    ];
    protected $enumReadName = true;
}
```

```
$user = User::find(1);
dump($user->status); // 输出 Normal
```

或使用`EnumTransform`接口实现`value`方法自定义输出转换需求。

```
<?php
namespace app\enum;

use think\model\contract\EnumTransform;

Enum Status : int implements EnumTransform
{
	case Normal = 1;
	case Disabled = 0;
	case Pending  = 2;

	public function value()
	{
		return match($this) {
			Status::Normal => '正常',
			Status::Disabled => '禁用',
			Status::Pending  => '待审核',
		};
	}
}
```

```
$user = User::find(1);
dump($user->status); // 输出 正常
```

## 对象类型转换

支持使用对象类名作为类型转换，需要满足下面几个条件之一：

- 该类具有`think\model\contract\FieldTypeTransform`接口实现（优先级最高）
- 该类使用枚举类（参考上面枚举类型）
- 该类具有`__toString`方法实现并且架构函数可以传值