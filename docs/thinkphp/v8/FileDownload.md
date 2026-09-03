# 文件下载

## 文件下载

支持文件下载功能，可以更简单的读取文件进行下载操作，支持直接下载输出内容。

你可以在控制器的操作方法中添加如下代码：

```
public function download()
    {
    	// download是系统封装的一个助手函数
        return download('image.jpg', 'my.jpg');
    }
```

访问`download`操作就会下载命名为`my.jpg`的图像文件。

> 下载文件的路径是服务器路径而不是URL路径，如果要下载的文件不存在，系统会抛出异常。

下载文件名可以省略后缀，会自动判断（后面的代码都以助手函数为例）

```
public function download()
    {
    	// 和上面的下载文件名是一样的效果
        return download('image.jpg', 'my');
    }
```

如果需要设置文件下载的有效期，可以使用

```
public function download()
    {
    	// 设置300秒有效期
        return download('image.jpg', 'my')->expire(300);
    }
```

除了`expire`方法外，还支持下面的方法：

|方法|描述|
|---|---|
|name|命名下载文件|
|expire|下载有效期|
|isContent|是否为内容下载|
|mimeType|设置文件的mimeType类型|
|force|是否强制下载|

助手函数提供了内容下载的参数，如果需要直接下载内容，可以在第三个参数传入`true`：

```
public function download()
{
    $data = '这是一个测试文件';
    return download($data, 'test.txt', true);
}
```

支持设置是否强制下载，例如需要打开图像文件而不是浏览器下载的话，可以使用：

```
public function download()
{
    return download('image.jpg', 'my.jpg')->force(false);
}
```