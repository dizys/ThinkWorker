<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


use Workerman\Protocols\Http;

class Response
{
    protected $connection;
    protected $req;
    private $sessionPrefix;
    private $cookiePrefix;
    private $sent;
    /**
     * Response constructor.
     * @param $connection
     */
    public function __construct($connection, $req)
    {
        $this->connection = $connection;
        $this->req = $req;
        $this->sessionPrefix = is_null(config("session.prefix"))?'':trim(config("session.prefix"));
        $this->cookiePrefix = is_null(config("cookie.prefix"))?'':trim(config("cookie.prefix"));
        $this->sent = false;
    }

    public function sessionStart(){
        return Http::sessionStart();
    }

    public function setSession($name, $value){
        $_SESSION[$this->sessionPrefix.$name] = $value;
        return true;
    }

    public function saveSession(){
        return Http::sessionWriteClose();
    }

    public function clearSession(){
        $_SESSION = ["placeholder"];
        return true;
    }

    public function setHeader($content, $replace = true, $statusCode = 0){
        return Http::header($content, $replace, $statusCode);
    }

    public function rmHeader($name){
        Http::headerRemove($name);
        return true;
    }

    public function setContentType($type, $charset = "utf-8"){
        return Http::header("Content-Type: ".$type.";charset=".$charset);
    }

    public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httponly = null){
        if(is_null($expire)){
            $expire = config("cookie.expire");
        }
        if(is_null($path)){
            $path = config("cookie.path");
        }
        if(is_null($domain)){
            $domain = config("cookie.domain");
        }
        if(is_null($secure)){
            $secure = config("cookie.secure");
        }
        if(is_null($httponly)){
            $httponly = config("cookie.httponly");
        }
        return Http::setcookie($this->cookiePrefix.$name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    public function send($body = ""){
        $this->saveSession();
        $this->connection->send($body);
        $this->sent = true;
    }

    public function json($data){
        $this->setContentType("application/json");
        $data = json($data);
        $this->send($data);
    }

    public function jsonp($data, $callback = null){
        $this->setContentType("application/javascript");
        $data = jsonp($data, $callback);
        $this->send($data);
    }

    public function xml($data){
        $this->setContentType("text/xml");
        $data = xml($data);
        $this->send($data);
    }

    public function redirect($url, $statusCode = 302){
        $this->setHeader("HTTP", true, $statusCode);
        $this->setHeader("Location: ".$url);
        $this->send();
    }

    public function sendFile($file_path){
        //Workerman Webserver file transfer protocol
        $connection = $this->connection;
        // Check 304.
        $info = stat($file_path);
        $modified_time = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $info) {
            // Http 304.
            if ($modified_time === $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
                // 304
                Http::header('HTTP/1.1 304 Not Modified');
                // Send nothing but http headers..
                $connection->close('');
                return false;
            }
        }

        // Http header.
        if ($modified_time) {
            $modified_time = "Last-Modified: $modified_time\r\n";
        }
        $file_size = filesize($file_path);
        $file_info = pathinfo($file_path);
        $extension = isset($file_info['extension']) ? $file_info['extension'] : '';
        $file_name = isset($file_info['filename']) ? $file_info['filename'] : '';
        $header = "HTTP/1.1 200 OK\r\n";
        $mimeTypeMap = StaticDispatcher::getMimeTypeMap();
        if (isset($mimeTypeMap[$extension])) {
            $header .= "Content-Type: " . $mimeTypeMap[$extension] . "\r\n";
        } else {
            $header .= "Content-Type: application/octet-stream\r\n";
            $header .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
        }
        $header .= "Connection: keep-alive\r\n";
        $header .= $modified_time;
        $header .= "Content-Length: $file_size\r\n\r\n";
        $trunk_limit_size = 1024*1024;
        if ($file_size < $trunk_limit_size) {
            return $connection->send($header.file_get_contents($file_path), true);
        }
        $connection->send($header, true);

        // Read file content from disk piece by piece and send to client.
        $connection->fileHandler = fopen($file_path, 'r');
        $do_write = function()use($connection)
        {
            // Send buffer not full.
            while(empty($connection->bufferFull))
            {
                // Read from disk.
                $buffer = fread($connection->fileHandler, 8192);
                // Read eof.
                if($buffer === '' || $buffer === false)
                {
                    return;
                }
                $connection->send($buffer, true);
            }
        };
        // Send buffer full.
        $connection->onBufferFull = function($connection)
        {
            $connection->bufferFull = true;
        };
        // Send buffer drain.
        $connection->onBufferDrain = function($connection)use($do_write)
        {
            $connection->bufferFull = false;
            $do_write();
        };
        $do_write();
    }


}