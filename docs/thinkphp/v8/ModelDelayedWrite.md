# 延迟写入

延迟写入指的是对于数据表的数值字段（通常为整型），可以设置一定时间的延迟（汇总）写入，比较适合用于文章阅读统计、用户关注数这种频繁更新的需求。

```
 // 延迟600秒写入阅读数
Blog::where('id', 5)->setInc('read_count', 1, 600);
 // 延迟600秒写入用户关注数
User::where('id', 5)->setInc('attention', 1, 600);
```

实际获取模型数据中的统计字段会产生600秒的延迟。如果你需要实时获取统计数据，需要在模型里面设置`lazyFields`属性：

```
<?php
namespace app\model;

use think\Model;

class Blog extends Model
{
    protected $lazyFields = ['read_count'];
}
```

`lazyFields`属性允许设置多个字段，每次读取模型中的延迟写入字段会实时从缓存中读取并且和模型数据进行叠加。