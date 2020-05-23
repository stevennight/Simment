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

    public function beforeAction()
    {
        if(!$this->setHeader()){
            $this->response([
                'code' => -1,
                'msg' => '非法调用'
            ]);
            return false;
        }

        return parent::beforeAction();
    }

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

        //site find
        $siteData = $this->db->site->findOne(['site' => $site], [
            'projection' => [
                '_id' => 1,
                'autoCreateArticle' => 1,
                'perPageCount' => 1,
                'subCommentMainCount' => 1,
                'requiredUsername' => 1,
                'requiredEmail' => 1,
                'replyNotify' => 1,
                'adminUsername' => 1,
                'adminEmail' => 1
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
        $adminUsername = @$siteData['adminUsername'];
        $adminEmail = @$siteData['adminEmail'];
        $replyNotify = @$siteData['replyNotify'];
        $isAdmin = $this->userLogined()?true:false;

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
                    'requiredUsername' => $requiredUsername,
                    'replyNotify' => $replyNotify,
                    'adminUsername' => $isAdmin?$adminUsername:'',
                    'adminEmail' => $isAdmin?$adminEmail:'',
                ],
                'isAdmin' => $isAdmin,
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
                'requiredUsername' => $requiredUsername,
                'replyNotify' => $replyNotify,
                'adminUsername' => $isAdmin?$adminUsername:'',
                'adminEmail' => $isAdmin?$adminEmail:'',
            ],
            'isAdmin' => $isAdmin,
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
        $id = @$params['id'];
        $site = @$params['site'];
        $getMore = @$params['more'];

        if(!$id){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ], 'json');
            return;
        }

        $siteData = $this->db->site->findOne([
            'site' => $site,
        ], [
            'projection' => [
                '_id' => 1, 'perPageCount' => 1
            ]
        ]);
        if(!$siteData){
            $this->response([
                'code' => -1,
                'msg' => '站点未找到'
            ], 'json');
            return;
        }
        $perPageCount = intval(@$siteData['perPageCount'])?:10;

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
                'isAdmin' => 1, 'date' => 1, 'articleId' => 1
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

        $articleData = $this->db->article->findOne([
            '_id' => $commentData['articleId'], 'siteId' => $siteData['_id']
        ], [
            'projection' => [
                '_id' => 1
            ]
        ]);
        if(!$articleData){
            $this->response([
                'code' => -1,
                'msg' => '数据不匹配'
            ], 'json');
            return;
        }

        $type = "";
        //获取更多的模式
        if($getMore){
            //生成object id 。
            try{
                $getMoreObj = new ObjectId($getMore);
            }catch (\Exception $exception){
                $this->response([
                    'code' => -1,
                    'msg' => '参数有误'
                ], 'json');
                return;
            }
        }
        if($commentData['parentRoot']){
            //二级评论
            //往前搜索
            $more = false;
            $subCommentsData = [];
            if($getMore){
                $parentIdObj = $getMoreObj;
            }else{
                $subCommentsData[] = $commentData;
                $subCommentData = $commentData;
                $parentIdObj = $subCommentData['parentId'];
            }
            for($i = 0; $parentIdObj && $i < $perPageCount; $i++){  //限制显示数量
                $subCommentData = $this->db->comment->findOne([
                    '_id' => $parentIdObj
                ], [
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

                $parentIdObj = $subCommentData['parentId'];
                if(!$parentIdObj){
                    $more = false;
                    break;
                }else{
                    $more = $parentIdObj;
                }
            }
//            $subCommentsData = array_reverse($subCommentsData); //翻转数组调整为正序。
//            $subCommentsData = array_merge([$commentData], $subCommentsData);   //第一个插入指定的评论。
            $type = 'dialog';
        }else{
            //一级评论
            $match = [
                'parentRoot' => $idObj, 'status' => 'public'
            ];
            if($getMore) {
                $match['_id'] = [
                    '$gte' => $getMoreObj
                ];
            }
            $subCommentsData = $this->db->comment->aggregate([
                [
                    '$match' => $match
                ],
                [
                    '$limit' => $perPageCount + 1
                ],
                [
                    '$project' => [
                        '_id' => 1, 'username' => 1, 'comment' => 1, 'status' => 1, 'parentId' => 1, 'parentRoot' => 1,
                        'isAdmin' => 1,
                        'date' => [ '$dateToString' => ['format' => "%Y-%m-%d %H:%M:%S", 'date' => '$date', 'timezone' => @CONFIG['global']['timezone']]],
                    ]
                ]
            ])->toArray();
            $more = false;
            if(count($subCommentsData) > $perPageCount){
                $lastComment = array_pop($subCommentsData);
                $more = $lastComment['_id'];
            }
            if(!$getMore){
                //获取更多时不需要插入当前评论，否则需要插入当前评论到评论列表中。
                $subCommentsData = array_merge([$commentData], $subCommentsData);   //第一个插入指定的评论。
            }
            $type = 'top_sub';
        }

        $this->response([
            'code' => 0,
            'msg' => '成功',
            'type' => $type,
            'more' => $more,
            'comment' => $subCommentsData,
            'current' => $commentData,
            'isAdmin' => $this->userLogined()?true:false
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
        $cacheKey = 'submit_rate_' . StringHelper::cacheKeyFilter($userIp); //IPV6下的:作为key是非法的。
        $submitTimes = $this->cache->get($cacheKey)?:0; //发送次数
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
        $replyNotify = @$params['replyNotify'];
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

        if(!$isAdmin && (empty($captcha) || @$_SESSION['captcha'] !== $captcha)){
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
            'site' => 1,
            'siteName' => 1,
            'autoCreateArticle' => 1,
            'audit' => 1,
            'requiredEmail' => 1,
            'requiredUsername' => 1,
            'commentMaxLen' => 1,
            'adminUsername' => 1,
            'replyNotify' => 1
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
        $siteDomain = $siteData['site'];
        $siteName = $siteData['siteName'];
        $auditOn = @$siteData['audit'];
        $commentMaxLen = @$siteData['commentMaxLen']?:250;
        $adminUsername = @$siteData['adminUsername']?:'Admin';
        $siteReplyNotify = @$siteData['replyNotify']?:false;
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
            'path' => 1,
            'commentSwitch' => 1
        ]]);
        $articleId = @$articleData['_id'];
        $articlePath = @$articleData['path'];
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
                '_id' => $parentIdObj,
                'articleId' => $articleId
            ], [
                'projection' => ['parentRoot' => 1, 'username' => 1, 'email' => 1, 'comment' => 1, 'replyNotify' => 1]
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

        $submitTimes = $this->cache->get($cacheKey); //发送次数，重新获取，减少脏数据写入概率。
        $this->cache->set($cacheKey, ++$submitTimes, $rateInterval);

        //insert comment
        $comment = $replyStr . $comment;
        $insertResult = $this->db->comment->insertOne([
            'articleId' => $articleId,
            'username' => $username,
            'email' => $email,
            'comment' => $comment,
            'date' => new MongoDB\BSON\UTCDateTime(microtime(true) * 1000),
            'ip' => $userIp,
            'status' => $auditOn?'audit':'public',
            'parentId' => $parentIdObj,
            'parentRoot' => $parentRootObj,
            'replyNotify' => $replyNotify,
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
        $commentId = $insertResult->getInsertedId();

        //增加计数
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
            'isPublic' => true,
            'comment' => $comment   //返回提交的评论（被后端处理过）
        ];
        if($auditOn){
            $_SESSION['captcha'] = null;
            $res['msg'] = '提交成功，将于审核后发布';
            $res['isPublic'] = false;
        }

        //评论可以直接发布时，邮件通知发送 (需要站点允许replyNotify；被回复者设置了邮箱、允许replyNotify)
        if(!$auditOn && $siteReplyNotify && !empty(@$parentCommentData['email']) && @$parentCommentData['replyNotify']){
            $unsubscribeData = $this->db->mailUnsubscribe->findOne([
                'siteId' => $siteId,
                'email' => $parentCommentData['email']
            ]);
            if(!$unsubscribeData){
                //不在退订列表中，发送邮件。
                $this->mailer->send(
                    $parentCommentData['email'],
                    StringHelper::emailNotifySubject($siteName),
                    StringHelper::emailNotifyBody(
                        $siteName,
                        strval($siteId),
                        'http://' . $siteDomain . $articlePath . '?comment-id=' . $commentId,
                        $parentCommentData['email'],
                        $parentCommentData['comment'],
                        $username,
                        $comment
                    ),
                    'html'
                );
            }
        }

        $this->response($res, 'json');
        return;
    }

    public function mailunsubscribeAction(){
        $params = $_GET;
        $email = @$params['email'];
        $siteId = @$params['siteId'];
        $nonce = @$params['nonce'];
        $key = @$params['key'];

        if(!$siteId) {
            echo '缺少参数';
            return;
        }
        try{
            $siteIdObj = new ObjectId($siteId);
        }catch (\Exception $exception){
            echo '参数有误';
            return;
        }
        if(!$email){
            echo '邮箱为空';
            return;
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo '邮箱有误';
            return;
        }
        if($key !== md5($siteId . $nonce . $email)){
            echo '非法调用';
            return;
        }

        $this->db->mailUnsubscribe->updateOne([
            'siteId' => $siteIdObj,
            'email' => $email
        ], [
            '$set' => [
                'siteId' => $siteIdObj,
                'email' => $email
            ]
        ], [
            'upsert' => true
        ]);
        echo '退订成功';
        return;
    }
}