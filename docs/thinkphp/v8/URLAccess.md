# URL访问

## URL设计

`8.0`的URL访问受路由影响，如果在没有定义或匹配路由的情况下（并且没有开启强制路由模式的话），则是基于：

```
http://serverName/index.php（或者其它入口文件）/控制器/操作/参数/值…
```

如果使用自动多应用模式的话，URL一般是

```
http://serverName/index.php/应用/控制器/操作/参数/值...
```

> 普通模式的URL访问不再支持，但参数可以支持普通方式传值

如果不支持PATHINFO的服务器可以使用兼容模式访问如下：

```
http://serverName/index.php?s=/控制器/操作/[参数名/参数值...]
```

## URL重写

可以通过URL重写隐藏应用的入口文件`index.php`（也可以是其它的入口文件，但URL重写通常只能设置一个入口文件）,下面是相关服务器的配置参考：

### [ Apache ]

1. `httpd.conf`配置文件中加载了`mod_rewrite.so`模块
2. `AllowOverride None` 将`None`改为 `All`
3. 把下面的内容保存为`.htaccess`文件放到应用入口文件的同级目录下

```
<IfModule mod_rewrite.c>
  Options +FollowSymlinks -Multiviews
  RewriteEngine On

  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
```

### [ IIS ]

如果你的服务器环境支持`ISAPI_Rewrite`的话，可以配置`httpd.ini`文件，添加下面的内容：

```
RewriteRule (.*)$ /index\.php\?s=$1 [I]
```

在IIS的高版本下面可以配置`web.Config`，在中间添加`rewrite`节点：

```
<rewrite>
 <rules>
 <rule name="OrgPage" stopProcessing="true">
 <match url="^(.*)$" />
 <conditions logicalGrouping="MatchAll">
 <add input="{HTTP_HOST}" pattern="^(.*)$" />
 <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
 <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
 </conditions>
 <action type="Rewrite" url="index.php/{R:1}" />
 </rule>
 </rules>
 </rewrite>
```

### [ Nginx ]

在Nginx低版本中，是不支持PATHINFO的，但是可以通过在`Nginx.conf`中配置转发规则实现：

```
location / { // …..省略部分代码
   if (!-f $request_filename) {
   		rewrite  ^(.*)$  /index.php?s=/$1  last;
    }
}
```

> 其实内部是转发到了ThinkPHP提供的兼容URL，利用这种方式，可以解决其他不支持PATHINFO的WEB服务器环境。