# 多语言

ThinkWorker 能使您的应用轻松拥有国际化的能力。如果您的应用尚未有国际化的需求，将所有对外输出的字符串写在语言包中也有利于输出文本的统一管理与未来国际化的准备。

## 设定默认语言

在`General配置`(`general.php`)中`think`一级配置项下的`default_lang`的值即为默认语言。

## 多语言包

多语言包即是类似于配置的 PHP 文件(如：`zh-cn.php`，表示*中文*语言包)，PHP 代码中返回了数组作为语言包内容。

项目总体有一个公共语言包，项目下的每个应用也可以单独设定自己的语言包。

#### 公共语言包

公共语言包位于`config`目录下的`lang`目录，以语言名英文简写命名，如：`zh-cn.php`、`en.php`、`de.php`等

语言包内容例子：(`zh-cn.php`)

```php
<?php
return [
    'username invalid' => '用户名无效',
    'password invalid' => '密码无效',
    'login failed' => '登录失败',
    'login succeeded' => '登录成功'
];
```

?> 作为规范，语言包里的键名一般都是小写的英文

#### 应用语言包

针对一个应用所编写的语言包应位于`应用目录`下的`lang`，比如`app/demo/lang`。语言包的命名和内容同公共语言包。

## 获取语言对象

`Lang`(`think\Lang`)不被设计成单例，因为它需要根据环境来判断所选取的语言包。比如语言的不同，比如不同应用下要采用各自相应的语言包*(如果应用下没有会自动采用公共语言包)*。闭包下因为没有应用上下文则只能使用公共语言包。

**借助 Request**

控制器方法内使用注入的 Request 下的 lang 成员来获取语言包。会自动根据上下文选取语言包。

```php
<?php
namespace app\demo\controller;
use think\Controller;

class User extends Controller
{
    public function login(){
        $lang = $this->req->lang;
        //...
    }
}
```

**借助 envar 助手函数**

`envar`是 ThinkWorker 中用于获取常用全局变量的助手函数，它在全局有效。会自动根据上下文选取语言包。

```php
public function login(){
    $lang = envar("lang");
    //...
}
```

## 读取内容

通过 `Lang` 对象的 `get` 方法来读取语言包的内容。

```php
public function login($req){
    $lang = $req->lang;
    //...
    return $lang->get("login failed");
}
```

## 使用变量

语言包的内容有时需要灵活可变的部分。这时候，语言包内容的定义就需要加入变量的语法：

```php
return [
    'welcome user' => '用户{$username}您好！您是第{$times}次登录！',
    'file info' => '文件大小为{$1}M，文件名为{$2}，类型为{$3}'
]
```

**读取时传入变量**

```php
public function login($req){
    $lang = $req->lang;
    //...
    return $lang->get("welcome user",
        [
            'username'=>'dizy',
            'times'=>6
        ]
    );
}

public function file($req){
    $lang = $req->lang;
    //...
    return $lang->get("file info", 100, "a.png", "png");
}
```

## 客户端切换语言

让客户端在任意请求的 GET 参数加上 `_lang=语言名` 即可切换语言。语言的切换状态会被保存到客户端的 `Cookie` 之中，决定下次客户请求时 `Lang` 对象的上下文。
