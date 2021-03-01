# 数据库

> ThinkWorker 移植了了 Laravel 的数据库部分，您可以参考 Laravel 的使用！

在 ThinkWorker 中连接多种数据库以及对数据库进行查询非常简单，不论使用原生 SQL、还是查询构建器。_(当然还有下章所讲的`Model`)_

在 ThinkWorker 中，使用单例`think\Db`来进行数据库操作。

## 配置数据库

目前支持四种数据库系统： `MySQL`、`Postgres`、`SQLite`、以及 `SQL Server`。
<br>
数据库的配置文件位于`config`目录下的`database.php`

```php
/**
 *  Database Settings
 */
return [
    'driver'    => 'mysql',
    //数据库驱动，支持mysql、pgsql、sqlite、sqlsrv
    'host' => 'localhost',//数据库服务器地址
    'database'  => 'mvctest',//数据库名
    'username'  => 'root',//用户名
    'password'  => 'root',//密码
    'charset'   => 'utf8',//数据库编码
    'collation' => 'utf8_unicode_ci',//排序方法
    'prefix'    => '',//表前缀
];
```

## 使用原生语句

如果配置好数据库连接，就可以通过 `Db` 执行原生 SQL 语句。

## 1. 原生 Select 查找

```php
$results = Db::select('select * from users where id = ?', [1]);
```

`select` 方法会返回一个 `array` 结果。

您也可以对查询进行参数绑定:_(参数绑定可以避免 SQL 注入攻击)_

```php
$results = Db::select('select * from users where id = :id',
    [
        'id' => 1
    ]);
```

## 2. 原生 Insert 语法

```php
Db::insert('insert into users (id, name) values (?, ?)',
    [1, 'Dayle']);
```

## 3. 原生 Update 语法

```php
Db::update('update users set votes = 100 where name = ?',
    ['John']);
```

## 4. 原生 Delete 语法

```php
Db::delete('delete from users');
```

> 注意： `update` 和 `delete` 语法会返回在操作中所影响的数据笔数。

## 使用查询构造器

数据库查询构造器提供方便、流畅的接口，用来建立及执行数据库查找语法。

## 1. 获取结果集

**从一张表中取出所有行**

在查询之前，使用`Db`的`table`方法，`table`方法为给定表返回一个查询构建器，允许你在查询上链接更多约束条件并最终返回查询结果。在本例中，我们使用`get`方法获取表中所有记录：

```php
<?php
namespace app\demo\controller;
use think\Db;
use think\Controller;

class User extends Controller{
    /**
     * 显示用户列表
     */
    public function index()
    {
        $users = Db::table('users')->get();
        $this->assign("users", $users);
        return $this->fetch();
    }
}
```

和原生查询一样，`get`方法返回结果集的数组，其中每一个结果都是`PHP对象`的`StdClass`实例。你可以像访问对象的属性一样访问列的值：

```php
foreach ($users as $user) {
    echo $user->name;
}
```

**从一张表中获取一行/一列**

如果你只是想要从数据表中获取一行数据，可以使用`first`方法，该方法将会返回单个`StdClass`对象：

```php
$user = Db::table('users')->where('name', 'John')->first();
echo $user->name;
```

如果你不需要完整的一行，可以使用`value`方法从结果中获取单个值，该方法会直接返回指定列的值：

```php
$email = Db::table('users')->where('name', 'John')
                           ->value('email');
```

**获取数据列值列表**

如果想要获取包含单个列值的数组，可以使用`lists`方法，在本例中，我们获取所有`title`的数组：

```php
$titles = Db::table('roles')->lists('title');

foreach ($titles as $title) {
    echo $title;
}
```

在还可以在返回数组中为列值指定更多的自定义键（该自定义键必须是该表的其它字段列名，否则会报错）：

```php
$roles = Db::table('roles')->lists('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}
```

**聚合函数**

队列构建器还提供了很多聚合方法，比如`count`, `max`, `min`, `avg`, 和 `sum`，你可以在构造查询之后调用这些方法：

```php
$users = Db::table('users')->count();
$price = Db::table('orders')->max('price');
```

当然，你可以联合其它查询子句和聚合函数来构建查询：

```php
$price = Db::table('orders')
                ->where('finalized', 1)
                ->avg('price');
```

## 2. 查询（Select）

**指定查询子句**

当然，我们并不总是想要获取数据表的所有列，使用`select`方法，你可以为查询指定自定义的`select子句`：

```php
$users = Db::table('users')
                ->select('name', 'email as user_email')
                ->get();
```

`distinct`方法允许你强制查询返回不重复的结果集：

```php
$users = Db::table('users')->distinct()->get();
```

如果你已经有了一个查询构建器实例并且希望添加一个查询列到已存在的`select`子句，可以使用`addSelect`方法：

```php
$query = Db::table('users')->select('name');
$users = $query->addSelect('age')->get();
```

## 3. Where 子句

**简单 where 子句**

使用查询构建器上的`where`方法可以添加`where子句`到查询中，调用`where`最基本的方法需要三个参数，第一个参数是`列名`，第二个参数是一个数据库系统支持的`任意操作符`，第三个参数是该列`要比较的值`。

例如，下面是一个验证 `votes` 列的值是否等于 100 的查询：

```php
$users = Db::table('users')->where('votes', '=', 100)->get();
```

为了方便，如果你只是简单比较列值和给定数值是否相等，可以将数值直接作为`where`方法的第二个参数：

```php
$users = Db::table('users')->where('votes', 100)->get();
```

当然，你可以使用其它操作符来编写`where子句`：

```php
$users = Db::table('users')
                ->where('votes', '>=', 100)
                ->get();

$users = Db::table('users')
                ->where('votes', '<>', 100)
                ->get();

$users = Db::table('users')
                ->where('name', 'like', 'T%')
                ->get();
```

**更多 Where 子句**

`whereBetween`: 方法验证列值是否在给定值之间
<br>
`whereNotBetween`: 方法验证列值不在给定值之间
<br>
`whereIn`/`whereNotIn`: 方法验证给定列的值是否在/不在给定数组中
<br>

## 4. 排序、分组

orderBy

```php
$users = Db::table('users')
                ->orderBy('name', 'desc')
                ->get();
```

groupBy / having

```php
$users = Db::table('users')
                ->groupBy('account_id')
                ->having('account_id', '>', 100)
                ->get();
```

## 5. 插入（Insert）

查询构建器还提供了`insert`方法来插入记录到数据表。`insert`方法接收数组形式的列名和值进行插入操作：

```php
Db::table('users')->insert([
    ['email' => 'taylor@example.com', 'votes' => 0],
    ['email' => 'dayle@example.com', 'votes' => 0]
]);
```

如果数据表有自增 ID，使用`insertGetId`方法来插入记录将会返回 ID 值：

```php
$id = Db::table('users')->insertGetId(
    ['email' => 'john@example.com', 'votes' => 0]
);
```

## 6. 更新（Update）

当然，除了插入记录到数据库，查询构建器还可以通过使用`update`方法更新已有记录。`update`方法和`insert`方法一样，接收列和值的键值对数组包含要更新的列，你可以通过`where子句`来对`update`查询进行约束：

```php
Db::table('users')
            ->where('id', 1)
            ->update(['votes' => 1]);
```

**增加/减少**

查询构建器还提供了方便增减给定列名数值的方法。

这两个方法都至少接收一个参数：需要修改的列。第二个参数是可选的，用于控制列值增加/减少的数目。

```php
Db::table('users')->increment('votes');
Db::table('users')->increment('votes', 5);
Db::table('users')->decrement('votes');
Db::table('users')->decrement('votes', 5);
```

## 7. 删除（Delete）

当然，查询构建器还可以通过`delete`方法从表中删除记录：

```php
Db::table('users')->where('votes', '<', 100)->delete();
```

## 数据库事务处理

你可以使用 `transaction` 方法，去执行一组数据库事务处理的操作：

```php
Db::transaction(function()
{
    DB::table('users')->update(['votes' => 1]);
    DB::table('posts')->delete();
});
```

> 注意： 在 `transaction` 闭包若抛出任何异常会导致事务自动回滚。

有时候你可能需要自己开始一个事务：

```php
Db::beginTransaction();
```

你可以通过 `rollback` 的方法回滚事务：

```php
Db::rollback();
```

最后，你可以通过 `commit` 的方法提交事务：

```php
Db::commit();
```
