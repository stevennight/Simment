<template>
    <div v-if="init" id="comment">
        <div v-if="!commentClosed" class="commentWrapper">
            <div class="send">
                <div v-if="replyComment" class="replyCommentWrapper">
                    <div class="replyComment">回复{{replyComment.username}}:<span v-html="replyComment.comment"></span></div>
                    <div class="close" @click="closeReply">×</div>
                </div>
                <div class="sendCommentWrapper">
                    <textarea class="sendComment" v-model="comment" placeholder="请输入评论内容"></textarea>
                </div>
                <div class="sendInfo">
                    <div class="sendUsernameWrapper">
                        <input class="sendUsername" type="text" v-model="username" :placeholder="'您的昵称' + (this.configRequiredUsername?'(必填)':'')">
                    </div>
                    <div class="sendEmailWrapper">
                        <input class="sendEmail" type="email" v-model="email" :placeholder="'您的邮箱' + (this.configRequiredEmail?'(必填)':'')">
                    </div>
                </div>
                <div style="clear: both"></div>
                <div v-if="configReplyNotify" class="sendReplyNotifyWrapper">
                    <label>
                        <input type="checkbox" v-model="replyNotify">
                        回复通知(请填写真实邮箱,否则无法收到通知）
                    </label>
                </div>
                <div class="captchaWrapper">
                    <input class="sendCaptcha" type="text" v-model="captcha" placeholder="右侧验证码">
                    <img v-show="captchaImageShow" class="captchaImage" :src="captchaImage" @load="captchaLoaded" @click="refreshCaptcha">
                    <div class="captchaImageLoading" v-show="!captchaImageShow">Loading</div>
                </div>
                <div class="sendBtnWrapper">
                    <button class="sendBtn" @click="submit">提交</button>
                </div>
            </div>
            <CommentList
                    :commentData="commentData"
                    @pageChange="getList"
                    @reply="replyClick"
                    @getOne="getOneComment"
                    @commentHistoryBack="commentHistoryBack"
            ></CommentList>
        </div>
        <div v-if="commentClosed" class="commentClosed">
            评论已关闭
        </div>
    </div>
</template>

<script>
    import CommentList from './components/CommentList';

    export default {
        name: "Comment",
        data() {
            return {
                captchaImage: "/api.php?controller=public&action=captcha",
                captchaImageShow: false,
                replyComment: null,
                comment: "",
                username: "",
                email: "",
                replyNotify: false,
                captcha: "",
                page: 0,
                commentData: {},
                commentStack: [],
                init: false, //判断是否刚加载页面
                configRequiredUsername: false,
                configRequiredEmail: false,
                configReplyNotify: false,
                configAdminUsername: ''
            };
        },
        components: {
            CommentList: CommentList,
        },
        computed: {
            commentClosed() {
                const comment = this.commentData;
                if(!comment.article) return false;
                return !comment.article.commentSwitch;
            }
        },
        methods: {
            submit() {
                let isAdmin = this.commentData.isAdmin;

                if(!isAdmin && this.captcha.trim().length < 1) {
                        alert('请输入验证码');
                    return false;
                }
                if(this.comment.length > 200) {
                    alert('评论内容过长');
                    return false;
                }
                if(this.comment.trim().length < 1){
                    alert('请输入评论内容');
                    return false;
                }
                if(!isAdmin && this.configRequiredUsername && this.username.trim().length < 1){
                    alert('请输入用户名');
                    return false;
                }
                if(!isAdmin && this.configRequiredEmail && this.email.trim().length < 1){
                    alert('请输入邮箱');
                    return false;
                }
                if(this.email && !/^\w+?@\w+?\.\w+?$/g.test(this.email)){
                    alert('输入邮箱有误');
                    return false;
                }
                let postData = {
                    site: decodeURIComponent(this.$route.params.site),
                    path: decodeURIComponent(this.$route.params.path),
                    comment: {
                        comment: this.comment,
                        username: this.username,
                        email: this.email
                    },
                    replyNotify: this.replyNotify,
                    captcha: this.captcha
                };
                if(this.replyComment && this.replyComment._id && this.replyComment._id['$oid']){
                    postData.comment.parentId = this.replyComment._id['$oid'];
                }
                this.axios({
                    method: 'POST',
                    url: '/api.php?controller=comment&action=submit',
                    responseType: 'json',
                    data: postData
                })
                    .then((response) => {
                        if(response.status !== 200){
                            alert('提交失败');
                            return;
                        }
                        const data = response.data;
                        if(data.code === undefined){
                            alert('解析失败');
                            return;
                        }
                        this.refreshCaptcha();
                        if(data.code !== 0){
                            alert(data.msg);
                            return;
                        }

                        this.commentData.comment.unshift({
                            _id: {'$oid': (new Date()).getTime()},
                            comment: data.comment,  //comment从后端返回中获取，因为后端把这个内容处理过。（懒 x_x...）
                            username: '我',
                            date: data.isPublic?'刚刚':'审核后显示',
                            isNew: true,
                            status: 'public'
                        });

                        this.comment = this.username = this.email = this.captcha = "";
                        this.replyComment = null;
                        if(this.commentData.isAdmin) this.username = this.configAdminUsername;
                        alert(data.msg);
                        // this.getList();
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            getList(page = 1) {
                this.axios({
                    method: 'get',
                    url: "/api.php",
                    params: {
                        controller: 'comment',
                        action: 'list',
                        site: this.$route.params.site,
                        path: this.$route.params.path,
                        page: (page - 1)
                    }
                })
                    .then((response) => {
                        if(response.status !== 200){
                            alert('通信失败');
                            return;
                        }
                        const data = response.data;
                        if(data.code === undefined){
                            alert('解析失败');
                            return;
                        }
                        if(data.code !== 0){
                            alert(data.msg);
                            return;
                        }

                        this.configRequiredUsername = data.config.requiredUsername;
                        this.configRequiredEmail = data.config.requiredEmail;
                        this.configReplyNotify = data.config.replyNotify;
                        this.configAdminUsername = data.config.adminUsername;
                        this.configAdminEmail = data.config.adminEmail;
                        if(this.configReplyNotify) this.replyNotify = true;
                        if(data.isAdmin){
                            this.username = this.configAdminUsername;  //如果后台已经登录，显示用户名为管理员
                            this.email = this.configAdminEmail;  //如果后台已经登录，邮箱显示为管理员邮箱
                        }

                        this.commentData = data;
                        if(!this.init && this.$route.query.comment !== undefined){
                            this.getOneComment({_id: {'$oid': this.$route.query.comment}});
                        }
                        this.init = true;
                    })
                    .catch(function(error){
                        console.log(error);
                    });
            },
            refreshCaptcha(){
                this.captchaImage = "/api.php?controller=public&action=captcha&token="+Math.random();
                this.captchaImageShow = false;
            },
            captchaLoaded(){
                this.captchaImageShow = true;
            },
            replyClick(comment){
                this.replyComment = comment;
                console.log(comment);
            },
            closeReply(){
                this.replyComment = null;
            },
            getOneComment(comment){
                var getMore = comment.getMore;
                if(getMore){
                    getMore = this.commentData.more['$oid']
                }
                this.axios({
                    method: 'get',
                    url: "/api.php",
                    params: {
                        controller: 'comment',
                        action: 'listone',
                        id: comment._id['$oid'],
                        more: getMore,
                        site: this.$route.params.site,
                        path: this.$route.params.path,
                    }
                })
                    .then((response) => {
                        if(response.status !== 200){
                            alert('通信失败');
                            return;
                        }
                        const data = response.data;
                        if(data.code === undefined){
                            alert('解析失败');
                            return;
                        }
                        if(data.code !== 0){
                            alert(data.msg);
                            return;
                        }

                        if(getMore){
                            this.commentData.comment = this.commentData.comment.concat(data.comment);
                            this.commentData.more = data.more;
                        } else {
                            this.commentStack.push(this.commentData);
                            this.commentData = data;
                        }
                    })
                    .catch(function(error){
                        console.log(error);
                    });
            },
            commentHistoryBack() {
                const commentList = this.commentStack.pop();
                if(!commentList) return;
                this.commentData = commentList;
            }
        },
        mounted() {
            this.getList();
        }
    }
</script>

<style lang="stylus" scoped>
    .send
        width 100%
        input, textarea
            padding .5rem
            border-radius .3rem
            border .1rem solid #ddd
            box-sizing border-box
        .sendCommentWrapper
            margin-top .3rem
            .sendComment
                width 100%
                height 4.8rem
                display block
                resize none
                padding .3rem
        .sendInfo
            width 100%
            margin .3rem 0
            .sendUsernameWrapper
                float left
                padding-right .15rem
                width: 50%
                box-sizing border-box
                .sendUsername
                    width 100%
            .sendEmailWrapper
                float right
                padding-left .15rem
                width: 50%
                box-sizing border-box
                .sendEmail
                    width 100%
        .sendReplyNotifyWrapper
            padding-top .3rem
            label, input
                cursor pointer
                user-select none
        .sendBtnWrapper
            margin .3rem 0
            width 100%
            box-sizing border-box
            .sendBtn
                width 100%
                box-sizing border-box
                border .1rem solid #ddd
                border-radius .3rem
                background #666
                color white
                line-height 1.5rem
                font-weight bolder
                cursor pointer
        .captchaWrapper
            width 100%
            display flex
            margin-top .3rem
            .sendCaptcha
                flex 1
                margin-right .5rem
            .captchaImage
                height 3rem
                cursor pointer
                margin-right .5rem
            .captchaImageLoading
                line-height 2rem
                margin-right .5rem
        .replyCommentWrapper
            display flex
            padding .5rem
            background skyblue
            border-radius .3rem
            text-align left
            .replyComment
                flex 1
            .close
                user-select none
                cursor pointer
                font-size 1.2rem
                font-weight bolder
</style>