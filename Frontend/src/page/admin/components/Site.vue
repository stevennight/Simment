<template>
    <div id="site">
        <div class="editWrapper">
            ID:(新增时留空)
            <input v-model="id">
            <br />
            网站：
            <input v-model="site">
            站点名称（对外显示）：
            <input v-model="siteName">
            <br />
            分页评论数量：
            <input type="number" v-model="perPageCount">
            回复评论嵌套显示数量
            <input type="number" v-model="subCommentMainCount">
            <br />
            评论最大长度：
            <input type="number" v-model="commentMaxLen">
            管理员用户名：
            <input type="text" v-model="adminUsername">
            管理员邮箱（通知）：
            <input type="text" v-model="adminEmail">
            <br />
            <input type="checkbox" v-model="autoCreateArticle">新页面自动创建
            <input type="checkbox" v-model="requiredUsername">用户名必填
            <input type="checkbox" v-model="requiredEmail">邮箱必填
            <input type="checkbox" v-model="replyNotify">回复邮件通知
            <input type="checkbox" v-model="audit">开启审核
            <br />
            <button class="submit" @click="submit">提交</button>
            <button class="reset" @click="resetForm">重置</button>
        </div>
        <div class="list">
            <table class="listTable">
                <tr>
                    <td>站点</td>
                    <td>站点名称</td>
                    <td>分页评论数量</td>
                    <td>回复评论嵌套显示数量</td>
                    <td>评论最大长度</td>
                    <td>管理员用户名</td>
                    <td>管理员邮箱</td>
                    <td>新页面自动创建</td>
                    <td>用户名必填</td>
                    <td>邮箱必填</td>
                    <td>回复通知</td>
                    <td>开启审核</td>
                    <td>操作</td>
                </tr>
                <tr v-for="(site, index) in list" :key="site['_id']['$oid']">
                    <td>{{site.site}}</td>
                    <td>{{site.siteName}}</td>
                    <td>{{site.perPageCount}}</td>
                    <td>{{site.subCommentMainCount}}</td>
                    <td>{{site.commentMaxLen}}</td>
                    <td>{{site.adminUsername}}</td>
                    <td>{{site.adminEmail}}</td>
                    <td>{{site.autoCreateArticle?'开启':'关闭'}}</td>
                    <td>{{site.requiredUsername?'开启':'关闭'}}</td>
                    <td>{{site.requiredEmail?'开启':'关闭'}}</td>
                    <td>{{site.replyNotify?'开启':'关闭'}}</td>
                    <td>{{site.audit?'开启':'关闭'}}</td>
                    <td><button @click="editSite(site)">编辑</button> <button @click="deleteSite(index)">删除</button></td>
                </tr>
            </table>
        </div>
        新页面自动创建：当该项开启时，新页面上用户评论后会自动创建路径（路径管理），并且开启允许评论。如果关闭，则新页面默认无法评论，需要到后台路径管理中添加路径后即可。
    </div>
</template>

<script>
    export default {
        name: "site",
        data() {
            return {
                site: '',
                siteName: '',
                id: '',
                autoCreateArticle: true,
                perPageCount: 10,
                subCommentMainCount: 5,
                commentMaxLen: 250,
                requiredUsername: true,
                requiredEmail: true,
                audit: true,
                replyNotify: false,
                adminUsername: 'Admin',
                adminEmail: '',
                list: []
            };
        },
        methods: {
            submit() {
                if(this.site.trim().length < 1){
                    alert('请输入站点');
                    return;
                }

                this.axios({
                    method: 'POST',
                    url: "/api.php?controller=admin&action=siteadd",
                    data: {
                        id: this.id,
                        site: this.site,
                        siteName: this.siteName,
                        autoCreateArticle: this.autoCreateArticle,
                        perPageCount: this.perPageCount,
                        subCommentMainCount: this.subCommentMainCount,
                        requiredUsername: this.requiredUsername,
                        requiredEmail: this.requiredEmail,
                        audit: this.audit,
                        replyNotify: this.replyNotify,
                        commentMaxLen: this.commentMaxLen,
                        adminUsername: this.adminUsername,
                        adminEmail: this.adminEmail
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

                        alert('保存成功');
                        window.location.reload();
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            },
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

                        this.list = data.data;
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            },
            editSite(site){
                this.id = site._id['$oid'];
                this.site = site.site;
                this.siteName = site.siteName;
                this.autoCreateArticle = site.autoCreateArticle;
                this.perPageCount = site.perPageCount;
                this.subCommentMainCount = site.subCommentMainCount;
                this.requiredUsername = site.requiredUsername;
                this.requiredEmail = site.requiredEmail;
                this.audit = site.audit;
                this.replyNotify = site.replyNotify;
                this.commentMaxLen = site.commentMaxLen;
                this.adminUsername = site.adminUsername;
                this.adminEmail = site.adminEmail;
            },
            deleteSite(i){
                if(!confirm('删除后属于该站点的评论数据将不可见，请问是否删除？')){
                    return;
                }
                const id = this.list[i]._id['$oid'];
                this.axios({
                    method: 'POST',
                    url: '/api.php?controller=admin&action=sitedel',
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

                        alert('删除成功');
                        this.list.splice(i, 1);
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            },
            resetForm(){
                this.site = this.siteName = this.id = "";
                this.autoCreateArticle = this.requiredEmail = this.requiredUsername = this.audit = true;
                this.replyNotify = false;
                this.commentMaxLen = 250;
                this.perPageCount = 10;
                this.subCommentMainCount = 5;
                this.adminUsername = 'Admin';
            }
        },
        mounted() {
            this.getSite();
        }
    }
</script>

<style lang="stylus" scoped>
    #site
        .editWrapper
            button
                margin-top .3rem
                margin-left .3rem
        .list
            margin .3rem
            width 100%
            .listTable
                width 100%
</style>