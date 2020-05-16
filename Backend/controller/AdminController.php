<?php


namespace controller;
use helper\DataHelper;
use helper\IpHelper;
use helper\ParamsHelper;
use helper\StringHelper;
use MongoDB\BSON\Regex;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class AdminController extends Controller
{
    public $needLogin = [
        'site', 'siteadd', 'sitedel', 'path', 'pathdel', 'pathupdate', 'comment', 'commentupdate', 'commentdel',
        'commentreply'
    ];

    /**
     * http://comment.local/api.php?controller=admin&action=site&search=www
     * @throws \Exception
     */
    public function siteAction(){
        $params = $_GET;
        $search = @$params['search'];

        $siteData = $this->db->site->find([
            'site' => new Regex('^.*?(' . preg_quote($search) . ').*?$', 'i')
        ])->toArray();
        $this->response([
            'code' => 0,
            'msg' => '成功',
            'data' => $siteData
        ]);
        return;
    }

    /**
     * http://comment.local/api.php?controller=admin&action=siteadd
     * @throws \Exception
     */
    public function siteaddAction(){
        $params = json_decode(file_get_contents('php://input'), true);
        if(!$params){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        $site = @$params['site'];
        $id = @$params['id'];
        $siteName = @$params['siteName'];
        $autoCreateArticle = @$params['autoCreateArticle']; //article(path)不存在时是否自动创建，关闭时新页面会禁止评论。
        $subCommentMainCount = @$params['subCommentMainCount'];    //在评论一级列表（非回复评论）页面中，回复评论显示条数 必填
        $adminUsername = @$params['adminUsername'];    //站点管理员用户名，管理员发表评论时会标记为管理员并且使用指定用户名 必填
        $adminEmail = @$params['adminEmail'];    //站点管理员邮箱
        $perPageCount = @$params['perPageCount'];
        $requiredUsername = @$params['requiredUsername'];
        $requiredEmail = @$params['requiredEmail'];
        $audit = @$params['audit'];
        $replyNotify = @$params['replyNotify']; //回复通知
        $commentMaxLen = @$params['commentMaxLen'];

        if(!$site || !$subCommentMainCount || !$perPageCount || !$commentMaxLen || !$adminUsername){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        $type = 'insert';
        if($id){
            try{
                $ObjId = new ObjectId($id);
            }catch (\Exception $exception){
                $this->response([
                    'code' => -1,
                    'msg' => '参数有误'
                ]);
                return;
            }
            $type = 'update';
        }

        $siteData = $this->db->site->findOne([
            'site' => $site,
            '_id' => ['$ne' => @$ObjId]
        ]);
        if($siteData){
            $this->response([
                'code' => -1,
                'msg' => '站点已存在'
            ]);
            return;
        }

        if(!$siteName) $siteName = $site;   //如果没有填写，则录入为site(网址）
        if($type === 'insert'){
            $insertResult = $this->db->site->insertOne([
                'site' => $site,
                'siteName' => $siteName,
                'autoCreateArticle' => $autoCreateArticle,
                'perPageCount' => $perPageCount,
                'requiredUsername' => $requiredUsername,
                'requiredEmail' => $requiredEmail,
                'audit' => $audit,
                'replyNotify' => $replyNotify,
                'commentMaxLen' => $commentMaxLen,
                'subCommentMainCount' => $subCommentMainCount,
                'adminUsername' => $adminUsername,
                'adminEmail' => $adminEmail
            ]);
            $updateCount = $insertResult->getInsertedCount();
        }else{
            $updateResult = $this->db->site->updateOne([
                '_id' => $ObjId
            ], [
                '$set' => [
                    'site' => $site,
                    'siteName' => $siteName,
                    'autoCreateArticle' => $autoCreateArticle,
                    'perPageCount' => $perPageCount,
                    'requiredUsername' => $requiredUsername,
                    'requiredEmail' => $requiredEmail,
                    'audit' => $audit,
                    'replyNotify' => $replyNotify,
                    'commentMaxLen' => $commentMaxLen,
                    'subCommentMainCount' => $subCommentMainCount,
                    'adminUsername' => $adminUsername,
                    'adminEmail' => $adminEmail
                ]
            ]);
            if($updateResult->getMatchedCount() < 1){
                $this->response([
                    'code' => -1,
                    'msg' => '未找到指定数据'
                ]);
                return;
            }
            $updateCount = $updateResult->getModifiedCount();
        }

        if($updateCount !== 1){
            $this->response([
                'code' => -1,
                'msg' => '保存失败'
            ]);
            return;
        }
        $this->response([
            'code' => 0,
            'msg' => '保存成功'
        ]);
        return;
    }

    public function sitedelAction(){
        $params = json_decode(file_get_contents('php://input'), true);
        if(!$params){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        $id = @$params['id'];

        if(!$id){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        try{
            $objId = new ObjectId($id);
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        $hasPath = $this->db->article->findOne([
            'siteId' => $objId
        ]);
        if($hasPath){
            $this->response([
                'code' => -1,
                'msg' => '该网站下存在文章（路径），终止删除'
            ]);
            return;
        }

        $deleteResult = $this->db->site->deleteOne([
            '_id' => new ObjectId($id)
        ]);
        if($deleteResult->getDeletedCount() < 1){
            $this->response([
                'code' => -1,
                'msg' => '删除失败'
            ]);
            return;
        }

        $this->response([
            'code' => 0,
            'msg' => '成功'
        ]);
        return;
    }

    public function pathAction(){
        $params = $_GET;
        $siteId = @$params['siteId'];
        $search = @$params['search'];
        $page = @$params['page'];
        $perPageCount = 20;

        if(!$siteId){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }
        try{
            $objId = new ObjectId($siteId);
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }

        if($page === null) $page = 0;
        $skip = $page * $perPageCount;

        $siteData = $this->db->article->find([
            'siteId' => $objId,
            'path' => new Regex('^.*?(' . preg_quote($search) . ').*?$', 'i')
        ], [
            'skip' => $skip,
            'limit' => $perPageCount,
            'sort' => ['_id' => -1]
        ])->toArray();
        $this->response([
            'code' => 0,
            'msg' => '成功',
            'data' => $siteData
        ]);
        return;
    }

    public function pathdelAction(){
        $params = json_decode(file_get_contents('php://input'), true);
        if(!$params){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        $id = @$params['id'];
        $autoDelComment = @$params['autoDelComment'];

        if(!$id){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }
        try{
            $objId = new ObjectId($id);
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        if($autoDelComment){
            //自动删除评论
            $this->db->comment->deleteMany([
                'articleId' => $objId
            ]);
        } else {
            $hasComment = $this->db->comment->findOne([
                'articleId' => $objId
            ]);
            if($hasComment){
                $this->response([
                    'code' => -1,
                    'msg' => '该路径下存在评论，终止删除'
                ]);
                return;
            }
        }

        $deleteResult = $this->db->article->deleteOne([
            '_id' => new ObjectId($id)
        ]);
        if($deleteResult->getDeletedCount() < 1){
            $this->response([
                'code' => -1,
                'msg' => '删除失败'
            ]);
            return;
        }

        $this->response([
            'code' => 0,
            'msg' => '成功'
        ]);
        return;
    }

    public function pathupdateAction(){
        $params = json_decode(file_get_contents("php://input"), true);
        if(!$params){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        $id = @$params['id'];
        $siteId = @$params['siteId'];
        $path = @$params['path'];
        $commentSwitch = @$params['commentSwitch'];

        if(!$path || !$siteId){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }
        $type = 'insert';
        if($id){
            $type = 'update';
            try {
                $objId = new ObjectId($id);
            }catch (\Exception $exception){
                $this->response([
                    'code' => -1,
                    'msg' => '参数有误'
                ]);
                return;
            }
        }
        try {
            $objSiteId = new ObjectId($siteId);
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        if($type === "insert"){
            $insertResult = $this->db->article->insertOne([
                'path' => $path,
                'commentCount' => 0,
                'siteId' => $objSiteId,
                'commentSwitch' => $commentSwitch
            ]);
            $success = $insertResult->getInsertedCount();
        } else {
            $updateResult = $this->db->article->updateOne([
                '_id' => $objId,
            ], [
                '$set' => [
                    'path' => $path,
                    'siteId' => $objSiteId,
                    'commentSwitch' => $commentSwitch
                ]
            ]);
            $success = $updateResult->getModifiedCount();
        }
        if(!$success){
            $this->response([
                'code' => -1,
                'msg' => '保存失败'
            ]);
            return;
        }

        $this->response([
            'code' => 0,
            'msg' => '成功'
        ]);
        return;
    }

    public function commentAction(){
        $params = $_GET;
        $siteId = @$params['siteId'];
        $path = @$params['path'];
        $search = @$params['search'];
        $status = @$params['status'];
        $id = @$params['id'];
        $parentId = @$params['parentId'];
        $parentRoot = @$params['parentRoot'];
        $page = $params['page'];

        $findWhere = [
            'comment' => new Regex('^.*?(' . preg_quote($search) . ').*?$', 'i'),
            'status' => ['$ne' => 'delete'],    //删除标识的评论不显示
        ];

        if($status){
            $findWhere['status'] = $status;
        }
        try {
            if($id){
                $findWhere['_id'] = new ObjectId($id);
            }
            if($parentId){
                $findWhere['parentId'] = new ObjectId($parentId);
            }
            if($parentRoot){
                $findWhere['parentRoot'] = new ObjectId($parentRoot);
            }
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => 'ID参数有误',
            ]);
            return;
        }

        $articleWhere = [];
        if($siteId){
            try {
                $siteIdObj = new ObjectId($siteId);
            }catch (\Exception $exception){
                $this->response([
                    'code' => -1,
                    'msg' => '参数有误',
                ]);
                return;
            }
            $siteData = $this->db->site->find([
                '_id' => $siteIdObj,
            ], [
                'projection' => ['_id' => 1],
            ])->toArray();
            if(!$siteData){
                $this->response([
                    'code' => -1,
                    'msg' => '参数有误',
                ]);
                return;

            }
            $articleWhere['siteId'] = $siteIdObj;
        }
        if($path){
            $articleWhere['path'] = new Regex('^.*?(' . preg_quote($path) . ').*?$', 'i');
        }
        if($articleWhere){
            $articleData = $this->db->article->find($articleWhere)->toArray();
            $articleIds = [];
            if($articleData){
                foreach($articleData as $articleObj){
                    $articleIds[] = $articleObj['_id'];
                }
            }
            $findWhere['articleId'] = [
                '$in' => $articleIds
            ];
        }

        $perPageCount = 10;
        $skip = $page * $perPageCount;
        $commentData = $this->db->comment->aggregate([
            [
                '$match' => $findWhere
            ],
            [
                '$sort' => ['_id' => -1],
            ],
            [
                '$skip' => $skip,
            ],
            [
                '$limit' => $perPageCount,
            ],
            [
                '$lookup' => [
                    'localField' => 'articleId',
                    'foreignField' => '_id',
                    'from' => 'article',
                    'as' => 'article',
                ]
            ],
            [
                '$lookup' => [
                    'localField' => 'article.0.siteId',
                    'foreignField' => '_id',
                    'from' => 'site',
                    'as' => 'site',
                ]
            ],
            [
                '$project' => [
                    'comment' => 1, 'email' => 1, 'ip' => 1, 'parentRoot' => 1, 'parentId' => 1, 'status' => 1, 'username' => 1,
                    'isAdmin' => 1, 'articleId' => 1, 'article' => 1, 'site' => 1, 'replyNotify' => 1,
                    'date' => [
                        '$dateToString' => ['format' => "%Y-%m-%d %H:%M:%S", 'date' => '$date', 'timezone' => '+08:00']
                    ],
                ]
            ]
        ])->toArray();
        $res = [
            'code' => 0,
            'msg' => '成功',
            'comment' => $commentData,
            'page' => $page
        ];
        $this->response($res, 'json');
        return;
    }

    public function commentupdateAction(){
        $params = json_decode(file_get_contents('php://input'), true);
        if(!$params){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }

        $id = @$params['id'];
        $status = @$params['status'];
        $comment = @$params['comment'];

        try {
            $idObj = new ObjectId($id);
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }

        if(!in_array($status, ParamsHelper::getCommentStatusValues())){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }

        //获取评论信息
        $commentData = $this->db->comment->findOne(['_id' => $idObj]);
        if(!$commentData){
            $this->response([
                'code' => -1,
                'msg' => '评论不存在'
            ]);
            return;
        }
        //获取上一级的评论
        if($commentData['parentId']){
            $parentCommentData = $this->db->comment->findOne(['_id' => $commentData['parentId']], ['projection' => [
                'email' => 1, 'username' => 1, 'comment' => 1, 'replyNotify' => 1
            ]]);
        }

        //获取文章（路径）
        $articleData = $this->db->article->findOne(['_id' => $commentData['articleId']]);
        if(!$articleData){
            $this->response([
                'code' => -1,
                'msg' => '内部错误'
            ]);
            return;
        }
        $articlePath = $articleData['path'];
        $siteIdObj = $articleData['siteId'];
        //获取站点
        $siteData = $this->db->site->findOne(['_id' => $siteIdObj]);
        if(!$siteData){
            $this->response([
                'code' => -1,
                'msg' => '内部错误'
            ]);
            return;
        }
        $siteName = $siteData['siteName'];
        $siteDomain = $siteData['site'];
        $replyNotify = $siteData['replyNotify'];

        $updateField = ['status' => $status];
        if($comment){
            $updateField['comment'] = StringHelper::htmlFilter($comment);
        }

        $updateResult = $this->db->comment->updateOne(
            ['_id' => $idObj ],
            ['$set' => $updateField]
        );
        if($updateResult->getModifiedCount() == 0){
            $this->response([
                'code' => -1,
                'msg' => '更新失败'
            ]);
            return;
        }

        //更新评论计数
        $commentCount = $commentRootCount = 0;
        if($commentData['status'] !== $status && $status === 'public'){
            //修改为通过审核（公共展示）计数加一
            //audit -> public, hidden -> public
            $commentCount = 1;
            $commentRootCount = 1;
            if($commentData['parentRoot']) $commentRootCount = 0;
        }elseif($commentData['status'] === 'public' && $status === 'hidden'){
            //通过转为拒绝（隐藏）计数减一
            //public -> hidden
            $commentCount = -1;
            $commentRootCount = -1;
            if($commentData['parentRoot']) $commentRootCount = 0;
        }
        DataHelper::updateCommentCount($this->db, $commentData['articleId'], $commentRootCount, $commentCount);

        //邮件通知发送，通知回复评论的对象 （status audit->public时才通知）(需要站点允许replyNotify；被回复者设置了邮箱、允许replyNotify)
        if($commentData['status'] === 'audit' && $status === 'public' && $replyNotify && !empty(@$parentCommentData['email']) && @$parentCommentData['replyNotify']){
            $unsubscribeData = $this->db->mailUnsubscribe->findOne([
                'siteId' => $siteIdObj,
                'email' => $parentCommentData['email']
            ]);
            if(!$unsubscribeData){
                //不在退订列表中，发送邮件。
                $this->mailer->send(
                    $parentCommentData['email'],
                    StringHelper::emailNotifySubject($siteName),
                    StringHelper::emailNotifyBody(
                        $siteName,
                        strval($siteIdObj),
                        'http://' . $siteDomain . $articlePath . '?comment-id=' . $idObj,
                        $parentCommentData['email'],
                        $parentCommentData['comment'],
                        $commentData['username'],
                        $commentData['comment']
                    ),
                    'html'
                );
            }
        }

        $this->response([
            'code' => 0,
            'msg' => '更新成功'
        ]);
        return;
    }

    public function commentdelAction(){
        $params = json_decode(file_get_contents('php://input'), true);
        if(!$params){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }

        $id = @$params['id'];

        if(!$id){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }
        try {
            $idObj = new ObjectId($id);
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }

        //标记为删除，不会直接删除数据。避免评论楼嵌套找不到parent。
        $updateResult = $this->db->comment->findOneAndUpdate([
            '_id' => $idObj
        ], [
            '$set' => [
                'status' => 'delete',
            ],
        ]);
        if($updateResult['status'] === 'public'){
            //通过的评论删除时要扣减计数
            $rootCount = @$updateResult['parentRoot']?0:-1;
            DataHelper::updateCommentCount($this->db, $updateResult['articleId'], $rootCount, -1);
        }

        $this->response([
            'code' => 0,
            'msg' => '删除成功',
        ]);
        return;
    }

    public function commentreplyAction(){
        $params = json_decode(file_get_contents('php://input'), true);
        if(!$params){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }

        $parentId = @$params['parentId'];
        $comment = @$params['comment'];

        if(!$comment || !$parentId){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }
        try{
            $parentIdObj = new ObjectId($parentId);
        }catch (\Exception $exception){
            $this->response([
                'code' => -1,
                'msg' => '参数有误',
            ]);
            return;
        }

        $parentCommentData = $this->db->comment->findOne([
            '_id' => $parentIdObj
        ], [
            'projection' => [
                'parentRoot' => 1, 'articleId' => 1, 'email' => 1, 'replyNotify' => 1, 'username' => 1, 'comment' => 1
            ]
        ]);
        if(!$parentCommentData){
            $this->response([
                'code' => -1,
                'msg' => '评论未找到',
            ]);
            return;
        }
        $parentCommentParentRoot = $parentCommentData['parentRoot'];
        $parentCommentArticleIdObj = $parentCommentData['articleId'];
        $parentCommentUsername = $parentCommentData['username'];

        //article
        $articleData = $this->db->article->findOne([
            '_id' => $parentCommentArticleIdObj
        ], [
            'projection' => [
                'siteId' => 1, 'path' => 1
            ]
        ]);
        if(!$articleData){
            $this->response([
                'code' => -1,
                'msg' => '页面未找到',
            ]);
            return;
        }
        $siteIdObj = $articleData['siteId'];
        $articlePath = $articleData['path'];

        //site
        $siteData = $this->db->site->findOne([
            '_id' => $siteIdObj
        ], [
            'projection' => [
                'siteName' => 1, 'replyNotify' => 1, 'site' => 1, 'adminEmail' => 1, 'adminUsername' => 1
            ]
        ]);
        if(!$siteData){
            $this->response([
                'code' => -1,
                'msg' => '站点未找到',
            ]);
            return;
        }
        $siteDomain = $siteData['site'];
        $replyNotify = $siteData['replyNotify'];
        $siteName = $siteData['siteName'];
        $adminUsername = $siteData['adminUsername'];
        $adminEmail = $siteData['adminEmail'];

        //父级为二级评论时，comment增加回复xxx的标识。
        if($parentCommentParentRoot){
            $comment = '回复 ' . $parentCommentUsername . '：' . $comment;
        }

        //插入评论
        $insertResult = $this->db->comment->insertOne([
            'articleId' => $parentCommentArticleIdObj,
            'username' => $adminUsername,
            'email' => $adminEmail, //自动填入站点中设置的管理员邮箱。
            'comment' => $comment,
            'date' => new UTCDateTime(microtime(true) * 1000),
            'ip' => IpHelper::getUserIp(),
            'status' => 'public',
            'parentId' => $parentIdObj,
            'parentRoot' => $parentCommentParentRoot?:$parentIdObj,
            'isAdmin' => true,
            'replyNotify' => $replyNotify   //根据站点设置去判断是否对回复进行通知
        ]);
        if($insertResult->getInsertedCount() < 1){
            $this->response([
                'code' => -1,
                'msg' => '保存失败',
            ]);
            return;
        }
        $idObj = $insertResult->getInsertedId();

        DataHelper::updateCommentCount($this->db, $parentCommentArticleIdObj, 0, 1);

        //邮件通知发送，通知回复评论的对象 （status audit->public时才通知）(需要站点允许replyNotify；被回复者设置了邮箱、允许replyNotify)
        if($replyNotify && !empty(@$parentCommentData['email']) && @$parentCommentData['replyNotify']){
            $unsubscribeData = $this->db->mailUnsubscribe->findOne([
                'siteId' => $siteIdObj,
                'email' => $parentCommentData['email']
            ]);
            if(!$unsubscribeData){
                //不在退订列表中，发送邮件。
                $this->mailer->send(
                    $parentCommentData['email'],
                    StringHelper::emailNotifySubject($siteName),
                    StringHelper::emailNotifyBody(
                        $siteName,
                        strval($siteIdObj),
                        'http://' . $siteDomain . $articlePath . '?comment-id=' . $idObj,
                        $parentCommentData['email'],
                        $parentCommentData['comment'],
                        $adminUsername,
                        $comment
                    ),
                    'html'
                );
            }
        }

        $this->response([
            'code' => 0,
            'msg' => '回复成功',
        ]);
        return;
    }

    /**
     * http://comment.local/api.php?controller=admin&action=login
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function loginAction(){
        $userIp = IpHelper::getUserIp();
        $cacheKey = 'login_rate_' . StringHelper::cacheKeyFilter($userIp);
        $tryTimes = $this->cache->get($cacheKey)?:0; //尝试登录次数
        $rateInterval = 86400;     //rateInterval秒内只允许尝试rateTimeLimit次登录。
        $rateTimeLimit = 3;
        if(isset($tryTimes) && $tryTimes >= $rateTimeLimit){
            $this->response([
                'code' => -1,
                'msg' => '过多的登录失败，请等待解封后再试'
            ], 'json');
            return;
        }

        $params = json_decode(file_get_contents("php://input"), true);
        if(!$params){
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }
        $username = @$params['username'];
        $password = @$params['password'];
        $captcha = @$params['captcha'];

        if(!$captcha){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '参数有误'
            ]);
            return;
        }
        if($captcha !== $_SESSION['captcha']){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '验证码错误'
            ]);
            return;
        }

        if(!isset(CONFIG['admin']['username']) || !isset(CONFIG['admin']['password'])){
            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '未设置后台账号密码，无法进行登录。'
            ]);
            return;
        }
        if($username !== CONFIG['admin']['username'] || md5($password . $username) != CONFIG['admin']['password']){
            //尝试计数增加
            $tryTimes = $this->cache->get($cacheKey); //尝试次数，重新获取，减少脏数据写入概率。
            $this->cache->set($cacheKey, ++$tryTimes, $rateInterval);

            $_SESSION['captcha'] = null;
            $this->response([
                'code' => -1,
                'msg' => '账号或密码错误，还能尝试' . ($rateTimeLimit - $tryTimes) . '次'
            ]);
            return;
        }

        //登录成功删除计数
        $this->cache->delete($cacheKey);

        $_SESSION['user'] = $username;
        $this->response([
            'code' => 0,
            'msg' => '登录成功'
        ]);
        return;
    }

    /**
     * http://comment.local/api.php?controller=admin&action=logout
     * @throws \Exception
     */
    public function logoutAction(){
        unset($_SESSION['user']);
        $this->response([
            'code' => 0,
            'msg' => '登出成功'
        ]);
        return;
    }

    public function checkloginAction() {
        if(!$this->userLogined()){
            $this->response(-1, 'plain');
        }
        return;
    }
}