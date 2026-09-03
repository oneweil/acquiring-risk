# 获取查询参数

# 获取数据库查询参数

有时候我们的查询条件是不固定的,这时候我们可以通过`getOptions()`获取最终的查询条件，这样我们可以很方便的对这次查询生成一次单独的缓存。

```
$model_list = User::order();
if (!empty($create_time)) {
    $model_list->where('create_time', '>',$create_time);
}
$options = $model_list->getOptions();
$list = $model_list->select();
```

返回`options`的数据类似:

```
{
	"json": [],
	"json_assoc": false,
	"field_type": [],
	"soft_delete": ["__TABLE__.delete_time", ["=", 0]],
	"where": {
		"AND": [
			["sass_uid", "=", "61011ed0c9610"],
			["hold_time", ">=", 1630252800, null],
			["hold_time", "<=", 1630339199, null],
			["custom_status", "=", 0]
		]
	}
}
```