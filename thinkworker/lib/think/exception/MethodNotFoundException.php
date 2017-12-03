<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\exception;


class MethodNotFoundException extends HttpException
{
    protected $controller, $method;
    public function __construct($origin, $controller, $method, $message = "")
    {
        $message = "Method Not Found: ".$controller."->".$method."()" ;
        parent::__construct(404, $message, true, $origin);
        $this->controller = $controller;
        $this->method = $method;
        if(config("think.debug")==true){
            $this->setHttpBody($this->getDebugHttpBody());
        }else{
            $this->setHttpBody($this->getProHttpBody());
        }
    }


    private function getDebugHttpBody(){
        return $this->loadTemplate("TracingPage", [
            'title' => think_core_lang("tracing page method not found"),
            'main_msg' => think_core_lang("tracing page method not found"),
            'main_msg_detail' => $this->controller."->".$this->method,
            'main_error_pos' => $this->formErrorPos(),
            'main_error_detail' => think_core_lang("tracing page method not found error detail", ["controller"=>$this->controller, "method"=>$this->method]),
            'lang_tracing' => think_core_lang("tracing page tracing"),
            'lang_src' => think_core_lang("tracing page src file"),
            'lang_line' => think_core_lang('tracing page line num'),
            'lang_call' => think_core_lang("tracing page call"),
            'tracing_table' => $this->formTracingTable(),
            'request_table' => $this->formRequestTable(),
            'env_table' => $this->formEnvTable(),
            'lang_key' => think_core_lang("tracing page key"),
            'lang_value' => think_core_lang("tracing page value"),
            'lang_request' => think_core_lang("tracing page request detail"),
            'lang_env' => think_core_lang("tracing page env")
        ]);
    }

    private function getProHttpBody(){
        return $this->loadTemplate("ErrorPage", [
            'title'=>think_core_lang("page not found title"),
            'code'=>404,
            'msg'=>think_core_lang("page not found msg")
        ]);
    }
}