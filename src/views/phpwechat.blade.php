<!DOCTYPE HTML>
<html xmlns:v-on="http://www.w3.org/1999/xhtml">
<head>
    <meta name="_token" content="{{ csrf_token() }}"/>
    <meta charset="UTF-8">
    <title>登录</title>
    <link href="{{ asset('css/common/header-white.css') }}" rel="stylesheet">
    <link href="{{ asset('css/auth/login.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/toastr/toastr.min.css') }}" rel="stylesheet">
</head>
<body>
<div class="ehs-header">
    <div class="ehs-nav"><a href="{{ url('mall') }}"><div class="ehs-logo"></div></a></div>
</div>
<div class="container">
    <div class="title">
        登录
    </div>
    <div class="subtitle">
        登录已经注册的账号
    </div>
    <div class="login-form">
        <form id="login" method="POST" action="{{ route('login') }}" onsubmit="return submit_sure()">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="phone" class="label-phone"></label>
                <input id="phone" name="phone" type="text" v-model="loginphone" class="input-phone" value="{{ old('phone') }}" placeholder="请输入手机号" required autofocus>
                <input name="login_type" type="hidden" :value="login_type">
            </div>
            <div class="form-group">
                <label for="password" class="label-password"></label>
                <input id="password" name="password" type="password" :placeholder="tip_text" class="input-password" >
                <!-- 显示这个按钮的位置时, form-group-forget 需要 margin-top:-38px; -->
                <!-- forget-tab 和 smslogin-tab 需要 display: none; -->
                <button type="button" class="btn-send-sms" :style="{display:captcha_display}" :disabled="disabled" v-on:click="get_captcha()">@{{ text }} </button>
            </div>
            <div class="form-group-forget" :style="{'margin-top':size+'px'}">
                <div class="forget-tab" :style="{display:forget_display}"><a href="{{ route('reset') }}"> 忘记密码? </a></div>
                <div class="smslogin-tab" :style="{display:phone_login_display}"><a v-on:click="login_by_phone()"> 手机动态登录 </a></div>

                <div class="password-tab" :style="{display:password_login_display}"><a v-on:click="login_by_password()"> 密码登录 </a></div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-login">登录</button>
            </div>

            <a href="{{ route('wechat_login') }}" class="btn-wechat-login-a">
                <div class="form-group btn-wechat-login">使用微信登陆</div>
            </a>
        </form>
        <div class="register-form">
            <label class="register-info">你目前还没有拥有账号?</label>
            <a class="btn-register" href="{{ route('register') }}">注册</a>
        </div>
        <div id="login_container"></div>

    </div>
</div>
@include('simple.success')
@include('simple.errors')
<script src="{{ asset('libs/toastr/jquery.min.js') }}"></script>
<script src="{{ asset('libs/vue/vue.min.js') }}"></script>
<script src="{{ asset('libs/toastr/toastr.min.js') }}"></script>
<script src="http://res.wx.qq.com/connect/zh_CN/htmledition/js/wxLogin.js"></script>

<script>
    var token = $('meta[name="_token"]').attr('content');
    var login = new Vue({
        el:"#login",
        data:{
            login_type:0,
            loginphone:"{{ old('phone') }}",
            disabled:false,
            tip_text:"请输入密码",
            text:"获取验证码",
            countdown:20,
            captcha_display:'none',
            forget_display:"inline",
            phone_login_display:"inline",
            password_login_display:"none",
            size:20
        },
        methods:{
            login_by_phone:function(event){
                login.login_type = 1;
                login.captcha_display = "inline";
                login.forget_display = 'none';
                login.phone_login_display = 'none';
                login.password_login_display = 'block';
                login.size = -38;
                login.tip_text = "请输入验证码";
            },
            login_by_password:function(event){
                login.login_type = 0;
                login.captcha_display = "none";
                login.forget_display = 'inline';
                login.phone_login_display = 'inline';
                login.password_login_display = 'none';
                login.size = 20;
                login.tip_text = "请输入密码";
            },
            get_captcha: function (event) {
                $.ajax({
                    url: "{{ url('mall/api/sendmessage') }}",
                    dataType: "JSON",
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    type: "post",
                    data: {'phone': login.loginphone},
                    success: function (data) {
                        if (data.status) {
                            toastr.success(data.info, '', {
                                timeOut: '1800',
                                positionClass: 'toast-top-center'
                            });
                        } else {
                            toastr.error(data.info, '', {
                                timeOut: '2000',
                                positionClass: 'toast-top-center'
                            });
                        }
                        interval = setInterval(function () {
                            if (login.countdown == 0) {
                                login.text = "获取验证码";
                                login.countdown = 20;
                                login.disabled = false;
                                clearInterval(interval);
                            } else {
                                login.disabled = true;
                                login.text = "重新发送(" + login.countdown + ")";
                                login.countdown--;
                            }
                        }, 1000);
                    }
                });
            },
        }
    });
    var obj = new WxLogin({
        self_redirect:false,
        id:"login_container",
        appid: "wx4813eaa597b9b558",
        scope: "snsapi_login",
        redirect_uri: "http://localhost:8000/mall/auth/login/wechat/normal",
        state: "STATE",
        style: "black",
        href: ""
    });
</script>
<script>
    function submit_sure(){
        var bool = false;
        $.ajax({
            url: "{{ route('login/validate') }}",
            dataType: "JSON",
            async:false,
            headers: {
                'X-CSRF-TOKEN': token
            },
            type: "post",
            data: $('#login').serialize(),
            success: function (data) {
                if (data.status) {
                    bool = true;
                    document.getElementById('login').submit();
                } else {
                    toastr.error(data.info, '', {
                        timeOut: '2000',
                        positionClass: 'toast-top-center'
                    });
                    bool = false;
                }
            }
        });
        return bool;
    }
</script>
</body>
</html>
</DOCTYPE>
