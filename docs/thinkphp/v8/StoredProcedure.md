# 存储过程

数据访问层支持存储过程调用，调用数据库存储过程使用下面的方法：

```
$resultSet = Db::query('call procedure_name');
foreach ($resultSet as $result) {

}
```

存储过程返回的是一个数据集，如果你的存储过程不需要返回任何的数据，那么也可以使用execute方法：

```
Db::execute('call procedure_name');
```

存储过程可以支持输入和输出参数，以及进行参数绑定操作。

```
$resultSet = Db::query('call procedure_name(:in_param1,:in_param2,:out_param)', [
    'in_param1' => $param1,
    'in_param2' => [$param2, PDO::PARAM_INT],
    'out_param' => [$outParam, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000],
]);
```

输出参数的绑定必须额外使用`PDO::PARAM_INPUT_OUTPUT`，并且可以和输入参数公用一个参数。

> 无论存储过程内部做了什么操作，每次存储过程调用仅仅被当成一次查询。