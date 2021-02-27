# 配置

ThinkWorker 拥有简单易用的配置功能。主要基于文件系统实现。
<br>
配置单例为`think\Config`

## 配置文件

配置文件统一放在项目根目录下的`config`目录，该目录下的所有配置文件会自动加载读入，并划分域。
<br>
框架自带`database.php`、`general.php`和`vhost.php`三个配置文件。_(目录下同时有`lang`目录，这是用于存放公共语言包的，这会在本教程[多语言](./lang.md)章节讲到)_

## 配置域

用于区分不同的配置文件，我们給每个配置文件内的配置划分了域，其域即为其文件名(不含后缀名)。如，`database.php`对应的域即为`database`。

## 读取配置

**语法：**<br>
`Config::get(配置项名, 域);` _(如省略域，则默认域为`general`)_
<br>
返回配置项对应的值

**基本用法：**

```php
$name = Config::get("app_name");//省略域则默认为general

$name = Config::get("app_name", "general");//获取general配置下的app_name

$username = Config::get("username", "database")
//获取database配置下的username
```

**支持二级配置项：** _(一级项名与二级项名间用`.`隔开)_

```php
$isDebug = Config::get("think.debug");
```

## 判断配置项存在

**语法：**<br>
`Config::has(配置项名, 域)`
<br>
返回`true`(配置项存在)或`false`(配置项不存在)

**用法与读取配置相似：**

```php
$has = Config::has("think.newconfig");
```

## 设定配置

!> 推荐您直接在配置文件中更改相关的值。如果您需要使用`Config`单例动态设定配置，请务必不要在请求处理过程中进行(如控制器或路由分发到的闭包)，建议在`app.php`公共入口直接设定。

**语法：**
`Config::set(配置项名, 设定值, 域)`
，如:

```php
Config::set("app_name", "demo");//省略域同样默认为general
```

## General 配置

`general.php`配置文件下为框架总体的基本配置，这里介绍一些常见的配置项的作用。

| 一级配置项    | 二级配置项                   | 备注                                                                                                                                             | 默认值     |
| ------------- | ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ | ---------- |
| worker_engine | listen_ip                    | 服务监听地址                                                                                                                                     | "0.0.0.0"  |
|               | listen_port                  | 服务监听端口                                                                                                                                     | 80         |
|               | count                        | 进程数(Linux 有效)                                                                                                                               | 4          |
|               | ssl                          | 是否开启 SSL                                                                                                                                     | false      |
|               | ssl_local_cert               | SSL 证书文件位置                                                                                                                                 | -          |
|               | ssl_local_pk                 | SSL 私钥文件位置                                                                                                                                 | -          |
| think         | debug                        | 框架开启调试模式，开启后具有错误详情和异常跟踪页面                                                                                               | false      |
|               | routing_cache_default        | 开启路由缓存，缓存路由规则匹配结果                                                                                                               | true       |
|               | default_filter               | 默认请求输入过滤器，框架会自动调用该值中的函数来对 get、post 等输入进行过滤。<br>多个函数之间用`,`隔开，如：<br>"`htmlspecialchars, strip_tags`" | 空         |
|               | deny_app_list                | 不允许被请求分发到的应用目录，为字符串数组                                                                                                       | ['common'] |
|               | default_lang                 | 默认语言包                                                                                                                                       | "zh-cn"    |
|               | auto_lang                    | 开启框架自动根据 url 和 cookie 切换语言包功能                                                                                                    | true       |
|               | default_return_array_encoder | 在接受到请求的控制器和闭包返回数组时，默认的数组包装器，可选`json`、`jsonp`、`xml`                                                               | "json"     |
| template      | engine                       | 模版引擎                                                                                                                                         | "smarty"   |
|               | tpl_ext                      | 模版文件后缀                                                                                                                                     | "html"     |
|               | caching                      | 开启模版缓存                                                                                                                                     | true       |
| session       | auto_start                   | 自动开启 session                                                                                                                                 | true       |
|               | prefix                       | session 前缀                                                                                                                                     | "think\_"  |
| cookie        | prefix                       | cookie 前缀                                                                                                                                      | 空         |
|               | expire                       | 过期时间单位为秒，`0`表示永久不过期                                                                                                              | 0          |
