## 查看页面Trace

通过查看页面Trace信息可以看到当前请求所有执行的SQL语句
## 查看SQL日志

如果开启了数据库的日志监听（`trigger_sql`)的话，可以在日志文件（或者设置的日志输出类型）中看到详细的SQL执行记录。

> 通常我们建议设置把SQL日志级别写入到单独的日志文件中，具体可以参考日志处理部分。

下面是一个典型的SQL日志：

```
[ SQL ] SHOW COLUMNS FROM `think_user` [ RunTime:0.001339s ]
[ SQL ] SELECT * FROM `think_user` LIMIT 1 [ RunTime:0.000539s ]
```

如果需要增加额外的SQL监听，可以使用

```
Db::listen(function($sql, $runtime, $master) {
    // 进行监听处理
});
```

监听方法支持三个参数，依次是执行的SQL语句，运行时间（秒），以及主从标记（如果没有开启分布式的话，该参数为null，否则为布尔值）。

## 调试执行的SQL语句

在模型操作中 ，为了更好的查明错误，经常需要查看下最近使用的SQL语句，我们可以用`getLastsql`方法来输出上次执行的sql语句。例如：

```
User::find(1);
echo User::getLastSql();
```

输出结果是

```
SELECT * FROM 'think_user' WHERE `id` = 1
```

> `getLastSql`方法只能获取最后执行的`SQL`记录。

也可以使用`fetchSql`方法直接返回当前的查询SQL而不执行，例如：

```
echo User::fetchSql()->find(1);
```

输出的结果是一样的。