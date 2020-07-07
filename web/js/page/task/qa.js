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
                        summary: []
                    },
                    online_info: {
                        summary: {
                            value: [],
                            rules: {
                                required: {value: true, err: {err_msg: '请输入bug总结'}}
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
                        summary: []
                    },
                    summary_info: {
                        summary: {
                            value: []
                        }
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
            showDialogOnline: function(id, summary) {
                new Promise(function(resolve, reject) {
                    //1.获取需要上线的需求
                    app.$data.dialog.online.online_info.id.value = id;
                    app.$data.dialog.online.online_info.summary.value = JSON.parse(summary);
//                    var summary1 = JSON.parse(summary);
//                    for (var index in summary1) {
//                        var tmp = JSON.parse(JSON.stringify(app.$data.rule.summary));
//                        tmp.key.value = summary1[index].key;
//                        tmp.value.value = summary1[index].value;
//                        app.$data.dialog.online.online_info.summary.value.push(tmp);
//                    }
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
            showDialogSummary: function(summary) {
                new Promise(function(resolve, reject) {
                    //1.获取需要上线的需求
                    console.log(summary);
                    app.$data.dialog.summary.summary_info.summary.value = JSON.parse(summary);
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
