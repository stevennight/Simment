<template>
    <div id="path">
        <div class="search">
            网站：
            <select v-model="searchSite">
                <option>请选择一个站点</option>
                <option v-for="siteOption in siteList" :value="siteOption._id['$oid']" :key="siteOption._id['$oid']">{{siteOption.site}}</option>
            </select>
            路径：
            <input type="text" v-model="searchPath">
            <button @click="search">搜索</button>
            <button @click="addSite">添加</button>
        </div>
        <div class="list">
            <table class="listTable">
                <tr>
                    <td>路径</td>
                    <td>评论数</td>
                    <td>评论开关</td>
                    <td>操作</td>
                </tr>
                <tr v-show="loading">
                    <td colspan="4">Loading...</td>
                </tr>
                <tr v-show="error">
                    <td colspan="4">{{error}}</td>
                </tr>
                <tr v-for="(path, index) in pathList" :key="path._id['$oid']">
                    <td>
                        <div>{{path.path}}</div>
                    </td>
                    <td>{{path.commentCount}}</td>
                    <td>{{path.commentSwitch?'开启':'关闭'}}</td>
                    <td>
                        <button @click="editListItem(index)">编辑</button>&nbsp;
                        <button @click="delPath(index)">删除</button>
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
        <div v-show="editShow" class="form">
            <div>id:<input v-model="editFieldId"/></div>
            <div>
                站点：
                <select v-model="editFieldSiteId">
                    <option>请选择一个站点</option>
                    <option v-for="siteOption in siteList" :value="siteOption._id['$oid']" :key="siteOption._id['$oid']">{{siteOption.site}}</option>
                </select>
            </div>
            <div>路径:<input v-model="editFieldPath"/></div>
            <div><input type="checkbox" v-model="editFieldCommentSwitch"/>开启评论</div>
            <div><button @click="editPath">提交</button> <button @click="editReset">重置</button> <button @click="editToggle(false)">关闭</button></div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "paths",
        data() {
            return {
                siteList: [],
                searchSite: '',
                searchPath: '',
                pathList: [],
                page: 1,
                loading: false,
                error: null,
                pageTimeout: null,  //跳转页码时的延迟timeout对象。用于判断是否在搜索前的延迟内。
                editShow: false, //显示、隐藏编辑div。
                editFieldId: '',
                editFieldSiteId: '',
                editFieldPath: '',
                editFieldIndex: '',
                editFieldCommentSwitch: true
            };
        },
        methods: {
            search(){
                this.page = 1;
                this.getPath();
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

                        this.siteList = data.data;
                    })
                    .catch((error) => {
                        console.log(error);
                    });
            },
            getPath(){
                if(!this.searchSite){
                    alert('请选择网站');
                    return;
                }
                this.pathList = [];
                this.loading = true;
                this.error = null;
                this.axios({
                    url: "/api.php?controller=admin&action=path",
                    params: {
                        siteId: this.searchSite,
                        search: this.searchPath,
                        page: this.page-1,
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

                        this.pathList = data.data;
                        this.loading = false;
                        this.error = null;
                    })
                    .catch((error) => {
                        console.log(error);
                        this.error = '内部错误';
                        this.loading = false;
                    });
            },
            editPath(){
                const id = this.editFieldId;
                const path = this.editFieldPath;
                const siteId = this.editFieldSiteId;
                const commentSwitch = this.editFieldCommentSwitch;
                const index = this.editFieldId?this.editFieldIndex:null;

                if(!this.editFieldSiteId){
                    alert('请选择站点');
                    return;
                }
                if(!this.editFieldPath){
                    alert('请设置路径');
                    return;
                }

                this.axios({
                    method: 'POST',
                    url: "/api.php?controller=admin&action=pathupdate",
                    data: {
                        id: id,
                        path: path,
                        siteId: siteId,
                        commentSwitch: commentSwitch
                    }
                }).then((response) => {
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

                    this.editReset();
                    this.editShow = false;
                    if(index || index === 0){
                        this.$set(this.pathList[index], 'path', path);
                        this.$set(this.pathList[index].siteId, '$oid', siteId);
                        this.$set(this.pathList[index], 'commentSwitch', commentSwitch);
                        alert('修改成功');
                    }else{
                        alert('添加成功');
                        this.searchSite = siteId;
                        this.getPath();
                    }
                }).catch((error) => {
                    console.log(error);
                    alert("内部错误");
                });
            },
            delPath(index){
                if(!confirm("请问是否确认删除？")){
                    return;
                }

                let id = this.pathList[index]._id['$oid'];
                let autoDelComment = confirm("是否清除该路径下的评论内容？")
                this.axios({
                    method: 'POST',
                    url: "/api.php?controller=admin&action=pathdel",
                    data: {
                        id: id,
                        autoDelComment: autoDelComment
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

                        this.pathList.splice(index, 1);
                    })
                    .catch((error) => {
                        console.log(error)
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
            editToggle(editShow){
                this.editShow = editShow;
            },
            editListItem(index){
                this.editFieldId = this.pathList[index]._id['$oid'];
                this.editFieldPath = this.pathList[index].path;
                this.editFieldSiteId = this.pathList[index].siteId['$oid'];
                this.editFieldCommentSwitch = this.pathList[index].commentSwitch;
                this.editFieldIndex = index;
                this.editToggle(true);
            },
            editReset(){
                this.editFieldId = this.editFieldPath = this.editFieldIndex = '';
                this.editFieldSiteId = this.searchSite; //默认重置为搜索时的站点。
                this.editFieldCommentSwitch = true;
            },
            addSite() {
                if(this.editShow) return;
                this.editReset();
                this.editShow = true;
            }
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
                    this.getPath();
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
    #path
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