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
    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $post;

    /**
     * @var array
     */
    protected $get;

    /**
     * @var array
     */
    protected $payload;

    /**
     * @var array
     */
    protected $cookie;

    /**
     * @var array|null
     */
    protected $session;

    /**
     * @var array
     */
    protected $files;

    /**
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $requestUri;

    /**
     * @var string
     */
    protected $fullRequestUri;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var string
     */
    private $cookiePrefix;

    //Unstable
    /**
     * @var null|Lang
     */
    protected $lang = null;

    /**
     * @var null|object
     */
    protected $controllerInfo = null;

    /**
     * Request constructor.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->headers = $data['server'];
        $this->method = strtoupper($this->headers['REQUEST_METHOD']);
        $this->cookiePrefix = is_null(config("cookie.prefix"))?'':trim(config("cookie.prefix"));
        $cookiePrefixLen = strlen($this->cookiePrefix);
        // Parsing get parameters
        foreach ($data['get'] as $key=>$value){
            $this->get[filter($key)] = filter($value);
        }
        // Parsing post parameters
        foreach ($data['post'] as $key=>$value){
            $this->post[filter($key)] = filter($value);
        }
        // Parsing cookie values
        foreach ($data['cookie'] as $key=>$value){
            if(substr($key, 0, $cookiePrefixLen) == $this->cookiePrefix){
                $this->cookie[filter(substr($key, $cookiePrefixLen))] = filter($value);
            }else{
                $this->cookie[filter($key)] = filter($value);
            }
            $this->cookie[filter($key)] = filter($value);
        }
        // Get sessions
        $this->session =  Session::get();
        // Parsing files
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

    /**
     * Get a GET parameter or all
     *
     * @param string|null $key
     * @param null $value
     * @return mixed|null|object
     */
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

    /**
     * Get a POST parameter or all
     *
     * @param string|null $key
     * @param null $value
     * @return mixed|null|object
     */
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

    /**
     * Get RAW POST data
     *
     * @param null $data
     * @return string
     */
    public function rawPost($data=null){
        if(is_null($data)){
            return filter($GLOBALS['HTTP_RAW_POST_DATA']);
        }else{
            $GLOBALS['HTTP_RAW_POST_DATA'] = $data;
        }
    }

    /**
     * Get a URI payload or all
     *
     * @param string|null $data
     * @return mixed|null|object
     */
    public function payload($data=null){
        if(is_array($data)){
            $this->payload = $data;
        }else if(is_null($data)){
            return (object)$this->payload;
        }else if(is_string($data)){
            return isset($this->payload[$data])?$this->payload[$data]:null;
        }
    }

    /**
     * Get a cookie value or all
     *
     * @param null $key
     * @return mixed|null|object
     */
    public function cookie($key = null){
        if(is_null($key)){
            return (object) $this->cookie;
        } else {
            return isset($this->cookie[$key])?$this->cookie[$key]:null;
        }
    }

    /**
     * Get a session value or all
     *
     * @param string|null $key
     * @return mixed|null|object
     */
    public function session($key = null){
        if(is_null($key)){
            return (object) $this->session;
        } else {
            return isset($this->session[$key]) ? $this->session[$key] : null;
        }
    }

    /**
     * Refresh session content
     *
     * @return void
     */
    public function freshSession(){
        $this->session = Session::get();
    }

    /**
     * Get a posted file or all
     *
     * @param string|null $name
     * @return array|File|null
     */
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

    /**
     * Get client IP
     *
     * @return string
     */
    public function getIp(){
        return $this->ip;
    }

    /**
     * Get requested hostname
     *
     * @return string
     */
    public function getHostname(){
        return $this->hostname;
    }

    /**
     * Get requested uri without parameters
     *
     * @return string
     */
    public function getUri(){
        return $this->requestUri;
    }

    /**
     * Get full requested uri
     *
     * @return string
     */
    public function getFullUri(){
        return $this->fullRequestUri;
    }

    /**
     * Get requested headers
     *
     * @return array
     */
    public function getHeaders(){
        return $this->headers;
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod(){
        return $this->method;
    }

    /**
     * Get Lang object, automatically adapt to the language context
     *
     * @return null|Lang
     */
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

    /**
     * Dynamically get property
     *
     * @param String $name
     * @return array|null|object|string|File
     */
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

    /**
     * Dynamically set property
     *
     * @param $name
     * @param $value
     * @return void
     */
    public function __set($name, $value)
    {
        if($name == "controllerInfo"){
            $this->controllerInfo = $value;
        }
    }
}