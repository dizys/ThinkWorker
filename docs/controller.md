# 控制器

我们通常用控制器来处理用户发来的请求。在 ThinkWorker 中，控制器可以不继承任何基类。当然，继承框架内封装的`think\Controller`可使用更多的现有特性。

## 定义控制器

控制器类文件放在应用目录下的`controller`目录。
<br>
_（比如：`app/demo/controller/Index.php`，这是`demo`应用下类名叫`Index`的控制器）_

> 控制器类名首字母通常大写，命名空间应满足`app\<应用目录名>\controller`

控制器类的定义：

```php
<?php
namespace app\demo\controller;

use think\Controller;

class Index extends Controller
{
    public function index(){
        return "Hello, ThinkWorker!";
    }
}
```

## 控制器方法

如上例定义控制器时，同时定义了一个控制器方法叫`index`。
控制器方法返回的数据会被发送给客户端，交给客户端浏览器渲染。

> 要使控制器方法能被访问，请务必为其加上`public`访问修饰符！

控制器方法可选按顺序接收两个参数：第一个为`Request`(请求)对象，即`think\Request`；第二个为`Response`(响应)对象，即`think\Response`。

```php
<?php
public function index(Request $req, Response $resp)
{
    $resp->json([
        'code' => 200,
        'msg' => '请求成功',
        'data' => null
    ]);
}

public function test($req, $resp){
    $resp->send("hello1!");//发送数据，第一次有效

    $resp->send("hello2!");//无效
    return "hello2!";//无效
    /*因为一次客户请求服务器只能有对应一次发送数据，因此这里发送
    和返回的数据都不会被客户端接收到，客户端只会接收到hello1!*/
}
```

请求与响应的具体用法请参见上一章[请求和响应](./reqandresp.md)

## 控制器初始化

如果想在所有控制器方法被调用前，对控制器进行初始化操作。您可以重写控制器的`_init`方法(该方法访问修饰符为`public`)，在其中加入初始化代码。

例子如下：

```php
<?php
namespace app\demo\controller;

use think\Controller;

class Index extends Controller
{
    protected $myValue;
    public function _init(){
        $this->myValue = "initialized";
    }

    public function index(){
        return "Hello, ThinkWorker!".$this->myValue;
    }
}
```

## 业务逻辑子层

当控制器方法中的业务逻辑较为复杂并且能够再抽象和重用时，我们可以通过使用业务逻辑子层的方式，再将业务逻辑细分。

在应用目录下，可以建立`domain`目录(如：`app/demo/domain`)，在其下放置用于处理某块业务逻辑的类。

**编写业务逻辑子层**

例如，我们在一个项目中需要将用户登陆后的购物车的业务功能抽象出来，称为`ShoppingCart`类。该类的代码文件应位于`app/demo/domain/ShoppingCart.php`

```php
<?php
namespace app\demo\domain;
class ShoppingCart{
    protected $uid;
    protected $cart;
    public function __construct($uid){
        $this->uid = $uid;
    }

    public function add($productId){
        //使用Model对相关表进行操作...
    }

    public function remove($productId){
        //使用Model对相关表进行操作...
    }

    public function list(){
        //使用Model对相关表进行操作...
    }

    public function checkout(){
        //相关业务操作
    }
    //... ...
}
```

**在控制器中使用业务逻辑子层**

将直接操作 Model 的大量操作都封装在`Domain`层后，控制器就能很轻松地调用`Domain`层来控制业务。

> 在业务逻辑子类命名空间正确的情况下，使用时 ThinkWorker 会自动找到并按需加载相应代码文件。

```php
<?php
namespace app\demo\controller
use app\demo\domain\ShoppingCart;//引入正确的命名空间
class Index{
    public function shop(){
        //... ...
        $cart = new ShoppingCart($uid);
        $cart->add(xxx);
        $cart->remove(xxx);
        $cart->checkout();
        //... ...
    }
}
```

## 自动绑定视图

当控制器继承`think\Controller`时，控制器方法在执行前会自动绑定相对应的视图模板。

> 自动对应关系为：控制器`User`下的方法`profile`对应的视图模板文件为`view/User/profile.html`，即`view/<控制器类名>/<方法名>.html`

**使用自动绑定的视图模板**

```php
<?php
namespace app\demo\controller;
use think\Controller;

class User extends Controller//一定记得继承Controller
{
    public function profile(){
        $this->assign("name", "dizy");//模板变量赋值
        return $this->fetch();//渲染模板HTML代码并返回
    }
}
```

关于视图的更多内容，请阅读本教程[视图](./view.md)章节。
