<template>
    <div id="login">
        用户名：<input class="username" type="text" v-model="username">
        密码：<input class="password" type="password" v-model="password">
        <br />
        验证码：
        <input class="captcha" type="text" v-model="captcha">
        <div>
            <img v-show="captchaImageShow" class="captchaImage" :src="captchaImage" @load="captchaLoaded" @click="refreshCaptcha">
            <div class="captchaImageLoading" v-show="!captchaImageShow">Loading</div>
        </div>
        <br />
        <button class="submit" @click="login">登录</button>
    </div>
</template>

<script>
    export default {
        name: "Login",
        data() {
            return {
                username: "",
                password: "",
                captcha: '',
                captchaImage: "/api.php?controller=public&action=captcha",
                captchaImageShow: true
            };
        },
        methods: {
            login() {
                if(this.username.trim().length < 1){
                    alert('请输入用户名');
                    return;
                }
                if(this.password.trim().length < 1){
                    alert('请输入密码');
                    return;
                }
                if(this.captcha.trim().length < 1){
                    alert('请输入验证码');
                    return;
                }
                this.axios({
                    method: 'POST',
                    url: "/api.php?controller=admin&action=login",
                    data: {
                        username: this.username,
                        password: this.password,
                        captcha: this.captcha
                    }
                })
                    .then((response) => {
                        if(response.status !== 200){
                            this.refreshCaptcha();
                            alert('提交失败');
                            return;
                        }
                        const data = response.data;
                        if(data.code === undefined){
                            this.refreshCaptcha();
                            alert('解析失败');
                            return;
                        }
                        if(data.code !== 0){
                            this.refreshCaptcha();
                            alert(data.msg);
                            return;
                        }

                        //处理
                        this.$router.push('/admin/site');
                    })
                    .catch((error) => {
                        this.refreshCaptcha();
                        console.log(error);
                    });
            },
            refreshCaptcha() {
                this.captchaImage = "/api.php?controller=public&action=captcha&token="+Math.random();
                this.captchaImageShow = false;
            },
            captchaLoaded() {
                this.captchaImageShow = true;
            }
        }
    }
</script>

<style lang="stylus" scoped>
    #login
        *
            margin-top .3rem
            margin-left .3rem
</style>