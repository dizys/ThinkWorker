<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/1
 * Time: 9:55
 */

namespace think\exception;


class DbException extends HttpException
{
    public function __construct($controller="", $method="", $message = "")
    {
        parent::__construct(500, $message, true);
        if(config("think.debug")==true){
            $this->setHttpBody("<html><head><title>数据库错误</title></head><body><h1>数据库错误</h1></body></html>");
        }else{
            $this->setHttpBody($this->loadTemplate("ErrorPage", ['title'=>'页面出错啦~', 'code'=>500, 'msg'=>'对不起欸，页面好像出错啦！']));
        }
    }
}