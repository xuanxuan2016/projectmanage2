(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var project = {
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
            bmplugin.ajax.post('/web/project/project/loadbaseinfo', {'is_show_loading': false}).then(function(data) {
                app.$data.base_info.person = data.person;
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
                bmplugin.ajax.post('/web/project/project/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
                    app.$data.list.page.total = data.total;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 设置项目数据
         */
        initProjectInfo: function(project_info) {
            for (var objKey in project_info) {
                if (typeof app.$data.dialog.project_info[objKey] === 'undefined') {
                    continue;
                }
                if (typeof app.$data.dialog.project_info[objKey].value !== 'undefined') {
                    app.$data.dialog.project_info[objKey].value = project_info[objKey];
                } else {
                    app.$data.dialog.project_info[objKey] = project_info[objKey];
                }
            }
        },
        /**
         * 加载项目数据
         */
        loadProjectInfo: function(id) {
            //数据重置
            project.initProjectInfo(app.$data.dialog.project_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/project/project/loadprojectinfo', {id: id}).then(function(data) {
                    project.initProjectInfo(data.info);
                });
            }
        },
        /**
         * 保存项目数据
         */
        saveProjectInfo: function(projectInfo) {
            return bmplugin.ajax.post('/web/project/project/saveprojectinfo', projectInfo);
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
                person: [{
                        id: 0,
                        label: '人员',
                        children: [{
                                id: 1,
                                label: '系统管理员'
                            }, {
                                id: 2,
                                label: '开发'
                            }]
                    }]
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
                title: '新增项目',
                visible: false,
                project_info_blank: {
                    id: '0',
                    cname: '',
                    status: '01',
                    person: []
                },
                project_info: {
                    id: '0',
                    cname: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入项目名称'}}
                        }
                    },
                    status: '01',
                    person: {
                        value: [],
                        rules: {
                            required: {value: true, err: {err_msg: '请设置项目人员'}}
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
                app.$data.dialog.project_info.person.value = app.$refs.tree.getCheckedKeys();
            },
            /**
             * 项目修改
             * 1.新增
             * 2.修改
             */
            showDialogProject: function(id) {
                //title
                if (id) {
                    app.$data.dialog.title = '编辑项目';
                } else {
                    app.$data.dialog.title = '新增项目';
                }
                //获取数据
                new Promise(function(resolve) {
                    //1.加载项目信息
                    resolve(project.loadProjectInfo(id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.visible = true;
                }).then(function() {
                    //3.设置tree选中，第一次要dialog显示，组件才会渲染
                    app.$refs.tree.setCheckedKeys(app.$data.dialog.project_info.person.value);
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 保存项目信息
             */
            saveProjectInfo: function() {
                //1.数据检查
                var projectInfo = validator.check(app.$data.dialog.project_info);
                projectInfo['person'] = JSON.stringify(projectInfo['person']);
                //2.后台请求
                if (projectInfo) {
                    new Promise(function(resolve) {
                        //1.保存项目信息
                        resolve(project.saveProjectInfo(projectInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.visible = false
                    }).then(function() {
                        //3.列表刷新
                        project.loadList();
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
                project.loadList();
            },
            /**
             * table格式化
             * 项目是否有效
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
                    project.loadList();
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                project.loadAuthButton();
                project.loadBaseInfo();
                project.loadList();
            });
        }
    });
}(Vue, window, document));
