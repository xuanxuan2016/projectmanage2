(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var login = {
        /**
         * 登录
         */
        login: function() {
            //1.数据检查
            var loginInfo = validator.check(app.logininfo);
            //2.后台请求
            if (loginInfo) {
                bmplugin.ajax.post('/web/common/login/login', Object.assign(loginInfo, {})).then(function(data) {
                    window.location.href = bmcommonjs.getparam('redirect') || '/web/common/home';
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        }
    };

    /**
     * vue对象
     */
    var app = new Vue({
        el: '#app',
        data: {
            logininfo: {
                username: {
                    value: '',
                    rules: {
                        trim: {value: true},
                        required: {value: true, err: {err_msg: '请输入用户名'}}
                    }
                },
                password: {
                    value: '',
                    rules: {
                        trim: {value: true},
                        required: {value: true, err: {err_msg: '请输入密码'}}
                    }
                }
            }
        },
        created: function() {
        },
        methods: {
            login: function() {
                login.login();
            }
        },
        mounted: function() {
            this.$nextTick(function() {
            });
        }
    });

}(Vue, window, document));


