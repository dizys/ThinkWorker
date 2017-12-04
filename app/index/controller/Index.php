<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace app\index\controller;

use think\Request;
use think\Response;

class Index
{
    public function index(Request $req, Response $resp){
        return "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"><title>Hello, ThinkWorker!</title><meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge,chrome=1\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1,maximum-scale=1\"><meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black\"><meta name=\"apple-mobile-web-app-capable\" content=\"yes\"><style>html,body{margin:0;font-family:Arial,\"Microsoft YaHei\",\"黑体\",\"宋体\",sans-serif}.content{width:35rem;margin:0 auto;display:block;margin-top:100px}.content-fof{width:100%;font-size:10rem;color:#B4BCCC;text-align:center}.content-divide{width:100%;margin-top:20px;border-bottom:1px solid #EDF2FC}.content-msg{width:100%;text-align:center;font-size:1.3rem;color:#D8DCE5;margin-top:20px;font-weight:100}</style></head><body><div class=\"content\"><div class=\"content-fof\">:)</div><div class=\"content-divide\"></div><div class=\"content-msg\">Hello & Welcome to ThinkWorker!</div></div></body></html>";
    }
}