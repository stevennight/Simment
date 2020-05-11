<template>
    <div id="comment">
        <div class="search">
            网站：
            <select v-model="searchSite">
                <option value="">不过滤</option>
                <option v-for="siteOption in siteList" :value="siteOption._id['$oid']" :key="siteOption._id['$oid']">{{siteOption.site}}</option>
            </select>
            路径：
            <input type="text" v-model="searchPath">
            状态：
            <select v-model="searchStatus">
                <option value="">不过滤</option>
                <option value="public">公开</option>
                <option value="audit">待审</option>
                <option value="hidden">隐藏</option>
            </select>
            <button @click="search">搜索</button>
        </div>
        <div class="list">
            <table class="listTable">
                <tr>
                    <td>序号</td>
                    <td>用户</td>
                    <td>管理员</td>
                    <td>邮箱</td>
                    <td>评论</td>
                    <td>时间</td>
                    <td>IP</td>
                    <td>状态</td>
                    <td>操作</td>
                </tr>
                <tr v-show="loading">
                    <td colspan="4">Loading...</td>
                </tr>
                <tr v-show="error">
                    <td colspan="4">{{error}}</td>
                </tr>
                <tr v-for="(comment, index) in commentList" :key="comment._id['$oid']">
                    <td>{{index+1}}</td>
                    <td>{{comment.username}}</td>
                    <td>{{comment.isAdmin?'√':'×'}}</td>
                    <td>{{comment.email}}</td>
                    <td>{{comment.comment}}</td>
                    <td>{{comment.date}}</td>
                    <td>{{comment.ip}}</td>
                    <td>{{comment.status}}</td>
                    <td>
                        <button v-if="['audit', 'hidden'].indexOf(comment.status) > -1" @click="updateComment(index, 'public')">通过</button>&nbsp;
                        <button v-if="['public', 'audit'].indexOf(comment.status) > -1" @click="updateComment(index, 'hidden')">拒绝</button>&nbsp;
                        <button @click="delComment(index)">删除</button>&nbsp;
                        <button @click="replyComment(index)">回复</button>
                    </td>
                </tr>
            </table>
        </div>
        <div class="pageWrapper">
            <div class="pageCurrent">第{{page}}页</div>
            <div class="pageChange">
                <button @click="pageChange(-1)">上一页</button>
                <button @click="pageChange(1)">下一页</button>
                跳转：<input v-model="page" />
            </div>
        </div>
<!--        <div v-show="editShow" class="form">-->
<!--            <div>id:<input v-model="editFieldId"/></div>-->
<!--            <div>-->
<!--                站点：-->
<!--                <select v-model="editFieldSiteId">-->
<!--                    <option>请选择一个站点</option>-->
<!--                    <option v-for="siteOption in siteList" :value="siteOption._id['$oid']" :key="siteOption._id['$oid']">{{siteOption.site}}</option>-->
<!--                </select>-->
<!--            </div>-->
<!--            <div>路径:<input v-model="editFieldPath"/></div>-->
<!--            <div><input type="checkbox" v-model="editFieldCommentSwitch"/>开启评论</div>-->
<!--            <div><button @click="editPath">提交</button> <button @click="editReset">重置</button> <button @click="editToggle(false)">关闭</button></div>-->
<!--        </div>-->
    </div>
</template>

<script>
    export default {
        name: "Comment",
        data() {
            return {
                siteList: [],
                searchSite: null,
                searchPath: '',
                searchStatus: null,
                commentList: [],
                loading: false,
                error: false,
                page: 1,
            };
        },
        methods: {
            getSite(){
                this.axios({
                    url: "/api.php?controller=admin&action=site"
                })
                    .then((response) => {
                        if(response.status !== 200){
                            alert('通讯失败');
                            return;
                        }
                        let data = response.data;
                        if(data.code === undefined){
                            alert('解析失败');
                            return;
                        }
                        if(data.code !== 0){
                            alert(data.msg);
                            return;
                        }

                        this.siteList = data.data;
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            },
            getComment() {
                this.axios({
                    url: "/api.php?controller=admin&action=comment",
                    params: {
                        siteId: this.searchSite,
                        path: this.searchPath,
                        status: this.searchStatus,
                        page: this.page - 1,
                    }
                })
                    .then((response) => {
                        if(response.status !== 200){
                            this.error = '通讯失败';
                            this.loading = false;
                            return;
                        }
                        let data = response.data;
                        if(data.code === undefined){
                            this.error = '解析失败';
                            this.loading = false;
                            return;
                        }
                        if(data.code !== 0){
                            this.error = data.msg;
                            this.loading = false;
                            return;
                        }

                        this.commentList = data.comment;
                        this.loading = false;
                        this.error = null;
                    })
                    .catch((error) => {
                        console.log(error);
                        this.error = '内部错误';
                        this.loading = false;
                    });
            },
            search() {
                this.page = 1;
                this.getComment();
            },
            updateComment(index, status) {
                const comment = this.commentList[index];
                const id = comment._id['$oid'];

                this.axios({
                    method: 'POST',
                    url: "/api.php?controller=admin&action=commentupdate",
                    data: {
                        id: id,
                        status: status,
                    }
                })
                    .then((response) => {
                        if(response.status !== 200){
                            alert('通讯失败');
                            return;
                        }
                        let data = response.data;
                        if(data.code === undefined){
                            alert('解析失败');
                            return;
                        }
                        if(data.code !== 0){
                            alert(data.msg);
                            return;
                        }

                        this.$set(this.commentList[index], 'status', status);
                        alert('修改成功');
                    })
                    .catch((error) => {
                        console.log(error);
                        alert('内部错误');
                    });
            },
            delComment(index) {
                if(!confirm('删除操作不可逆，是否确定删除？')){
                    return;
                }

                const id = this.commentList[index]._id['$oid'];
                this.axios({
                    method: 'POST',
                    url: "/api.php?controller=admin&action=commentdel",
                    data: {
                        id: id
                    }
                })
                    .then((response) => {
                        if(response.status !== 200){
                            alert('通讯失败');
                            return;
                        }
                        let data = response.data;
                        if(data.code === undefined){
                            alert('解析失败');
                            return;
                        }
                        if(data.code !== 0){
                            alert(data.msg);
                            return;
                        }

                        this.commentList.splice(index, 1);
                    })
                    .catch((error) => {
                        console.log(error);
                        alert('内部错误');
                    });
            },
            replyComment(index) {
                const parentId = this.commentList[index]._id['$oid'];
                const comment = prompt('请输入回复内容：');
                if(comment === null) return;
                this.axios({
                    method: 'POST',
                    url: "/api.php?controller=admin&action=commentreply",
                    data: {
                        parentId: parentId,
                        comment: comment
                    }
                })
                    .then((response) => {
                        if(response.status !== 200){
                            alert('通讯失败');
                            return;
                        }
                        let data = response.data;
                        if(data.code === undefined){
                            alert('解析失败');
                            return;
                        }
                        if(data.code !== 0){
                            alert(data.msg);
                            return;
                        }

                        alert('回复成功');
                    })
                    .catch((error) => {
                        console.log(error);
                        alert('内部错误');
                    });
            },
            pageChange(offset){
                //上一页、下一页
                let page = this.page + offset;
                if(page <= 0){
                    return;
                }
                this.page = page;
            },
        },
        watch: {
            page() {
                if(this.pageTimeout !== null){
                    clearTimeout(this.pageTimeout);
                }
                this.pageTimeout = setTimeout(() => {
                    if(isNaN(this.page)){
                        alert('页数必须为数字');
                        return;
                    }
                    if(this.page <= 0){
                        alert('页数必须大于1');
                        return;
                    }
                    this.getComment();
                    this.pageTimeout = null;
                }, 1000);
            }
        },
        mounted() {
            this.getSite();
        }
    }
</script>

<style lang="stylus" scoped>
    #comment
        .search
            button
                margin .3rem
        .list
            margin .3rem
            width 100%
            border-top 1px solid #ccc
            border-bottom 1px solid #ccc
            .listTable
                width 100%
                tr:hover
                    background yellowgreen
        .pageWrapper
            .pageCurrent
                float left
            .pageChange
                float right
            *
                margin-left .3rem
            input
                width 3rem
        .form
            position absolute
            right 0
            top 0
            bottom 0
            background white
            padding 1.2rem
            border-left 1px solid #ccc
            box-sizing border-box
            div
                padding .8rem
</style>