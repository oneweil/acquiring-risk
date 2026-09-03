# 数据库迁移工具

## 数据库迁移工具

使用数据库迁移工具可以将数据库结构和数据很容易的在不同的数据库之间管理迁移。

在以前，为了实现“程序安装”，你可能会导出一份sql文件，安装时，用程序解析这个sql文件，执行里面的语句，这样做有诸多的局限性，但现在使用数据库迁移工具，你可使用一个强大的类库API来创建数据库结构和记录，并且可以容易的安装到Mysql，sqlite，sqlserver等数据库。

原项目手册：[phinx](https://book.cakephp.org/phinx/0/en/index.html)

使用范例：

> 使用之前你应当正确的连接到数据库,不论是mysql,sqlite,sqlserver

### 安装

```
composer require topthink/think-migration
```

### 创建迁移工具文件

```
//执行命令,创建一个操作文件,一定要用大驼峰写法,如下
php think migrate:create AnyClassNameYouWant
//执行完成后,会在项目根目录多一个database目录,这里面存放类库操作文件
//文件名类似/database/migrations/20190615151716_any_class_name_you_want.php
```

### 编辑文件

```
<?php

use think\migration\Migrator;
use think\migration\db\Column;
 
class  AnyClassNameYouWant extends  Migrator
{
    /**
    * Change Method.
    *
    * Write your reversible migrations using this method.
    *
    * More information on writing migrations is available here:
    * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
    *
    * The following commands can be used in this method and Phinx will
    * automatically reverse them when rolling back:
    *
    * createTable
    * renameTable
    * addColumn
    * renameColumn
    * addIndex
    * addForeignKey
    *
    * Remember to call "create()" or "update()" and NOT "save()" when working
    * with the Table class.
    */
    
    public  function  change()
    {
        // create the table
        $table  =  $this->table('users',array('engine'=>'MyISAM'));
        $table->addColumn('username', 'string',array('limit'  =>  15,'default'=>'','comment'=>'用户名，登陆使用'))
        ->addColumn('password', 'string',array('limit'  =>  32,'default'=>md5('123456'),'comment'=>'用户密码')) 
        ->addColumn('login_status', 'boolean',array('limit'  =>  1,'default'=>0,'comment'=>'登陆状态'))
        ->addColumn('login_code', 'string',array('limit'  =>  32,'default'=>0,'comment'=>'排他性登陆标识'))
        ->addColumn('last_login_ip', 'integer',array('limit'  =>  11,'default'=>0,'comment'=>'最后登录IP'))
        ->addColumn('last_login_time', 'datetime',array('default'=>0,'comment'=>'最后登录时间'))
        ->addColumn('is_delete', 'boolean',array('limit'  =>  1,'default'=>0,'comment'=>'删除状态，1已删除'))
        ->addIndex(array('username'), array('unique'  =>  true))
        ->create();
    }

}
```

### 执行迁移工具

```
php think migrate:run
//此时数据库便创建了prefix_users表.
```

> 数据库会有一个migrations表,这个是工具使用的表,不要修改

### 例子

```
 $table = $this->table('followers', ['id' => false, 'primary_key' => ['user_id', 'follower_id']]);
$table->addColumn('user_id', 'integer')
      ->addColumn('follower_id', 'integer')
      ->addColumn('created', 'datetime')
      ->addIndex(['email','username'], ['limit' => ['email' => 5, 'username' => 2]])
      ->addIndex('user_guid', ['limit' => 6])

     ->create();
```

### 表支持的参数选项

|选项|描述|
|---|---|
|comment|给表结构设置文本注释|
|row_format|设置行记录模格式|
|engine|表引擎 _(默认 `InnoDB`)_|
|collation|表字符集 _(默认 `utf8\_general\_ci`)_|
|signed|是否无符号 `signed` _(默认 `true`)_|

### 常用列

- biginteger
- binary
- boolean
- date
- datetime
- decimal
- float
- integer
- string
- text
- time
- timestamp
- uuid

### 所有的类型都支持的参数

|Option|Description|
|---|---|
|limit|文本或者整型的长度|
|length|`limit`别名|
|default|默认值|
|null|允许 `NULL` 值 (不该给主键设置|
|after|在哪个字段名后 _(只对MySQL有效)_|
|comment|给列设置文本注释|

### 索引的用法

```
      ->addIndex(['email','username'], ['limit' => ['email' => 5, 'username' => 2]])
      ->addIndex('user_guid', ['limit' => 6])
      ->addIndex('email',['type'=>'fulltext'])
```

如上面例子所示，默认是`普通索引`，mysql可设置生效`复合索引`，mysql可以设置`fulltext`.

## 自动版本升级降级

该项目可以升级和还原，就像git/svn一样rollback。

如果希望实现自动升级降级，那就把逻辑写在change方法里，只最终调用`create`和`update`方法，不要调用`save`方法。

`change`方法内仅支持以下操作

- createTable
- renameTable
- addColumn
- renameColumn
- addIndex
- addForeignKey

如果真的有调用其他方法，可以写到`up`和`down`方法里，这里的逻辑不支持自动还原，up写升级的逻辑，down写降级的逻辑。

```
    public function change()
    {
        // create the table
        $table = $this->table('user_logins');
        $table->addColumn('user_id', 'integer')
              ->addColumn('created', 'datetime')
              ->create();
    }

    /**
     * Migrate Up.
     */
    public function up()
    {

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
```