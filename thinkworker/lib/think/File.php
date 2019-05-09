<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


class File
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var mixed
     */
    protected $filedata;

    /**
     * @var int
     */
    protected $filesize;

    /**
     * @var string
     */
    protected $filetype;

    /**
     * File constructor.
     *
     * @param array $fileinfo
     */
    public function __construct($fileinfo)
    {
        $this->name = isset($fileinfo['name'])?$fileinfo['name']:null;
        $this->filename = isset($fileinfo['file_name'])?$fileinfo['file_name']:null;
        $this->filedata = isset($fileinfo['file_data'])?$fileinfo['file_data']:null;
        $this->filesize = isset($fileinfo['file_size'])?$fileinfo['file_size']:null;
        $this->filetype = isset($fileinfo['file_type'])?$fileinfo['file_type']:null;
    }

    /**
     * Get the key for posting this file
     *
     * @return string
     */
    public function getName(){
        return filter($this->name);
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename(){
        return $this->filename;
    }

    /**
     * Get the content of the file
     *
     * @return mixed|null
     */
    public function getData(){
        return $this->filedata;
    }

    /**
     * Get the size of the file
     *
     * @return int
     */
    public function getSize(){
        return $this->filesize;
    }

    /**
     * Get the type of the file
     *
     * @return string
     */
    public function getType(){
        return $this->filetype;
    }

    /**
     * Save the file from memory to file system
     *
     * @param string $directory
     * @param string|null $filename
     * @param bool $replace
     * @return bool
     */
    public function save($directory, $filename = null, $replace = true){
        $directory = rtrim(fix_slashes_in_path($directory), DS).DS;
        if(is_null($filename)){
            $filename = $this->filename;
        }
        if(!$replace && is_file($directory.$filename)){
            return false;
        }
        $res = file_put_contents($directory.$filename, $this->filedata);
        return ($res!=false);
    }

    /**
     * Magical method for property getting
     *
     * @param string $name
     * @return int|mixed|null|string
     */
    public function __get($name)
    {
        switch ($name){
            case "name":
                return $this->getName();
            case "filename":
                return $this->getFilename();
            case "data":
                return $this->getData();
            case "size":
                return $this->getSize();
            case "type":
                return $this->getType();
        }
    }


}
