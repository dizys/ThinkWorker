# 快速入门

本章会以最简单的流程一步步带您领略 ThinkWorker 的基本开发。

> 开发前须知：因为在 ThinkWorker 中，类和配置文件只被加载一次，之后就常驻内存，所以所有代码在修改后必须[重启](./quickstart?id=_10%e5%90%af%e5%8a%a8%e5%81%9c%e6%ad%a2%e9%87%8d%e5%90%af)ThinkWorker 才能生效！

## 1.了解项目结构

一个 ThinkWorker 项目应该具有下面的目录结构。

- 项目根目录
  - app _(应用开发目录)_
    - index _(样例 App)_
      - controller _(该 App 的控制器目录)_
      - lang _(该 App 的多语言包)_
      - model _(该 App 的模型目录)_
      - view _(该 App 的模板目录)_
    - app.php _(公共入口文件)_
  - config _(配置文件目录)_
    - lang _(公共语言包目录)_
    - database.php _(数据库配置文件)_
    - general.php _(框架总体配置文件)_
    - vhost.php _(多域名解析配置文件)_
  - extend _(扩展目录)_
  - public _(静态资源服务器根目录)_
  - route _(路由规则目录)_
  - runtime _(运行时缓存、日志等目录)_
  - thinkworker _(ThinkWorker 框架)_
  - vendor _(Composer 依赖目录)_

## 2.基本配置

目录`config`下存放框架的配置文件，其中`general.php`为框架总体的基本配置。

首先，这里我们将`think`下的二级配置项`debug`的值设为`true`，这样在出现错误时页面会显示完整的错误和跟踪信息。

要了解更多相关内容，请查看本教程的[配置](./config.md)章节

## 3.设定域名解析(VHost)

目录`config`下有配置文件`vhost.php`。当有多个域名同时解析到本服务器上时，我们可以通过设定该配置文件将来自不同域名的请求区分开，并规定其单独的路由规则。

一个 vhost.php 的例子：

```php
<?php
/**
 *  VHost Settings
 */
return [
    'localhost' => 'localhost',//将localhost解析到localhost.php路由规则文件

    'mydomain.com,*.mydomain.com' => 'mydomain', /* 支持通配符'*'，将
    mydomain.com和所有以.mydomain.com结尾的子域名全部解析到mydomain.php
    这个路由规则文件上 */

    'pic.static.com,img.static.com' => 'picstatic',
];
```

配置中域名所指向的路由规则文件应被存放于`route`目录下。

比如上例中，当 ThinkWorker 服务器接收到来自 mydomain.com 的请求时，会找到第二项配置中设定的值`mydomain`，则 ThinkWorker 会自动找到`route`目录下的`mydomain.php`(即`mydomain`+`.php`)文件，并使用其中的路由规则。

要了解更多相关内容，请查看本教程的[多域名 Vhost](./vhost.md)章节

## 4.设定路由规则

在上一步中，我们设定了`'localhost' => 'localhost'`的 VHost 规则。这意味着 ThinkWorker 在运行时，我们如果在本机浏览器访问`localhost`，路由规则将由`route`目录下的`localhost.php`定义。

我们来看看这个`localhost.php`是怎么写的吧。框架源码本身已经带有一个`localhost.php`，实现了域名根路径到 index 应用下的 Index 控制器下的 index 方法的映射。

这里有一个更多规则的 localhost.php 例子：

```php
<?php
/**
 *  Routing Rules for localhost
 */
return [
    '/' => 'index/Index/index',//根目录请求分发给index应用下的Index控制器的index方法
    '/demo' => ['demo/Index/index'],/* 将对 http://localhost/demo 的访问分发給demo应用下
    的Index控制器的index方法 */

    '/demo/get_only' => ['demo/Index/getOnly', 'GET'],/* 仅分发 http://localhost/demo
    /get_only 的GET访问給demo应用下的Index控制器的getOnly方法 */

    '/demo/get_and_post' => ['demo/Index/getAndPost', 'GET,POST'],/* 仅对对应的GET和POST
    访问进行分发 */

    '/demo/user/:id' => ['demo/User/getProfile', '', ['id'=>"\d*"]]/* 将对 http://lo
    calhost/demo/user/(数字) 的访问分发給demo应用下的User控制器的getProfile方法
    并将该url中的id数字作为payload存储在请求(Request)对象中 */
];
```

ThinkWorker 不支持`隐式路由`，因为多 app 多域名的情况下使用`隐式路由`会使域名与控制器之间的关系变得难以限制。

要了解更多相关内容，请查看本教程的[路由](./route.md)章节

## 5.新添一个 app(应用)

ThinkWorker 允许在一个框架下运行多个应用 app，这其中一个 app 通常就是一个网站。

我们自己入手创建一个新的 app，假设我们給自己的这个 app 起名为 demo。

**我们只需要做的是**：在 app 目录下新建目录 demo。（这时候 app 下面就同时有 index 和 demo 两个目录了，这表示这个框架下目前有两个 app。_app 目录下同时还有 app.php，这是所有 app 加载前自动执行的公共入口，关于公共入口的详情会在之后讲到_）

> `app`目录是所有应用的根目录，也是我们应用开发最主要的战场。

## 6.为 app 新添控制器

我们要为 demo 应用新添控制器，有控制器才能处理路由分发过来的请求。应用的控制器应被放在应用目录下的`controller`目录（本例中就是`app/demo/controller`）。控制器的类名首字母应大写，如 Index、User、FunGate 等。

我们在`app/demo`下新建`controller`目录，并在其中新建`Index.php`文件，填入以下内容：

```php
<?php
namespace app\demo\controller;
class Index{
    public function index(){
        return "\
<html>\
    <head>\
        <title>demo/Index/index对应控制器方法！</title>\
    </head>\
    <body>\
        <h1>通过配置路由，您访问到我了呢！</h1>\
    </body>\
</html>";//控制器方法直接返回的字符串会直接渲染到客户端浏览器
    }

    public function getOnly($req, $resp){
        $resp->send("Get参数: ".json($req->get));
        /*控制器方法可选接收两个参数($req和$resp)，分别为Request(请求对象)和Response(响应对象)。
        $resp->send(String) 可以将字符串直接渲染到客户端浏览器，和 return(String) 作用相同，
        一次请求只能send一次*/
    }

    public function getAndPost($req, $resp){
        $resp->json(["Get参数"=>$req->get,"Post参数"=>$req->post]);
        /*$resp->json(array) 可以返回給客户端json格式
        同理还有$resp->jsonp()、$resp->xml()
        */
    }
}
```

> 注意控制器的命名空间应符合`app\<应用目录名>\controller`

这样，第 4 步例子中设定的第 2、3、4 条路由规则都有了对应的控制器方法实体，相应请求能得以处理。

这时候[启动](./quickstart?id=_10%e5%90%af%e5%8a%a8%e5%81%9c%e6%ad%a2%e9%87%8d%e5%90%af)ThinkWorker，然后本机访问 http://localhost/demo 看看效果吧！

更多关于控制器的内容，请查看本教程的[控制器](./controller.md)章节

## 7.使用视图模板

> 如上一步中 Index 控制器中的 index 方法，在其中直接编写 HTML 代码十分不美观，而且 HTML 中动态的数据不能再像普通 PHP 标签语法一样加入到其中了。我们需要使用模板引擎帮我们渲染 HTML 代码，并在其中嵌入动态变量。

应用目录下的`view`目录(具体到我们这里的例子就是`app/demo/view`)用于存放模板文件。模板文件默认以`.html`为后缀。

> 通常情况下一个控制器方法对应一个模板文件，它们的对应关系如下：
> <br>
> `controller/控制器类名->方法名()`对应的模板文件是`view/控制器类名/方法名.html`

这里，我们着手创建一个模板文件，我们想要创建的是 demo 应用下 Index 控制器的 index 方法所对应的模板，则我们创建如下文件`app/demo/view/Index/index.html`，其内容我们编写如下：

```html
<html>
  <head>
    <title>由模板引擎渲染！</title>
  </head>
  <body>
    <h1>您好！我是由{$template}引擎渲染的页面！</h1>
  </body>
</html>
```

注意到代码中的`{$template}`，这则是模板引擎中表示变量的语法。这里就引用了名叫`template`的变量。

然后我们需要在控制器中使用这个模板，并給`template`变量赋值并渲染出 HTML 代码。则对应控制器方法的代码修改成：

```php
<?php
namespace app\demo\controller;

use think\Controller;
use think\Request;
use think\Response;

class Index extends Controller
{
    public function index(Request $req, Response $resp){
        $this->assign("template", "Smarty");//将模板中的变量$template赋值为字符串"Smarty"
        return $this->fetch();//渲染HTML页面并将页面返回給客户端
    }
    //... 其余省略

```

> 要使控制器具有自动对应模板文件的功能必须让控制器继承`Controller`类，继承后`$this->assign()`与`$this->fetch()`方法才有效。

这时候[重启](/zh-cn/guide/essentials/quickstart?id=_10%e5%90%af%e5%8a%a8%e5%81%9c%e6%ad%a2%e9%87%8d%e5%90%af)ThinkWorker，然后本机访问 http://localhost/demo 看看效果吧：
![](image/zh-cn/guide/essentials/quickstart_view.png)

当然模板引擎能做到的可不止这麽多。要了解更多相关内容，请查看本教程的[视图](zh-cn/guide/essentials/view.md)章节

## 8.数据库配置和基本操作

使用数据库前我们需要对框架进行配置，使其能正确产生数据库连接。有关数据库的配置文件是`config`目录下`database.php`

一个 database.php 文件大概长这样：

```php
<?php
/**
 *  Database Settings
 */
return [
    'driver'    => 'mysql',//数据库驱动，所有PDO支持的扩展
    'host'      => 'localhost',//数据库服务器地址
    'database'  => 'mvctest',//数据库库名
    'username'  => 'root',//数据库用户名
    'password'  => 'root',//数据库密码
    'charset'   => 'utf8',//数据库编码
    'collation' => 'utf8_unicode_ci',//排序规则
    'prefix'    => '',//表前缀
];
```

完成好配置后，控制器方法中可以使用`Db`这样一个单例来访问数据库。下面是一些例子：

**执行原生 SQL 语句**

```php
/** SELECT查询 **/
$users = Db::select('select * from users where active = ?', [1]);
/*传递给 select 方法的第一个参数是原生的 SQL 语句，第二个参数需要绑定到查询的参数绑定，
通常，这些都是 where 子句约束中的值。参数绑定可以避免 SQL 注入攻击*/

/*除了使用 ? 占位符来代表参数绑定外，也可以使用命名绑定:*/
$users = Db::select('select * from users where id = :id', ['id' => 1]);

foreach ($users as $user) {//结果返回数组
    echo $user->name;
}

/** INSERT查询 **/
Db::insert('insert into users (id, name) values (?, ?)', [1, 'ThinkWorker']);

/** INSERT查询 **/
Db::insert('insert into users (id, name) values (?, ?)', [1, 'ThinkWorker']);

/** UPDATE查询 **/
$affected = Db::update('update users set votes = 100 where name = ?',
['ThinkWorker']);

/** DELETE查询 **/
$deleted = Db::delete('delete from users');
```

**使用查询构建器**

```php
// 获取users表中的所有记录
$users = Db::table('users')->get();

// 获取满足条件的一行数据
$user = Db::table('users')->where('name', 'John')->first();//条件name=John
echo $user->name;

// 获取满足条件的一行数据需要的列
$email = Db::table('users')->where('name', 'John')->value('email');

/** 插入insert **/
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);

/** 查询select **/
$users = Db::table('users')
            ->select('name', 'email as user_email')
            ->where('name', 'John')
            ->where('votes','>=',100)
            ->get();

/** 更新update **/
Db::table('users')
            ->where('id', 1)
            ->update(['votes' => 1]);

/** 删除delete **/
Db::table('users')->where('votes', '<', 100)->delete();
```

> 请谨慎使用`update`和`delete`操作，不当的 SQL 语句或构造器操作可能会导致无法挽回的数据丢失！

要了解更多相关内容，请查看本教程的[数据库](./database.md)章节

## 9.使用模型

直接使用`Db`操作数据库是直接粗暴的，我们也可以选用模型来对数据库表操作进行对象化封装，方便我们处理表间的关联关系。

> **模型**(_Model_)提供了一个美观、简单的与数据库打交道的 ActiveRecord 实现，每张数据表都对应一个与该表进行交互的“模型”，模型允许你在表中进行数据查询，以及插入、更新、删除等操作。

#### 模型定义

`Model`类文件放在应用目录下的`model`子目录（本例中就是`app/demo/model`），命名空间应符合`app\<应用目录名>\model`。模型类须继承'Model'类。

这里我们创建一个`User`模型，在`app/demo/model`目录下新建文件`User.php`，填入内容如下：

```php
<?php
namespace app\demo\model;
use think\Model;
class User extends Model{
    protected $table="user";//指定模型对应表明为user
}
```

#### 使用模型

模型定义好后我们就可以在控制器中使用模型来进行表操作啦。

1. **查询构造器依旧可用**

每个模型实际上也是一个查询构造器，所以查询构造器的使用方法依旧适用，如：

```php
User::where('active', 1)
            ->orderBy('name', 'desc')
            ->take(10)
            ->get();
```

2. **查找到模型**

```php
$user = User::find(1);//找到主键id为1的模型对象
echo $user->name;

$users = User::all();//找到表下所有的模型对象
foreach($users as $user){
    echo $user->name;
}
```

3. **插入数据**

```php
$newUser = new User;//新建User模型实例
$newUser->name = "NewBuddy";//为新记录表项赋值
$newUser->save();//完成插入
```

4. **更新数据**

```php
$user = User::find(1);//找到主键id为1的Model对象
$user->name = 'New Name';//修改记录表项值
$user->save();//完成更新
```

5. **删除数据**

```php
$user = User::find(1);
$user->delete();//进行删除记录操作
```

要了解更多相关内容，请查看本教程的[模型](./model.md)章节

## 10.启动/停止/重启

> Windows 系统下不支持`守护进程`(_即后台运行_)和`热重启`。

ThinkWorker 基于`Workerman`，因此服务的启动、停止、重启等操作与所有`Workerman`应用一致。

#### 启动

**1) 普通方式启动**

```bash
php start.php start
```

**2) 以守护进程方式启动** _(仅 Linux)_

与普通方式启动不同的是启动守护进程后服务会在后台运行，CLI 终端退出后也不会退出，需要通过停止命令停止。

```bash
php start.php start -d
```

#### 停止

以`普通方式`启动的服务，按下`ctrl+c`键即可退出，不需此命令。

而以`守护进程方式`启动的服务则需要以下命令才能停止：

```bash
php start.php stop
```

#### 重启

ThinkWorker 的载入机制使部署完新代码后，需要重新重启才能生效。

**1) 普通方式重启**

```bash
php start.php restart
```

**2) 热重启**

> `Workerman`中也叫`平滑重启`。`平滑重启`的过程中，用户的请求不会因为重启而有短暂时间的失败，而是先沿用已经载入内存的旧代码，直到新代码载入完成。这样用户不会感知到重启的过程。

> 但是要注意的是，`热重启`只重新载入应用目录(即`app`)和扩展依赖下的代码，而不会重新载入配置文件(如：`config`目录下和路由目录`route`下的文件)。因此当这些配置文件更改时，需要通过`普通方式重启`才能使其生效。

当只是 app 应用目录(`app`)和扩展依赖目录(`extend`、`vendor`)下的代码、文件更改时，可以采用`热重启`来重启服务。

```bash
php start.php reload
```

#### 查看状态

```bash
php start.php status
```
