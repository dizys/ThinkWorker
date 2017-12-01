<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/11/29
 * Time: 22:10
 */

namespace app\demo\controller;


use app\demo\model\User;
use think\Controller;
use think\Db;
use think\Lang;
use think\Request;
use think\Response;
use think\View;


class Index extends Controller
{

    public function index(Request $req, Response $resp){
        //$user = User::find(1);
        //var_dump($user);
        $lang = envar("lang");
        var_dump($lang);
        $v = new View("demo@Index/index");
        $v->assign("haha","ha");
        return $v->fetch();
    }

    public function hatest(){
        $user = Db::table("user")->find(1);
        var_dump($user);
        return ["hatest!"];
    }
}