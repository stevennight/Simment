<template>
    <div id="commentList">
        <div class="total" v-if="commentData.type === 'main'"><span>{{commentCount}}</span>条评论</div>
        <div class="listHistoryBack" v-if="commentData.type !== 'main'" @click="commentHistroyBack">返回列表</div>
        <div class="commentContainer" v-for="comment in commentList" :key="comment._id['$oid']">
            <div class="commentWrapper" :class="commentData.highlight === comment._id['$oid']?'highlight':''">
                <div class="left avatar">{{comment.username[0]}}</div>
                <div v-if="comment.status === 'public'" class="right">
                    <div class="username">{{comment.username}}</div>
                    <div v-if="comment.isAdmin" class="commentAdmin">管理员</div>
                    <div v-if="!comment.isNew" class="reply" @click="replyClick(comment)">回复</div>
                    <div v-if="commentData.type === 'top_sub'" class="commentDialog" @click="getOne(comment)">查看对话</div>
                    <div class="commentId">ID:{{comment._id['$oid']}}</div>
                    <div class="date">{{comment.date}}</div>
                    <div style="clear: both"></div>
                    <div class="comment"><span v-html="comment.comment"></span></div>
                </div>
                <div v-if="comment.status !== 'public'" class="right">
                    评论不见了哦~
                </div>
            </div>
            <div style="clear: both"></div>
            <div v-if="comment.sub && comment.sub.length > 0" class="subComments">
                <div class="subCommentWraper" v-for="(subComment,subIndex) in comment.sub" :key="'sub'+subComment._id['$oid']">
                    <div class="subComment" v-if="subIndex < commentData.config.subCommentMainCount">
                        <div class="subCommentUsername">{{subComment.username}}</div>
                        <div v-if="subComment.isAdmin" class="subCommentAdmin">管理员</div>
                        <div class="subCommenteply" @click="replyClick(subComment)">回复</div>
                        <div class="subCommentDialog" @click="getOne(subComment)">查看对话</div>
                        <div class="subCommentId">ID:{{subComment._id['$oid']}}</div>
                        <div class="subCommentDate">{{subComment.date}}</div>
                        <div style="clear: both"></div>
                        <div class="subCommentComment">{{subComment.comment}}</div>
                        <div hidden>{{subIndex}}</div>
                    </div>
                </div>
                <div v-if="comment.sub.length > commentData.config.subCommentMainCount" class="subCommentMore" @click="getOne(comment)">查看更多</div>
            </div>
        </div>
        <div v-if="commentData.type === 'main'" class="pageList">
            <a :class="page === commentData.page + 1?'active':''" v-for="(page, index) in pageCount" :key="index" @click="pageChange(page)">
                {{page}}
            </a>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CommentList",
        props: ['commentData'],
        data: function() {
            return {
                pageList: []
            }
        },
        computed: {
            commentList() {
                if(!this.commentData) return [];
                return this.commentData.comment;
            },
            commentCount(){
                if(!this.commentData) return 0;
                if(!this.commentData.article) return 0;
                return this.commentData.article.commentCount;
            },
            pageCount(){
                if(!this.commentData) return [];
                if(!this.commentData.article) return [];
                if(!this.commentData.config) return [];
                const count = this.commentData.article.commentRootCount;    //使用顶级（根）评论的数量来计算页数，因为只显示一级的评论在列表上。
                const perPageCount = this.commentData.config.perPageCount; //分页最大评论数
                const pageTotal = Math.ceil(count / perPageCount);
                const currentPage = this.commentData.page + 1;
                if(pageTotal < 1){
                    return [];  //一页时不显示页码
                } else if(pageTotal < 8) {
                    let pageList = [];
                    for(let i = 1; i <= pageTotal; i++){
                        pageList[i-1] = i;
                    }
                    return pageList;
                } else {
                    let pageList = [1, 2];
                    const prePage = currentPage - 1;
                    const nextPage = currentPage + 1;
                    if(prePage > 2){
                        pageList.push('...');
                        pageList.push(prePage);
                    }
                    if(currentPage > 2 && currentPage < pageTotal-1){
                        pageList.push(currentPage);
                    }
                    if(nextPage > 2 && nextPage < pageTotal-1){
                        pageList.push(nextPage);
                        pageList.push('...');
                    }
                    if(nextPage <= 2){
                        pageList.push('...');
                    }
                    pageList.push(pageTotal - 1, pageTotal);
                    return pageList;
                }
            }
        },
        methods: {
            pageChange(page){
                if(page === '...') return;
                this.$emit('pageChange', page);
            },
            replyClick(comment){
                this.$emit('reply', comment);
            },
            getOne(comment){
                this.$emit('getOne', comment);
            },
            commentHistroyBack(){
                this.$emit('commentHistoryBack');
            }
        }
    }
</script>

<style lang="stylus" scoped>
    #commentList
        margin-top .5rem
        .total
            span
                background darkred
                color white
                font-weight bolder
                padding .12rem .36rem
                border-radius .3rem
        .listHistoryBack
            user-select none
            cursor pointer
            background black
            color white
            padding .3rem
            border-radius 1.2rem
            font-weight bolder
        .pageList
            padding .3rem
            user-select none
            a
                margin-right .3rem
                padding .3rem
                cursor pointer
            a.active
                color red
        .commentContainer
            border-bottom 1px solid #ddd
            .commentWrapper.highlight
                color darkgoldenrod
                font-weight bolder
            .commentWrapper
                display flex
                .left
                    width 3rem
                    height 3rem
                    line-height 3rem
                    margin .3rem
                .avatar
                    color white
                    background black
                    border-radius 1.5rem
                    font-weight bolder
                    font-size 1.5rem
                .right
                    flex: 1
                    min-width 0
                    margin .3rem
                    text-align left
                    word-wrap break-word
                    .username
                        font-weight bold
                        float left
                    .commentAdmin
                        float left
                        line-height 1.4rem
                        margin-left .5rem
                        background darkred
                        color white
                        padding 0 .3rem
                        border-radius .3rem
                        font-size .5rem
                        cursor pointer
                        user-select none
                    .reply
                        float left
                        line-height 1.4rem
                        margin-left .5rem
                        background #666
                        color white
                        padding 0 .3rem
                        border-radius .3rem
                        font-size .5rem
                        cursor pointer
                        user-select none
                    .commentDialog
                        float left;
                        line-height 1.4rem
                        margin-left .5rem
                        background #666
                        color white
                        padding 0 .3rem
                        border-radius .3rem
                        font-size .5rem
                        cursor pointer
                        user-select none
                    .commentId
                        float left
                        color #ccc
                        font-size .8rem
                        padding 0 .3rem
                        line-height 1.4rem
                    .date
                        float right
                        color #ccc
                        font-size .8rem
                        margin-right .5rem
            .subComments
                padding-left 3rem
                font-size .9rem
                text-align left
                .subCommentMore
                    padding .12rem
                    background #eee
                    width 4.8rem
                    text-align center
                    margin .3rem 0 .3rem 1.1rem
                    user-select none
                    cursor pointer
                    border-radius .3rem
                .subCommentWraper
                    padding 0
                    .subComment
                        padding .3rem
                        border-top 1px solid #ddd
                        *
                            padding-left .8rem
                        .subCommentUsername
                            float left
                            font-weight bold
                        .subCommentAdmin
                            float left
                            margin-left .5rem
                            background darkred
                            color white
                            padding 0 .3rem
                            border-radius .3rem
                            font-size .5rem
                            cursor pointer
                            user-select none
                        .subCommenteply
                            float left
                            margin-left .5rem
                            background #666
                            color white
                            padding 0 .3rem
                            border-radius .3rem
                            font-size .5rem
                            cursor pointer
                            user-select none
                        .subCommentDialog
                            float left
                            margin-left .5rem
                            background #666
                            color white
                            padding 0 .3rem
                            border-radius .3rem
                            font-size .5rem
                            cursor pointer
                            user-select none
                        .subCommentId
                            float left
                            color #ccc
                            font-size .8rem
                            padding 0 .3rem
                            line-height 1.2rem
                        .subCommentDate
                            float right
                            color #ccc
                            font-size .8rem
                            margin-right .5rem
</style>