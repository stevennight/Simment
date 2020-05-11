<template>
    <div v-if="init" id="admin">
        <div class="body">
            <div class="menu">
                <div class="site"><router-link tag="span" to="/admin/site">站点管理</router-link></div>
                <div class="path"><router-link tag="span" to="/admin/path">站点路径</router-link> </div>
                <div class="comment"><router-link tag="span" to="/admin/comment">评论管理</router-link></div>
                <div class="logout" @click="logout">登出</div>
            </div>
            <div class="content">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Admin",
        data() {
            return {
                init: false
            };
        },
        methods: {
            logout() {
                this.axios({
                    method: "POST",
                    url: "/api.php?controller=admin&action=logout"
                })
                    .then((response) => {
                        if(response.status !== 200){
                            alert('通信故障');
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

                        this.$router.push('/admin/login');
                    })
                    .catch((error) => {
                        console.log(error)
                    });
            },
            async checkLogin(){
                this.axios({
                    method: "GET",
                    url: "/api.php?controller=admin&action=checklogin"
                }).then((response) => {
                    if(response.status !== 200){
                        alert('通信故障');
                        return;
                    }
                    if(response.data){
                        alert('请先登录');
                        this.$router.replace('/admin/login');
                        return;
                    }

                    this.init = true;
                });
            }
        },
        created() {
            this.checkLogin();
        }
    }
</script>

<style lang="stylus" scoped>
    #admin
        position fixed
        height 100%
        width 100%
        .body
            display flex
            height 100%
            .menu
                display flex
                flex-direction column
                justify-content center
                height 100%
                padding .8rem
                width 8rem
                cursor pointer
                user-select none
                border-right 1px solid #ccc
                div
                    line-height 5rem
            .content
                flex 1
                min-width 0
                overflow auto
                padding 1rem
</style>