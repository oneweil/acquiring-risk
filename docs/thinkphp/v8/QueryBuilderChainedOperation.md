# 链式操作

数据库提供的链式操作方法，可以有效的提高数据存取的代码清晰度和开发效率，并且支持所有的CURD操作（原生查询不支持链式操作）。

使用也比较简单，假如我们现在要查询一个User表的满足状态为1的前10条记录，并希望按照用户的创建时间排序 ，代码如下：

```
use think\facade\Db;

Db::table('think_user')
    ->where('status',1)
    ->order('create_time')
    ->limit(10)
    ->select();
```

这里的`where`、`order`和`limit`方法就被称之为链式操作方法，除了`select`方法必须放到最后一个外（因为`select`方法并不是链式操作方法），链式操作的方法调用顺序没有先后，例如，下面的代码和上面的等效：

```
Db::table('think_user')
    ->order('create_time')
    ->limit(10)
    ->where('status',1)
    ->select();
```

其实不仅仅是查询方法可以使用连贯操作，包括所有的CURD方法都可以使用，例如：

```
Db::table('think_user')
    ->where('id',1)
    ->field('id,name,email')
    ->find(); 
    
Db::table('think_user')
    ->where('status',1)
    ->where('id',1)
    ->delete();
```

每次`Db`类的静态方法调用是创建一个新的查询对象实例，如果你需要多次复用使用链式操作值，可以使用下面的方法。

```
$user = Db::table('user');
$user->order('create_time')
    ->where('status',1)
    ->select();
    
// 会自动带上前面的where条件和order排序的值    
$user->where('id', '>', 0)->select();
```

当前查询对象在查询之后仍然会保留链式操作的值，除非你调用`removeOption`方法清空链式操作的值。

```
$user = Db::table('think_user');
$user->order('create_time')
    ->where('status',1)
    ->select();
    
// 清空where查询条件值 保留其它链式操作   
$user->removeOption('where')
	->where('id', '>', 0)
    ->select();
```

系统支持的链式操作方法包含：

|连贯操作|作用|支持的参数类型|
|---|---|---|
|where*|用于AND查询|字符串、数组和对象|
|whereOr*|用于OR查询|字符串、数组和对象|
|whereTime*|用于时间日期的快捷查询|字符串|
|table|用于定义要操作的数据表名称|字符串和数组|
|alias|用于给当前数据表定义别名|字符串|
|field*|用于定义要查询的字段（支持字段排除）|字符串和数组|
|order*|用于对结果排序|字符串和数组|
|limit|用于限制查询结果数量|字符串和数字|
|page|用于查询分页（内部会转换成limit）|字符串和数字|
|group|用于对查询的group支持|字符串|
|having|用于对查询的having支持|字符串|
|join*|用于对查询的join支持|字符串和数组|
|union*|用于对查询的union支持|字符串、数组和对象|
|view*|用于视图查询|字符串、数组|
|distinct|用于查询的distinct支持|布尔值|
|lock|用于数据库的锁机制|布尔值|
|cache|用于查询缓存|支持多个参数|
|with*|用于关联预载入|字符串、数组|
|bind*|用于数据绑定操作|数组或多个参数|
|comment|用于SQL注释|字符串|
|force|用于数据集的强制索引|字符串|
|master|用于设置主服务器读取数据|布尔值|
|strict|用于设置是否严格检测字段名是否存在|布尔值|
|sequence|用于设置Pgsql的自增序列名|字符串|
|failException|用于设置没有查询到数据是否抛出异常|布尔值|
|partition|用于设置分区信息|数组 字符串|
|replace|用于设置使用REPLACE方式写入|布尔值|
|extra|用于设置额外查询规则|字符串|
|duplicate|用于设置DUPLCATE信息|数组 字符串|

> 所有的连贯操作都返回当前的模型实例对象（this），其中带*标识的表示支持多次调用。
# where

`where`方法的用法是ThinkPHP查询语言的精髓，也是ThinkPHP `ORM`的重要组成部分和亮点所在，可以完成包括普通查询、表达式查询、快捷查询、区间查询、组合查询在内的查询操作。`where`方法的参数支持的变量类型包括字符串、数组和闭包。

> 和`where`方法相同用法的方法还包括`whereOr`、`whereIn`等一系列快捷查询方法，下面仅以`where`为例说明用法。

## 表达式查询

> 表达式查询是官方推荐使用的查询方式

查询表达式的使用格式：

```
Db::table('think_user')
    ->where('id','>',1)
    ->where('name','thinkphp')
    ->select(); 
```

更多的表达式查询语法，可以参考前面的查询表达式部分。

## 数组条件

数组方式有两种查询条件类型：关联数组和索引数组。

### 关联数组

主要用于等值`AND`条件，例如：

```
// 传入数组作为查询条件
Db::table('think_user')->where([
	'name'	=>	'thinkphp',
    'status'=>	1
])->select(); 
```

最后生成的SQL语句是

```
SELECT * FROM think_user WHERE `name`='thinkphp' AND status = 1
```

### 索引数组

索引数组方式批量设置查询条件，使用方式如下：

```
// 传入数组作为查询条件
Db::table('think_user')->where([
	['name','=','thinkphp'],
    ['status','=',1]
])->select(); 
```

最后生成的SQL语句是

```
SELECT * FROM think_user WHERE `name`='thinkphp' AND status = 1
```

如果需要事先组装数组查询条件，可以使用：

```
$map[] = ['name','like','think'];
$map[] = ['status','=',1];
```

> 数组方式查询还有一些额外的复杂用法，我们会在后面的高级查询章节提及。

## 字符串条件

使用字符串条件直接查询和操作，例如：

```
Db::table('think_user')->whereRaw('type=1 AND status=1')->select(); 
```

最后生成的SQL语句是

```
SELECT * FROM think_user WHERE type=1 AND status=1
```

> 注意使用字符串查询条件和表达式查询的一个区别在于，不会对查询字段进行避免关键词冲突处理。

使用字符串条件的时候，如果需要传入变量，建议配合预处理机制，确保更加安全，例如：

```
Db::table('think_user')
->whereRaw("id=:id and username=:name", ['id' => 1 , 'name' => 'thinkphp'])
->select();
```
# table

`table`方法主要用于指定操作的数据表。

## 用法

一般情况下，操作模型的时候系统能够自动识别当前对应的数据表，所以，使用`table`方法的情况通常是为了：

- 切换操作的数据表；
- 对多表进行操作；

例如：

```
Db::table('think_user')->where('status>1')->select();
```

也可以在table方法中指定数据库，例如：

```
Db::table('db_name.think_user')->where('status>1')->select();
```

table方法指定的数据表需要完整的表名，但可以采用`name`方式简化数据表前缀的传入，例如：

```
Db::name('user')->where('status>1')->select();
```

会自动获取当前模型对应的数据表前缀来生成 `think_user` 数据表名称。

需要注意的是`table`方法不会改变数据库的连接，所以你要确保当前连接的用户有权限操作相应的数据库和数据表。

如果需要对多表进行操作，可以这样使用：

```
Db::field('user.name,role.title')
->table('think_user user,think_role role')
->limit(10)->select();
```

为了尽量避免和mysql的关键字冲突，可以建议使用数组方式定义，例如：

```
Db::field('user.name,role.title')
->table([
    'think_user'=>'user',
    'think_role'=>'role'
])
->limit(10)->select();
```

使用数组方式定义的优势是可以避免因为表名和关键字冲突而出错的情况。
# alias

`alias`用于设置当前数据表的别名，便于使用其他的连贯操作例如join方法等。

示例：

```
Db::table('think_user')
->alias('a')
->join('think_dept b ','b.user_id= a.id')
->select();
```

最终生成的SQL语句类似于：

```
SELECT * FROM think_user a INNER JOIN think_dept b ON b.user_id= a.id
```

可以传入数组批量设置数据表以及别名，例如：

```
Db::table('think_user')
->alias(['think_user'=>'user','think_dept'=>'dept'])
->join('think_dept','dept.user_id= user.id')
->select();
```

最终生成的SQL语句类似于：

```
SELECT * FROM think_user user INNER JOIN think_dept dept ON dept.user_id= user.id
```

# field

`field`方法主要作用是标识要返回或者操作的字段，可以用于查询和写入操作。

## 用于查询

### 指定字段

在查询操作中`field`方法是使用最频繁的。

```
Db::table('user')->field('id,title,content')->select();
```

这里使用field方法指定了查询的结果集中包含id,title,content三个字段的值。执行的SQL相当于：

```
SELECT id,title,content FROM user
```

可以给某个字段设置别名，例如：

```
Db::table('user')->field('id,nickname as name')->select();
```

执行的SQL语句相当于：

```
SELECT id,nickname as name FROM user
```

### 使用SQL函数

可以在`fieldRaw`方法中直接使用函数，例如：

```
Db::table('user')->fieldRaw('id,SUM(score)')->select();
```

执行的SQL相当于：

```
SELECT id,SUM(score) FROM user
```

> 除了`select`方法之外，所有的查询方法，包括`find`等都可以使用`field`方法。

### 使用数组参数

`field`方法的参数可以支持数组，例如：

```
Db::table('user')->field(['id','title','content'])->select();
```

最终执行的SQL和前面用字符串方式是等效的。

数组方式的定义可以为某些字段定义别名，例如：

```
Db::table('user')->field(['id','nickname'=>'name'])->select();
```

执行的SQL相当于：

```
SELECT id,nickname as name FROM user
```

### 获取所有字段

如果有一个表有非常多的字段，需要获取所有的字段（这个也许很简单，因为不调用field方法或者直接使用空的field方法都能做到）：

```
Db::table('user')->select();
Db::table('user')->field('*')->select();
```

上面的用法是等效的，都相当于执行SQL：

```
SELECT * FROM user
```

但是这并不是我说的获取所有字段，而是显式的调用所有字段（对于对性能要求比较高的系统，这个要求并不过分，起码是一个比较好的习惯），下面的用法可以完成预期的作用：

```
Db::table('user')->field(true)->select();
```

`field(true)`的用法会显式的获取数据表的所有字段列表，哪怕你的数据表有100个字段。

### 字段排除

如果我希望获取排除数据表中的`content`字段（文本字段的值非常耗内存）之外的所有字段值，我们就可以使用field方法的排除功能，例如下面的方式就可以实现所说的功能：

```
Db::table('user')->withoutField('content')->select();
```

则表示获取除了content之外的所有字段，要排除更多的字段也可以：

```
Db::table('user')->withoutField('user_id,content')->select();
//或者用
Db::table('user')->withoutField(['user_id','content'])->select();
```

> 注意的是 字段排除功能不支持跨表和join操作。

## 用于写入

除了查询操作之外，`field`方法还有一个非常重要的安全功能--**字段合法性检测**。`field`方法结合数据库的写入方法使用就可以完成表单提交的字段合法性检测，如果我们在表单提交的处理方法中使用了：

```
Db::table('user')->field('title,email,content')->insert($data);
```

即表示表单中的合法字段只有`title`,`email`和`content`字段，无论用户通过什么手段更改或者添加了浏览器的提交字段，都会直接屏蔽。因为，其他所有字段我们都不希望由用户提交来决定，你可以通过自动完成功能定义额外需要自动写入的字段。

> 在开启数据表字段严格检查的情况下，提交了非法字段会抛出异常，可以在数据库设置文件中设置：

> ```
> // 关闭严格字段检查
> 'fields_strict'	=>	false,
> ```
# strict

`strict`方法用于设置是否严格检查字段名，用法如下：

```
// 关闭字段严格检查
Db::name('user')
    ->strict(false)
    ->insert($data);
```

注意，系统默认值是由数据库配置参数`fields_strict`决定，因此修改数据库配置参数可以进行全局的严格检查配置，如下：

```
// 关闭严格检查字段是否存在
'fields_strict'  => false,
```

> 如果开启字段严格检查的话，在更新和写入数据库的时候，一旦存在非数据表字段的值，则会抛出异常。
# limit

`limit`方法主要用于指定查询和操作的数量。

> `limit`方法可以兼容所有的数据库驱动类的

## 限制结果数量

例如获取满足要求的10个用户，如下调用即可：

```
Db::table('user')
    ->where('status',1)
    ->field('id,name')
    ->limit(10)
    ->select();
```

`limit`方法也可以用于写操作，例如更新满足要求的3条数据：

```
Db::table('user')
->where('score',100)
->limit(3)
->update(['level'=>'A']);
```

如果用于`insertAll`方法的话，则可以分批多次写入，每次最多写入`limit`方法指定的数量。

```
Db::table('user')
->limit(100)
->insertAll($userList);
```

## 分页查询

用于文章分页查询是`limit`方法比较常用的场合，例如：

```
Db::table('article')->limit(10,25)->select();
```

表示查询文章数据，从第10行开始的25条数据（可能还取决于where条件和order排序的影响 这个暂且不提）。

对于大数据表，尽量使用`limit`限制查询结果，否则会导致很大的内存开销和性能问题。
# page

page方法主要用于分页查询。

我们在前面已经了解了关于`limit`方法用于分页查询的情况，而`page`方法则是更人性化的进行分页查询的方法，例如还是以文章列表分页为例来说，如果使用`limit`方法，我们要查询第一页和第二页（假设我们每页输出10条数据）写法如下：

```
// 查询第一页数据
Db::table('article')->limit(0,10)->select(); 
// 查询第二页数据
Db::table('article')->limit(10,10)->select(); 
```

虽然利用扩展类库中的分页类Page可以自动计算出每个分页的`limit`参数，但是如果要自己写就比较费力了，如果用`page`方法来写则简单多了，例如：

```
// 查询第一页数据
Db::table('article')->page(1,10)->select(); 
// 查询第二页数据
Db::table('article')->page(2,10)->select(); 
```

显而易见的是，使用`page`方法你不需要计算每个分页数据的起始位置，`page`方法内部会自动计算。

`page`方法还可以和`limit`方法配合使用，例如：

```
Db::table('article')->limit(25)->page(3)->select();
```

当`page`方法只有一个值传入的时候，表示第几页，而`limit`方法则用于设置每页显示的数量，也就是说上面的写法等同于：

```
Db::table('article')->page(3,25)->select(); 
```
# order

`order`方法用于对操作的结果排序或者优先级限制。

用法如下：

```
Db::table('user')
->where('status', 1)
->order('id', 'desc')
->limit(5)
->select();
```

```
 SELECT * FROM `user` WHERE `status` = 1 ORDER BY `id` desc LIMIT 5
```

> 如果没有指定`desc`或者`asc`排序规则的话，默认为`asc`。

支持使用数组对多个字段的排序，例如：

```
Db::table('user')
->where('status', 1)
->order(['order','id'=>'desc'])
->limit(5)
->select(); 
```

最终的查询SQL可能是

```
SELECT * FROM `user` WHERE `status` = 1 ORDER BY `order`,`id` desc LIMIT 5
```

对于更新数据或者删除数据的时候可以用于优先级限制

```
Db::table('user')
->where('status', 1)
->order('id', 'desc')
->limit(5)
->delete(); 
```

生成的SQL

```
DELETE FROM `user` WHERE `status` = 1 ORDER BY `id` desc LIMIT 5
```

如果你需要在`order`方法中使用mysql函数的话，必须使用下面的方式：

```
Db::table('user')
->where('status', 1)
->orderRaw("field(name,'thinkphp','onethink','kancloud')")
->limit(5)
->select();
```
# group

`GROUP`方法通常用于结合合计函数，根据一个或多个列对结果集进行分组 。

`group`方法只有一个参数，支持使用字符串或数组。

例如，我们都查询结果按照用户id进行分组统计：

```
Db::table('user')
    ->field('user_id,username,max(score)')
    ->group('user_id')
    ->select();
```

生成的SQL语句是：

```
SELECT user_id,username,max(score) FROM score GROUP BY user_id
```

也支持对多个字段进行分组，例如：

```
Db::table('user')
    ->field('user_id,test_time,username,max(score)')
    ->group('user_id,test_time')
    ->select();
```

生成的SQL语句是：

```
SELECT user_id,test_time,username,max(score) FROM user GROUP BY user_id,test_time
```
# having

`HAVING`方法用于配合group方法完成从分组的结果中筛选（通常是聚合条件）数据。

`having`方法只有一个参数，并且只能使用字符串，例如：

```
Db::table('score')
    ->field('username,max(score)')
    ->group('user_id')
    ->having('count(test_time)>3')
    ->select(); 
```

生成的SQL语句是：

```
SELECT username,max(score) FROM score GROUP BY user_id HAVING count(test_time)>3
```
# join

`JOIN`方法用于根据两个或多个表中的列之间的关系，从这些表中查询数据。join通常有下面几种类型，不同类型的join操作会影响返回的数据结果。

- **INNER JOIN**: 等同于 JOIN（默认的JOIN类型）,如果表中有至少一个匹配，则返回行
- **LEFT JOIN**: 即使右表中没有匹配，也从左表返回所有的行
- **RIGHT JOIN**: 即使左表中没有匹配，也从右表返回所有的行
- **FULL JOIN**: 只要其中一个表中存在匹配，就返回行

## 说明

```
join ( mixed join [, mixed $condition = null [, string $type = 'INNER']] )
leftJoin ( mixed join [, mixed $condition = null ] )
rightJoin ( mixed join [, mixed $condition = null ] )
fullJoin ( mixed join [, mixed $condition = null ] )
```

**参数**

### join

```
要关联的（完整）表名以及别名
```

支持的写法：

- 写法1：[ '完整表名或者子查询'=>'别名' ]
- 写法2：'不带数据表前缀的表名'（自动作为别名）
- 写法2：'不带数据表前缀的表名 别名'

### condition

```
关联条件，只能是字符串。
```

### type

```
关联类型。可以为:`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
```

**返回值**  
模型对象

## 举例

```
Db::table('think_artist')
->alias('a')
->join('work w','a.id = w.artist_id')
->join('card c','a.card_id = c.id')
->select();
```

```
Db::table('think_user')
->alias('a')
->join(['think_work'=>'w'],'a.id=w.artist_id')
->join(['think_card'=>'c'],'a.card_id=c.id')
->select();
```

默认采用INNER JOIN 方式，如果需要用其他的JOIN方式，可以改成

```
Db::table('think_user')
->alias('a')
->leftJoin('word w','a.id = w.artist_id')
->select();
```

表名也可以是一个子查询

```
$subsql = Db::table('think_work')
->where('status',1)
->field('artist_id,count(id) count')
->group('artist_id')
->buildSql();

Db::table('think_user')
->alias('a')
->join([$subsql=> 'w'], 'a.artist_id = w.artist_id')
->select();
```
# union

UNION操作用于合并两个或多个 SELECT 语句的结果集。

使用示例：

```
Db::field('name')
    ->table('think_user_0')
    ->union('SELECT name FROM think_user_1')
    ->union('SELECT name FROM think_user_2')
    ->select();
```

闭包用法：

```
Db::field('name')
    ->table('think_user_0')
    ->union(function ($query) {
        $query->field('name')->table('think_user_1');
    })
    ->union(function ($query) {
        $query->field('name')->table('think_user_2');
    })
    ->select();
```

或者

```
Db::field('name')
    ->table('think_user_0')
    ->union([
        'SELECT name FROM think_user_1',
        'SELECT name FROM think_user_2',
    ])
    ->select();
```

支持UNION ALL 操作，例如：

```
Db::field('name')
    ->table('think_user_0')
    ->unionAll('SELECT name FROM think_user_1')
    ->unionAll('SELECT name FROM think_user_2')
    ->select();
```

或者

```
Db::field('name')
    ->table('think_user_0')
    ->union(['SELECT name FROM think_user_1', 'SELECT name FROM think_user_2'], true)
    ->select();
```

每个union方法相当于一个独立的SELECT语句。

> UNION 内部的 SELECT 语句必须拥有相同数量的列。列也必须拥有相似的数据类型。同时，每条 SELECT 语句中的列的顺序必须相同。

# distinct

DISTINCT 方法用于返回唯一不同的值 。
以下代码会返回`user_login`字段不同的数据

```
Db::table('think_user')->distinct(true)->field('user_login')->select();
```

生成的SQL语句是：

```
SELECT DISTINCT user_login FROM think_user
```

返回以下数组

```
array(2) {
  [0] => array(1) {
    ["user_login"] => string(7) "chunice"
  }
  [1] => array(1) {
    ["user_login"] => string(5) "admin"
  }
}
```

`distinct`方法的参数是一个布尔值。

# lock

`Lock`方法是用于数据库的锁机制，如果在查询或者执行操作的时候使用：

```
Db::name('user')->where('id',1)->lock(true)->find();
```

就会自动在生成的SQL语句最后加上 `FOR UPDATE`或者`FOR UPDATE NOWAIT`（Oracle数据库）。

lock方法支持传入字符串用于一些特殊的锁定要求，例如：

```
Db::name('user')->where('id',1)->lock('lock in share mode')->find();
```

# cache

`cache`方法用于查询缓存操作，也是连贯操作方法之一。

**cache**可以用于`select`、`find`、`value`和`column`方法，以及其衍生方法，使用`cache`方法后，在缓存有效期之内不会再次进行数据库查询操作，而是直接获取缓存中的数据，关于数据缓存的类型和设置可以参考缓存部分。

下面举例说明，例如，我们对find方法使用cache方法如下：

```
Db::table('user')->where('id',5)->cache(true)->find();
```

第一次查询结果会被缓存，第二次查询相同的数据的时候就会直接返回缓存中的内容，而不需要再次进行数据库查询操作。

默认情况下， 缓存有效期是由默认的缓存配置参数决定的，但`cache`方法可以单独指定，例如：

```
Db::table('user')->cache(true,60)->find();
// 或者使用下面的方式 是等效的
Db::table('user')->cache(60)->find();
```

表示对查询结果的缓存有效期60秒。

cache方法可以指定缓存标识：

```
Db::table('user')->cache('key',60)->find();
```

> 指定查询缓存的标识可以使得查询缓存更有效率。

这样，在外部就可以通过`\think\Cache`类直接获取查询缓存的数据，例如：

```
$result = Db::table('user')->cache('key',60)->find();
$data = \think\facade\Cache::get('key');
```

`cache`方法支持设置缓存标签，例如：

```
Db::table('user')->cache('key',60,'tagName')->find();
```

## 缓存自动更新

这里的缓存自动更新是指一旦数据更新或者删除后会自动清理缓存（下次获取的时候会自动重新缓存）。

当你删除或者更新数据的时候，可以调用相同`key`的`cache`方法，会自动更新（清除）缓存，例如：

```
Db::table('user')->cache('user_data')->select([1,3,5]);
Db::table('user')->cache('user_data')->update(['id'=>1,'name'=>'thinkphp']);
Db::table('user')->cache('user_data')->select([1,3,5]);
```

最后查询的数据不会受第一条查询缓存的影响，确保查询和更新或者删除使用相同的缓存标识才能自动清除缓存。

如果使用主键进行查询和更新(或者删除）的话，无需指定缓存标识会自动更新缓存

```
Db::table('user')->cache(true)->find(1);
Db::table('user')->cache(true)->where('id', 1)->update(['name'=>'thinkphp']);
Db::table('user')->cache(true)->find(1);
```

# cacheAlways

`cacheAlways`的使用方法与`cache`一样,只不过在最终表现中,如果本次查询数据为空,那么`cache`不会缓存,而`cacheAlways`会把这次`空数据`缓存下来,下次查询的时候会直接返回`空数据`.

```
Db::table('user')->cacheAlways('key',60,'tagName')->where('username','think')->find();
```

# comment

COMMENT方法 用于在生成的SQL语句中添加注释内容，例如：

```
Db::table('think_score')->comment('查询考试前十名分数')
    ->field('username,score')
    ->limit(10)
    ->order('score desc')
    ->select();
```

最终生成的SQL语句是：

```
SELECT username,score FROM think_score ORDER BY score desc LIMIT 10 /* 查询考试前十名分数 */
```

# fetchSql

`fetchSql`用于直接返回SQL而不是执行查询，适用于任何的CURD操作方法。 例如：

```
echo Db::table('user')->fetchSql(true)->find(1);
```

输出结果为：

```
SELECT * FROM user where `id` = 1
```

> 对于某些NoSQL数据库可能不支持fetchSql方法
# force

force 方法用于数据集的强制索引操作，例如：

```
Db::table('user')->force('user')->select();
```

对查询强制使用`user`索引，`user`必须是数据表实际创建的索引名称。

# partition

`partition` 方法用于`MySQL`数据库的分区查询，用法如下：

```
// 用于查询
Db::name('log')
    ->partition(['p1','p2'])
    ->select();

// 用于写入
Db::name('user')
    ->partition('p1')
    ->insert(['name' => 'think', 'score' => 100']);
```

# failException

`failException`设置查询数据为空时是否需要抛出异常，用于`select`和`find`方法，例如：

```
// 数据不存在的话直接抛出异常
Db::name('blog')
	->where('status',1)
    ->failException()
    ->select();

// 数据不存在返回空数组 不抛异常
Db::name('blog')
	->where('status',1)
    ->failException(false)
    ->select();
```

或者可以使用更方便的查空报错

```
// 查询多条
Db::name('blog')
	->where('status', 1)
    ->selectOrFail();

// 查询单条
Db::name('blog')
	->where('status', 1)
    ->findOrFail();
```

# sequence

`sequence`方法用于`pgsql`数据库指定自增序列名，其它数据库不必使用，用法为：

```
Db::name('user')
->sequence('user_id_seq')
->insert(['name'=>'thinkphp']);
```

# replace

`replace`方法用于设置`MySQL`数据库`insert`方法或者`insertAll`方法写入数据的时候是否适用`REPLACE`方式。

```
Db::name('user')
    ->replace()
    ->insert($data);
```

# extra

`extra`方法可以用于`CURD`查询，例如：

```
Db::name('user')
    ->extra('IGNORE')
    ->insert(['name' => 'think']);

Db::name('user')
    ->extra('DELAYED')
    ->insert(['name' => 'think']);

Db::name('user')
    ->extra('SQL_BUFFER_RESULT')
    ->select();
```

# duplicate

用于设置`DUPLICATE`查询，用法示例：

```
Db::name('user')
    ->duplicate(['score' => 10])
    ->insert(['name' => 'think']);
```

# procedure

`procedure`方法用于设置当前查询是否为存储过程查询，用法如下：

```
$resultSet = Db::procedure(true)
    ->query('call procedure_name');
```