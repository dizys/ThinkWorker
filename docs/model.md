# 模型

> 在开始之前，确保在 `config/database.php` 文件中配置好了数据库连接。数据库配置请查看[上章](./db.md)内容。

ThinkWorker 的`Model`(_模型_)基于`Laravel`的`Eloquent ORM`。

`Model`提供了一个美观、简单的与数据库打交道的 `ActiveRecord` 实现，每张数据表都对应一个与该表进行交互的`模型`，`模型`允许你在表中进行数据查询，以及插入、更新、删除等操作。

## 定义模型

控制器类文件放在应用目录下的`model`目录。
<br>
_（比如：`app/demo/model/Flight.php`，这是 demo 应用下类名叫 Flight 的模型）_

> 模型类名首字母通常大写，命名空间应满足`app\<应用目录名>\model`

控制类必须继承`think\Model`基类。定义如下：

```php
<?php
namespace app\demo\model;

use think\Model;

class Flight extends Model
{
    //...
}
```

#### 表名

注意在上例中，我们并没有指定 `Flight` 模型使用哪张表。这种情况下，框架会默认是名为 **模型类名复数** _(即加`s`)_ 的表——除非在模型类中明确指定了其它表。所以，在本例中，框架认为 `Flight` 模型的记录在 `flights` 表中。你也可以在模型中定义`table`属性来指定自定义的表名：

```php
<?php
namespace app\demo\model;

use think\Model;

class Flight extends Model
{
    protected $table = 'my_flights';
}
```

#### 主键

默认情况下认为每张表的主键名为`id`，你可以在模型类中定义一个 `$primaryKey` 属性来覆盖该约定。

```php
class Flight extends Model
{
    protected $primaryKey = 'fid';
}
```

## 获取模型

创建完模型及其关联的数据表后，就要准备从数据库中获取数据。将`Model`(_模型_)看作功能强大的`查询构建器`，您可以使用它来流畅的查询与其关联的数据表。
<br>
例如：

```php
$flights = app\demo\model\Flight::all();

foreach ($flights as $flight) {
    echo $flight->name;
}
```

**添加额外约束**

`Model` 的 `all` 方法返回模型表的所有结果，由于每一个模型都是一个`查询构建器`，你还可以添加约束条件到查询，然后使用 `get` 方法获取对应结果：

```php
use app\demo\model\Flight;

$flights = Flight::where('active', 1)
               ->orderBy('name', 'desc')
               ->take(10)
               ->get();
```

## 获取单个模型/聚合

当然，除了从给定表中获取所有记录之外，还可以使用 `find` 和 `first` 获取单个记录。这些方法返回单个模型实例而不是返回模型集合：

```php
// 通过主键获取模型...
$flight = Flight::find(1);

// 获取匹配查询条件的第一个模型...
$flight = Flight::where('active', 1)->first();
```

<br>
**获取聚合**

当然，你还可以使用查询构建器提供的聚合方法，例如 `count`、`sum`、`max`，以及其它查询构建器提供的聚合方法。这些方法返回计算后的结果而不是整个模型实例：

```php
$count = Flight::where('active', 1)->count();

$max = Flight::where('active', 1)->max('price');
```

## 插入/更新模型

#### 插入

想要在数据库中插入新的记录，只需创建一个新的模型实例，设置模型的属性，然后调用 `save` 方法：

```php
<?php
namespace app\demo\controller;
use think\Controller;
use app\demo\model\Flight;

class FlightController extends Controller{
    public function store(Request $req)
    {
        // 验证请求...

        $flight = new Flight;

        $flight->name = $req->post("name");

        $flight->save();
    }
}
```

#### 更新

`save` 方法还可以用于更新数据库中已存在的模型。要更新一个模型，应该先获取它，设置你想要更新的属性，然后调用 `save` 方法。

```php
$flight = Flight::find(1);//要更新，先获取
$flight->name = 'New Flight Name';
$flight->save();
```

**批量更新**

更新操作还可以同时修改给定查询提供的多个模型实例，在本例中，所有有效且 `destination=San Diego` 的航班都被标记为延迟：

```php
Flight::where('active', 1)
      ->where('destination', 'San Diego')
      ->update(['delayed' => 1]);
```

## 删除模型

要删除一个模型，调用模型实例上的 `delete` 方法：

```php
$flight = Flight::find(1);
$flight->delete();
```

<br>
**通过主键删除模型**

在上面的例子中，我们在调用 `delete` 方法之前从数据库中获取该模型，不过，如果你知道模型的主键的话，可以调用 `destroy` 方法直接删除而不需要获取它：

```php
Flight::destroy(1);
Flight::destroy([1, 2, 3]);
Flight::destroy(1, 2, 3);
```

<br>
**通过查询删除模型**

当然，你还可以通过查询删除多个模型，在本例中，我们删除所有被标记为无效的航班：

```php
$deletedRows = App\Flight::where('active', 0)->delete();
```

## 关联

模型的关联关系以 `模型类方法` 的方式定义。和 `模型` 本身一样，关联关系也是强大的 `查询构建器`，定义关联关系为方法可以提供功能强大的方法链和查询能力。

例如，我们可以链接更多约束条件到 `post` 关联关系：

```php
$user->posts()->where('active', 1)->get();
```

但是，在使用关联关系之前，让我们先学习如何定义每种关联类型。

#### 一对一

一对一关联是一个非常简单的关联关系，例如，一个 `User` 模型有一个与之对应的 `Phone` 模型。要定义这种模型，我们需要将 `phone` 方法置于 `User` 模型中，`phone` 方法会调用`Model` 基类上 `hasOne` 方法并返回其结果：

```php
<?php

namespace app\demo\model;

use think\Model;

class User extends Model{
    public function phone()
    {
        return $this->hasOne('app\demo\model\Phone');
    }
}
```

!> 在不明确规定的情况下，框架会默认外键为 `user_id`，内键为 `id`。

那我们也可以明确规定：

```php
return $this->hasOne('app\demo\model\Phone', 'foreign_key', 'local_key');
```

#### 一对多

`一对多` 是用于定义单个模型拥有多个其它模型的关联关系。例如，一篇博客文章拥有无数评论，和其他关联关系一样，一对多关联通过在模型中定义方法来定义：

```php
<?php
namespace app\demo\model;
use think\Model;

class Post extends Model{
    public function comments()
    {
         return $this->hasMany('app\demo\model\Comment');
    }
}
```

!> 记住，如上所说，框架会自动判断 `Comment` 模型的外键，为方便起见，框架将拥有者模型名称加上 `_id` 后缀作为外键。因此，在本例中，框架假设 `Comment` 模型上的外键是 `post_id`

关联关系被定义后，我们就可以通过访问 `comments` 属性来访问评论集合。由于模型提供了`动态属性`，我们可以像访问模型的属性一样访问关联方法：

```php
$comments = app\demo\model\Post::find(1)->comments;

foreach ($comments as $comment) {
    //
}
```

#### 多对多

多对多关系比 `hasOne` 和 `hasMany` 关联关系要稍微复杂一些。这种关联关系的一个例子就是一个用户有多个角色，同时一个角色被多个用户共用。例如，很多用户可能都有一个 `Admin` 角色。要定义这样的关联关系，需要三张数据表：`users`、`roles` 和 `role_user`，`role_user` 表按照关联模型名的字母顺序命名，并且包含 `user_id` 和 `role_id` 两个列。

多对多关联通过编写返回 `belongsToMany` 方法返回结果的方法来定义，例如，我们在 `User` 模型上定义 `roles` 方法：

```php
<?php
namespace app\demo\model;
use think\Model;

class User extends Model{
    public function roles()
    {
        return $this->belongsToMany('app\demo\model\Role');
    }
}
```

关联关系被定义之后，可以使用动态属性 `roles` 来访问用户的角色：

```php
$user = app\demo\model\User::find(1);

foreach ($user->roles as $role) {
    //
}
```
