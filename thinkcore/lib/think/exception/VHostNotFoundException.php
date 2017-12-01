<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\exception;


class VHostNotFoundException extends HttpException
{
    public function __construct($message = "")
    {
        parent::__construct(400, $message, false);
        $this->setHttpBody("<html><head><title>域名对应空间不存在</title></head><body>域名对应空间找不到</body></html>");
        $this->setHttpBody($this->loadTemplate("ErrorPage", ['title'=>'网站貌似还没有开通', 'code'=>400, 'msg'=>'抱歉，没有找到域名对应空间。网站还没有开通！']));
    }

}