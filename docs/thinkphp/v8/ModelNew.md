# 新增

模型数据的新增和数据库的新增数据有所区别，数据库的新增只是单纯的写入给定的数据，而模型的数据写入会包含修改器、自动完成以及模型事件等环节，数据库的数据写入参考数据库章节。

## 添加一条数据

第一种是实例化模型对象后赋值并保存：

```
$user           = new User;
$user->name     = 'thinkphp';
$user->email    = 'thinkphp@qq.com';
$user->save();
```

> `save`方法成功会返回`true`，并且只有当`before_insert`事件返回`false`的时候返回`false`，一旦有错误就会抛出异常。所以无需判断返回类型。

也可以直接传入数据到`save`方法批量赋值：

```
$user = new User;
$user->save([
    'name'  =>  'thinkphp',
    'email' =>  'thinkphp@qq.com'
]);
```

> `save`方法支持传入模型实例或实体对象实例。

默认只会写入数据表已有的字段，如果你通过外部提交赋值给模型，并且希望指定某些字段写入，可以使用：

```
$user = new User;
// post数组中只有name和email字段会写入
$user->allowField(['name','email'])->save($_POST);
```

最佳的建议是模型数据赋值之前就进行数据过滤，例如：

```
$user = new User;
// 过滤post数组中的非数据表字段数据
$data = Request::only(['name','email']);
$user->save($data);
```

> `save`方法新增数据返回的是布尔值。

## `Replace`写入

`save`方法可以支持`replace`写入。

```
$user           = new User;
$user->name     = 'thinkphp';
$user->email    = 'thinkphp@qq.com';
$user->replace()->save();
```

> 注意不是所有的数据库都支持`replace`写入。

## 获取自增ID

如果要获取新增数据的自增ID，可以使用下面的方式：

```
$user           = new User;
$user->name     = 'thinkphp';
$user->email    = 'thinkphp@qq.com';
$user->save();
// 获取自增ID
echo $user->id;
```

这里其实是获取模型的主键，如果你的主键不是`id`，而是`user_id`的话，其实获取自增ID就变成这样：

```
$user           = new User;
$user->name     = 'thinkphp';
$user->email    = 'thinkphp@qq.com';
$user->save();
// 获取自增ID
echo $user->user_id;
```

如果希望自动获取主键值，也可以使用`getKey`方法。

```
$user           = new User;
$user->name     = 'thinkphp';
$user->email    = 'thinkphp@qq.com';
$user->save();
// 获取自增ID
echo $user->getKey();
```

> 不要在同一个实例里面多次新增数据，如果确实需要多次新增，可以使用后面的静态方法处理。

## 主键自动写入

如果你希望主键自动写入（不采用数据库的自增主键机制），可以在模型类中定义如下。

```
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    protected $autoWriteId = true;
    protected function autoWriteId()
    {
        // 假设在公共函数文件中定义了uuid函数
        return uuid();
    }
}
```

在新增数据的时候会自动写入主键字段值

```
$user = User::create([
    'name'  =>  'thinkphp',
    'email' =>  'thinkphp@qq.com'
]);
echo $user->name;
echo $user->email;
echo $user->id; // 获取主键值 EA9AFB21-8F21-8507-7379-814CAC5A419B
```

## 数据自动写入

如果你需要在创建数据的时候自动写入相关字段，可以通过定义`insert`属性并配合修改器来完成。下面是一个自动写入订单编号的例子：

```
<?php
namespace app\model;

use think\Model;

class Order extends Model
{
    protected $insert = ['order_no'];
    protected $readonly = ['order_no'];
    protected function setOrderNoAttr($value, $data)
    {
        return $data['type'] . '-' . date('YmdHis') . mt_rand(1000, 9999);
    }
}
```

创建订单会自动写入订单编号字段`order_no`，自动写入的前提是必须有定义修改器方法或定义了类型转换接口实现。同时这里还设置了只读字段，更新的时候不会写入。

如果你需要自动写入一个固定的值，可以使用下面的定义

```
<?php
namespace app\model;
use think\Model;

class Order extends Model
{
    protected $insert = ['status' => 1];
}
```

在新增数据的时候会自动写入`status`为1的状态值。

## 批量增加数据

支持批量新增，可以使用：

```
$user = new User;
$list = [
    ['name'=>'thinkphp','email'=>'thinkphp@qq.com'],
    ['name'=>'onethink','email'=>'onethink@qq.com']
];
$user->saveAll($list);
```

> `saveAll`方法新增数据返回的是包含新增模型（带自增ID）的数据集对象。

`saveAll`方法新增数据默认会自动识别数据是需要新增还是更新操作，当数据中存在主键的时候会认为是更新操作。

## 静态方法

还可以直接静态调用`create`方法创建并写入：

```
$user = User::create([
    'name'  =>  'thinkphp',
    'email' =>  'thinkphp@qq.com'
]);
echo $user->name;
echo $user->email;
echo $user->id; // 获取自增ID
```

> 和`save`方法不同的是，`create`方法返回的是当前模型的对象实例。

`create`方法默认会过滤不是数据表的字段信息，可以在第二个参数可以传入允许写入的字段列表，例如：

```
// 只允许写入name和email字段的数据
$user = User::create([
    'name'  =>  'thinkphp',
    'email' =>  'thinkphp@qq.com'
], ['name', 'email']);
echo $user->name;
echo $user->email;
echo $user->id; // 获取自增ID
```

支持`replace`操作，使用下面的方法：

```
$user = User::create([
    'name'  =>  'thinkphp',
    'email' =>  'thinkphp@qq.com'
], ['name','email'], true);
```

## 最佳实践

> 新增数据的最佳实践原则：使用`create`方法新增数据，使用`saveAll`批量新增数据。