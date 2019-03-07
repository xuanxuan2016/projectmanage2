(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var module = {
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
            bmplugin.ajax.post('/web/project/module/loadbaseinfo', {'is_show_loading': false}).then(function(data) {
                app.$data.base_info.project = data.project;
                if (data.project.length > 0) {
                    app.$data.search.project_id = data.project[0]['id'];
                    module.loadList();
                }
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
            listInfo.project_id = app.$data.search.project_id;
            //2.后台请求
            if (listInfo) {
                bmplugin.ajax.post('/web/project/module/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
                    app.$data.list.page.total = data.total;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 设置模块数据
         */
        initModuleInfo: function(module_info) {
            for (var objKey in module_info) {
                if (typeof app.$data.dialog.module_info[objKey] === 'undefined') {
                    continue;
                }
                if (typeof app.$data.dialog.module_info[objKey].value !== 'undefined') {
                    app.$data.dialog.module_info[objKey].value = module_info[objKey];
                } else {
                    app.$data.dialog.module_info[objKey] = module_info[objKey];
                }
            }
        },
        /**
         * 加载模块数据
         */
        loadModuleInfo: function(id, project_id) {
            //数据重置
            module.initModuleInfo(app.$data.dialog.module_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/project/module/loadmoduleinfo', {id: id, project_id: project_id}).then(function(data) {
                    module.initModuleInfo(data.info);
                });
            }
        },
        /**
         * 保存模块数据
         */
        saveModuleInfo: function(moduleInfo) {
            return bmplugin.ajax.post('/web/project/module/savemoduleinfo', moduleInfo);
        },
        /**
         * 删除模块数据
         */
        deleteModuleInfo: function(moduleInfo) {
            return bmplugin.ajax.post('/web/project/module/deletemoduleinfo', moduleInfo);
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
                project: []
            },
            /**
             * 查询条件
             */
            search: {
                project_id: '',
                type: ''
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
                title: '新增模块',
                visible: false,
                module_info_blank: {
                    id: '0',
                    cname: '',
                    project_id: '',
                    type: ''
                },
                module_info: {
                    id: '0',
                    cname: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入模块名称'}}
                        }
                    },
                    project_id: {
                        value: '',
                        rules: {
                            required: {value: true, err: {err_msg: '请设置模块项目'}}
                        }
                    },
                    type: {
                        value: '',
                        rules: {
                            required: {value: true, err: {err_msg: '请设置模块类别'}}
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
             * 模块修改
             * 1.新增
             * 2.修改
             */
            showDialogModule: function(id, project_id) {
                //title
                if (id) {
                    app.$data.dialog.title = '编辑模块';
                } else {
                    app.$data.dialog.title = '新增模块';
                }
                //获取数据
                new Promise(function(resolve) {
                    //1.加载模块信息
                    resolve(module.loadModuleInfo(id, project_id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.visible = true;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 保存模块信息
             */
            saveModuleInfo: function() {
                //1.数据检查
                var moduleInfo = validator.check(app.$data.dialog.module_info);
                //2.后台请求
                if (moduleInfo) {
                    new Promise(function(resolve) {
                        //1.保存模块信息
                        resolve(module.saveModuleInfo(moduleInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.visible = false
                    }).then(function() {
                        //3.列表刷新
                        module.loadList();
                    }).catch(function(error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            },
            /**
             * 删除模块信息
             */
            deleteModuleInfo: function() {
                this.$confirm('确定删除此模块吗?', {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function() {
                    //1.数据检查
                    var moduleInfo = {id: app.$data.dialog.module_info.id, project_id: app.$data.dialog.module_info.project_id.value};
                    //2.后台请求
                    if (moduleInfo) {
                        new Promise(function(resolve) {
                            //1.保存模块信息
                            resolve(module.deleteModuleInfo(moduleInfo));
                        }).then(function() {
                            //2.关闭弹框
                            app.$data.dialog.visible = false
                        }).then(function() {
                            //3.列表刷新
                            module.loadList();
                        }).catch(function(error) {
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function() {

                });
            },
            /**
             * 查询条件变化
             */
            searchChange: function() {
                app.$data.list.page.page_index = 1;
                module.loadList();
            },
            /**
             * table格式化
             * 模块类型
             */
            formatType: function(row, column, cellValue, index) {
                switch (cellValue) {
                    case '01':
                        return '系统';
                        break;
                    case '02':
                        return '业务';
                        break;
                    default:
                        return cellValue;
                        break;
                }
            },
            /**
             * table分页
             * 页数变化
             */
            pageIndexChange: function(curIndex) {
                if (app.$data.list.page.page_index != curIndex) {
                    app.$data.list.page.page_index = curIndex;
                    module.loadList();
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                module.loadAuthButton();
                module.loadBaseInfo();
                //module.loadList();
            });
        }
    });
}(Vue, window, document));
