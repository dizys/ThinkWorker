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
        $this->setHttpBody(
            $this->loadTemplate("ErrorPage", [
                'title'=>think_core_lang("vhost not found title"),
                'code'=>400,
                'msg'=>think_core_lang("vhost not found msg")
            ])
        );
    }

}