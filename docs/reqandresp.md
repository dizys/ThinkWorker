# 请求和响应

与 Express.Js 框架很相似的是，我们为处理用户请求的控制器方法、闭包注入了`Request`(请求)对象和`Response`(响应)对象。

- `Request`对象用于帮助您获取用户传入的请求信息，比如 GET 参数、POST 数据、cookie、session 等。功能主要是读取。

- `Response`对象用于帮助您向客户端返回数据，可以是 HTML、Json、XML 等。也可以写入 session、cookie、headers 等。

## 对象的获取

在处理请求的控制器和闭包下有多种方式可以获取到`Request`和`Response`对象。这里简单介绍两种。

#### 推荐的方式

推荐的方式是让控制器方法或闭包按顺序接收两个参数，第一个就会是`Request`对象，第二个就会是`Response`对象。

控制器：

```php
class Demo{
    public function index(Request $req, Response $resp){
        $name = $req->get("name");
        $resp->send("Hello,".$name."!");
    }
}
```

闭包：

```php
Route::get("/demo", function(Request $req, Response $resp){
    $name = $req->get("name");
    $resp->send("Hello,".$name."!");
});
```

#### 控制器成员变量

在继承了`think\Controller`时，请求和响应对象会自动注入到控制器，作为其成员变量。
<br>
_(但前提是控制器继承了`think\Controller`类)_

例子：

```php
class Demo extends Controller{
    public function index(){
        $req = $this->req;
        $resp = $this->resp;
        $name = $req->get("name");
        $resp->send("Hello,".$name."!");
    }
}
```

## 获取路由 Payload

```php
$uid = $req->payload("uid");//获取路由变量uid

$uid = $req->payload->uid;//对象方式获取

$all = $req->payload();//获取所有payload
$uid = $all->uid;

```

## 获取 GET 参数

```php
$name = $req->get("name");//获取参数name的值

$name = $req->get->name;//对象方式获取

$all = $req->get();//获取所有参数
$name = $all->name;
```

## 获取 POST 数据

```php
$password = $req->post("password");//获取post的password对应值

$password = $req->post->password;//对象方式获取

$all = $req->post();//获取所有项
$password = $all->password;
```

## 获取 Cookie

```php
$userToken = $req->cookie("user_token");//获取Key为user_token的cookie值

$userToken = $req->cookie->user_token;//对象方式获取

$cookies = $req->cookie();//获取所有项
$userToken = $cookies->user_token;
```

## 获取 Session

```php
$userToken = $req->session("user_token");//获取Key为user_token的session值

$userToken = $req->session->user_token;//对象方式获取

$sessions = $req->session();//获取所有项
$userToken = $sessions->user_token;
```

## 获取上传的文件

ThinkWorker 将上传的文件封装成了`File`对象(`think\File`)。
<br>
而 Request 中的 file 是`File`对象数组。

```php
$image = $req->file("image");//取到POST时Key为image的File对象
$fileSize = $image->size;//文件大小
$filename = $image->filename;//文件名
$data = $image->data;//文件内容
$filetype = $image->type//文件MIME类型
$image->save(TEMP_PATH."1.png");//保存文件

foreach($req->file as $file){//遍历所有文件
    echo $file->filename."\n";
}

```

## 设置 Cookie

```php
$resp->setCookie("user_token", "hI1m9iwBkgZ");
```

## 设置 Session

```php
$resp->setSession("user_token", "hI1m9iwBkgZ");
```

## 清除 Session

```php
$resp->clearSession();//清除所有Session
```

## 发送数据

> 数据的发送只能有一次，发送后客户端即断开。因此多次发送数据仅有第一次能被客户端接到。`发送JSON数据`、`发送JSONP数据`、`发送XML数据`和`发送路径下文件`同理。

```php
$resp->send("Hello, world!");
```

## 发送 JSON 数据

```php
$resp->json([
    'code' => 200,
    'msg' => '请求成功',
    'data' => '请求数据'
]);
```

## 发送 JSONP 数据

```php
$resp->jsonp([
    'code' => 200,
    'msg' => '请求成功',
    'data' => '请求数据'
]);
```

## 发送 XML 数据

```php
$resp->xml([
    'code' => 200,
    'msg' => '请求成功',
    'data' => '请求数据'
]);
```

## 发送路径下文件

```php
$resp->sendFile(TEMP_PATH."1.png");
```

## 重定向

```php
$resp->redirect("http://www.workerman.cn");
```
