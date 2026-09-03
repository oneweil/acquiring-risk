# 查询范围

可以对模型的查询和写入操作进行封装，例如：

```
<?php
namespace app\model;

use think\Model;

class User extends Model
{

    public function scopeThinkphp($query)
    {
        $query->where('name','thinkphp')->field('id,name');
    }
    
    public function scopeAge($query)
    {
        $query->where('age','>',20)->limit(10);
    }    
    
}
```

就可以进行下面的条件查询：

```
// 查找name为thinkphp的用户
User::scope('thinkphp')->find();
// 查找年龄大于20的10个用户
User::scope('age')->select();
// 查找name为thinkphp的用户并且年龄大于20的10个用户
User::scope('thinkphp,age')->select();
```

查询范围的方法可以定义额外的参数，例如User模型类定义如下：

```
<?php
namespace app\model;

use think\Model;

class User extends Model
{
	public function scopeEmail($query, $email)
    {
    	$query->where('email', 'like', '%' . $email . '%');
    }
    
    public function scopeScore($query, $score)
    {
    	$query->where('score', '>', $score);
    }
    
}
```

在查询的时候可以如下使用：

```
// 查询email包含thinkphp和分数大于80的用户
User::email('thinkphp')->score(80)->select();
```

可以直接使用闭包函数进行查询，例如：

```
User::scope(function($query){
    $query->where('age','>',20)->limit(10);
})->select();
```

> 查询范围只能支持`find`或者`select`查询。

## 全局查询范围

支持在模型里面设置`globalScope`属性，定义全局的查询范围

```
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    // 定义全局的查询范围
    protected $globalScope = ['status'];

    public function scopeStatus($query)
    {
        $query->where('status',1);
    }
}
```

然后，执行下面的代码：

```
$user = User::find(1);
```

最终的查询条件会是

```
status = 1 AND id = 1
```

如果需要动态关闭所有的全局查询范围，可以使用：

```
// 关闭全局查询范围
User::withoutGlobalScope()->select();
```

可以使用`withoutGlobalScope`方法动态关闭部分全局查询范围。

```
User::withoutGlobalScope(['status'])->select();
```