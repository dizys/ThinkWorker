# 公共入口

公共入口是指应用根目录下的`app.php`

## 加载时机

`app.php`会在 ThinkWorker 的所有子进程初始化时调用：晚于配置文件的加载、系统的初始化；先于接收用户请求、载入控制器、模型、数据库连接等。

## 入口用途

- **加载规定外的源文件**
  <br>
  比如：想加载不符合 ThinkWorker 自动加载规范的库文件时，可以直接在此入口 require 或者 include 相应文件。

- **新添、删除、重写路由规则**
  <br>
  因为晚于配置文件的载入和系统的初始化，因此您可以选择在公共入口修改配置文件已设定的路由规则，或者是动态新添路由规则，这都是允许的。

- **更改配置参数**
  <br>
  同理，您也可以在这里更改配置参数，这会影响到所有的控制器下读到的配置。但是不会影响到框架内部已完成的配置，比如服务端口、数据库连接等。

- **用于开发无控制器应用**
  <br>
  当应用比较简单，不需要使用到控制器、模型等 app 下的完整环境时，通过在此设定路由规则，将控制器用简单的闭包替代，即可完成对用户请求的分发处理。

## 注意事项

1. **请务必不要在路由规则所映射的`闭包`内进行路由规则的更改**
   <br>请求分发到闭包时，闭包执行过程在子进程。因此，对路由的操作只会影响到其中的一个子进程，设定的新路由规则，只有很小的几率生效，大部分的请求还是按更改前的规则分发。_(控制器内也是同样的道理，不能对路由进行更改)_
2. **同理路由规则所设定的`闭包`内不要进行配置更改**
   <br>与上条一致的原因。_(同理，控制器内也不能对配置进行更改)_
3. **不要在所设定的`闭包`内进行语言包更改**

## 例子

一个 app.php 的**正确**例子：

```php
<?php
/** Application Default Entrance */
require __DIR__."/common.php";//引入自己的库

use think\Route;
use think\Config;
use think\Request;
use think\Response;

Config::set("app_name", "demo");//对配置进行更改

Route::get("localhost", "/hello", "index/Index/hello");//为路由添加GET方法规则

Route::any("localhost", "/anymethod", function(){
    return "Hello, Welcome!";
});//添加支持任意请求方法的规则，使用闭包处理请求

Route::post("localhost", "/posthere", function(Request $req, Response $resp){
    $resp->json([
        'code' => 200,
        'name' => $req->post("name"),
        'msg' => 'Hello!'
    ]);
});/*只匹配POST请求，闭包接收请求(Request)和响应(Response)对象
闭包中使用Request对象取到客户端POST过来的数据
使用Response对象向客户端返回JSON格式的数据
*/
```

**错误**的用法：

```php
Route::get("localhost", "/rerule", function(){
    Route::get("localhost", "/hello", "index/Index/index");
    //错误用法！不能在路由分发到的闭包内再新添、更改路由规则！
});

Route::get("localhost", "/reconfig", function(){
    Config::set("app_name", "new name");
    //错误用法！同样不能在此更改配置
})

```
