# 字段映射

## 字段映射

可以统一定义模型属性的字段映射，例如下面的定义把数据表的`name`字段映射为模型的`nickname`属性。

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    protected $mapping = [
        'name'    =>    'nickname', // 数据表的name字段映射为模型的nickname属性
        ...
    ];
}
```

查询User模型数据后（包括数据集）获取该属性或模型输出的时候，会自动处理映射字段。

```
$user = User::find(1);
echo $user->nickname; 
dump($user->toArray());
```

写入或更新数据的时候，也会自动处理映射字段。

```
$user = User::find(1);
$user->nickname = 'new nickname';
$user->save();
```

> 注意：字段映射后获取和设置映射字段的值的时候，字段名必须和映射名保持一致，系统不会自动进行驼峰转换。

也可以在查询的时候动态设置字段映射

```
User::where('status', 1)->select()->mapping(['name' => 'nickname']);
```