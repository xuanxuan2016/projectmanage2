(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var interface = {
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
                bmplugin.ajax.post('/web/auth/interface/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
                    app.$data.list.page.total = data.total;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 设置权限数据
         */
        initInterfaceInfo: function(interface_info) {
            for (var objKey in interface_info) {
                if (typeof app.$data.dialog.interface_info[objKey].value !== 'undefined') {
                    app.$data.dialog.interface_info[objKey].value = interface_info[objKey];
                } else {
                    app.$data.dialog.interface_info[objKey] = interface_info[objKey];
                }
            }
        },
        /**
         * 加载权限数据
         */
        loadInterfaceInfo: function(id) {
            //数据重置
            interface.initInterfaceInfo(app.$data.dialog.interface_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/auth/interface/loadinterfaceinfo', {id: id}).then(function(data) {
                    interface.initInterfaceInfo(data.info);
                });
            }
        },
        /**
         * 保存权限数据
         */
        saveInterfaceInfo: function(interfaceInfo) {
            return bmplugin.ajax.post('/web/auth/interface/saveinterfaceinfo', interfaceInfo);
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
                itype: ''
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
                title: '新增权限',
                visible: false,
                interface_info_blank: {
                    id: '0',
                    cname: '',
                    code: '',
                    icode: '',
                    itype: '0',
                    url: '',
                    icon: '',
                    status: '01'
                },
                interface_info: {
                    id: '',
                    cname: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入权限名称'}}
                        }
                    },
                    code: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入权限code'}}
                        }
                    },
                    icode: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入权限key'}}
                        }
                    },
                    itype: '',
                    url: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: false, err: {err_msg: '请输入页面地址'}}
                        }
                    },
                    icon: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入页面图标'}}
                        }
                    },
                    status: ''
                }
            }
        },
        watch: {
            'dialog.interface_info.itype': function(val) {
                switch (val) {
                    case '0':
                        app.$data.dialog.interface_info.icon.rules.required.value = true;
                        app.$data.dialog.interface_info.url.rules.required.value = false;
                        break;
                    case '1':
                        app.$data.dialog.interface_info.icon.rules.required.value = false;
                        app.$data.dialog.interface_info.url.rules.required.value = true;
                        break;
                    case '2':
                        app.$data.dialog.interface_info.icon.rules.required.value = false;
                        app.$data.dialog.interface_info.url.rules.required.value = false;
                        break;
                }
            }
        },
        created: function() {
        },
        methods: {
            /**
             * 权限修改
             * 1.新增
             * 2.修改
             */
            showDialogInterface: function(id) {
                //title
                if (id) {
                    app.$data.dialog.title = '编辑权限';
                } else {
                    app.$data.dialog.title = '新增权限';
                }
                //获取数据
                new Promise(function(resolve) {
                    //1.加载权限信息
                    resolve(interface.loadInterfaceInfo(id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.visible = true;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 保存权限信息
             */
            saveInterfaceInfo: function() {
                //1.数据检查
                var interfaceInfo = validator.check(app.$data.dialog.interface_info);
                //2.后台请求
                if (interfaceInfo) {
                    new Promise(function(resolve) {
                        //1.保存权限信息
                        resolve(interface.saveInterfaceInfo(interfaceInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.visible = false
                    }).then(function() {
                        //3.列表刷新
                        interface.loadList();
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
                interface.loadList();
            },
            /**
             * table格式化
             * 权限类别
             */
            formatIType: function(row, column, cellValue, index) {
                switch (cellValue) {
                    case '0':
                        return '模块';
                        break;
                    case '1':
                        return '页面';
                        break;
                    case '2':
                        return '按钮';
                        break;
                    default:
                        return cellValue;
                        break;
                }
            },
            /**
             * table格式化
             * 是否有效
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
                    interface.loadList();
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                interface.loadAuthButton();
                interface.loadBaseInfo();
                interface.loadList();
            });
        }
    });
}(Vue, window, document));
