# 路由

ThinkWorker 提供了简单&强大的路由规则，您可以通过设定路由将用户请求的路径随心所欲地分发到控制器方法或者闭包去处理。

## 配置文件

定义路由规则的文件存放于应用根目录下的`route`目录，一般一个网站对应一个路由规则文件。域名与路由规则的对应关系由上章介绍的[多域名 VHost](./vhost.md)来定义。

比如，在`vhost.php`有这样一条规则：

```php
    "www.mydomain.com" => "myweb",
```

当用户发出向域名`www.mydomain.com`的请求时，框架会找到该条规则，并取出对应值`myweb`。框架会尝试在`route`目录下寻找对应的`myweb.php`文件，并应用其中的路由规则。

## 基本规则

**语法：**<br>
在配置文件中，规则以 php 数组形式定义：`'路径' => '应用目录名/控制器类名/方法名'`

```php
return [
    '/' => 'index/Index/index'
    //将对根路径'/'的请求分发到index应用下的Index控制器的index方法

    '/user' => 'index/User/index'
    //将对路径'/user'的请求分发到应用index应用下的User控制器的index方法
];
```

## 限定请求方法

**语法**<br>
`'路径' => ['应用目录名/控制器类名/方法名','允许的请求方法']`

```php
return [
    '/getonly' => ['index/Demo/getOnly', 'GET'],
    //仅将对路径'/getonly'的GET请求分发到index应用下的Demo控制器的getOnly方法

    '/get_and_post' => ['index/Demo/getAndPost', 'GET,POST'],
    //只分发GET和POST方法

    '/user/delete' => ['index/User/delete', 'DELETE']
    //RESTful API Style
];
```

## 路由变量(Payload)

很多情况下，路由需要更多的灵活性。比如像去匹配`/user/12`这样具有用户 id 变量的路径。或者说，`/blog/john-2017-12/15`这样的路径，其中的 john、2017、12 和 15 亦都是变量。

> 这种在路由路径中出现的变量，在 ThinkWorker 中称为`路径Payload`，或直接称为`Payload`

#### 简单变量

简单变量是指变量占据路径中完整的一段 _(两个`/`之间，或者一个`/`到路径末尾之间称为完整的一段)_

比如：`/user/12/profile`，其中变量`12`占据了`/`到下一个`/`之间的所有空间；
`/user/12`，其中变量`12`占据了`/`一直到路径的末尾的所有空间。

**表示方式：**简单变量在路由表达式中用`:变量名`表示

简单变量的定义例子：

```php
    '/user/:id' => 'index/User/home',
    '/user/:id/profile' => 'index/User/profile',

```

#### 组合变量

组合变量指在路径的一段中出现多个变量的组合形式。

比如：`/blog/2013-12-23`这里的`2013`、`12`、`23`都是要单独获取的 Payload

组合变量定义例子：

```php
    '/blog/{year}-{month}-{day}' => 'index/Blog/read',

    '/blog/:uid/{year}-{month}-{day}' => 'index/User/blog'
    //与简单变量混用
```

#### 变量检查

有时我们需要限定变量的匹配条件，比如用户 id 是数字才允许匹配到该条路由规则。ThinkWorker 允许您使用`正则表达式`来检查变量，检查通过才算匹配成功。

```php
    '/user/:id' => ['index/User/home', '', ['id'=>'\d+']],
    //限定变量id为数字

    '/blog/:uid/{year}-{month}-{day}' =>
            ['index/User/blog', '',
                [
                    'uid'=>'\d+', //限定简单变量uid为数字
                    'year'=>'\d{4}', //限定年份为4位
                    'month'=>'\d{1,2}', //限定月份为1-2位
                    'day'=>'\d{1,2}',//同上
                ]
            ],
```

#### 获得 Payload 值

在处理请求的控制器方法或者闭包内，我们如果想要获取到路由变量的具体值，则需要借助`Request`请求对象(具体命名空间为`think\Request`)。

**例子：**

在控制器中：

```php
<?php
namespace app\index\controller;
use think\Request;
use think\Response;
class User{
    public function blog(Request $req, Response $resp){
        $uid = $req->payload("uid");
        $year = $req->payload("year");
        $month = $req->payload("month");
        $day = $req->payload("day");

        //或者也可以使用对象成员形式(不存在会有警告，建议先判断isset)
        $uid = $req->payload->uid;

        //获取所有payload
        $all = $req->payload;
        //or
        $all = $req->payload();

        return $all;
    }
}

```

在闭包中与控制器方法相似：

```php
Route::get("/user/:uid", function(Request $req, Response $resp){
    $uid = $req->payload("uid");
    return $uid;
});
```

## 闭包支持

ThinkWorker 支持您在路由规则中直接使用闭包来处理请求。_(虽然，我们更推荐使用控制器)_

您可以在路由规则配置文件中使用闭包：

```php
return [
    '/' => 'index/Index/index',
    '/closure' => function(){
        return "这真的不好看~";
    },
    '/closure/getonly' => [function($req, $resp){
        return "Hello, ".$req->get("name");
    }, "GET"],
];
```

## 自定义正则表达式

如果 ThinkWorker 的内置表达式不能满足您的话，您可以自定义表达式来随心所欲匹配 uri 路径。

路径表达式前加上`@`符号，表示之后是完整的`正则表达式`_(包含分界`/`)_。

规则文件中的写法：

```php
return [
    '@/^\/user\/(\d+)$/' => function(){
        return "You've come this far!";
    }
];
```

> 自定义正则表达式时不支持 Payload

## 分组路由

当有很多路径拥有一样的开头的时候，比如`/user/`、`/user/profile`、`/user/edit`、`/user/comments`、`/user/favors`等一系列路径都以`/user`开头。重复地定义这样的开头是件很没效率的事情，于是我们采用分组路由的方法来解决这个问题。

看例子就很容易明白了，规则文件中的写法：

```php
return [
    '/' => 'index/Index/index',

    '[user]'=>//将前缀用[]括起来，表示所有/user开头的分组
        [
            '/'=>'index/User/index',//路径为 /user + / = /user/
            '/profile' => 'index/User/profile', //同上为 /user/profile
            '/edit' => 'index/User/edit', //为 /user/edit
            '/comments' => 'index/User/comments',
            '/favors' => 'index/User/favors'
        ]
]
```

## 注意事项

1. 路由规则**从上往下**匹配，匹配成功后就立即分发。因此，如果一个路径，有多条理论上均可匹配的规则，则实际上只会匹配先申明的。<br>
   也就是说前面的规则会`拦截`后面冲突的规则。

2. 通过 Route(`think\Route`)动态注册的路由，能覆盖之前配置文件设定的路由。但是路由的动态注册**只能发生在接受请求之前**，因此最合适的时机则是公共入口`app.php`。

3. 在 Linux 使用`热重启`时，路由配置**不会**重新加载。因此，若对路由配置进行了更改，需要`普通方式重启`后，新的路由配置才会生效。
