var commentEl = document.getElementById('nyatoriCommentWrapper0511');
var commentSystem = commentEl.getAttribute('data-system');
var commentSite = commentEl.getAttribute('data-site');
var commentWithStyle = commentEl.getAttribute('data-with-style');

var nyatoriCommentWrapper0511CSS = "<style>\n" +
    "    .nyatoriCommentWrapper0511, .nyatoriCommentWrapper0511Loading {\n" +
    "        text-align: center;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send {\n" +
    "        width: 100%;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send input {\n" +
    "        padding: 0.5rem;\n" +
    "        border-radius: 0.3rem;\n" +
    "        border: 0.1rem solid #ddd;\n" +
    "        box-sizing: border-box;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendCommentWrapper {\n" +
    "        margin-top: 0.3rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendCommentWrapper .sendComment {\n" +
    "        width: 100%;\n" +
    "        display: block;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendInfo {\n" +
    "        width: 100%;\n" +
    "        margin: 0.3rem 0;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendInfo .sendUsernameWrapper {\n" +
    "        float: left;\n" +
    "        padding-right: 0.15rem;\n" +
    "        width: 50%;\n" +
    "        box-sizing: border-box;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendInfo .sendUsernameWrapper .sendUsername {\n" +
    "        width: 100%;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendInfo .sendEmailWrapper {\n" +
    "        float: right;\n" +
    "        padding-left: 0.15rem;\n" +
    "        width: 50%;\n" +
    "        box-sizing: border-box;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendInfo .sendEmailWrapper .sendEmail {\n" +
    "        width: 100%;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendReplyNotifyWrapper {\n" +
    "        padding-top: .3rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendReplyNotifyWrapper label, .nyatoriCommentWrapper0511 .send .sendReplyNotifyWrapper input {\n" +
    "        cursor: pointer;\n" +
    "        user-select: none;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendBtnWrapper {\n" +
    "        margin: 0.3rem 0;\n" +
    "        width: 100%;\n" +
    "        box-sizing: border-box;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .sendBtnWrapper .sendBtn {\n" +
    "        width: 100%;\n" +
    "        box-sizing: border-box;\n" +
    "        border: 0.1rem solid #ddd;\n" +
    "        border-radius: 0.3rem;\n" +
    "        background: #666;\n" +
    "        color: #fff;\n" +
    "        line-height: 1.5rem;\n" +
    "        font-weight: bolder;\n" +
    "        cursor: pointer;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .captchaWrapper {\n" +
    "        width: 100%;\n" +
    "        display: flex;\n" +
    "        margin-top: 0.3rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .captchaWrapper .sendCaptcha {\n" +
    "        flex: 1;\n" +
    "        margin-right: 0.5rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .captchaWrapper .captchaImage {\n" +
    "        height: 3rem;\n" +
    "        width: 6rem;\n" +
    "        cursor: pointer;\n" +
    "        margin-right: 0.5rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .captchaWrapper .captchaImageLoading {\n" +
    "        line-height: 2rem;\n" +
    "        margin-right: 0.5rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .replyCommentWrapper {\n" +
    "        display: flex;\n" +
    "        padding: 0.5rem;\n" +
    "        background: #87ceeb;\n" +
    "        border-radius: 0.3rem;\n" +
    "        text-align: left;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .replyCommentWrapper .replyComment {\n" +
    "        flex: 1;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 .send .replyCommentWrapper .close {\n" +
    "        user-select: none;\n" +
    "        cursor: pointer;\n" +
    "        font-size: 1.2rem;\n" +
    "        font-weight: bolder;\n" +
    "    }\n" +
    "\n" +
    "    //from commentList.vue\n" +
    "    .nyatoriCommentWrapper0511 #commentList {\n" +
    "        margin-top: 0.5rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .total span {\n" +
    "        background: #8b0000;\n" +
    "        color: #fff;\n" +
    "        font-weight: bolder;\n" +
    "        padding: 0.12rem 0.36rem;\n" +
    "        border-radius: 0.3rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .listHistoryBack {\n" +
    "        user-select: none;\n" +
    "        cursor: pointer;\n" +
    "        background: #000;\n" +
    "        color: #fff;\n" +
    "        padding: 0.3rem;\n" +
    "        border-radius: 1.2rem;\n" +
    "        font-weight: bolder;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .pageList {\n" +
    "        padding: 0.3rem;\n" +
    "        user-select: none;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .pageList a {\n" +
    "        margin-right: 0.3rem;\n" +
    "        padding: 0.3rem;\n" +
    "        cursor: pointer;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .pageList a.active {\n" +
    "        color: #f00;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer {\n" +
    "        border-bottom: 1px solid #ddd;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper {\n" +
    "        display: flex;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .left {\n" +
    "        width: 3rem;\n" +
    "        height: 3rem;\n" +
    "        line-height: 3rem;\n" +
    "        margin: 0.3rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .avatar {\n" +
    "        color: #fff;\n" +
    "        background: #000;\n" +
    "        border-radius: 1.5rem;\n" +
    "        font-weight: bolder;\n" +
    "        font-size: 1.5rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .right {\n" +
    "        flex: 1;\n" +
    "        min-width: 0;\n" +
    "        margin: 0.3rem;\n" +
    "        text-align: left;\n" +
    "        word-wrap: break-word;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .right .username {\n" +
    "        font-weight: bold;\n" +
    "        float: left;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .right .commentAdmin {\n" +
    "        float: left;\n" +
    "        line-height: 1.4rem;\n" +
    "        margin-left: 0.5rem;\n" +
    "        background: #8b0000;\n" +
    "        color: #fff;\n" +
    "        padding: 0 0.3rem;\n" +
    "        border-radius: 0.3rem;\n" +
    "        font-size: 0.5rem;\n" +
    "        cursor: pointer;\n" +
    "        user-select: none;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .right .reply {\n" +
    "        float: left;\n" +
    "        line-height: 1.4rem;\n" +
    "        margin-left: 0.5rem;\n" +
    "        background: #666;\n" +
    "        color: #fff;\n" +
    "        padding: 0 0.3rem;\n" +
    "        border-radius: 0.3rem;\n" +
    "        font-size: 0.5rem;\n" +
    "        cursor: pointer;\n" +
    "        user-select: none;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .right .commentDialog {\n" +
    "        float: left;\n" +
    "        line-height: 1.4rem;\n" +
    "        margin-left: 0.5rem;\n" +
    "        background: #666;\n" +
    "        color: #fff;\n" +
    "        padding: 0 0.3rem;\n" +
    "        border-radius: 0.3rem;\n" +
    "        font-size: 0.5rem;\n" +
    "        cursor: pointer;\n" +
    "        user-select: none;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .right .commentId {\n" +
    "        float: left;\n" +
    "        color: #ccc;\n" +
    "        font-size: 0.8rem;\n" +
    "        padding: 0 0.3rem;\n" +
    "        line-height: 1.4rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .commentWrapper .right .date {\n" +
    "        float: right;\n" +
    "        color: #ccc;\n" +
    "        font-size: 0.8rem;\n" +
    "        margin-right: 0.5rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments {\n" +
    "        padding-left: 3rem;\n" +
    "        font-size: 0.9rem;\n" +
    "        text-align: left;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentMore {\n" +
    "        padding: 0.12rem;\n" +
    "        background: #eee;\n" +
    "        width: 4.8rem;\n" +
    "        text-align: center;\n" +
    "        margin: 0.3rem 0 0.3rem 1.1rem;\n" +
    "        user-select: none;\n" +
    "        cursor: pointer;\n" +
    "        border-radius: 0.3rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper {\n" +
    "        padding: 0;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper .subComment {\n" +
    "        padding: 0.3rem;\n" +
    "        border-top: 1px solid #ddd;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper .subComment * {\n" +
    "        padding-left: 0.8rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper .subComment .subCommentUsername {\n" +
    "        float: left;\n" +
    "        font-weight: bold;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper .subComment .subCommentAdmin {\n" +
    "        float: left;\n" +
    "        margin-left: 0.5rem;\n" +
    "        background: #8b0000;\n" +
    "        color: #fff;\n" +
    "        padding: 0 0.3rem;\n" +
    "        border-radius: 0.3rem;\n" +
    "        font-size: 0.5rem;\n" +
    "        cursor: pointer;\n" +
    "        user-select: none;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper .subComment .subCommenteply {\n" +
    "        float: left;\n" +
    "        margin-left: 0.5rem;\n" +
    "        background: #666;\n" +
    "        color: #fff;\n" +
    "        padding: 0 0.3rem;\n" +
    "        border-radius: 0.3rem;\n" +
    "        font-size: 0.5rem;\n" +
    "        cursor: pointer;\n" +
    "        user-select: none;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper .subComment .subCommentDialog {\n" +
    "        float: left;\n" +
    "        margin-left: 0.5rem;\n" +
    "        background: #666;\n" +
    "        color: #fff;\n" +
    "        padding: 0 0.3rem;\n" +
    "        border-radius: 0.3rem;\n" +
    "        font-size: 0.5rem;\n" +
    "        cursor: pointer;\n" +
    "        user-select: none;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper .subComment .subCommentId {\n" +
    "        float: left;\n" +
    "        color: #ccc;\n" +
    "        font-size: 0.8rem;\n" +
    "        padding: 0 0.3rem;\n" +
    "        line-height: 1.2rem;\n" +
    "    }\n" +
    "    .nyatoriCommentWrapper0511 #commentList .commentContainer .subComments .subCommentWraper .subComment .subCommentDate {\n" +
    "        float: right;\n" +
    "        color: #ccc;\n" +
    "        font-size: 0.8rem;\n" +
    "        margin-right: 0.5rem;\n" +
    "    }\n" +
    "</style>\n";
    var nyatoriCommentWrapper0511 = "<div id=\"commentApp\" style=\"display: none\">\n" +
    "    <div v-if=\"init\" class=\"nyatoriCommentWrapper0511\">\n" +
    "        <div v-if=\"!commentClosed\" class=\"commentWrapper\">\n" +
    "            <div class=\"send\">\n" +
    "                <div v-if=\"replyComment\" class=\"replyCommentWrapper\">\n" +
    "                    <div class=\"replyComment\">回复{{replyComment.username}}:<span v-html=\"replyComment.comment\"></span></div>\n" +
    "                    <div class=\"close\" @click=\"closeReply\">×</div>\n" +
    "                </div>\n" +
    "                <div class=\"sendCommentWrapper\">\n" +
    "                    <input class=\"sendComment\" v-model=\"comment\" placeholder=\"请输入评论内容\">\n" +
    "                </div>\n" +
    "                <div class=\"sendInfo\">\n" +
    "                    <div class=\"sendUsernameWrapper\">\n" +
    "                        <input class=\"sendUsername\" type=\"text\" v-model=\"username\" :placeholder=\"'您的昵称' + (this.configRequiredUsername?'(必填)':'')\">\n" +
    "                    </div>\n" +
    "                    <div class=\"sendEmailWrapper\">\n" +
    "                        <input class=\"sendEmail\" type=\"email\" v-model=\"email\" :placeholder=\"'您的邮箱' + (this.configRequiredEmail?'(必填)':'')\">\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "                <div style=\"clear: both\"></div>\n" +
    "                <div v-if=\"configReplyNotify\" class=\"sendReplyNotifyWrapper\">\n" +
    "                    <label>\n" +
    "                        <input type=\"checkbox\" v-model=\"replyNotify\">\n" +
    "                        回复通知(请填写真实邮箱,否则无法收到通知）\n" +
    "                    </label>\n" +
    "                </div>" +
    "                <div class=\"captchaWrapper\">\n" +
    "                    <input class=\"sendCaptcha\" type=\"text\" v-model=\"captcha\" placeholder=\"右侧验证码\">\n" +
    "                    <img v-show=\"captchaImageShow\" class=\"captchaImage\" :src=\"captchaImage\" @load=\"captchaLoaded\" @click=\"refreshCaptcha\">\n" +
    "                    <div class=\"captchaImageLoading\" v-show=\"!captchaImageShow\">Loading</div>\n" +
    "                </div>\n" +
    "                <div class=\"sendBtnWrapper\">\n" +
    "                    <button class=\"sendBtn\" @click=\"submit\">提交</button>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "            <div id=\"commentList\">\n" +
    "                <div class=\"total\" v-if=\"commentData.type === 'main'\"><span>{{commentCount}}</span>条评论</div>\n" +
    "                <div class=\"listHistoryBack\" v-if=\"commentData.type !== 'main'\" @click=\"commentHistoryBack\">返回列表</div>\n" +
    "                <div class=\"commentContainer\" v-for=\"comment in commentList\" :key=\"comment._id['$oid']\">\n" +
    "                    <div class=\"commentWrapper\">\n" +
    "                        <div class=\"left avatar\">{{comment.username[0]}}</div>\n" +
    "                        <div v-if=\"comment.status === 'public'\" class=\"right\">\n" +
    "                            <div class=\"username\">{{comment.username}}</div>\n" +
    "                            <div v-if=\"comment.isAdmin\" class=\"commentAdmin\">管理员</div>\n" +
    "                            <div v-if=\"!comment.isNew\" class=\"reply\" @click=\"replyClick(comment)\">回复</div>\n" +
    "                            <div v-if=\"commentData.type === 'top_sub'\" class=\"commentDialog\" @click=\"getOneComment(comment)\">查看对话</div>\n" +
    "                            <div class=\"commentId\">ID:{{comment._id['$oid']}}</div>\n" +
    "                            <div class=\"date\">{{comment.date}}</div>\n" +
    "                            <div style=\"clear: both\"></div>\n" +
    "                            <div class=\"comment\"><span v-html=\"comment.comment\"></span></div>\n" +
    "                        </div>\n" +
    "                        <div v-if=\"comment.status !== 'public'\" class=\"right\">\n" +
    "                            评论不见了哦~\n" +
    "                        </div>\n" +
    "                    </div>\n" +
    "                    <div style=\"clear: both\"></div>\n" +
    "                    <div v-if=\"comment.sub && comment.sub.length > 0\" class=\"subComments\">\n" +
    "                        <div class=\"subCommentWraper\" v-for=\"(subComment,subIndex) in comment.sub\" :key=\"'sub'+subComment._id['$oid']\">\n" +
    "                            <div class=\"subComment\" v-if=\"subIndex < commentData.config.subCommentMainCount\">\n" +
    "                                <div class=\"subCommentUsername\">{{subComment.username}}</div>\n" +
    "                                <div v-if=\"subComment.isAdmin\" class=\"subCommentAdmin\">管理员</div>\n" +
    "                                <div class=\"subCommenteply\" @click=\"replyClick(subComment)\">回复</div>\n" +
    "                                <div class=\"subCommentDialog\" @click=\"getOneComment(subComment)\">查看对话</div>\n" +
    "                                <div class=\"subCommentId\">ID:{{subComment._id['$oid']}}</div>\n" +
    "                                <div class=\"subCommentDate\">{{subComment.date}}</div>\n" +
    "                                <div style=\"clear: both\"></div>\n" +
    "                                <div class=\"subCommentComment\">{{subComment.comment}}</div>\n" +
    "                                <div hidden>{{subIndex}}</div>\n" +
    "                            </div>\n" +
    "                        </div>\n" +
    "                        <div v-if=\"comment.sub.length > commentData.config.subCommentMainCount\" class=\"subCommentMore\" @click=\"getOneComment(comment)\">查看更多</div>\n" +
    "                    </div>\n" +
    "                </div>\n" +
    "                <div v-if=\"commentData.type === 'main'\" class=\"pageList\">\n" +
    "                    <a :class=\"page === commentData.page + 1?'active':''\" v-for=\"(page, index) in pageCount\" :key=\"index\" @click=\"pageChange(page)\">\n" +
    "                        {{page}}\n" +
    "                    </a>\n" +
    "                </div>\n" +
    "            </div>\n" +
    "        </div>\n" +
    "        <div v-if=\"commentClosed\" class=\"commentClosed\">\n" +
    "            评论已关闭\n" +
    "        </div>\n" +
    "    </div>\n" +
    "    <div v-if=\"!init\" class=\"nyatoriCommentWrapper0511Loading\">Loading</div>\n" +
    "</div>";
    var nyatoriCommentWrapper0511JS = "<script type=\"text/javascript\">\n" +
        "    var commentEl = document.getElementById('nyatoriCommentWrapper0511');\n" +
        "    var commentSystem = commentEl.getAttribute('data-system');\n" +
        "    var commentSite = commentEl.getAttribute('data-site');\n" +
        "    // var commentPath = commentEl.getAttribute('data-path');\n" +
        "    var commentPath = GetUrlRelativePath();\n" +
        "    var commentId = getQueryVariable('comment-id');\n" +
        "\n" +
        "    var vm = new Vue({\n" +
        "        el: '#commentApp',\n" +
        "        data: {\n" +
        "            captchaImage: commentSystem + \"/api.php?controller=public&action=captcha\",\n" +
        "            captchaImageShow: false,\n" +
        "            replyComment: null,\n" +
        "            comment: \"\",\n" +
        "            username: \"\",\n" +
        "            email: \"\",\n" +
        "            replyNotify: false,\n" +
        "            captcha: \"\",\n" +
        "            page: 0,\n" +
        "            commentData: {},\n" +
        "            commentStack: [],\n" +
        "            pageList: [],\n" +
        "            init: false, //判断是否刚加载页面\n" +
        "            configRequiredUsername: false,\n" +
        "            configRequiredEmail: false,\n" +
        "            configReplyNotify: false,\n" +
        "            configAdminUsername: ''\n" +
        "        },\n" +
        "        computed: {\n" +
        "            commentClosed() {\n" +
        "                const comment = this.commentData;\n" +
        "                if(!comment.article) return false;\n" +
        "                return !comment.article.commentSwitch;\n" +
        "            },\n" +
        "            commentList() {\n" +
        "                if(!this.commentData) return [];\n" +
        "                return this.commentData.comment;\n" +
        "            },\n" +
        "            commentCount(){\n" +
        "                if(!this.commentData) return 0;\n" +
        "                if(!this.commentData.article) return 0;\n" +
        "                return this.commentData.article.commentCount;\n" +
        "            },\n" +
        "            pageCount(){\n" +
        "                if(!this.commentData) return [];\n" +
        "                if(!this.commentData.article) return [];\n" +
        "                if(!this.commentData.config) return [];\n" +
        "                const count = this.commentData.article.commentRootCount;    //使用顶级（根）评论的数量来计算页数，因为只显示一级的评论在列表上。\n" +
        "                const perPageCount = this.commentData.config.perPageCount; //分页最大评论数\n" +
        "                const pageTotal = Math.ceil(count / perPageCount);\n" +
        "                const currentPage = this.commentData.page + 1;\n" +
        "                if(pageTotal < 1){\n" +
        "                    return [];  //一页时不显示页码\n" +
        "                } else if(pageTotal < 8) {\n" +
        "                    let pageList = [];\n" +
        "                    for(let i = 1; i <= pageTotal; i++){\n" +
        "                        pageList[i-1] = i;\n" +
        "                    }\n" +
        "                    return pageList;\n" +
        "                } else {\n" +
        "                    let pageList = [1, 2];\n" +
        "                    const prePage = currentPage - 1;\n" +
        "                    const nextPage = currentPage + 1;\n" +
        "                    if(prePage > 2){\n" +
        "                        pageList.push('...');\n" +
        "                        pageList.push(prePage);\n" +
        "                    }\n" +
        "                    if(currentPage > 2 && currentPage < pageTotal-1){\n" +
        "                        pageList.push(currentPage);\n" +
        "                    }\n" +
        "                    if(nextPage > 2 && nextPage < pageTotal-1){\n" +
        "                        pageList.push(nextPage);\n" +
        "                        pageList.push('...');\n" +
        "                    }\n" +
        "                    if(nextPage <= 2){\n" +
        "                        pageList.push('...');\n" +
        "                    }\n" +
        "                    pageList.push(pageTotal - 1, pageTotal);\n" +
        "                    return pageList;\n" +
        "                }\n" +
        "            }\n" +
        "        },\n" +
        "        methods: {\n" +
        "            submit() {\n" +
        "                let isAdmin = this.commentData.isAdmin;\n" +
        "\n" +
        "                if(!isAdmin && this.captcha.trim().length < 1) {\n" +
        "                    alert('请输入验证码');\n" +
        "                    return false;\n" +
        "                }\n" +
        "                if(this.comment.length > 200) {\n" +
        "                    alert('评论内容过长');\n" +
        "                    return false;\n" +
        "                }\n" +
        "                if(this.comment.trim().length < 1){\n" +
        "                    alert('请输入评论内容');\n" +
        "                    return false;\n" +
        "                }\n" +
        "                if(!isAdmin && this.configRequiredUsername && this.username.trim().length < 1){\n" +
        "                    alert('请输入用户名');\n" +
        "                    return false;\n" +
        "                }\n" +
        "                if(!isAdmin && this.configRequiredEmail && this.email.trim().length < 1){\n" +
        "                    alert('请输入邮箱');\n" +
        "                    return false;\n" +
        "                }\n" +
        "                if(this.email && !/^\\w+?@\\w+?\\.\\w+?$/g.test(this.email)){\n" +
        "                    alert('输入邮箱有误');\n" +
        "                    return false;\n" +
        "                }\n" +
        "                let postData = {\n" +
        "                    site: commentSite,\n" +
        "                    path: commentPath,\n" +
        "                    comment: {\n" +
        "                        comment: this.comment,\n" +
        "                        username: this.username,\n" +
        "                        email: this.email\n" +
        "                    },\n" +
        "                    replyNotify: this.replyNotify,\n" +
        "                    captcha: this.captcha\n" +
        "                };\n" +
        "                if(this.replyComment && this.replyComment._id && this.replyComment._id['$oid']){\n" +
        "                    postData.comment.parentId = this.replyComment._id['$oid'];\n" +
        "                }\n" +
        "                $jquery.ajax({\n" +
        "                    method: 'POST',\n" +
        "                    url: commentSystem + '/api.php?controller=comment&action=submit&site=' + commentSite,\n" +
        "                    data: JSON.stringify(postData),\n" +
        "                    dataType: 'json',\n" +
        "                    contentType: \"application/json\",\n" +
        "                    xhrFields: {\n" +
        "                        withCredentials: true //默认情况下，标准的跨域请求是不会发送cookie的\n" +
        "                    },\n" +
        "                })\n" +
        "                    .then((response) => {\n" +
        "                        const data = response;\n" +
        "                        if(data.code === undefined){\n" +
        "                            alert('解析失败');\n" +
        "                            return;\n" +
        "                        }\n" +
        "                        this.refreshCaptcha();\n" +
        "                        if(data.code !== 0){\n" +
        "                            alert(data.msg);\n" +
        "                            return;\n" +
        "                        }\n" +
        "\n" +
        "                        this.commentData.comment.unshift({\n" +
        "                            _id: {'$oid': (new Date()).getTime()},\n" +
        "                            comment: this.comment,\n" +
        "                            username: '我',\n" +
        "                            date: data.isPublic?'刚刚':'审核后显示',\n" +
        "                            isNew: true,\n" +
        "                            status: 'public'\n" +
        "                        });\n" +
        "\n" +
        "                        this.comment = this.username = this.email = this.captcha = \"\";\n" +
        "                        this.replyComment = null;\n" +
        "                        if(this.commentData.isAdmin) this.username = this.configAdminUsername;\n" +
        "                        alert(data.msg);\n" +
        "                        // this.getList();\n" +
        "                    })\n" +
        "                    .catch(function (error) {\n" +
        "                        console.log(error);\n" +
        "                    });\n" +
        "            },\n" +
        "            getList(page = 1) {\n" +
        "                $jquery.ajax({\n" +
        "                    method: 'get',\n" +
        "                    url: commentSystem + \"/api.php\",\n" +
        "                    data: {\n" +
        "                        controller: 'comment',\n" +
        "                        action: 'list',\n" +
        "                        site: commentSite,\n" +
        "                        path: commentPath,\n" +
        "                        page: (page - 1)\n" +
        "                    },\n" +
        "                    dataType: \"json\",\n" +
        "                    xhrFields: {\n" +
        "                        withCredentials: true //默认情况下，标准的跨域请求是不会发送cookie的\n" +
        "                    },\n" +
        "                })\n" +
        "                    .then((response) => {\n" +
        "                        var data = response;\n" +
        "                        if(data.code === undefined){\n" +
        "                            alert('解析失败');\n" +
        "                            return;\n" +
        "                        }\n" +
        "                        if(data.code !== 0){\n" +
        "                            alert(data.msg);\n" +
        "                            return;\n" +
        "                        }\n" +
        "\n" +
        "                        this.configRequiredUsername = data.config.requiredUsername;\n" +
        "                        this.configRequiredEmail = data.config.requiredEmail;\n" +
        "                        this.configReplyNotify = data.config.replyNotify;\n" +
        "                        this.configAdminUsername = data.config.adminUsername;\n" +
        "                        if(this.configReplyNotify) this.replyNotify = true;\n" +
        "                        if(data.isAdmin) this.username = this.configAdminUsername;  //如果后台已经登录，显示用户名为管理员\n" +
        "\n" +
        "                        this.commentData = data;\n" +
        "\n" +
        "                        if(!this.init && commentId !== undefined){\n" +
        "                            this.getOneComment({_id: {'$oid': commentId}});\n" +
        "                        }\n" +
        "\n" +
        "                        this.init = true;\n" +
        "                    })\n" +
        "                    .catch(function(error){\n" +
        "                        console.log(error);\n" +
        "                    });\n" +
        "            },\n" +
        "            refreshCaptcha(){\n" +
        "                this.captchaImage = commentSystem + \"/api.php?controller=public&action=captcha&token=\"+Math.random();\n" +
        "                this.captchaImageShow = false;\n" +
        "            },\n" +
        "            captchaLoaded(){\n" +
        "                this.captchaImageShow = true;\n" +
        "            },\n" +
        "            replyClick(comment){\n" +
        "                this.replyComment = comment;\n" +
        "                console.log(comment);\n" +
        "            },\n" +
        "            closeReply(){\n" +
        "                this.replyComment = null;\n" +
        "            },\n" +
        "            getOneComment(comment){\n" +
        "                $jquery.ajax({\n" +
        "                    url: commentSystem + \"/api.php\",\n" +
        "                    data: {\n" +
        "                        controller: 'comment',\n" +
        "                        action: 'listone',\n" +
        "                        id: comment._id['$oid'],\n" +
        "                        site: commentSite,\n" +
        "                        path: commentPath,\n" +
        "                    },\n" +
        "                    dataType: 'json',\n" +
        "                    xhrFields: {\n" +
        "                        withCredentials: true //默认情况下，标准的跨域请求是不会发送cookie的\n" +
        "                    },\n" +
        "                })\n" +
        "                    .then((response) => {\n" +
        "                        const data = response;\n" +
        "                        if(data.code === undefined){\n" +
        "                            alert('解析失败');\n" +
        "                            return;\n" +
        "                        }\n" +
        "                        if(data.code !== 0){\n" +
        "                            alert(data.msg);\n" +
        "                            return;\n" +
        "                        }\n" +
        "\n" +
        "                        this.commentStack.push(this.commentData);\n" +
        "                        this.commentData = data;\n" +
        "                    })\n" +
        "                    .catch(function(error){\n" +
        "                        console.log(error);\n" +
        "                    });\n" +
        "            },\n" +
        "            commentHistoryBack() {\n" +
        "                const commentList = this.commentStack.pop();\n" +
        "                if(!commentList) return;\n" +
        "                this.commentData = commentList;\n" +
        "            },\n" +
        "            pageChange(page){\n" +
        "                if(page === '...') return;\n" +
        "                this.getList(page);\n" +
        "            }\n" +
        "        },\n" +
        "        mounted() {\n" +
        "            this.$el.style.display = 'block';   //避免闪现上面vue代码的未解析的文本。\n" +
        "            this.getList();\n" +
        "        }\n" +
        "    }); //会去除锚点，如果使用vue router等类似单页应用可能会出现问题。\n" +
        "\n" +
        "    function GetUrlRelativePath()\n" +
        "    {\n" +
        "        var url = document.location.toString();\n" +
        "        var arrUrl = url.split(\"//\");\n" +
        "\n" +
        "        var start = arrUrl[1].indexOf(\"/\");\n" +
        "        var relUrl = arrUrl[1].substring(start);//stop省略，截取从start开始到结尾的所有字符\n" +
        "\n" +
        "        if(relUrl.indexOf(\"#\") != -1){  //去除锚点\n" +
        "            relUrl = relUrl.split(\"#\")[0];\n" +
        "        }\n" +
        "        if(relUrl.indexOf(\"?\") != -1){\n" +
        "            relUrl = relUrl.split(\"?\")[0];\n" +
        "        }\n" +
        "        return relUrl;\n" +
        "    }\n" +
        "\n" +
        "    function getQueryVariable(variable)\n" +
        "    {\n" +
        "        var query = window.location.search.substring(1);\n" +
        "        var vars = query.split(\"&\");\n" +
        "        for (var i=0;i<vars.length;i++) {\n" +
        "            var pair = vars[i].split(\"=\");\n" +
        "            if(pair[0] == variable){return pair[1];}\n" +
        "        }\n" +
        "        return(false);\n" +
        "    }\n" +
        "</script>"
commentElHtml = (commentWithStyle?nyatoriCommentWrapper0511CSS:'') + nyatoriCommentWrapper0511;
commentEl.innerHTML = commentElHtml;
document.write(nyatoriCommentWrapper0511JS);