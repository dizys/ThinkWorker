<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\exception;


class ControllerException extends HttpException
{
    public function __construct($controller, $method, $message = "")
    {
        parent::__construct(500, $message, true);
        if(config("think.debug")==true) {
            $this->setHttpBody("<html><head><title>控制器出错</title></head><body><h1>控制器方法内部出错</h1></body></html>");
        }else{
            $this->setHttpBody($this->loadTemplate("ErrorPage", ['title'=>'页面出错啦~', 'code'=>500, 'msg'=>'对不起欸，页面好像出错啦！']));
        }
    }
}