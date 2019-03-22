(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var onlineerror = {
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
            bmplugin.ajax.post('/web/task/onlineerror/loadbaseinfo', {'is_show_loading': false}).then(function(data) {
                app.$data.base_info.project = data.project;
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
                bmplugin.ajax.post('/web/task/onlineerror/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
                    app.$data.list.page.total = data.total;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 设置弹框数据
         */
        initDialogInfo: function(dialogName, dialogInfo) {
            for (var objKey in dialogInfo) {
                if (typeof app.$data.dialog[dialogName][dialogName + '_info'][objKey] === 'undefined') {
                    continue;
                }
                if (typeof app.$data.dialog[dialogName][dialogName + '_info'][objKey].value !== 'undefined') {
                    app.$data.dialog[dialogName][dialogName + '_info'][objKey].value = dialogInfo[objKey];
                } else {
                    app.$data.dialog[dialogName][dialogName + '_info'][objKey] = dialogInfo[objKey];
                }
            }
        },
        /**
         * 加载上线问题数据
         */
        loadOnlineErrorInfo: function(id) {
            //数据重置
            onlineerror.initDialogInfo('onlineerror', app.$data.dialog.onlineerror.onlineerror_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/task/onlineerror/loadonlineerrorinfo', {id: id}).then(function(data) {
                    onlineerror.initDialogInfo('onlineerror', data.info);
                });
            }
        },
        /**
         * 编辑上线问题
         */
        editOnlineErrorInfo: function(onlineerrorInfo) {
            onlineerrorInfo = Object.assign(onlineerrorInfo);
            return bmplugin.ajax.post('/web/task/onlineerror/editonlineerrorinfo', onlineerrorInfo);
        }
    };

    /**
     * 其它组件
     */
    var plugin = {
        quilleditor: {
            error_desc: null,
            error_solve: null,
            error_summary: null
        },
        /**
         * 文本编辑器
         */
        initCKEditor: function(type) {
            //删除并重建组件，解决element-ui重复打开不能编辑的问题
            if (CKEDITOR.instances[type]) {
                CKEDITOR.remove(CKEDITOR.instances[type]);
            }
            if (document.querySelector('#cke_' + type)) {
                document.querySelector('#cke_' + type).remove();
            }
            plugin.quilleditor[type] = CKEDITOR.replace(type);
            plugin.quilleditor[type].on('change', function() {
                app.$data.dialog.onlineerror.onlineerror_info[type].value = plugin.quilleditor[type].getData();
            });
            plugin.quilleditor[type].on('instanceReady', function() {
                //https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_editor.html#property-status
                //放在外部会报setReadOnly不存在，放在实例初始化完成后执行
                plugin.quilleditor[type].setData(app.$data.dialog.onlineerror.onlineerror_info[type].value);
            });
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
             * 账号角色
             */
            auth_role: 'admin',
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
                keyword: ''
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
                onlineerror: {
                    title: '新增上线问题',
                    visible: false,
                    tab_index: '0',
                    onlineerror_info_blank: {
                        id: '0',
                        title: '',
                        project_id: '',
                        error_desc: '',
                        error_solve: '',
                        error_summary: ''
                    },
                    onlineerror_info: {
                        id: '0',
                        title: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入问题标题'}}
                            }
                        },
                        project_id: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请选择所属项目'}}
                            }
                        },
                        error_desc: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入问题描述'}}
                            }
                        },
                        error_solve: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入解决方法'}}
                            }
                        },
                        error_summary: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入问题总结'}}
                            }
                        }
                    }
                }
            }
        },
        watch: {
        },
        created: function() {
        },
        computed: {
        },
        methods: {
            /**
             * 上线问题修改(新增，修改，完成，作废)
             */
            showDialogOnlineError: function(id) {
                //title
                if (id) {
                    app.$data.dialog.onlineerror.title = '编辑上线问题';
                } else {
                    app.$data.dialog.onlineerror.title = '新增上线问题';
                }
                //获取数据
                new Promise(function(resolve) {
                    //1.加载上线问题信息
                    resolve(onlineerror.loadOnlineErrorInfo(id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.onlineerror.visible = true;
                }).then(function() {
                    //onlineerror.initDialogInfo时包含被watch的属性，会触发vue的更新，所以需要在$nextTick中执行
                    //否则，第一次弹框不能被渲染
                    app.$nextTick(function() {
                        //3.editor需要在dialog生成后才能创建
                        plugin.initCKEditor('error_desc');
                        plugin.initCKEditor('error_solve');
                        plugin.initCKEditor('error_summary');
                    });
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 保存上线问题
             */
            editOnlineErrorInfo: function() {
                //1.数据检查
                var onlineerrorInfo = validator.check(app.$data.dialog.onlineerror.onlineerror_info);
                //2.后台请求
                if (onlineerrorInfo) {
                    new Promise(function(resolve) {
                        //1.保存上线问题信息
                        resolve(onlineerror.editOnlineErrorInfo(onlineerrorInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.onlineerror.visible = false
                    }).then(function() {
                        //3.列表刷新
                        onlineerror.loadList();
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
                onlineerror.loadList();
            },
            /**
             * 行样式
             */
            handleRowClassName: function(row, rowIndex) {
                return row.row.is_timeout == '1' ? 'timeout' : '';
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
                    onlineerror.loadList();
                }
            },
            /**
             * table分页
             * 每页条数变化
             */
            pageSizeChange: function() {
                app.$data.list.page.page_index = 1;
                onlineerror.loadList();
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                onlineerror.loadAuthButton();
                onlineerror.loadBaseInfo();
                onlineerror.loadList();
            });
        }
    });
}(Vue, window, document));