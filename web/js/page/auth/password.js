(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var password = {
        /**
         * 加载按钮权限
         */
        loadAuthButton: function() {
            app.$data.auth_button = JSON.parse((document.getElementsByClassName('auth-button')[0] && document.getElementsByClassName('auth-button')[0].innerText) || '{}');
        },
        /**
         * 保存密码数据
         */
        savePasswordInfo: function(passwordInfo) {
            return bmplugin.ajax.post('/web/auth/password/savepasswordinfo', passwordInfo);
        }
    };

    /**
     * vue对象
     */
    var app = new Vue({
        el: '#app',
        data: {
            /**
             * 按钮权限
             */
            auth_button: {
            },
            /**
             * 页面基础信息
             */
            base_info: {
                roles: []
            },
            /**
             * 查询条件
             */
            search: {
                status: '01',
                is_can_search: '1'
            },
            /**
             * table信息
             */
            list: {
                data: [],
                page: {
                    total: 0,
                    page_size: 10,
                    page_index: 1
                }
            },
            /**
             * 弹框
             */
            dialog: {
                title: '新增密码',
                visible: false,
                password_info_blank: {
                },
                password_info: {
                    new_pwd1: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入新密码'}}
                        }
                    },
                    new_pwd2: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请再次输入新密码'}}
                        }
                    },
                }
            }
        },
        watch: {
        },
        created: function() {
        },
        methods: {
            /**
             * 保存密码信息
             */
            savePasswordInfo: function() {
                //1.数据检查
                var passwordInfo = validator.check(app.$data.dialog.password_info);
                //2.后台请求
                if (passwordInfo) {
                    new Promise(function(resolve, reject) {
                        //1.密码是否一致
                        if (passwordInfo.new_pwd1 != passwordInfo.new_pwd2) {
                            reject(new Error('2次输入的密码不一致'));
                        } else {
                            resolve();
                        }
                    }).then(function() {
                        //2.保存密码信息
                        return password.savePasswordInfo(passwordInfo);
                    }).then(function() {
                        //3.重置信息
                        app.$data.dialog.password_info.new_pwd1.value = '';
                        app.$data.dialog.password_info.new_pwd2.value = '';
                        bmplugin.showErrMsg('密码修改成功');
                    }).catch(function(error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                password.loadAuthButton();
            });
        }
    });
}(Vue, window, document));
