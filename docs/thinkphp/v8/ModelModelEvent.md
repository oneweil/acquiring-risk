# 模型事件

## 模型事件

模型事件是指在进行模型的查询和写入操作的时候触发的操作行为。

> 模型事件只在调用模型的方法生效，使用Db查询构造器操作是无效的

模型支持如下事件：

|事件|描述|事件方法名|
|---|---|---|
|AfterRead|查询后|onAfterRead|
|BeforeInsert|新增前|onBeforeInsert|
|AfterInsert|新增后|onAfterInsert|
|BeforeUpdate|更新前|onBeforeUpdate|
|AfterUpdate|更新后|onAfterUpdate|
|BeforeWrite|写入前|onBeforeWrite|
|AfterWrite|写入后|onAfterWrite|
|BeforeDelete|删除前|onBeforeDelete|
|AfterDelete|删除后|onAfterDelete|
|BeforeRestore|恢复前|onBeforeRestore|
|AfterRestore|恢复后|onAfterRestore|

注册的回调方法支持传入一个参数（当前的模型对象实例），但支持依赖注入的方式增加额外参数。可以支持直接通过事件监听和订阅。

```
Event::listen('app\model\User.BeforeUpdate', function($user) {
    // 
});
Event::listen('app\model\User.AfterDelete', function($user) {
    // 
});
```

> 如果`before_write`、`before_insert`、 `before_update` 、`before_delete`事件方法中返回`false`或者抛出`think\exception\ModelEventException`异常的话，则不会继续执行后续的操作。

## 模型事件定义

最简单的方式是在模型类里面定义静态方法来定义模型的相关事件响应。

```
<?php
namespace app\model;

use think\Model;
use app\model\Profile;

class User extends Model
{
    public static function onBeforeUpdate($user)
    {
    	if ('thinkphp' == $user->name) {
        	return false;
        }
    }
    
    public static function onAfterDelete($user)
    {
		Profile::destroy($user->id);
    }
}
```

参数是当前的模型对象实例，支持使用依赖注入传入更多的参数。

如果当前操作无需响应事件，可以使用`withEvent`方法关闭。

```
$user = User::find(1);
$user->name     = 'thinkphp';
$user->email    = 'thinkphp@qq.com';
$user->withEvent(false)->save();
```

## 模型观察者

`V8.1.0+`开始，可以通过注册模型事件观察者，把模型事件单独管理，无需在模型中定义事件响应。

```
<?php
namespace app\observer;

use app\model\User;

class UserObserver 
{
    public function onBeforeUpdate(User $user)
    {
    }
    
    public function onAfterDelete(User $user)
    {
    }
}
```

同样，事件方法名的参数支持使用依赖注入传入更多的参数。

然后在模型中设置观察者

```
<?php
namespace app\model;

use think\Model;
use app\model\Profile;
use app\observer\UserObserver;

class User extends Model
{
    protected $eventObserver = UserObserver::class;
}
```

## 写入事件

`onBeforeWrite`和`onAfterWrite`事件会在`新增`操作和`更新`操作都会触发.

具体的触发顺序:

```
// 执行 onBeforeWrite
// 如果事件没有返回`false`,那么继续执行
// 执行新增或更新操作(onBeforeInsert/onAfterInsert或onBeforeUpdate/onAfterUpdate)
// 新增或更新执行成功
// 执行 onAfterWrite
```

> 注意:模型的新增或更新是自动判断的.

## 自定义事件

可以给模型自定义事件以满足业务需求