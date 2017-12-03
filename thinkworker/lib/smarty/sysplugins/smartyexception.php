<?php
use think\exception\HttpException;
/**
 * Smarty exception class
 *
 * @package Smarty
 */
class SmartyException extends HttpException
{
    public static $escape = false;
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {

        parent::__construct(500, $message, true, null);
        if(config("think.debug")==true) {
            $this->setHttpBody($this->getDebugHttpBody());
        }else{
            $this->setHttpBody($this->getProHttpBody());
        }
    }

    private function getDebugHttpBody(){
        return $this->loadTemplate("TracingPage", [
            'title' => think_core_lang("tracing page template smarty error"),
            'main_msg' => think_core_lang("tracing page template smarty error"),
            'main_msg_detail' => "",
            'main_error_pos' => $this->formErrorPos(),
            'main_error_detail' => $this->formErrorMsg(),
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
            'title'=>think_core_lang('page error title'),
            'code'=>500,
            'msg'=>think_core_lang('page error msg')
        ]);
    }


    public function __toString()
    {
        return ' --> Smarty: ' . (self::$escape ? htmlentities($this->message) : $this->message) . ' <-- ';
    }
}
