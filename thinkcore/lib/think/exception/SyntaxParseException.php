<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\exception;


class SyntaxParseException extends HttpException
{
    public function __construct($message = "")
    {
        parent::__construct(500, $message, true);
        if(config("think.debug")==true) {
            $this->setHttpBody("<html><head><title>语法错误</title></head><body><h1>控制器出现语法错误</h1></body></html>");
        }else{
            $this->setHttpBody($this->loadTemplate("ErrorPage", ['title'=>'页面出错啦~', 'code'=>500, 'msg'=>'对不起欸，页面好像出错啦！']));
        }
    }
}