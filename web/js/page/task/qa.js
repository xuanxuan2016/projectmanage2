(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var qa = {
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
                bmplugin.ajax.post('/web/task/qa/loadlist', listInfo).then(function(data) {
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
         * 设置送测数据
         */
        initQaInfo: function(qa_info) {
            for (var objKey in qa_info) {
                if (typeof app.$data.dialog.qa_info[objKey] === 'undefined') {
                    continue;
                }
                if (typeof app.$data.dialog.qa_info[objKey].value !== 'undefined') {
                    app.$data.dialog.qa_info[objKey].value = qa_info[objKey];
                } else {
                    app.$data.dialog.qa_info[objKey] = qa_info[objKey];
                }
            }
        },
        /**
         * 加载送测数据
         */
        loadQaInfo: function(id, task_id) {
        },
        /**
         * 送测
         */
        qaQaInfo: function(qaInfo) {
            qaInfo = Object.assign(qaInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/qa/qaqainfo', qaInfo);
        },
        /**
         * 上线
         */
        onlineQaInfo: function(qaInfo) {
            qaInfo = Object.assign(qaInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/qa/onlineqainfo', qaInfo);
        },
        /**
         * 撤销
         */
        revokeQaInfo: function(qaInfo) {
            qaInfo = Object.assign(qaInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/qa/revokeqainfo', qaInfo);
        },
        /**
         * 下载
         */
        downQaInfo: function(qaInfo) {
            qaInfo = Object.assign(qaInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/qa/downqainfo', qaInfo);
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
            },
            /**
             * 查询条件
             */
            search: {
                project_id: bmcommonjs.getparam('project_id'),
                qa_name: ''
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
                online: {
                    title: '需求上线',
                    visible: false,
                    online_info_blank: {
                        bug_count: '',
                        account_name: '',
                        needer_name: '',
                        account_summary: '',
                        needer_summary: ''
                    },
                    online_info: {
                        bug_count: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请输入bug数量'}},
                                number: {value: true, err: {err_msg: '请输入bug数量'}}
                            }
                        },
                        account_name: '',
                        needer_name: '',
                        account_summary: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请输入开发人员总结'}}
                            }
                        },
                        needer_summary: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请输入产品人员总结'}}
                            }
                        },
                        id: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请选择需要上线的需求'}}
                            }
                        }
                    }
                },
                summary: {
                    title: '需求bug总结',
                    visible: false,
                    summary_info_blank: {
                        bug_count: 0,
                        account_name: '',
                        needer_name: '',
                        account_summary: '',
                        needer_summary: ''
                    },
                    summary_info: {
                        bug_count: 0,
                        account_name: '',
                        needer_name: '',
                        account_summary: '',
                        needer_summary: ''
                    }
                }
            },
            /**
             * 规则模板
             */
            rule: {
                summary: {
                    key: {
                        value: ''
                    },
                    value: {
                        value: '',
                        rules: {
                            required: {value: true, err: {err_msg: '请输入所有参与人员的bug总结'}}
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
             * 送测
             */
            qaQaInfo: function(id) {
                this.$confirm('确定送测吗?', {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function() {
                    //1.数据检查
                    var qaInfo = {id: id};
                    //2.后台请求
                    if (qaInfo) {
                        new Promise(function(resolve) {
                            //1.送测
                            resolve(qa.qaQaInfo(qaInfo));
                        }).then(function() {
                            //2.列表刷新
                            qa.loadList();
                        }).catch(function(error) {
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function() {

                });
            },
            /**
             * 上线弹框
             */
            showDialogOnline: function(id, account_name,needer_name) {
                new Promise(function(resolve, reject) {
                    //数据重置
                    qa.initDialogInfo('online', app.$data.dialog.online.online_info_blank);
                    //数据填充
                    app.$data.dialog.online.online_info.id.value = id;
                    app.$data.dialog.online.online_info.account_name = account_name;
                    app.$data.dialog.online.online_info.needer_name = needer_name;
                    resolve();
                }).then(function() {
                    //2.加载弹框信息
                }).then(function() {
                    //3.显示弹框
                    app.$data.dialog.online.visible = true;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 上线
             */
            onlineQaInfo: function() {
                this.$confirm('确定上线吗?', {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function() {
                    //1.数据检查
                    var qaInfo = validator.check(app.$data.dialog.online.online_info);
                    //2.后台请求
                    if (qaInfo) {
                        new Promise(function(resolve) {
                            //1.上线
                            resolve(qa.onlineQaInfo(qaInfo));
                        }).then(function() {
                            //2.关闭弹框
                            app.$data.dialog.online.visible = false
                        }).then(function() {
                            //3.列表刷新
                            qa.loadList();
                        }).catch(function(error) {
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function() {

                });
            },
            /**
             * 总结弹框
             */
            showDialogSummary: function(row) {
                new Promise(function(resolve, reject) {
                    //数据重置
                    qa.initDialogInfo('summary', app.$data.dialog.summary.summary_info_blank);
                    //数据填充
                    app.$data.dialog.summary.summary_info.bug_count = row.bug_count;
                    app.$data.dialog.summary.summary_info.account_name = row.account_name;
                    app.$data.dialog.summary.summary_info.needer_name = row.needer_name;
                    app.$data.dialog.summary.summary_info.account_summary = row.account_summary;
                    app.$data.dialog.summary.summary_info.needer_summary = row.needer_summary;
                    resolve();
                }).then(function() {
                    //2.加载弹框信息
                }).then(function() {
                    //3.显示弹框
                    app.$data.dialog.summary.visible = true;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 撤销
             */
            revokeQaInfo: function(id) {
                this.$confirm('确定撤销吗?', {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function() {
                    //1.数据检查
                    var qaInfo = {id: id};
                    //2.后台请求
                    if (qaInfo) {
                        new Promise(function(resolve) {
                            //1.撤销
                            resolve(qa.revokeQaInfo(qaInfo));
                        }).then(function() {
                            //2.列表刷新
                            qa.loadList();
                        }).catch(function(error) {
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function() {

                });
            },
            /**
             * 下载
             */
            downQaInfo: function(id) {
                //1.数据检查
                var qaInfo = {id: id};
                //2.后台请求
                if (qaInfo) {
                    new Promise(function(resolve) {
                        //1.下载
                        resolve(qa.downQaInfo(qaInfo));
                    }).then(function(data) {
                        //2.下载
                        bmplugin.downloadFile(data['attach_id']);
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
                qa.loadList();
            },
            /**
             * table格式化
             * 状态
             */
            formatStatus: function(row, column, cellValue, index) {
                var status = {'01': '送测', '02': '上线'};
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
                    qa.loadList();
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                qa.loadAuthButton();
                qa.loadBaseInfo();
                qa.loadList();
            });
        }
    });
}(Vue, window, document));
