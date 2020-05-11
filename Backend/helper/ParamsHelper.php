<?php
namespace helper;


class ParamsHelper
{
    public static function getCommentStatusValues(){
        return [
            'public',
            'audit',
            'hidden',
        ];
    }
}