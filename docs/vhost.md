# 多域名 VHost

ThinkWorker 支持域名解析，您可以通过 VHost 的配置来使不同的域名对应不同的路由规则，从而实现多个网站的功能。

## 配置文件

VHost 配置文件为位于`config`目录下的`vhost.php`

## 基础配置

下载框架源码后，`vhost.php`的初始代码如下：

```php
<?php
return [
    'localhost' => 'localhost'
];
```

这里将所有来自域名`localhost`的请求交给`localhost.php`文件定义的路由规则来分发。(`localhost.php`文件应存放于`route`目录下)

同理，我们可以添加一个新规则，将`www.mydomain.com`的请求交给`mydomain.php`定义的路由规则来分发，只需要作如下修改：

```php
return [
    'localhost' => 'localhost',
    'www.mydomain.com' => 'mydomain'
    //mydomain 即是 mydomain.php 省略 .php，该文件在 route 目录下
];
```

## 多域名同规则

当多个域名需要复用同一个规则文件时，我们可以在域名之间用`,`隔开表示该项对多个域名有效。

```php
return [
    'localhost' => 'localhost',
    'www.mydomain.com,mydomain.com,www.mydomain.cn,mydomain.cn'
        => 'mydomain'//多个域名都指向mydomain路由规则
];
```

## 使用域名通配符

支持使用通配符`*`匹配域名的任意部分:

1. 匹配 mydomain.com 和其所有子域名(如`abc.mydomain.com`)：

```php
return [
    'localhost' => 'localhost',
    '*.mydomain.com,mydomain.com'
        => 'mydomain'//使用通配符*
];
```

2. 匹配任意部分：

```php
return [
    'localhost' => 'localhost',
    'server-*.mydomain.com'
        => 'mydomain',//可匹配如:server-123.mydomain.com
    '*' => 'miss' //匹配剩余的所有域名
];
```

## 直接使用路由规则

通常情况下，我们都建议单独使用路由规则文件，这样更容易阅读和管理。当规则十分简单或者您真的执意将路由写进 vhost.php 的话，ThinkWorker 也提供了支持。

```php
return [
    'localhost' => [//域名后直接跟路由规则
        '/' => 'index/Index/index',
        '/demo' => 'index/Index/demo'
    ]
];
```

关于路由规则详情，请阅读本教程[路由](./route.md)章节
