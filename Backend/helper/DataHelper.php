<?php
namespace helper;

use MongoDB\Database;

class DataHelper
{
    public static function updateCommentCount(Database $db, $articleId, $rootCount, $allCount){
        $updateResult = $db->article->updateOne([
            '_id' => $articleId
        ], [
            '$inc' => ['commentCount' => $allCount, 'commentRootCount' => $rootCount]
        ]);
    }
}