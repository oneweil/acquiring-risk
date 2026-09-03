# 更新数据

在使用查询构造器的时候，确保已经引入了门面类：

```
use think\facade\Db;
```

## 更新数据

可以使用save方法更新数据

```
Db::name('user')
    ->save(['id' => 1, 'name' => 'thinkphp']);
```

或者使用`update`方法。

```
Db::name('user')
    ->where('id', 1)
    ->update(['name' => 'thinkphp']);
```

实际生成的SQL语句可能是：

```
UPDATE `think_user`  SET `name`='thinkphp'  WHERE  `id` = 1
```

> `update` 方法返回影响数据的条数，没修改任何数据返回 0

支持使用`data`方法传入要更新的数据

```
Db::name('user')
    ->where('id', 1)
    ->data(['name' => 'thinkphp'])
    ->update();
```

> 如果`update`方法和`data`方法同时传入更新数据，则以`update`方法为准。

如果数据中包含主键，可以直接使用：

```
Db::name('user')
    ->update(['name' => 'thinkphp','id' => 1]);
```

实际生成的SQL语句和前面用法是一样的：

```
UPDATE `think_user`  SET `name`='thinkphp'  WHERE  `id` = 1
```

如果要更新的数据需要使用`SQL`函数或者其它字段，可以使用下面的方式：

```
Db::name('user')
    ->where('id',1)
    ->exp('name','UPPER(name)')
    ->update();
```

实际生成的SQL语句：

```
UPDATE `think_user`  SET `name` = UPPER(name)  WHERE  `id` = 1
```

支持使用`raw`方法进行数据更新。

```
Db::name('user')
    ->where('id', 1)
    ->update([
        'name'		=>	Db::raw('UPPER(name)'),
        'score'		=>	Db::raw('score-3'),
        'read_time'	=>	Db::raw('read_time+1')
    ]);
```

## 自增/自减

可以使用`inc/dec`方法自增或自减一个字段的值（ 如不加第二个参数，默认步长为1）。

```
// score 字段加 1
Db::table('think_user')
    ->where('id', 1)
    ->inc('score')
    ->update();

// score 字段加 5
Db::table('think_user')
    ->where('id', 1)
    ->inc('score', 5)
    ->update();

// score 字段减 1
Db::table('think_user')
    ->where('id', 1)
    ->dec('score')
    ->update();

// score 字段减 5
Db::table('think_user')
    ->where('id', 1)
    ->dec('score', 5)
    ->update();
```

`inc`/`dec`方法可以多次调用更新多个字段，如果你每次只需要更新一个字段，也可以使用`setInc`/`setDec`方法。

```
// score 字段加 1
Db::table('think_user')
    ->where('id', 1)
    ->setInc('score');

// score 字段加 5
Db::table('think_user')
    ->where('id', 1)
    ->setInc('score', 5);

// score 字段减 1
Db::table('think_user')
    ->where('id', 1)
    ->setDec('score');

// score 字段减 5
Db::table('think_user')
    ->where('id', 1)
    ->setDec('score', 5);
```

最终生成的SQL语句可能是：

```
UPDATE `think_user`  SET `score` = `score` + 1  WHERE  `id` = 1 
UPDATE `think_user`  SET `score` = `score` + 5  WHERE  `id` = 1
UPDATE `think_user`  SET `score` = `score` - 1  WHERE  `id` = 1
UPDATE `think_user`  SET `score` = `score` - 5  WHERE  `id` = 1
```

## 延迟更新

对于数据表的统计字段，还提供了延迟更新方法，在`setInc`/`setDec`方法的第三个参数传入延迟更新的时间（秒）。

```
// 阅读统计字段延迟600秒写入
Db::table('think_blog')
    ->where('id', 1)
    ->setInc('read_count', 1, 600);

// 用户关注数延迟600秒写入
Db::table('think_user')
    ->where('id', 1)
    ->setInc('attention', 1, 600);
```

> 如果需要对字段做递减操作的可以使用`setDec`方法，用法一样。

在实际写入数据库字段之前，统计数据会保留在缓存，所以实际读取数据库统计数据会产生延迟。如果你需要实时获取统计数据，需要使用`lazyFields`方法设置需要实时读取延迟数据的字段。

```
// 实时读取延迟写入的阅读统计字段
Db::table('think_blog')
    ->where('id', 1)
    ->lazyFields(['read_count'])
    ->find();

// 实时读取延迟写入的关注字段
Db::table('think_user')
    ->where('id', 1)
    ->lazyFields(['attention'])
    ->find();
```