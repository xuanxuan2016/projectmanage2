(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var role = {
        /**
         * 加载按钮权限
         */
        loadAuthButton: function() {
            app.$data.auth_button = JSON.parse((document.getElementsByClassName('auth-button')[0] && document.getElementsByClassName('auth-button')[0].innerText) || '{}');
        },
        /**
         * 加载页面基础数据
         * 1.权限数据
         */
        loadBaseInfo: function() {
            bmplugin.ajax.post('/web/auth/role/loadbaseinfo', {'is_show_loading': false}).then(function(data) {
                app.$data.base_info.interface = data.interface;
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
                bmplugin.ajax.post('/web/auth/role/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
                    app.$data.list.page.total = data.total;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 设置角色数据
         */
        initRoleInfo: function(role_info) {
            for (var objKey in role_info) {
                if (typeof app.$data.dialog.role_info[objKey] === 'undefined') {
                    continue;
                }
                if (typeof app.$data.dialog.role_info[objKey].value !== 'undefined') {
                    app.$data.dialog.role_info[objKey].value = role_info[objKey];
                } else {
                    app.$data.dialog.role_info[objKey] = role_info[objKey];
                }
            }
        },
        /**
         * 加载角色数据
         */
        loadRoleInfo: function(id) {
            //数据重置
            role.initRoleInfo(app.$data.dialog.role_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/auth/role/loadroleinfo', {id: id}).then(function(data) {
                    role.initRoleInfo(data.info);
                });
            }
        },
        /**
         * 保存角色数据
         */
        saveRoleInfo: function(roleInfo) {
            return bmplugin.ajax.post('/web/auth/role/saveroleinfo', roleInfo);
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
                interface: []
            },
            /**
             * 查询条件
             */
            search: {
                status: '01'
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
                title: '新增角色',
                visible: false,
                role_info_blank: {
                    id: '0',
                    cname: '',
                    status: '01',
                    auth: []
                },
                role_info: {
                    id: '0',
                    cname: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入角色名称'}}
                        }
                    },
                    status: '01',
                    auth: {
                        value: [],
                        rules: {
                            required: {value: true, err: {err_msg: '请设置权限点'}}
                        }
                    }
                }
            }
        },
        watch: {
        },
        created: function() {
        },
        methods: {
            /**
             * 选择权限点
             */
            treeCheck: function() {
                app.$data.dialog.role_info.auth.value = [].concat(app.$refs.tree.getCheckedKeys(), app.$refs.tree.getHalfCheckedKeys());
            },
            /**
             * 角色修改
             * 1.新增
             * 2.修改
             */
            showDialogRole: function(id) {
                //title
                if (id) {
                    app.$data.dialog.title = '编辑角色';
                } else {
                    app.$data.dialog.title = '新增角色';
                }
                //获取数据
                new Promise(function(resolve) {
                    //1.加载角色信息
                    resolve(role.loadRoleInfo(id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.visible = true;
                }).then(function() {
                    //3.设置tree选中，第一次要dialog显示，组件才会渲染
                    app.$refs.tree.setCheckedKeys(app.$data.dialog.role_info.auth.value, false);
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 保存角色信息
             */
            saveRoleInfo: function() {
                //1.数据检查
                var roleInfo = validator.check(app.$data.dialog.role_info);
                roleInfo['auth'] = JSON.stringify(roleInfo['auth']);
                //2.后台请求
                if (roleInfo) {
                    new Promise(function(resolve) {
                        //1.保存角色信息
                        resolve(role.saveRoleInfo(roleInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.visible = false
                    }).then(function() {
                        //3.列表刷新
                        role.loadList();
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
                role.loadList();
            },
            /**
             * table格式化
             * 角色是否有效
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
                    role.loadList();
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                role.loadAuthButton();
                role.loadBaseInfo();
                role.loadList();
            });
        }
    });
}(Vue, window, document));
