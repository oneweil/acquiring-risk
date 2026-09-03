# think助手工具库

### 常用的一些扩展类库

目前已有字符串操作,数组操作.

安装

```
composer require topthink/think-helper
```

> 更新完善中

> 以下类库都在`\think\helper`命名空间下

## Str

> 字符串操作

```
use think\\helper\\Str;

// 检查字符串中是否包含某些字符串
Str::contains($haystack, $needles)

// 检查字符串是否以某些字符串结尾
Str::endsWith($haystack, $needles)

// 获取指定长度的随机字母数字组合的字符串
Str::random($length = 16)

// 字符串转小写
Str::lower($value)

// 字符串转大写
Str::upper($value)

// 获取字符串的长度
Str::length($value)

// 截取字符串
Str::substr($string, $start, $length = null)

//驼峰转下划线
Str::snake($value, $delimiter  =  '_')

//下划线转驼峰(首字母小写)
Str::camel($value)

//下划线转驼峰(首字母大写)
Str::studly

//转为首字母大写的标题格式
Str::title($value)
```

## Arr

> 数组操作

```
use think\\helper\\Arr;
```

#### 判断能否当做数组一样访问

```
//数组返回真
//模型查询的结果也为真
Arr::accessible($value)
```

#### 向数组添加一个元素,支持"点"分割

```
Arr::add($array, $key, $value)
//如下操作
$arr  = [];
$arr = Arr::add($arr,'name.3.ss','thinkphp'); //本行结果$arr['name'][3]['ss'] = 'thinkphp'
Arr::add($arr,'name','thinkphp2');//本行不会产生影响,因为'name'已存在.
```

#### 将数据集管理类转换为数组

```
Arr::collapse($array)
```

#### 排列数组组合

```
$arr  =  Arr::crossJoin(['dd'],['ff'=>'gg'],[2],[['a'=>'mm','kk'],'5']);
//上面行没有什么意义,但是可以看到该函数的作用,数组索引被忽略,数组得值被全部组合
$arr  =  Arr::crossJoin(['a','b','c'],['aa','bb','cc','dd']);
//这一行可以看到组合的效果,返回一个二维数组,第二维每个数组是的给定值排列,比如(a,aa),(a,bb),(a,cc)...直到(c,dd)
```