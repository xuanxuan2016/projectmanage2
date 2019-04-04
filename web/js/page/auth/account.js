(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var account = {
        /**
         * 加载按钮权限
         */
        loadAuthButton: function() {
            app.$data.auth_button = JSON.parse((document.getElementsByClassName('auth-button')[0] && document.getElementsByClassName('auth-button')[0].innerText) || '{}');
        },
        /**
         * 加载页面基础数据
         * 1.下拉框数据
         */
        loadBaseInfo: function() {
            bmplugin.ajax.post('/web/auth/account/loadbaseinfo', {'is_show_loading': false}).then(function(data) {
                app.$data.base_info.roles = data.roles;
            }).catch(function(error) {
                bmplugin.showErrMsg(error);
            });
        },
        /**
         * 加载列表数据
         */
        loadList: function() {
            //1.数据检查
            var listInfo = app.$data.list.page;
            listInfo.search_param = JSON.stringify(app.$data.search);
            //2.后台请求
            if (listInfo) {
                bmplugin.ajax.post('/web/auth/account/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
                    app.$data.list.page.total = data.total;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 设置账号数据
         */
        initAccountInfo: function(account_info) {
            for (var objKey in account_info) {
                if (typeof app.$data.dialog.account_info[objKey] === 'undefined') {
                    continue;
                }
                if (typeof app.$data.dialog.account_info[objKey].value !== 'undefined') {
                    app.$data.dialog.account_info[objKey].value = account_info[objKey];
                } else {
                    app.$data.dialog.account_info[objKey] = account_info[objKey];
                }
            }
        },
        /**
         * 加载账号数据
         */
        loadAccountInfo: function(id) {
            //数据重置
            account.initAccountInfo(app.$data.dialog.account_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/auth/account/loadaccountinfo', {id: id}).then(function(data) {
                    account.initAccountInfo(data.info);
                });
            }
        },
        /**
         * 保存账号数据
         */
        saveAccountInfo: function(accountInfo) {
            return bmplugin.ajax.post('/web/auth/account/saveaccountinfo', accountInfo);
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
                role_id: '',
                status: '01',
                is_can_search: '1',
                cname: ''
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
                title: '新增账号',
                visible: false,
                account_info_blank: {
                    id: '0',
                    cname: '',
                    username: '',
                    password: '',
                    status: '01',
                    role_id: '',
                    is_can_search: '1'
                },
                account_info: {
                    id: '0',
                    cname: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入用户昵称'}}
                        }
                    },
                    username: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入账号名称'}}
                        }
                    },
                    password: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入账号密码'}}
                        }
                    },
                    status: '01',
                    role_id: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请选择账号角色'}}
                        }
                    },
                    is_can_search: '1'
                }
            }
        },
        watch: {
            'dialog.account_info.id': function(val) {
                app.$data.dialog.account_info.password.rules.required.value = val == '0';
            }
        },
        created: function() {
        },
        methods: {
            /**
             * 账号修改
             * 1.新增
             * 2.修改
             */
            showDialogAccount: function(id) {
                //title
                if (id) {
                    app.$data.dialog.title = '编辑账号';
                } else {
                    app.$data.dialog.title = '新增账号';
                }
                //获取数据
                new Promise(function(resolve) {
                    //1.加载账号信息
                    resolve(account.loadAccountInfo(id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.visible = true;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 保存账号信息
             */
            saveAccountInfo: function() {
                //1.数据检查
                var accountInfo = validator.check(app.$data.dialog.account_info);
                //2.后台请求
                if (accountInfo) {
                    new Promise(function(resolve) {
                        //1.保存账号信息
                        resolve(account.saveAccountInfo(accountInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.visible = false
                    }).then(function() {
                        //3.列表刷新
                        account.loadList();
                    }).catch(function(error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            },
            /**
             * 查询条件变化
             */
            searchChange: function() {
                app.$data.list.page.page_index = 1;
                account.loadList();
            },
            /**
             * table格式化
             * 账号是否可查询
             */
            formatIsCanSearch: function(row, column, cellValue, index) {
                return cellValue == 1 ? '是' : '否';
            },
            /**
             * table格式化
             * 账号是否有效
             */
            formatStatus: function(row, column, cellValue, index) {
                return cellValue == '01' ? '有效' : '无效';
            },
            /**
             * table分页
             * 页数变化
             */
            pageIndexChange: function(curIndex) {
                if (app.$data.list.page.page_index != curIndex) {
                    app.$data.list.page.page_index = curIndex;
                    account.loadList();
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                account.loadAuthButton();
                account.loadBaseInfo();
                account.loadList();
            });
        }
    });
}(Vue, window, document));
