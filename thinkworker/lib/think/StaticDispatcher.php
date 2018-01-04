<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;

//TODO: Caching
use Workerman\Protocols\Http;

class StaticDispatcher
{
    /**
     * Mime mapping.
     *
     * @var array
     */
    protected static $mimeTypeMap = array();

    /**
     * Static resource dispatcher initialization method
     *
     * @return void
     */
    public static function _init(){
        self::initMimeTypeMap();
    }

    /**
     * Initialize mime-type map
     *
     * @return void
     */
    private static function initMimeTypeMap(){
        //Workerman Http protocol inner mime type maps
        $mime_file = Http::getMimeTypesFile();
        if (!is_file($mime_file)) {
            Log::e("$mime_file mime.type file not fond");
            return;
        }
        $items = file($mime_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($items)) {
            Log::e("get $mime_file mime.type content fail");
            return;
        }
        foreach ($items as $content) {
            if (preg_match("/\s*(\S+)\s+(\S.+)/", $content, $match)) {
                $mime_type                      = $match[1];
                $workerman_file_extension_var   = $match[2];
                $workerman_file_extension_array = explode(' ', substr($workerman_file_extension_var, 0, -1));
                foreach ($workerman_file_extension_array as $workerman_file_extension) {
                    self::$mimeTypeMap[$workerman_file_extension] = $mime_type;
                }
            }
        }
    }

    /**
     * Get mime-type map
     *
     * @return array
     */
    public static function getMimeTypeMap(){
        return self::$mimeTypeMap;
    }

    /**
     * Try match and dispatch static resources
     *
     * @param Request $req
     * @param Response $resp
     * @return bool
     */
    public static function dispatch(Request $req, Response $resp){
        //todo: http 1.1 Content-Range Support
        $filepath = self::tryMatchFileName($req);
        if($filepath === false){return false;}
        self::sendFile($filepath, $resp);
        return true;

    }

    /**
     * Match static filename with Request
     *
     * @param Request $req
     * @return bool|string
     */
    private static function tryMatchFileName(Request $req){
        $uri = str_replace("/", DS, $req->uri);
        $uri = substr($uri, 1);
        $urlInfo = pathinfo($uri);
        $filePath = PUBLIC_PATH.$uri;
        if(is_file($filePath)){
            $filePath = realpath($filePath);
            return $filePath;
        }else{
            return false;
        }
    }

    /**
     * Send file to client
     *
     * @param string $filepath
     * @param Response $resp
     */
    private static function sendFile($filepath, Response $resp){
        $resp->sendFile($filepath);
    }
}