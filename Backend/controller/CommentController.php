<?php
namespace controller;
use helper\DataHelper;
use helper\IpHelper;
use helper\StringHelper;
use MongoDB\BSON\ObjectId;
use MongoDB;

class CommentController extends Controller
{
    public $site = null;

    /**
     * http://comment.local/api.php?controller=comment&action=list&site=blog&path=/1&page=0&sign=1
     * @throws \Exception
     */
    public function listAction(){
        $params = $_GET;
        $site = @$params['site'];
        $path = @$params['path'];
        if(!$site || !$path){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ], 'json');
            return;
        }

        $this->site = $site;
        if(!$this->setHeader()) return;

        //site find
        $siteData = $this->db->site->findOne(['site' => $site], [
            'projection' => [
                '_id' => 1,
                'autoCreateArticle' => 1,
                'perPageCount' => 1,
                'subCommentMainCount' => 1,
                'requiredUsername' => 1,
                'requiredEmail' => 1
            ]
        ]);
        if(!$siteData){
            $this->response([
                'code' => -1,
                'msg' => '非法站点'
            ], 'json');
            return;
        }
        $siteId = $siteData['_id'];
        $perPageCount = intval(@$siteData['perPageCount'])?:10;
        $subCommentMainCount = intval(@$siteData['subCommentMainCount'])?:5;
        $requiredEmail = @$siteData['requiredEmail'];
        $requiredUsername = @$siteData['requiredUsername'];

        //article find
        $articleData = $this->db->article->findOne(['siteId' => $siteId, 'path' => $path], ['projection' => [
            '_id' => 1,
            'commentCount' => 1,
            'commentRootCount' => 1,
            'commentSwitch' => 1
        ]]);
        if(!$articleData){
            $res = [
                'code' => 0,
                'msg' => '成功',
                'site' => $site,
                'type' => 'main',
                'article' => [
                    'path' => $path,
                    'commentCount' => 0,
                    'commentRootCount' => 0,
                    'commentSwitch' => @$siteData['autoCreateArticle']
                ],
                'config' => [
                    'subCommentMainCount' => $subCommentMainCount,
                    'perPageCount' => $perPageCount,
                    'requiredEmail' => $requiredEmail,
                    'requiredUsername' => $requiredUsername
                ],
                'comment' => [],
                'page' => 0
            ];
            $this->response($res, 'json');
            return;
        }
        $articleId = $articleData['_id'];

        //comment find
        $page = isset($params['page'])?intval($params['page']):0;
        $skip = $page * $perPageCount;
        //只查找public状态、无父节点（不是回复评论的顶级评论）的评论
        $data = $this->db->comment->aggregate([
            [
                '$match' => [
                    'articleId' => $articleId,
                    'status' => 'public',
                    'parentRoot' => null
                ],
            ],
            [
                '$lookup' => [
//                    'localField' => '_id',
//                    'foreignField' => 'parentRoot',
                    'from' => 'comment',
                    'as' => 'sub',
                    'let' => [
                        'id' => '$_id',
                    ],
                    'pipeline' => [
                        [
                            '$match' => [
                                '$expr' => [    //两表的关联字段
                                    '$eq' => [
                                        '$parentRoot', '$$id',
                                    ]
                                ],
                                'status' => 'public',
                            ],
                        ],
                        [
                            '$sort' => [
                                'date' => 1,
                            ]
                        ],
                        //限制10条，多出一条用于判断是否还有更多的评论
                        [
                            '$limit' => $subCommentMainCount+1
                        ]
                    ]
                ]
            ],
            [
                '$sort' => ['date' => -1],
            ],
            [
                '$skip' => $skip,
            ],
            [
                '$limit' => $perPageCount,
            ],
            [
                '$project' => [
                    'comment' => 1, 'parentId' => 1, 'status' => 1, 'username' => 1, 'isAdmin' => 1,
                    'date' => [ '$dateToString' => ['format' => "%Y-%m-%d %H:%M:%S", 'date' => '$date', 'timezone' => @CONFIG['global']['timezone']]],
                    'sub' => [
                        '$map' => [
                            'input' => '$sub',
                            'as' => 'subItem',
                            'in' => [
                                'comment' => '$$subItem.comment',
                                'parentId' => '$$subItem.parentId',
                                'status' => '$$subItem.status',
                                'username' => '$$subItem.username',
                                'isAdmin' => '$$subItem.isAdmin',
                                '_id' => '$$subItem._id',
                                'date' => [
                                    '$dateToString' => ['format' => "%Y-%m-%d %H:%M:%S", 'date' => '$$subItem.date', 'timezone' => @CONFIG['global']['timezone']]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ])->toArray();

        $res = [
            'code' => 0,
            'msg' => '成功',
            'site' => $site,
            'type' => 'main',
            'article' => [
                'path' => $path,
                'commentRootCount' => @$articleData['commentRootCount']?:0,
                'commentCount' => @$articleData['commentCount']?:0,
                'commentSwitch' => @$articleData['commentSwitch'],
            ],
            'config' => [
                'subCommentMainCount' => $subCommentMainCount,
                'perPageCount' => $perPageCount,
                'requiredEmail' => $requiredEmail,
                'requiredUsername' => $requiredUsername
            ],
            'comment' => $data,
            'page' => $page
        ];
        $this->response($res, 'json');
        return;
    }

    /** 获取一级评论下的所有回复或者二级评论下的所有父级（对话）
     * @throws \Exception
     */
    public function listoneAction(){
        $params = $_GET;
        $id = $params['id'];
        $site = $params['site'];

        if(!$id){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ], 'json');
            return;
        }

        $this->site = $site;
        if(!$this->setHeader()) return;

        try {
            $idObj = new ObjectId($id);
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ], 'json');
            return;
        }

        $commentData = $this->db->comment->findOne([
            '_id' => $idObj, 'status' => 'public'
        ], [
            'projection' => [
                '_id' => 1, 'username' => 1, 'comment' => 1, 'status' => 1, 'parentId' => 1, 'parentRoot' => 1,
                'isAdmin' => 1, 'date' => 1
            ]
        ]);
        if(!$commentData) {
            $this->response([
                'code' => -1,
                'msg' => '评论不存在'
            ], 'json');
            return;
        }
        $commentData['date'] = date('Y-m-d H:i:s', strval($commentData['date']) / 1000);

        $type = "";
        if($commentData['parentRoot']){
            //二级评论
            //往前搜索
            $subCommentsData = [$commentData];
            $subCommentData = $commentData;
            while(true){
                if(!$subCommentData) {
                    $this->response([
                        'code' => -1,
                        'msg' => '内部错误'
                    ], 'json');
                    return;
                }
                $parentIdObj = $subCommentData['parentId'];
                if(!$parentIdObj){
                    break;
                }
                $subCommentData = $this->db->comment->findOne(['_id' => $parentIdObj], [
                    'projection' => [
                        '_id' => 1, 'username' => 1, 'comment' => 1, 'status' => 1, 'parentId' => 1, 'parentRoot' => 1,
                        'isAdmin' => 1, 'date' => 1
                    ]
                ]);
                if($subCommentData['status'] !== 'public'){
                    //status不是public（通过）时，替换内容。
                    $subCommentData['comment'] = '评论不见啦~';
                    $subCommentData['username'] = '灭';
                }
                $subCommentData['date'] = date('Y-m-d H:i:s', strval($subCommentData['date']) / 1000);
                $subCommentsData[] = $subCommentData;
            }
            $subCommentsData = array_reverse($subCommentsData); //翻转数组调整为正序。
//            $subCommentsData = array_merge([$commentData], $subCommentsData);   //第一个插入指定的评论。
            $type = 'dialog';
        }else{
            //一级评论
            $subCommentsData = $this->db->comment->aggregate([
                [
                    '$match' => [
                        'parentRoot' => $idObj, 'status' => 'public'
                    ]
                ],
                [
                    '$project' => [
                        '_id' => 1, 'username' => 1, 'comment' => 1, 'status' => 1, 'parentId' => 1, 'parentRoot' => 1,
                        'isAdmin' => 1,
                        'date' => [ '$dateToString' => ['format' => "%Y-%m-%d %H:%M:%S", 'date' => '$date', 'timezone' => @CONFIG['global']['timezone']]],
                    ]
                ]
            ])->toArray();
            $subCommentsData = array_merge([$commentData], $subCommentsData);   //第一个插入指定的评论。
            $type = 'top_sub';
        }

        $this->response([
            'code' => 0,
            'msg' => '成功',
            'type' => $type,
            'comment' => $subCommentsData
        ], 'json');
        return;
    }

    /**
     * POST http://comment.local/api.php?controller=comment&action=submit&site=blog&path=/1&page=0&sign=1&comment[comment]=test&comment[email]=2007@123.com&comment[username]=test
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function submitAction(){
        $userIp = IpHelper::getUserIp();
        $submitTimes = $this->cache->get('submit_rate_' . $userIp)?:0; //发送次数
        $rateInterval = 10;     //rateInterval秒内只允许发送rateTimeLimit次。
        $rateTimeLimit = 1;
        if(isset($submitTimes) && $submitTimes >= $rateTimeLimit){
            $this->response([
                'code' => -1,
                'msg' => '提交评论速度过快，请歇会'
            ], 'json');
            return;
        }


        $params = json_decode(file_get_contents('php://input'), true);
        if(!$params) {
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ], 'json');
            return;
        }

        $site = @$params['site'];
        $path = @$params['path'];
        $captcha = @$params['captcha'];
        $comment = @$params['comment']['comment'];
        $username = @$params['comment']['username'];
        $email = @$params['comment']['email'];
        $parentId = @$params['comment']['parentId'];
        $isAdmin = $this->userLogined();

        if(!$site || !$path){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ], 'json');
            return;
        }

        $this->site = $site;
        if(!$this->setHeader()) return;

        if(!$isAdmin && (empty($captcha) || $_SESSION['captcha'] !== $captcha)){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '验证码错误'
            ], 'json');
            return;
        }
        if(!$comment || mb_strlen(trim($comment)) < 1){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '评论内容不能为空'
            ], 'json');
            return;
        }

        //check comment content
        $comment = StringHelper::htmlFilter($comment);

        //site find
        $siteData = $this->db->site->findOne(['site' => $site], ['projection' => [
            '_id' => 1,
            'autoCreateArticle' => 1,
            'audit' => 1,
            'requiredEmail' => 1,
            'requiredUsername' => 1,
            'commentMaxLen' => 1,
            'adminUsername' => 1
        ]]);
        if(!$siteData){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '非法站点'
            ], 'json');
            return;
        }
        $siteId = $siteData['_id'];
        $auditOn = @$siteData['audit'];
        $commentMaxLen = @$siteData['commentMaxLen']?:250;
        $adminUsername = @$siteData['adminUsername']?:'Admin';
        if($isAdmin) $auditOn = false;  //管理员评论不需要审核。

        //验证内容长度、邮箱用户名，需要从site集合中取出配置来判断是否必填。
        if(mb_strlen($comment) > $commentMaxLen){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '评论内容过长(最多' . $commentMaxLen . '字符)'
            ], 'json');
            return;
        }
        if(@$siteData['requiredEmail'] && !$email && !$isAdmin){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '邮箱不能为空'
            ], 'json');
            return;
        }
        if($email && !filter_var($email, FILTER_VALIDATE_EMAIL)){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '邮箱填写有误'
            ], 'json');
            return;
        }
        $usernameEmpty = (!$username || mb_strlen(trim($username)) < 1);
        if(@$siteData['requiredUsername'] && !$isAdmin && $usernameEmpty){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '用户名不能为空'
            ], 'json');
            return;
        }
        if($usernameEmpty) $username = "匿名用户";
        if($isAdmin) $username = $adminUsername;    //管理员用户使用后台设置的管理员名昵称。

        //article find
        $articleData = $this->db->article->findOne(['siteId' => $siteId, 'path' => $path], ['projection' => [
            '_id' => 1,
            'commentSwitch' => 1
        ]]);
        $articleId = @$articleData['_id'];
        $commentOpened = true;
        if(!$articleData){
            if(@$siteData['autoCreateArticle']){
                //insert article (站点中要开启新页面自动创建autoCreateArticle）
                $insertResult = $this->db->article->insertOne(['siteId' => $siteId, 'path' => $path, 'commentCount' => 0, 'commentSwitch' => true]);
                $articleId = $insertResult->getInsertedId();
            }else{
                $commentOpened = false;
            }
        }else{
            $commentOpened = @$articleData['commentSwitch'];
        }
        if(!$commentOpened){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '评论已关闭，无法提交评论'
            ], 'json');
            return;
        }

        //查找上级评论 判断parentId的合法性以及获取父级根节点（顶级评论）
        $parentIdObj = $parentRootObj = null; //初始化parentId, parentRoot的值。
        $replyStr = '';
        if($parentId){
            try{
                $parentIdObj = new ObjectId($parentId);
            }catch (\Exception $exception){
                $this->response([
                    'code' => -1,
                    'msg' => '参数有误'
                ], 'json');
                return;
            }

            $parentCommentData = $this->db->comment->findOne([
                '_id' => $parentIdObj
            ], [
                'projection' => ['parentRoot' => 1, 'username' => 1]
            ]);
            if(!$parentCommentData){
                $this->response([
                    'code' => -1,
                    'msg' => '回复的评论未找到，请刷新重试'
                ], 'json');
                return;
            }
            $parentRootObj = @$parentCommentData['parentRoot']?:$parentIdObj;
            //当parentRoot为空时父级为一级评论，否则为二级以上评论。
            //二级以上评论带上回复xxx的字样。
            if(!empty(strval($parentCommentData['parentRoot']))){
                $replyStr = '回复 ' . $parentCommentData['username'] . '：';
            }
        }

        $submitTimes = $this->cache->get('submit_rate_' . $userIp); //发送次数，重新获取，减少脏数据写入概率。
        $this->cache->set('submit_rate_' . $userIp, ++$submitTimes, $rateInterval);

        //insert comment
        $insertResult = $this->db->comment->insertOne([
            'articleId' => $articleId,
            'username' => $username,
            'email' => $email,
            'comment' => $replyStr . $comment,
            'date' => new MongoDB\BSON\UTCDateTime(microtime(true) * 1000),
            'ip' => $userIp,
            'status' => $auditOn?'audit':'public',
            'parentId' => $parentIdObj,
            'parentRoot' => $parentRootObj,
            'isAdmin' => $isAdmin?true:false
        ]);
        if($insertResult->getInsertedCount() !== 1){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '保存失败'
            ], 'json');
            return;
        }

        if(!$auditOn) {
            //comment count add
            $commentRootCount = 1;
            if($parentRootObj) $commentRootCount = 0;
            DataHelper::updateCommentCount($this->db, $articleId, $commentRootCount, 1);
        }


        $_SESSION['captcha'] = null;
        $res = [
            'code' => 0,
            'msg' => '发布成功',
            'isPublic' => true
        ];
        if($auditOn){
            $_SESSION['captcha'] = null;
            $res = [
                'code' => 0,
                'msg' => '提交成功，将于审核后发布',
                'isPublic' => false
            ];
        }
        $this->response($res, 'json');
        return;
    }

    public function setHeader(){
        $origin = $_SERVER['HTTP_REFERER'];
        if(!preg_match('/^((https|http):\/\/' . $this->site . ')\//', $origin, $matches)){
            $this->response([
                'code' => -1,
                'msg' => '非法调用'
            ]);
            preg_match('/^(https|http):\/\//', $origin, $matches);
            header("Access-Control-Allow-Origin: " . $matches[1] . '://' . $this->site);
            return false;
        }
        header("Access-Control-Allow-Origin: " . $matches[1]);
        return true;
    }
}