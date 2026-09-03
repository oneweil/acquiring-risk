# 变量调试

输出某个变量是开发过程中经常会用到的调试方法，除了使用php内置的`var_dump`和`print_r`之外，框架内置了一个对浏览器友好的`dump`方法，用于输出变量的信息到浏览器查看，并且支持[远程调试](https://developer.topthink.com/thinkphp/dumper)。

用法和PHP内置的`var_dump`一致：

```
dump($var1, ...$varN)
```

使用示例：

```
$blog = Db::name('blog')->where('id', 3)->find();
$user = User::find();
dump($blog, $user);
```

如果需要在调试变量输出后中止程序的执行，可以使用`halt`函数，例如：

```
$blog = Db::name('blog')->where('id', 3)->find();
$user = User::find();
halt($blog, $user);
echo '这里的信息是看不到的';
```

执行后会输出同样的结果并中止执行后续的程序。

> 为了方便查看，某些核心对象由于定义了`__debugInfo`方法，因此在`dump`输出的时候属性可能做了简化。