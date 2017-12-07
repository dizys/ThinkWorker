<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


use Workerman\Protocols\Http;

class Request
{
    //Stable
    protected $headers;
    protected $method;
    protected $post;
    protected $get;
    protected $payload;
    protected $cookie;
    protected $session;
    protected $files;

    protected $hostname;
    protected $requestUri;
    protected $fullRequestUri;
    protected $ip;

    private $sessionPrefix;
    private $cookiePrefix;

    //Unstable
    protected $lang = null;
    protected $controllerInfo = null;
    /**
     * Request constructor.
     */
    public function __construct($data)
    {
        $this->headers = $data['server'];
        $this->method = strtoupper($this->headers['REQUEST_METHOD']);
        $this->sessionPrefix = is_null(config("session.prefix"))?'':trim(config("session.prefix"));
        $this->cookiePrefix = is_null(config("cookie.prefix"))?'':trim(config("cookie.prefix"));
        $sessionPrefixLen = strlen($this->sessionPrefix);
        $cookiePrefixLen = strlen($this->cookiePrefix);
        foreach ($data['get'] as $key=>$value){
            $this->get[filter($key)] = filter($value);
        }
        foreach ($data['post'] as $key=>$value){
            $this->post[filter($key)] = filter($value);
        }
        foreach ($data['cookie'] as $key=>$value){
            if(substr($key, 0, $cookiePrefixLen) == $this->cookiePrefix){
                $this->cookie[filter(substr($key, $cookiePrefixLen))] = filter($value);
            }else{
                $this->cookie[filter($key)] = filter($value);
            }
            $this->cookie[filter($key)] = filter($value);
        }
        foreach ($_SESSION as $key=>$value){
            if(substr($key, 0, $sessionPrefixLen) == $this->sessionPrefix){
                $this->session[filter(substr($key, $sessionPrefixLen))] = filter($value);
            }else{
                $this->session[filter($key)] = filter($value);
            }
        }
        $this->files = [];
        foreach ($data['files'] as $fileinfo){
            array_push($this->files, new File($fileinfo));
        }
        $this->hostname = $this->headers['HTTP_HOST'];
        $this->fullRequestUri = $this->headers['REQUEST_URI'];
        $this->requestUri = $this->headers['REQUEST_URI'];
        if(!!strpos($this->requestUri,"?")){
            $this->requestUri = strtolower(substr($this->requestUri, 0, strpos($this->requestUri, "?")));
        }
        $this->ip = $this->headers['REMOTE_ADDR'];
    }

    public function get($key=null, $value=null){
        if(is_null($key)){
            return (object)$this->get;
        }
        if(is_null($value)){
            return isset($this->get[$key])?$this->get[$key]:null;
        }else{
            $this->get[$key] = $value;
        }
    }

    public function post($key=null, $value=null){
        if(is_null($key)){
            return (object)$this->post;
        }
        if(is_null($value)){
            return isset($this->post[$key])?$this->post[$key]:null;
        }else{
            $this->post[$key] = $value;
        }
    }

    public function rawPost($data=null){
        if(is_null($data)){
            return filter($GLOBALS['HTTP_RAW_POST_DATA']);
        }else{
            $GLOBALS['HTTP_RAW_POST_DATA'] = $data;
        }
    }

    public function payload($data=null){
        if(is_array($data)){
            $this->payload = $data;
        }else if(is_null($data)){
            return (object)$this->payload;
        }else if(is_string($data)){
            return isset($this->payload[$data])?$this->payload[$data]:null;
        }
    }

    public function cookie($key = null){
        if(is_null($key)){
            return (object) $this->cookie;
        } else {
            return isset($this->cookie[$key])?$this->cookie[$key]:null;
        }
    }

    public function session($key = null){
        if(is_null($key)){
            return (object) $this->session;
        } else {
            return isset($this->session[$key]) ? $this->session[$key] : null;
        }
    }

    public function file($name = null){
        if(is_null($name)){
            return $this->files;
        }else{
            foreach ($this->files as $file){
                if($file->getName() === $name){
                    return $file;
                }
            }
        }
        return null;
    }

    public function getIp(){
        return $this->ip;
    }

    public function getHostname(){
        return $this->hostname;
    }

    public function getUri(){
        return $this->requestUri;
    }

    public function getFullUri(){
        return $this->fullRequestUri;
    }

    public function getHeaders(){
        return $this->headers;
    }

    public function getMethod(){
        return $this->method;
    }

    public function getLang(){
        if(!is_null($this->lang)){
            return $this->lang;
        }
        $autoLang = config("think.auto_lang");
        $autoLang = is_null($autoLang)?true:$autoLang;
        $langVar = config("think.var_lang");
        $langVar = is_null($langVar)?"_lang":$langVar;
        $lang = null;
        if($autoLang){
            $lang = $this->get($langVar);
        }
        $cookiePrefix = is_null(config("cookie.prefix"))?'':trim(config("cookie.prefix"));
        if(!is_null($lang)){
            Http::setcookie($cookiePrefix.$langVar, $lang);
        }else{
            if($autoLang){
                $lang = $this->cookie($cookiePrefix.$langVar);
            }
        }
        if(is_null($this->controllerInfo)){
            $this->lang = new Lang($lang);
            return $this->lang;
        }else{
            $this->lang = new Lang($lang, $this->controllerInfo->appNameSpace);
            return $this->lang;
        }
    }

    public function __get($name)
    {
        if($name == "get"){
            return (object)$this->get;
        }else if($name == "post"){
            return (object)$this->post;
        }else if($name == "rawPost"){
            return $this->rawPost();
        }else if($name == "file"){
            return $this->file();
        }else if($name == "payload"){
            return (object)$this->payload;
        }else if($name == "cookie"){
            return (object)$this->cookie;
        }else if($name == "session") {
            return (object)$this->session;
        }else if($name == "ip"){
            return $this->getIp();
        }else if($name == "hostname"){
            return $this->getHostname();
        }else if($name == "uri"){
            return $this->getUri();
        }else if($name == "fullUri"){
            return $this->getFullUri();
        }else if($name == "headers"){
            return $this->getHeaders();
        }else if($name == "method"){
            return $this->getMethod();
        }else if($name == "controllerInfo"){
            return $this->controllerInfo;
        }else if($name == "lang"){
            return $this->getLang();
        }
    }

    public function __set($name, $value)
    {
        if($name == "controllerInfo"){
            $this->controllerInfo = $value;
        }
    }
}