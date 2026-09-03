# 查询事件

## 查询事件

数据库操作的回调也称为查询事件，是针对数据库的CURD操作而设计的回调方法，主要包括：

|事件|描述|
|---|---|
|before_select|`select`查询前回调|
|before_find|`find`查询前回调|
|after_insert|`insert`操作成功后回调|
|after_update|`update`操作成功后回调|
|after_delete|`delete`操作成功后回调|

使用下面的方法注册数据库查询事件

```
\think\facade\Db::event('before_select', function ($query) {
    // 事件处理
    // 在这里可以对$query进行操作
    $query->where('site_id',1)->order('update_time desc');
    
    $model = $query->getModel();        // 实例化的模型对象,可以给模型自定义一些属性,在这里读取,相当于传参.
});
```

> 不需要在事件中返回任何东西,不要在事件中进行select操作

同一个查询事件可以注册多个响应执行。查询事件在新版里面也已经被事件系统接管了，因此如果你注册了一个`before_select`查询事件监听，底层其实是向标识为`db.before_select`的事件注册了一个监听。

> 查询事件的方法参数只有一个：当前的查询对象。但你可以通过依赖注入的方式添加额外的参数。