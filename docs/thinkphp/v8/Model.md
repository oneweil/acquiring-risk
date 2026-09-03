# 模型

模型是`ThinkORM`的一个重要组成，`Db`和模型的存在只是`ThinkORM`架构设计中的职责和定位不同，`Db`负责的只是数据（表）访问，模型负责的是业务数据和业务逻辑，实现了数据对象化访问的封装。

> 本手册内容主要针对`ThinkORM3.0`，如果你使用的是`ThinkORM4+`，请参考[ThinkORM手册](https://doc.thinkphp.cn/@think-orm/v4_0/default.html)，将更具有参考价值。

  

相对于`Db`类来说模型的优势主要在于：

- 支持`ActiveRecord`实现；
- 灵活的事件机制；
- 数据自动处理能力；
- 简单直观的数据关联操作；
- 封装业务逻辑；