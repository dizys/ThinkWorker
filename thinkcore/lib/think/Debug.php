<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


class Debug
{
    public static function checkPHPSyntax($filename, &$error_msg){
        $file_content = file_get_contents($filename);

        $check_code = "return true; ?>";
        $file_content = $check_code . $file_content . "<?php ";

        try{
            if(!eval($file_content)) {
                $error_msg = "Parse error: parse error in ".$filename;
                return false;
            }
        }catch (\Error $e){
            $error_msg = $e->getMessage();
            return false;
        }


        return true;
    }
}