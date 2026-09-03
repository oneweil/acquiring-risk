# 上传

## 上传文件

> 内置的上传只是上传到本地服务器，上传到远程或者第三方平台的话需要安装额外的扩展。

上传到远程需要安装`think-filesystem`扩展，首先使用下面的命令安装。

```
composer require topthink/think-filesystem
```

假设表单代码如下：

```
<form action="/index/upload" enctype="multipart/form-data" method="post">
<input type="file" name="image" /> <br> 
<input type="submit" value="上传" /> 
</form> 
```

然后在控制器中添加如下的代码：

```
public function upload(){
    // 获取表单上传文件 例如上传了001.jpg
    $file = request()->file('image');
    // 上传到本地服务器
    $savename = \think\facade\Filesystem::putFile( 'topic', $file);
}
```

`$file`变量是一个`\think\File`对象，你可以获取相关的文件信息，支持使用`SplFileObject`类的属性和方法。

## 上传规则

默认情况下是上传到本地服务器，会在`runtime/storage`目录下面生成以当前日期为子目录，以微秒时间的`md5`编码为文件名的文件，例如上面生成的文件名可能是：

```
runtime/storage/topic/20160510/42a79759f284b767dfcb2a0197904287.jpg
```

如果是多应用的话，上传根目录默认是`runtime/index/storage`，如果你希望上传的文件是可以直接访问或者下载的话，可以使用`public`存储方式。

```
$savename = \think\facade\Filesystem::disk('public')->putFile( 'topic', $file);
```

你可以在`config/filesystem.php`配置文件中配置上传根目录及上传规则，例如：

```
return [
    'default' =>  'local',
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root'   => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            'type'     => 'local',
            'root'       => app()->getRootPath() . 'public/storage',
            'url'        => '/storage',
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
    ],
];
```

系统默认根据日期和微秒数生成文件命名规则，为了避免并发导致的冲突可能，我们可以指定上传文件的命名规则，例如：

```
$savename = \think\facade\Filesystem::putFile( 'topic', $file, 'md5');
```

最终生成的文件名类似于：

```
runtime/storage/topic/72/ef580909368d824e899f77c7c98388.jpg
```

> 你可以使用`hash_file`支持的任意哈希规则（也就是`hash_algos`函数返回的规则列表），会自动以散列值的前两个字符作为子目录，后面的散列值作为文件名。

如果需要使用自定义命名规则，可以在`rule`方法中传入函数或者使用闭包方法，例如：

```
$savename = \think\facade\Filesystem::putFile( 'topic', $file, 'uniqid');
```

如果希望以指定的文件名保存,可调用`putFileAs`方法,例如:

```
$savename = \think\facade\Filesystem::putFileAs( 'topic', $file,'abc.jpg');
```

## 多文件上传

如果你使用的是多文件上传表单，例如：

```
<form action="/index/index/upload" enctype="multipart/form-data" method="post">
<input type="file" name="image[]" /> <br> 
<input type="file" name="image[]" /> <br> 
<input type="file" name="image[]" /> <br> 
<input type="submit" value="上传" /> 
</form> 
```

控制器代码可以改成：

```
public function upload(){
    // 获取表单上传文件
    $files = request()->file('image');
    $savename = [];
    foreach($files as $file){
        $savename[] = \think\facade\Filesystem::putFile( 'topic', $file);
    }
}
```

## 上传验证

支持使用验证类对上传文件的验证，包括文件大小、文件类型和后缀：

```
public function upload(){
    // 获取表单上传文件
    $files = request()->file();
    try {
        validate(['image'=>'fileSize:10240|fileExt:jpg|image:200,200,jpg'])
            ->check($files);
        $savename = [];
        foreach($files as $file) {
            $savename[] = \think\facade\Filesystem::putFile( 'topic', $file);
        }
    } catch (\think\exception\ValidateException $e) {
        echo $e->getMessage();
    }
}
```

|验证参数|说明|
|---|---|
|fileSize|上传文件的最大字节|
|fileExt|文件后缀，多个用逗号分割或者数组|
|fileMime|文件MIME类型，多个用逗号分割或者数组|
|image|验证图像文件的尺寸和类型|

具体用法可以参考验证章节的内置规则-> 上传验证。

## 获取文件hash散列值

可以获取上传文件的哈希散列值，例如：

```
// 获取表单上传文件
$file = request()->file('image');
// 获取上传文件的hash散列值
echo $file->md5();
echo $file->sha1();
```

可以统一使用hash方法获取文件散列值

```
// 获取表单上传文件
$file = request()->file('image');
// 获取上传文件的hash散列值
echo $file->hash('sha1');
echo $file->hash('md5');
```