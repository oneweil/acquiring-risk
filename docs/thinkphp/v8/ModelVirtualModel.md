# 虚拟模型

## 虚拟模型

虚拟模型不会写入数据库，数据只能保存在内存中，而且只能通过实例化的方式来创建数据，虚拟模型可以保留模型的大部分功能，包括获取器、模型事件，甚至是关联操作。

要使用虚拟模型，只需要在模型定义的时候引入`Virtual` trait，例如：

```
<?php
namespace app\model;

use think\Model;
use think\model\concern\Virtual;

class User extends Model
{
    use Virtual;

    public function blog()
    {
        return $this->hasMany('Blog');
    }
}
```

你不需要在数据库中定义user表，但仍然可以进行相关数据操作，下面是一些例子。

```
// 创建数据
$user = User::create($data);
// 修改数据
$user->name = 'thinkphp';
$user->save();
// 获取关联数据
$blog = $user->blog()->limit(3)->select();
// 删除数据（同时删除关联blog数据）
$user->together(['blog'])->delete();
```

由于虚拟模型没有实际的数据表，所以你不能进行查询操作，下面的代码就会抛出异常

```
User::find(1);
// 会抛出下面的异常
// virtual model not support db query
```

> 另外，注意，虚拟模型不再支持自动时间戳功能，如果需要时间字段需要在实例化的时候传入。