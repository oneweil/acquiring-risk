# 模型关联

## 模型关联

通过模型关联操作把数据表的关联关系对象化，解决了大部分常用的关联场景，封装的关联操作比起常规的数据库联表操作更加智能和高效，并且直观。

> 避免在模型内部使用复杂的`join`查询和视图查询。

从面向对象的角度来看关联的话，模型的关联其实应该是模型的某个属性，比如用户的档案关联，就应该是下面的情况：

```
// 获取用户模型实例
$user = User::find(1);
// 获取用户的档案
$user->profile;
// 获取用户的档案中的手机资料
$user->profile->mobile;
```

为了更方便和灵活的定义模型的关联关系，框架选择了方法定义而不是属性定义的方式，每个**关联属性**其实是对应了一个模型的（关联）方法，这个关联属性和模型的数据一样是动态的，并非模型类的实体属性。

例如上面的关联属性就是在`User`模型类中定义了一个`profile`方法（`mobile`属性是`Profile`模型的属性）：

```
<?php

namespace app\model;

use think\Model;

class User extends Model
{
	public function profile()
    {
    	return $this->hasOne(Profile::class);
    }
}
```

> 一个模型可以定义多个不同的关联，增加不同的关联方法即可

同时，我们必须定义一个`Profile`模型（即使是一个空模型）。

```
<?php

namespace app\model;

use think\Model;

class Profile extends Model
{
}
```

关联方法返回的是不同的关联对象，例如这里的`profile`方法返回的是一个`HasOne`关联对象（`think\model\relation\HasOne`）实例。

当我们访问`User`模型对象实例的`profile`属性的时候，其实就是调用了`profile`方法来完成关联查询。

按照`PSR-2`规范，模型的方法名都是驼峰命名的，所以系统做了一个兼容处理，如果我们定义了一个`userProfile`的关联方法的时候，在获取关联属性的时候，下面两种方式都是有效的：

```
$user->userProfile;
$user->user_profile; // 建议使用
```

> 推荐关联属性统一使用后者，和数据表的字段命名规范一致，因此在很多时候系统自动获取关联属性的时候采用的也是后者。

可以简单的理解为关联定义就是在模型类中添加一个方法（注意不要和模型的对象属性以及其它业务逻辑方法冲突），一般情况下无需任何参数，并在方法中指定一种关联关系，比如上面的`hasOne`关联关系，支持的关联关系包括下面8种，后面会给大家陆续介绍：

|模型方法|关联类型|
|---|---|
|`hasOne`|一对一|
|`belongsTo`|一对一|
|`hasMany`|一对多|
|`hasOneThrough`|远程一对一|
|`hasManyThrough`|远程一对多|
|`belongsToMany`|多对多|
|`morphMany`|多态一对多|
|`morphOne`|多态一对一|
|`morphTo`|多态|

关联方法的第一个参数就是要关联的模型名称，也就是说当前模型的关联模型必须也是已经定义好的一个模型。

也可以使用完整命名空间定义，例如：

```
<?php

namespace app\model;

use think\Model;

class User extends Model
{
	public function profile()
    {
    	return $this->hasOne(Profile::class);
    }
}
```

两个模型之间因为参照模型的不同就会产生相对的但不一定相同的关联关系，并且相对的关联关系只有在需要调用的时候才需要定义，下面是每个关联类型的相对关联关系对照：

|类型|关联关系|相对的关联关系|
|---|---|---|
|一对一|`hasOne`|`belongsTo`|
|一对多|`hasMany`|`belongsTo`|
|多对多|`belongsToMany`|`belongsToMany`|
|远程一对多|`hasManyThrough`|不支持|
|多态一对一|`morphOne`|`morphTo`|
|多态一对多|`morphMany`|`morphTo`|

例如，`Profile`模型中就可以定义一个相对的关联关系。

```
<?php

namespace app\model;

use think\Model;

class Profile extends Model
{
	public function user()
    {
    	return $this->belongsTo(User::class);
    }
}
```

在进行关联查询的时候，也是类似，只是当前模型不同。

```
// 获取档案实例
$profile = Profile::find(1);
// 获取档案所属的用户名称
echo $profile->user->name;
```

如果是数据集查询的话，关联获取的用法如下：

```
// 获取档案实例
$profiles = Profile::where('id', '>', 1)->select();
foreach($profiles as $profile) {
	// 获取档案所属的用户名称
	echo $profile->user->name;
}
```

如果你需要对关联模型进行更多的查询约束，可以在关联方法的定义方法后面追加额外的查询链式方法（但切忌不要滥用，并且不要使用实际的查询方法），例如：

```
<?php

namespace app\model;

use think\Model;

class User extends Model
{
	public function book()
    {
    	return $this->hasMany(Book::class)->order('pub_time');
    }
}
```

> 模型关联支持调用模型的方法

# 一对一关联

## 一对一关联

### 关联定义

定义一对一关联，例如，一个用户都有一个个人资料，我们定义`User`模型如下：

```
<?php
namespace app\model;

use think\Model;

class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}
```

`hasOne`方法的参数包括：

> ### hasOne('关联模型类名', '外键', '主键');

除了关联模型外，其它参数都是可选。

- **关联模型**（必须）：关联模型类名
- **外键**：默认的外键规则是当前模型名（不含命名空间，下同）+`_id` ，例如`user_id`
- **主键**：当前模型主键，默认会自动获取也可以指定传入

一对一关联定义的时候还支持额外的方法，包括：

|方法名|描述|
|---|---|
|bind|绑定关联属性到父模型|
|joinType|JOIN方式查询的JOIN方式，默认为`INNER`|

> 如果使用了JOIN方式的关联查询方式，你可以在额外的查询条件中使用关联对象名（不含命名空间）作为表的别名。

### 关联查询

定义好关联之后，就可以使用下面的方法获取关联数据：

```
$user = User::find(1);
// 输出Profile关联模型的email属性
echo $user->profile->email;
```

默认情况下， 我们使用的是`user_id` 作为外键关联，如果不是的话则需要在关联定义的时候指定，例如：

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    public function profile()
    {
        return $this->hasOne(Profile::class, 'uid');
    }
}
```

> 有一点需要注意的是，关联方法的命名规范是驼峰法，而关联属性则一般是小写+下划线的方式，系统在获取的时候会自动转换对应，读取`user_profile`关联属性则对应的关联方法应该是`userProfile`。

### 根据关联数据查询

可以根据关联条件来查询当前模型对象数据，例如：

```
// 查询用户昵称是think的用户
// 注意第一个参数是关联方法名（不是关联模型名）
$users = User::hasWhere('profile', ['nickname'=>'think'])->select();

// 可以使用闭包查询
$users = User::hasWhere('profile', function(Query $query) {
	$query->where('nickname', 'like', 'think%');
})->select();
```

> 注意：如果`hasWhere`需要和`where`同时使用的话，`hasWhere`必须在前面，否则`where`不会生效。

### 预载入查询

可以使用预载入查询解决典型的`N+1`查询问题，使用：

```
$users = User::with('profile')->select();
foreach ($users as $user) {
	echo $user->profile->name;
}
```

上面的代码使用的是`IN`查询，只会产生2条SQL查询。

如果要对关联模型进行约束，可以使用闭包的方式。

```
$users = User::with(['profile'	=> function(Query $query) {
	$query->field('id,user_id,name,email');
}])->select();

foreach ($users as $user) {
	echo $user->profile->name;
}
```

默认情况下，关联查询的数据是不包含软删除数据的，如果需要包含软删除数据，可以在关联定义的时候或预载入查询的闭包方法里面使用`withTrashed()`方法。

```
$users = User::with(['profile'	=> function(Query $query) {
	$query->withTrashed();
}])->select();
```

如果需要在关联查询的时候指定不需要的全局查询范围，可以使用`withoutScope()`方法。

```
$users = User::with(['profile'	=> function(Query $query) {
	$query->withoutScope(['age']);
}])->select();
```

`with`方法可以传入数组，表示同时对多个关联模型（支持不同的关联类型）进行预载入查询。

```
$users = User::with(['profile','book'])->select();
foreach ($users as $user) {
	echo $user->profile->name;
    foreach($user->book as $book) {
    	echo $book->name;
    }
}
```

如果需要使用`JOIN`方式的查询，直接使用`withJoin`方法，如下：

```
$users = User::withJoin('profile')->select();
foreach ($users as $user) {
	echo $user->profile->name;
}
```

`withJoin`方法默认使用的是`INNER JOIN`方式，如果需要使用其它的，可以改成

```
$users = User::withJoin('profile', 'LEFT')->select();
foreach ($users as $user) {
	echo $user->profile->name;
}
```

需要注意的是`withJoin`方式不支持嵌套关联，通常你可以直接传入多个需要关联的模型。

如果需要约束关联字段，可以使用下面的简便方法。

```
$users = User::withJoin([
	'profile'	=>	['user_id', 'name', 'email']
])->select();
foreach ($users as $user) {
	echo $user->profile->name;
}
```

特别注意的是 `withJoin` 不支持通过 `alias` 方法指定别名。因此如果你需要指定别名查询的时候，ORM 会自动设定别名。比如我们有表 `user` 和表 `user_profile`，我们也分别定义了 模型 `User` 和 模型 `UserProfile` ，在通过如下的查询时：

```
$users = User::withJoin([
	'profile'	=>	function (Query $query) {
          $query->where('profile.is_delete', 1);
    }])->where('user.is_delete', 1)->select();
```

我们就可以通过，模型名的小写来指定别名，比如 模型名叫 `User` 则别名为 `user`， 模型名叫 `UserData` 则别名为 `user_data`。

而通过 `withJoin` 关联的表的别名，就是 我们在使用 `withJoin` 的时候指定的key，比如本例子就是 `profile`。

### 关联保存

```
$user = User::find(1);
// 如果还没有关联数据 则进行新增
$user->profile()->save(['email' => 'thinkphp']);
```

系统会自动把当前模型的主键传入`Profile`模型。

和新增一样使用`save`方法进行更新关联数据。

```
$user = User::find(1);
$user->profile->email = 'thinkphp';
$user->profile->save();
// 或者
$user->profile->save(['email' => 'thinkphp']);
```

### 定义相对关联

我们可以在`Profile`模型中定义一个相对的关联关系，例如：

```
<?php
namespace app\model;

use think\Model;

class Profile extends Model 
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

`belongsTo`的参数包括：

> ### belongsTo('关联模型','外键', '关联主键');

除了关联模型外，其它参数都是可选。

- **关联模型**（必须）：关联模型类名
- **外键**：当前模型外键，默认的外键名规则是关联模型名+`_id`
- **关联主键**：关联模型主键，一般会自动获取也可以指定传入

默认的关联外键是`user_id`，如果不是，需要在第二个参数定义

```
<?php
namespace app\model;

use think\Model;

class Profile extends Model 
{
    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }
}
```

我们就可以根据档案资料来获取用户模型的信息

```
$profile = Profile::find(1);
// 输出User关联模型的属性
echo $profile->user->account;
```

## 绑定属性到父模型

可以在定义关联的时候使用`bind`方法绑定属性到父模型，例如：

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    public function profile()
    {
        return $this->hasOne(Profile::class, 'uid')->bind(['nickname', 'email']);
    }
}
```

或者指定绑定属性别名

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    public function profile()
    {
        return $this->hasOne(Profile::class, 'uid')->bind([
        		'email',
                'truename'	=> 'nickname', // 把关联模型的nickname字段绑定到当前模型的truename属性
            ]);
    }
}
```

然后使用关联预载入查询的时候，可以使用

```
$user = User::with('profile')->find(1);
// 直接输出Profile关联模型的绑定属性
echo $user->email;
echo $user->truename;
```

绑定关联模型的属性支持读取器。

> 如果不是预载入查询，请使用模型的`appendRelationAttr`方法追加属性。

也可以使用动态绑定关联属性，可以使用

```
$user = User::find(1)->bindAttr('profile',['email','nickname']);
// 输出Profile关联模型的email属性
echo $user->email;
echo $user->nickname;
```

同样支持指定属性别名

```
$user = User::find(1)->bindAttr('profile',[
	'email',
    'truename'	=> 'nickname',
]);
// 输出Profile关联模型的email属性
echo $user->email;
echo $user->truename;
```

## 关联自动写入

我们可以使用`together`方法更方便的进行关联自动写入操作。

写入

```
$blog = new Blog;
$blog->name = 'thinkphp';
$blog->title = 'ThinkPHP5关联实例';
$content = new Content;
$content->data = '实例内容';
$blog->content = $content;
$blog->together(['content'])->save();
```

如果绑定了子模型的属性到当前模型，可以指定子模型的属性

```
$blog = new Blog;
$blog->name = 'thinkphp';
$blog->title = 'ThinkPHP5关联实例';
$blog->content = '实例内容';
// title和content是子模型的属性
$blog->together(['content' => ['title', 'content']])->save();
```

更新

```
// 查询
$blog = Blog::find(1);
$blog->title = '更改标题';
$blog->content->data = '更新内容';
// 更新当前模型及关联模型
$blog->together(['content'])->save();
```

删除

```
// 查询
$blog = Blog::with('content')->find(1);
// 删除当前及关联模型
$blog->together(['content'])->delete();
```

# 一对多关联

## 一对多关联

### 关联定义

一对多关联的情况也比较常见，使用`hasMany`方法定义，参数包括：

> ### hasMany('关联模型','外键','主键');

除了关联模型外，其它参数都是可选。

- **关联模型**（必须）：关联模型类名
- **外键**：关联模型外键，默认的外键名规则是当前模型名+`_id`
- **主键**：当前模型主键，一般会自动获取也可以指定传入

例如一篇文章可以有多个评论

```
<?php
namespace app\model;

use think\Model;

class Article extends Model 
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

同样，也可以定义外键的名称

```
<?php
namespace app\model;

use think\Model;

class Article extends Model 
{
    public function comments()
    {
        return $this->hasMany(Comment::class,'art_id');
    }
}
```

### 关联查询

我们可以通过下面的方式获取关联数据

```
$article = Article::find(1);
// 获取文章的所有评论
dump($article->comments);
// 也可以进行条件搜索
dump($article->comments()->where('status',1)->select());
```

### 根据关联条件查询

可以根据关联条件来查询当前模型对象数据，例如：

```
// 查询评论超过3个的文章
$list = Article::has('comments','>',3)->select();
// 查询评论状态正常的文章
$list = Article::hasWhere('comments',['status'=>1])->select();
```

如果需要更复杂的关联条件查询，可以使用

```
$where = Comment::where('status',1)->where('content', 'like', '%think%');
$list = Article::hasWhere('comments', $where)->select();
```

> 注意：如果`hasWhere`需要和`where`同时使用的话，`hasWhere`必须在前面。

### 关联新增

```
$article = Article::find(1);
// 增加一个关联数据
$article->comments()->save(['content'=>'test']);
// 批量增加关联数据
$article->comments()->saveAll([
    ['content'=>'thinkphp'],
    ['content'=>'onethink'],
]);
```

### 定义相对的关联

要在 Comment 模型定义相对应的关联，可使用 `belongsTo` 方法：

```
<?php
name app\model;

use think\Model;

class Comment extends Model 
{
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
```

### 关联删除

在删除文章的同时删除下面的评论

```
$article = Article::with('comments')->find(1);
$article->together(['comments'])->delete();
```

# 远程一对多

远程一对多关联用于定义有跨表的一对多关系，例如：

- 每个城市有多个用户
- 每个用户有多个话题
- 城市和话题之间并无关联

## 关联定义

就可以直接通过远程一对多关联获取每个城市的多个话题，`City`模型定义如下：

```
<?php
namespace app\model;

use think\Model;

class City extends Model 
{
    public function topics()
    {
        return $this->hasManyThrough(Topic::class, User::class);
    }
}
```

> 远程一对多关联，需要同时存在`Topic`和`User`模型，当前模型和中间模型的关联关系可以是一对一或者一对多。

`hasManyThrough`方法的参数如下：

> ### hasManyThrough('关联模型', '中间模型', '外键', '中间表关联键','当前模型主键','中间模型主键');

- **关联模型**（必须）：关联模型类名
- **中间模型**（必须）：中间模型类名
- **外键**：默认的外键名规则是当前模型名+`_id`
- **中间表关联键**：默认的中间表关联键名的规则是中间模型名+`_id`
- **当前模型主键**：一般会自动获取也可以指定传入
- **中间模型主键**：一般会自动获取也可以指定传入

## 关联查询

我们可以通过下面的方式获取关联数据

```
$city = City::find(1);
// 获取同城的所有话题
dump($city->topics);
// 也可以进行条件搜索
dump($city->topics()->where('topic.status',1)->select());
```

> 条件搜索的时候，需要带上模型名作为前缀

### 根据关联条件查询

如果需要根据关联条件来查询当前模型，可以使用

```
$list = City::hasWhere('topics', ['status' => 1])->select();
```

更复杂的查询条件可以使用

```
$where = Topic::where('status', 1)->where('title', 'like', '%think%');
$list = City::hasWhere('topics',$where)->select();
```

> 注意：如果`hasWhere`需要和`where`同时使用的话，`hasWhere`必须在前面。


# 远程一对一

远程一对一关联用于定义有跨表的一对一关系，例如：

- 每个用户有一个档案
- 每个档案有一个档案卡
- 用户和档案卡之间并无关联

## 关联定义

就可以直接通过远程一对一关联获取每个用户的档案卡，`User`模型定义如下：

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    public function card()
    {
        return $this->hasOneThrough(Card::class,Profile::class);
    }
}
```

远程一对一关联，需要同时存在`Card`和`Profile`模型。

`hasOneThrough`方法的参数如下：

> ### hasOneThrough('关联模型', '中间模型', '外键', '中间表关联键','当前模型主键','中间模型主键');

- **关联模型**（必须）：关联模型类名
- **中间模型**（必须）：中间模型类名
- **外键**：默认的外键名规则是当前模型名+`_id`
- **中间表关联键**：默认的中间表关联键名的规则是中间模型名+`_id`
- **当前模型主键**：一般会自动获取也可以指定传入
- **中间模型主键**：一般会自动获取也可以指定传入

## 关联查询

我们可以通过下面的方式获取关联数据

```
$user = User::find(1);
// 获取用户的档案卡
dump($user->card);
```

# 多对多关联

## 多对多关联

## 关联定义

例如，我们的用户和角色就是一种多对多的关系，我们在`User`模型定义如下：

```
<?php
namespace app\model;

use think\Model;

class User extends Model 
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'access');
    }
}
```

`belongsToMany`方法的参数如下：

> ### belongsToMany('关联模型','中间表','外键','关联键');

- **关联模型**（必须）：关联模型类名
- **中间表**：默认规则是当前模型名+`_`+关联模型名 （可以指定模型名）
- **外键**：中间表的当前模型外键，默认的外键名规则是关联模型名+`_id`
- **关联键**：中间表的当前模型关联键名，默认规则是当前模型名+`_id`

中间表名无需添加表前缀，并支持定义中间表模型，例如：

```
    public function roles()
    {
        return $this->belongsToMany(Role::class, Access::class);
    }
```

中间表模型类必须继承`think\model\Pivot`，例如：

```
<?php
namespace app\model;

use think\model\Pivot;

class Access extends Pivot
{
    protected $autoWriteTimestamp = true;
}
```

中间表模型的基类`Pivot`默认关闭了时间戳自动写入，上面的中间表模型则开启了时间戳字段自动写入。

### 关联查询

我们可以通过下面的方式获取关联数据

```
$user = User::find(1);
// 获取用户的所有角色
$roles = $user->roles;
foreach ($roles as $role) {
	// 输出用户的角色名
	echo $role->name;
    // 获取中间表模型
    dump($role->pivot);
}
```

### 关联新增

```
$user = User::find(1);
// 给用户增加管理员权限 会自动写入角色表和中间表数据
$user->roles()->save(['name'=>'管理员']);
// 批量授权
$user->roles()->saveAll([
    ['name'=>'管理员'],
    ['name'=>'操作员'],
]);
```

只新增中间表数据（角色已经提前创建完成），可以使用

```
$user = User::find(1);
// 仅增加管理员权限（假设管理员的角色ID是1）
$user->roles()->save(1);
// 或者
$role = Role::find(1);
$user->roles()->save($role);
// 批量增加关联数据
$user->roles()->saveAll([1,2,3]);
```

单独更新中间表数据，可以使用：

```
$user = User::find(1);
// 增加关联的中间表数据
$user->roles()->attach(1);
// 传入中间表的额外属性
$user->roles()->attach(1,['remark'=>'test']);
// 删除中间表数据
$user->roles()->detach([1,2,3]);
```

> `attach`方法的返回值是一个`Pivot`对象实例，如果是附加多个关联数据，则返回`Pivot`对象实例的数组。

### 定义相对的关联

我们可以在`Role`模型中定义一个相对的关联关系，例如：

```
<?php
namespace app\model;

use think\Model;

class Role extends Model 
{
    public function users()
    {
        return $this->belongsToMany(User::class, Access::class);
    }
}
```

可以通过`wherePivot`方法查询和过滤中间表数据

```
<?php
namespace app\model;

use think\Model;

class Role extends Model 
{
    public function users()
    {
        return $this->belongsToMany(User::class, Access::class)
            ->wherePivot('priority', 'in', [1,2]);
    }
}
```

# 多态关联

## 多态一对多关联

多态关联允许一个模型在单个关联定义方法中从属一个以上其它模型，例如用户可以评论书和文章，但评论表通常都是同一个数据表的设计。多态一对多关联关系，就是为了满足类似的使用场景而设计。

下面是关联表的数据表结构：

```
article
    id - integer
    title - string
    content - text

book
    id - integer
    title - string

comment
    id - integer
    content - text
    commentable_id - integer
    commentable_type - string
```

有两个需要注意的字段是 `comment` 表中的 `commentable_id` 和 `commentable_type`我们称之为多态字段。其中， `commentable_id` 用于存放书或者文章的 id（主键） ，而 `commentable_type` 用于存放所属模型的类型。通常的设计是多态字段有一个公共的前缀（例如这里用的`commentable`），当然，也支持设置完全不同的字段名（例如使用`data_id`和`type`）。

### 多态关联定义

接着，让我们来查看创建这种关联所需的模型定义：

文章模型：

```
<?php
namespace app\model;

use think\Model;

class Article extends Model
{
    /**
     * 获取所有针对文章的评论。
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

`morphMany`方法的参数如下：

> ### morphMany('关联模型','多态字段','多态类型');

**关联模型**（必须）：关联的模型类名

**多态字段**（可选）：支持两种方式定义 如果是字符串表示多态字段的前缀，多态字段使用 `多态前缀_type`和`多态前缀_id`，如果是数组，表示使用['多态类型字段名','多态ID字段名']，默认为当前的关联方法名作为字段前缀。

**多态类型**（可选）：当前模型对应的多态类型，默认为当前模型名，可以使用模型名（如`Article`）或者完整的命名空间模型名（如`app\index\model\Article`）。

书籍模型：

```
<?php
namespace app\model;

use think\Model;

class Book extends Model
{
    /**
     * 获取所有针对书籍的评论。
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

书籍模型的设置方法同文章模型一致，区别在于多态类型不同，但由于多态类型默认会取当前模型名，因此不需要单独设置。

下面是评论模型的关联定义：

```
<?php
namespace app\model;

use think\Model;

class Comment extends Model
{
    /**
     * 获取评论对应的多态模型。
     */
    public function commentable()
    {
        return $this->morphTo();
    }
}
```

`morphTo`方法的参数如下：

> ### morphTo('多态字段',['多态类型别名']);

**多态字段**（可选）：支持两种方式定义 如果是字符串表示多态字段的前缀，多态字段使用 `多态前缀_type`和`多态前缀_id`，如果是数组，表示使用['多态类型字段名','多态ID字段名']，默认为当前的关联方法名作为字段前缀  
**多态类型别名**（可选）：数组方式定义

### 获取多态关联

一旦你的数据表及模型被定义，则可以通过模型来访问关联。例如，若要访问某篇文章的所有评论，则可以简单的使用 `comments` 动态属性：

```
$article = Article::find(1);

foreach ($article->comments as $comment) {
    dump($comment);
}
```

你也可以从多态模型的多态关联中，通过访问调用 `morphTo` 的方法名称来获取拥有者，也就是此例子中 `Comment` 模型的 `commentable` 方法。所以，我们可以使用动态属性来访问这个方法：

```
$comment = Comment::find(1);
$commentable = $comment->commentable;
```

`Comment` 模型的 `commentable` 关联会返回 `Article` 或 `Book` 模型的对象实例，这取决于评论所属模型的类型。

### 自定义多态关联的类型字段

默认情况下，ThinkPHP 会使用模型名作为多态表的类型区分，例如，`Comment`属于 `Article` 或者 `Book` , `commentable_type` 的默认值可以分别是 `Article` 或者 `Book` 。我们可以通过定义多态的时候传入参数来对数据库进行解耦。

```
    public function commentable()
    {
        return $this->morphTo('commentable',[
        	'book'	=>	'app\index\model\Book',
            'post'	=>	'app\admin\model\Article',
        ]);
    }
```

## 多态一对一关联

多态一对一相比多态一对多关联的区别是动态的一对一关联，举个例子说有一个个人和团队表，而无论个人还是团队都有一个头像需要保存但都会对应同一个头像表

```
member
    id - integer
    name - string
    
team
    id - integer
    name - string
    
avatar
    id - integer
    avatar - string
    imageable_id - integer
    imageable_type - string 
```

会员模型：

```
<?php
namespace app\model;

use think\Model;

class Member extends Model
{
    /**
     * 获取用户的头像
     */
    public function avatar()
    {
        return $this->morphOne(Avatar::class, 'imageable');
    }
}
```

团队模型：

```
<?php
namespace app\model;

use think\Model;

class Team extends Model
{
    /**
     * 获取团队的头像
     */
    public function avatar()
    {
        return $this->morphOne(Avatar::class, 'imageable');
    }
}
```

`morphOne`方法的参数如下：

> ### morphOne('关联模型','多态字段','多态类型');

**关联模型**（必须）：关联的模型类名。

**多态字段**（可选）：支持两种方式定义 如果是字符串表示多态字段的前缀，多态字段使用 `多态前缀_type`和`多态前缀_id`，如果是数组，表示使用['多态类型字段名','多态ID字段名']，默认为当前的关联方法名作为字段前缀。

**多态类型**（可选）：当前模型对应的多态类型，默认为当前模型名，可以使用模型名（如`Member`）或者完整的命名空间模型名（如`app\index\model\Member`）。

下面是头像模型的关联定义：

```
<?php
namespace app\model;

use think\Model;

class Avatar extends Model
{
    /**
     * 获取头像对应的多态模型。
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
```

理解了多态一对多关联后，多态一对一关联其实就很容易理解了，区别就是当前模型和动态关联的模型之间的关联属于一对一关系。

## 绑定属性到父模型

可以在定义关联的时候使用`bind`方法绑定属性到父模型，例如：

```
<?php
namespace app\model;

use think\Model;

class Team extends Model
{
    /**
     * 获取团队的头像
     */
    public function avatar()
    {
        return $this->morphOne(Avatar::class, 'imageable')->bind(['nickname', 'email']);
    }
}
```

或者指定绑定属性别名

```
<?php
namespace app\model;

use think\Model;

class Team extends Model
{
    /**
     * 获取团队的头像
     */
    public function avatar()
    {
        return $this->morphOne(Avatar::class, 'imageable')->bind([
        		'email',
                'truename'	=> 'nickname',
            ]);
    }
}
```

然后使用关联预载入查询的时候，可以使用

```
$team = Team::with('avatar')->find(1);
// 直接输出Avatar关联模型的绑定属性
echo $team->email;
echo $team->truename;
```

绑定关联模型的属性支持读取器。

# 关联预载入

## 关联预载入

关联查询的预查询载入功能，主要解决了`N+1`次查询的问题，例如下面的查询如果有3个记录，会执行4次查询：

```
$list = User::select([1,2,3]);
foreach($list as $user){
    // 获取用户关联的profile模型数据
    dump($user->profile);
}
```

如果使用关联预查询功能，就可以变成2次查询（对于一对一关联来说，如果使用`withJoin`方式只有一次查询），有效提高性能。

```
$list = User::with(['profile'])->select([1,2,3]);
foreach($list as $user){
    // 获取用户关联的profile模型数据
    dump($user->profile);
}
```

或者通过`getRelation`方法获取关联数据

```
$list = User::with(['profile'])->select([1,2,3]);
foreach($list as $user){
    // 获取用户关联的profile模型数据
    dump($user->getRelation('profile'));
}
```

支持预载入多个关联，例如：

```
$list = User::with(['profile', 'book'])->select([1,2,3]);
```

如果需要获取全部的关联数据，可以使用`getRelation`方法

```
$list = User::with(['profile', 'book'])->select([1,2,3]);
foreach($list as $user){
    // 获取用户的关联数据
    dump($user->getRelation());
}
```

> `with`方法只能调用一次，请不要多次调用，如果需要对多个关联模型预载入使用数组即可。

也可以支持嵌套预载入，例如：

```
$list = User::with(['profile.phone'])->select([1,2,3]);
foreach($list as $user){
    // 获取用户关联的phone模型
    dump($user->profile->phone);
}
```

支持使用数组方式定义嵌套预载入，例如下面的预载入要同时获取用户的`Profile`关联模型的`Phone`、`Job`和`Img`子关联模型数据：

```
$list = User::with(['profile'=>['phone','job','img']])->select([1,2,3]);
foreach($list as $user){
    // 获取用户关联
    dump($user->profile->phone);
    dump($user->profile->job);    
    dump($user->profile->img);    
}
```

如果要指定属性查询，可以使用：

```
$list = User::field('id,name')->with(['profile' => function(Query $query){
	$query->withField(['user_id','email','phone']);
}])->select([1,2,3]);

foreach($list as $user){
    // 获取用户关联的profile模型数据
    dump($user->profile);
}
```

> 记得指定属性的时候一定要包含关联键。

也可以使用`withoutField`方法排除某些字段，例如：

```
$list = User::field('id,name')->with(['profile' => function(Query $query){
	$query->withoutField('create_time,delete_time,update_time');
}])->select([1,2,3]);
```

如果是一对一关联，可以动态绑定属性到父模型

```
$users = User::with(['profile'	=> function(Query $query) {
	$query->withBind(['name']);
}])->select();
```

如果是多对多关联，可以使用同样的方法动态绑定中间表的属性到父模型。

对于一对多关联来说，如果需要设置返回的关联数据数量，可以使用`withLimit`方法。

```
Article::with(['comments' => function(Query $query) {
    $query->order('create_time', 'desc')->withLimit(3);
}])->select();
```

关联预载入名称是关联方法名，支持传入方法名的小写和下划线定义方式，例如关联方法名是`userProfile`和`userBook`的话：

```
$list = User::with(['userProfile','userBook'])->select([1,2,3]);
```

和下面的方法是等效的：

```
$list = User::with(['user_profile','user_book'])->select([1,2,3]);
```

区别在于你获取关联数据的时候必须和传入的关联名称保持一致。

```
$user = User::with(['userProfile'])->find(1);
dump($user->userProfile);

$user = User::with(['user_profile'])->find(1);
dump($user->user_profile);
```

一对一关联预载入支持两种方式：`JOIN`方式（一次查询）和`IN`方式（两次查询，默认方式），如果要使用`JOIN`方式关联预载入，可以使用`withJoin`方法。

```
$list = User::withJoin(['profile' => function(Query $query){
	$query->where('is_delete',0);
}])->select([1,2,3]);
```

## 延迟预载入

有些情况下，需要根据查询出来的数据来决定是否需要使用关联预载入，当然关联查询本身就能解决这个问题，因为关联查询是惰性的，不过用预载入的理由也很明显，性能具有优势。

延迟预载入仅针对多个数据的查询，因为单个数据的查询用延迟预载入和关联惰性查询没有任何区别，所以不需要使用延迟预载入。

如果你的数据集查询返回的是数据集对象，可以使用调用数据集对象的`load`实现延迟预载入：

```
// 查询数据集
$list = User::select([1,2,3]);
// 延迟预载入
$list->load(['cards']);
foreach($list as $user){
    // 获取用户关联的card模型数据
    dump($user->cards);
}
```

## 关联预载入缓存

关联预载入可以支持查询缓存，例如：

```
$list = User::with(['profile'])->withCache(30)->select([1,2,3]);
```

表示对关联数据缓存30秒。

如果你有多个关联数据，也可以仅仅缓存部分关联

```
$list = User::with(['profile', 'book'])->withCache(['profile'],30)->select([1,2,3]);
```

对于延迟预载入查询的话，可以在第二个参数传入缓存参数。

```
// 查询数据集
$list = User::select([1,2,3]);
// 延迟预载入
$list->load(['cards'], 30);
```

# 关联统计

## 关联统计

有些时候，并不需要获取关联数据，而只是希望获取关联数据的统计，这个时候可以使用`withCount`方法进行指定关联的统计。

```
$list = User::withCount('cards')->select([1,2,3]);
foreach($list as $user){
    // 获取用户关联的card关联统计
    echo $user->cards_count;
}
```

> 你必须给User模型定义一个名称是`cards`的关联方法。

关联统计功能会在模型的对象属性中自动添加一个以“关联方法名+`_count`”为名称的动态属性来保存相关的关联统计数据。

可以通过数组的方式同时查询多个统计字段。

```
$list = User::withCount(['cards', 'phone'])->select([1,2,3]);
foreach($list as $user){
    // 获取用户关联关联统计
    echo $user->cards_count;
    echo $user->phone_count;
}
```

支持给关联统计指定统计属性名，例如：

```
$list = User::withCount(['cards' => 'card_count'])->select([1,2,3]);
foreach($list as $user){
    // 获取用户关联的card关联统计
    echo $user->card_count;
}
```

> 关联统计暂不支持多态关联

如果需要对关联统计进行条件过滤，可以使用闭包方式。

```
$list = User::withCount(['cards' => function($query) {
    $query->where('status',1);
}])->select([1,2,3]);

foreach($list as $user){
    // 获取用户关联的card关联统计
    echo $user->cards_count;
}
```

使用闭包的方式，如果需要自定义统计字段名称，可以使用

```
$list = User::withCount(['cards' => function($query, &$alias) {
    $query->where('status',1);
    $alias = 'card_count';
}])->select([1,2,3]);

foreach($list as $user){
    // 获取用户关联的card关联统计
    echo $user->card_count;
}
```

和`withCount`类似的方法，还包括：

|关联统计方法|描述|
|---|---|
|`withSum`|关联SUM统计|
|`withMax`|关联Max统计|
|`withMin`|关联Min统计|
|`withAvg`|关联Avg统计|

除了`withCount`之外的统计方法需要在第二个字段传入统计字段名，用法如下：

```
$list = User::withSum('cards', 'total')->select([1,2,3]);

foreach($list as $user){
    // 获取用户关联的card关联余额统计
    echo $user->cards_sum;
}
```

同样，也可以指定统计字段名

```
$list = User::withSum(['cards' => 'card_total'], 'total')->select([1,2,3]);

foreach($list as $user){
    // 获取用户关联的card关联余额统计
    echo $user->card_total;
}
```

所有的关联统计方法可以多次调用，每次查询不同的关联统计数据。

```
$list = User::withSum('cards', 'total')
    ->withSum('score', 'score') 
    ->select([1,2,3]);

foreach($list as $user){
    // 获取用户关联的card关联余额统计
    echo $user->card_total;
}
```

# 关联输出

关联数据的输出也可以使用`hidden`、`visible`和`append`方法进行控制，下面举例说明。

## 隐藏关联属性

如果要隐藏关联模型的属性，可以使用

```
$list = User::with('profile')->select();
$list->hidden(['profile.email'])->toArray();
```

输出的结果中就不会包含`Profile`模型的`email`属性，如果需要隐藏多个属性可以使用

```
$list = User::with('profile')->select();
$list->hidden(['profile' => ['address','phone','email']])->toArray();
```

## 显示关联属性

同样，可以使用visible方法来显示关联属性：

```
$list = User::with('profile')->select();
$list->visible(['profile' => ['address','phone','email']])->toArray();
```

## 追加关联属性

追加一个`Profile`模型的额外属性（非实际数据，可能是定义了获取器方法）

```
$list = User::with('profile')->select();
$list->append(['profile.status'])->toArray();
```

也可以追加一个额外关联对象的属性

```
$list = User::with('profile')->select();
$list->append(['Book.name'])->toArray();
```

