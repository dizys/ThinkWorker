<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\exception;


class ControllerNotFoundException extends HttpException
{
    public function __construct($controller, $message = "")
    {
        parent::__construct(404, $message, true);
        if(config("think.debug")==true){
            $this->setHttpBody("<html><head><title>找不到控制器</title></head><body><h1>找不到控制器，请检查路由!</h1></body></html>");
        }else{
            $this->setHttpBody($this->loadTemplate("ErrorPage", ['title'=>'页面找不到啦~', 'code'=>404, 'msg'=>'对不起，您要的页面找不到了尼！']));
        }
    }
}