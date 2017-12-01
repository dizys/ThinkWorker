<?php
/**
 * Created by PhpStorm.
 * User: Dizy
 * Date: 2017/12/1
 * Time: 18:14
 */

namespace think\exception;


class ClosureException extends HttpException
{
    public function __construct($message = "")
    {
        parent::__construct(500, $message, true);
        if(config("think.debug")==true) {
            $this->setHttpBody("<html><head><title>闭包出错</title></head><body><h1>闭包出错内部出错</h1></body></html>");
        }else{
            $this->setHttpBody($this->loadTemplate("ErrorPage", ['title'=>'页面出错啦~', 'code'=>500, 'msg'=>'对不起欸，页面好像出错啦！']));
        }
    }
}