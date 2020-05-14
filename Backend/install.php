<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

//DB连接
$connStr = 'mongodb://' . CONFIG['DB']['host'] . ':' . CONFIG['DB']['port'];
$conn = new MongoDB\Client($connStr, CONFIG['DB']['params']);
$db = $conn->selectDatabase(CONFIG['DB']['db']);

//增加表，增加index
$db->createCollection('site');
$db->createCollection('article');
$db->createCollection('comment');
$db->createCollection('mailUnsubscribe');

$db->site->createIndex(['_id' => 1]);
$db->site->createIndex(['site' => 1]);
$db->article->createIndex(['_id' => 1]);
$db->article->createIndex(['siteId' => 1, 'path' => 1]);
$db->comment->createIndex(['_id' => 1]);
$db->comment->createIndex(['parentRoot' => 1]);
$db->comment->createIndex(['articleId' => 1, 'status' => 1, 'parentRoot' => 1]);
$db->mailUnsubscribe->createIndex(['siteId' => 1, 'email' => 1]);

print('完成' . PHP_EOL);