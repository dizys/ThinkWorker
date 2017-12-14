<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think\exception;


use Throwable;
use Workerman\Worker;

class HttpException extends \Exception
{
    private $debugOnly, $httpBody="", $statusCode = 400;
    protected $origin = null;
    public function __construct($statusCode, $message = "", $debugOnly = true, $origin = null)
    {
        $this->debugOnly = $debugOnly;
        $this->statusCode = $statusCode;
        $this->origin = $origin;
        $message = think_core_charset_auto_revert($message);
        parent::__construct($message, 0, null);
    }

    public function setOrigin($e){
        $this->origin = $e;
    }

    public function setHttpBody($content){
        $this->httpBody = $content;
    }

    public function getHttpBody(){
        return $this->httpBody;
    }

    public function getStatusCode(){
        return $this->statusCode;
    }

    public function isDebugOnly(){
        return $this->debugOnly;
    }

    public function loadTemplate($template, $vars = []){
        $template = file_get_contents(THINK_PATH."tpl".DS.$template.".html");
        if($template!=false && is_array($vars)){
            foreach ($vars as $key => $value){
                $template = str_replace("{\$".$key."}", $value, $template);
            }
            return $template;
        }else{
            return "";
        }
    }

    public function formErrorPos(){
        $traceSrc = is_null($this->origin)?$this:$this->origin;
        return fix_slashes_in_path($traceSrc->getFile())." (".$traceSrc->getLine().think_core_lang("tracing page line")."):";
    }

    public function formErrorMsg(){
        $traceSrc = is_null($this->origin)?$this:$this->origin;
        $head = "";
        try{
            $eRef = new \ReflectionClass($traceSrc);
            $head = "[".$eRef->getShortName()."] ";
        }catch (Throwable $e){

        }
        $msg = $head.think_core_charset_auto_revert($traceSrc->getMessage());
        return $msg;
    }

    public function formTracingTable(){
        $traceSrc = is_null($this->origin)?$this:$this->origin;
        $tableHtml = "";
        $count = 0;
        $tracingLines = config("think.tracing_max_lines");
        $tracingLines = is_null($tracingLines)?false:$tracingLines;
        $traceSrcs = $traceSrc->getTrace();
        foreach ($traceSrcs as $trace){
            if($tracingLines!=false && is_numeric($tracingLines) && $count >= $tracingLines){
                $tableHtml .= "<tr><td class=\"debug-tracing-table-num\">"."</td><td class=\"debug-tracing-table-filepath\">".(sizeof($traceSrcs) - $count - 1)." More ...</td><td class=\"debug-tracing-table-line\"></td><td class=\"debug-tracing-table-call\"></td></tr>";
                break;
            }
            $file = think_core_form_tracing_table_filepath($trace);
            $args = think_core_form_tracing_table_args($trace);
            $call = think_core_form_tracing_table_call($trace);
            $tableHtml .= "<tr><td class=\"debug-tracing-table-num\">".$count."</td><td class=\"debug-tracing-table-filepath\">".$file."</td><td class=\"debug-tracing-table-line\">".(isset($trace['line'])?$trace['line']:" - ")."</td><td class=\"debug-tracing-table-call\">".$call."(".$args.")</td></tr>";
            $count++;
        }
        return $tableHtml;
    }

    public function formRequestTable(){
        $tableHtml = "<tr><td class=\"debug-tracing-table-key\"></td><td class=\"debug-tracing-table-value\">".think_core_lang("tracing page null")."</td></tr>";
        $request = envar("request");
        if(!is_null($request)){
            $tableHtml = "";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request domain")."</td><td class=\"debug-tracing-table-value\">".$request->hostname."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request from")."</td><td class=\"debug-tracing-table-value\">".$request->ip."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request method")."</td><td class=\"debug-tracing-table-value\">".$request->method."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request fulluri")."</td><td class=\"debug-tracing-table-value\">".$request->fullUri."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request route uri")."</td><td class=\"debug-tracing-table-value\">".$request->uri."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request route payload")."</td><td class=\"debug-tracing-table-value\">".json($request->payload)."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request get content")."</td><td class=\"debug-tracing-table-value\">".json($request->get)."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request post content")."</td><td class=\"debug-tracing-table-value\">".json($request->post)."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request raw post content")."</td><td class=\"debug-tracing-table-value\">".(empty($request->rawPost)?think_core_lang("tracing page null"):$request->rawPost)."</td></tr>";
            $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page request file post num")."</td><td class=\"debug-tracing-table-value\">".((sizeof($request->file)===0)?think_core_lang("tracing page null"):(sizeof($request->file).think_core_lang("tracing page request file post num unit")))."</td></tr>";
        }
        return $tableHtml;
    }

    public function formEnvTable(){
        $tableHtml = "";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env system")."</td><td class=\"debug-tracing-table-value\">".php_uname()."</td></tr>";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env php version")."</td><td class=\"debug-tracing-table-value\">".PHP_VERSION."</td></tr>";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env workerman version")."</td><td class=\"debug-tracing-table-value\">".Worker::VERSION."</td></tr>";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env thinkworker version")."</td><td class=\"debug-tracing-table-value\">".THINK_VERSION."</td></tr>";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env pcntl supported")."</td><td class=\"debug-tracing-table-value\">".(extension_loaded('pcntl')?think_core_lang("tracing page env supported"):think_core_lang("tracing page env not supported"))."</td></tr>";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env posix supported")."</td><td class=\"debug-tracing-table-value\">".(extension_loaded('posix')?think_core_lang("tracing page env supported"):think_core_lang("tracing page env not supported"))."</td></tr>";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env pdo supported")."</td><td class=\"debug-tracing-table-value\">".(extension_loaded('PDO')?think_core_lang("tracing page env supported"):think_core_lang("tracing page env not supported"))."</td></tr>";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env inotify supported")."</td><td class=\"debug-tracing-table-value\">".(extension_loaded('inotify')?think_core_lang("tracing page env supported"):think_core_lang("tracing page env not supported"))."</td></tr>";
        $tableHtml .= "<tr><td class=\"debug-tracing-table-key\">".think_core_lang("tracing page env installed extensions")."</td><td class=\"debug-tracing-table-value\">". think_core_get_all_extensions()."</td></tr>";
        return $tableHtml;
    }


}