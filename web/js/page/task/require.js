(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var require = {
        /**
         * 加载按钮权限
         */
        loadAuthButton: function() {
            app.$data.auth_button = JSON.parse((document.getElementsByClassName('auth-button')[0] && document.getElementsByClassName('auth-button')[0].innerText) || '{}');
        },
        /**
         * 加载页面基础数据
         */
        loadBaseInfo: function() {
            bmplugin.ajax.post('/web/task/require/loadbaseinfo', {'is_show_loading': false, 'project_id': app.$data.search.project_id}).then(function(data) {
                app.$data.base_info.account = data.account;
                app.$data.base_info.needer = data.needer;
                app.$data.base_info.module = data.module;
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
                bmplugin.ajax.post('/web/task/require/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
                    app.$data.list.page.total = data.total;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 设置需求数据
         */
        initRequireInfo: function(require_info) {
            for (var objKey in require_info) {
                if (typeof app.$data.dialog.require_info[objKey] === 'undefined') {
                    continue;
                }
                if (typeof app.$data.dialog.require_info[objKey].value !== 'undefined') {
                    app.$data.dialog.require_info[objKey].value = require_info[objKey];
                } else {
                    app.$data.dialog.require_info[objKey] = require_info[objKey];
                }
            }
        },
        /**
         * 加载需求数据
         */
        loadRequireInfo: function(id, project_id) {
            //数据重置
            require.initRequireInfo(app.$data.dialog.require_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/task/require/loadrequireinfo', {id: id, project_id: project_id}).then(function(data) {
                    require.initRequireInfo(data.info);
                });
            }
        },
        /**
         * 保存需求数据
         */
        saveRequireInfo: function(requireInfo) {
            return bmplugin.ajax.post('/web/task/require/saverequireinfo', requireInfo);
        },
        /**
         * 删除需求数据
         */
        deleteRequireInfo: function(requireInfo) {
            return bmplugin.ajax.post('/web/task/require/deleterequireinfo', requireInfo);
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
                status: [
                    {key: '00', 'value': '作废'},
                    {key: '01', 'value': '需求'},
                    {key: '02', 'value': '开发'},
                    {key: '03', 'value': '就绪'},
                    {key: '04', 'value': '送测'},
                    {key: '05', 'value': '上线'}
                ],
                account: [],
                needer: [],
                module: []
            },
            /**
             * 查询条件
             */
            search: {
                project_id: bmcommonjs.getparam('project_id'),
                task_name: '',
                status: ['01', '02', '03', '04'],
                account_id: '',
                needer: '',
                module_type: '',
                module_id: ''
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
                title: '新增需求',
                visible: false,
                require_info_blank: {
                    id: '0',
                    cname: '',
                    project_id: '',
                    type: ''
                },
                require_info: {
                    id: '0',
                    cname: {
                        value: '',
                        rules: {
                            trim: {value: true},
                            required: {value: true, err: {err_msg: '请输入需求名称'}}
                        }
                    },
                    project_id: {
                        value: '',
                        rules: {
                            required: {value: true, err: {err_msg: '请设置需求项目'}}
                        }
                    },
                    type: {
                        value: '',
                        rules: {
                            required: {value: true, err: {err_msg: '请设置需求类别'}}
                        }
                    }
                }
            },
            /**
             * 需求分配
             */
            checkListAllot: {
            },
            /**
             * 需求送测
             */
            checkListQa: {
            }
        },
        watch: {
            'search.module_type': function() {
                this.search.module_id = '';
            }
        },
        created: function() {
        },
        computed: {
            search_module: function() {
                var moduleType = this.search.module_type;
                return this.base_info.module.filter(function(currentValue) {
                    switch (moduleType) {
                        case '01':
                            return currentValue['label'] == '系统';
                            break;
                        case '02':
                            return currentValue['label'] == '业务';
                            break;
                        case '':
                            return true;
                            break;
                    }
                });
            }
        },
        methods: {
            /**
             * 需求修改
             * 1.新增
             * 2.修改
             */
            showDialogRequire: function(id, project_id) {
                //title
                if (id) {
                    app.$data.dialog.title = '编辑需求';
                } else {
                    app.$data.dialog.title = '新增需求';
                }
                //获取数据
                new Promise(function(resolve) {
                    //1.加载需求信息
                    resolve(require.loadRequireInfo(id, project_id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.visible = true;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 保存需求信息
             */
            saveRequireInfo: function() {
                //1.数据检查
                var requireInfo = validator.check(app.$data.dialog.require_info);
                //2.后台请求
                if (requireInfo) {
                    new Promise(function(resolve) {
                        //1.保存需求信息
                        resolve(require.saveRequireInfo(requireInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.visible = false
                    }).then(function() {
                        //3.列表刷新
                        require.loadList();
                    }).catch(function(error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            },
            /**
             * 删除需求信息
             */
            deleteRequireInfo: function() {
                this.$confirm('确定删除此需求吗?').then(function() {
                    //1.数据检查
                    var requireInfo = {id: app.$data.dialog.require_info.id, project_id: app.$data.dialog.require_info.project_id.value};
                    //2.后台请求
                    if (requireInfo) {
                        new Promise(function(resolve) {
                            //1.保存需求信息
                            resolve(require.deleteRequireInfo(requireInfo));
                        }).then(function() {
                            //2.关闭弹框
                            app.$data.dialog.visible = false
                        }).then(function() {
                            //3.列表刷新
                            require.loadList();
                        }).catch(function(error) {
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function() {

                });
            },
            allotRequireInfo: function() {

            },
            qaRequireInfo: function() {

            },
            outputRequireInfo: function() {

            },
            /**
             * 查询条件变化
             */
            searchChange: function() {
                app.$data.list.page.page_index = 1;
                require.loadList();
            },
            /**
             * 行样式
             */
            handleRowClassName: function(row, rowIndex) {
                if (row.row.need_done_date) {
                    var needDate = new Date(row.row.need_done_date.substring(0, 10));
                    var curDate = new Date(new Date().getFullYear() + '-' + (new Date().getMonth() + 1) + '-' + new Date().getDate());
                    return curDate > needDate ? 'timeout' : '';
                }
            },
            /**
             * table格式化
             * 需求类型
             */
            formatModuleType: function(row, column, cellValue, index) {
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
             * table格式化
             * 需求状态
             */
            formatStatus: function(row, column, cellValue, index) {
                var status = {'00': '作废', '01': '需求', '02': '开发', '03': '就绪', '04': '送测', '05': '上线'};
                return status[cellValue];
            },
            /**
             * table格式化
             * 需求性质
             */
            formatXingzhi: function(row, column, cellValue, index) {
                var status = {'01': '确定', '02': '待定'};
                return status[cellValue];
            },
            /**
             * table格式化
             * 日期
             */
            formatDate: function(row, column, cellValue, index) {
                return cellValue ? cellValue.substring(0, 10) : '';
            },
            /**
             * table分页
             * 页数变化
             */
            pageIndexChange: function(curIndex) {
                if (app.$data.list.page.page_index != curIndex) {
                    app.$data.list.page.page_index = curIndex;
                    require.loadList();
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                require.loadAuthButton();
                require.loadBaseInfo();
                require.loadList();
                console.log(app);
            });
        }
    });
}(Vue, window, document));