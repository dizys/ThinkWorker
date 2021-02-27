# 视图

ThinkWorker 的`View`(视图)需要依赖模板引擎，ThinkWorker 内置的模板引擎为`Smarty`。
<br>
本章就主要讲述以`Smarty`为模板引擎时，视图的主要使用方法。

## 实例化视图

**手动实例化**

```php
new View("应用目录名","模板相对view目录的位置")
```

例子：

```php
$view = new View("demo", "User/profile");//.html可省
//则用app/demo/view/User/profile.html模板文件初始化视图

//也可以这样写：
$view = new View("demo@User/profile");

```

**控制器自动绑定**

在上一章节中有讲到，控制器继承`think\Controller`基类后会自动将控制器方法和视图模板文件绑定起来。_(其实所谓绑定，即是自动找到对应的模板文件，并依此文件实例化`View`对象)_

?> 自动对应关系为：控制器`User`下的方法`profile`对应的视图模板文件为`view/User/profile.html`，即`view/<控制器类名>/<方法名>.html`

## 变量赋值

对于一个`think\View`的实例`$view`可以使用语法：
<br>
`$view->assign("变量名", 值)`
变量值可为字符串、数字、数组、对象等。

例子：

```php
$view->assign("news", [
    ['name' => '新闻1', 'link' => 'http://xxx.com/1'],
    ['name' => '新闻2', 'link' => 'http://xxx.com/2'],
    ['name' => '新闻3', 'link' => 'http://xxx.com/3'],
]);
```

?> 在继承`think\Controller`的控制器内可以使用`$this->assign(...)`的方法来为绑定的视图内变量赋值

## 模板字符串替换

当模板文件内的某些字符串需要被替换时，比如：`__CSS__`是一个编写模板文件时尚未决定的静态资源文件根目录，使用模板时需被替换成真正的路径`/css`。

```php
$view->replace("__CSS__", '/css');

$view->replace([
    "__CSS__" => '/css',
    '__JS__' => '/js'
]);
```

这样模板内就能使用：

```html
<link rel="stylesheet" href="__CSS__/style.css"/>
<script src="__JS__/script.js">
```

!> 模板字符串替换在模板编译前发生，也就是说模板变量值不会被替换，因为模板变量值的加入在编译时才发生。

## 渲染模板

```php
$html = $view->fetch();//获取渲染后的HTML代码
```

## 基本模板语法

这里介绍`Smarty`模板文件基本的语法。您也可以参考`Smarty`的[官方手册](https://www.smarty.net/docs/zh_CN/smarty.for.designers.tpl)

#### 注释代码

模板注释被`*`号包围,例如 `{* this is a comment *} `
<br>
Smarty 注释不会在模板文件的编译输出中出现。

#### 简单输出变量

简单地这样就好：`{$变量名}`

数组变量：`{$变量名[键名]}`

变量是含有键值索引的数组时：`{$变量名.键名}`

#### 循环遍历数组变量

遍历数组的语法与 php 本身语法很相似:
<br>
`{foreach $arrayvar as $itemvar}`
<br>
或者
<br>
`{foreach $arrayvar as $key=>$value}`

具体的例子：

```html
<html>
  <head>
    <title>{$title}</title>
  </head>
  <body>
    <ul>
      {foreach $news as $item}
      <li>
        <a href="{$item.link}">{$item.name}</a>
      </li>
      {foreachelse}
      <li>没有新闻啦~</li>
      {/foreach}
    </ul>
  </body>
</html>
```

#### 逻辑判断

Smarty 的`{if}`条件判断和 PHP 的 if 非常相似，只是增加了一些特性。全部的 PHP 条件表达式和函数都可以在 if 内使用，如`||`, `or`, `&&`, `and`, `is_array()`, 等等.

```html
{if $variable 条件修饰符 value1}
<!--something-->
{elseif $variable 条件修饰符 value2}
<!--something-->
{else}
<!--something-->
{/if} 例如 {if ( $amount < 0 or $amount > 1000 ) and $volume >= #minVolAmt# and
$name == 'Blog'} ... {/if}
```

#### 原样输出

如果模板本身想显示的字符串与模板语法冲突了，可以使用`literal`标签来防止模板标签被解析，例如：

```html
{literal} This is exaclty what {$it} is! {/literal}
```

`{$it}`不会作为名叫`it`的变量被解析，而是一五一十地按`{$it}`显示出来。

#### 使用 PHP 代码

!>使用 PHP 代码是不推荐的，因为视图应该尽量只负责数据的输出而不应与业务逻辑耦合。

例子：

```php
{php}
    echo "hello";//模板引擎内的echo和var_dump是会显示在网页的
{/php}

<!-- 或者是使用原生标签 -->

<?php
    echo "hello";
?>
```
