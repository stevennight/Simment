<?php
namespace helper;

class StringHelper {
    public static function htmlFilter($str){
        $str = str_replace([
            '&',
            '<',
            '>',
            ' ',
            '\'',
            '"'
        ], [
            '&amp;',
            '&lt;',
            '&gt;',
            '&nbsp;',
            '&apos;',
            '&quot;'
        ], $str);
        return $str;
    }
}