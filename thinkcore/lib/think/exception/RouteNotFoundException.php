<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\exception;


class RouteNotFoundException extends HttpException
{
    public function __construct($message = "")
    {
        parent::__construct(404, $message, true);
        if(config("think.debug")==true) {
            $this->setHttpBody("<html><head><title>路由匹配失败</title></head><body><h1>没有路由匹配结果</h1></body></html>");
        }else{
            $this->setHttpBody($this->loadTemplate("ErrorPage", ['title'=>'页面找不到啦~', 'code'=>404, 'msg'=>'对不起，您要的页面找不到了尼！']));
        }
    }
}