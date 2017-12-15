<?php
/**
 *  ThinkWorker - THINK AND WORK FAST
 *  Copyright (c) 2017 http://thinkworker.cn All Rights Reserved.
 *  Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  Author: Dizy <derzart@gmail.com>
 */

namespace think;


class Filter
{
    /**
     * Filter the String with default functions
     *
     * @param string $body
     * @return string
     */
    public static function filt($body){
        $filters = config("think.default_filter");
        $filters = explode(",", $filters);
        foreach ($filters as $filter){
            $filter = trim($filter);
            !function_exists($filter) or $body = $filter($body);
        }
        return $body;
    }
}