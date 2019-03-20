(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var todo = {
        /**
         * 加载按钮权限
         */
        loadAuthButton: function() {
            app.$data.auth_button = JSON.parse((document.getElementsByClassName('auth-button')[0] && document.getElementsByClassName('auth-button')[0].innerText) || '{}');
            app.$data.auth_role = document.getElementsByClassName('auth-role')[0] && document.getElementsByClassName('auth-role')[0].innerText.replace(/\n/g, '').trim() || '';
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
            //2.后台请求
            if (listInfo) {
                bmplugin.ajax.post('/web/task/todo/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
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
         * 加载待办数据
         */
        loadTodoInfo: function(id) {
            //数据重置
            todo.initDialogInfo('todo', app.$data.dialog.todo.todo_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/task/todo/loadtodoinfo', {id: id}).then(function(data) {
                    todo.initDialogInfo('todo', data.info);
                });
            }
        },
        /**
         * 编辑
         */
        editTodoInfo: function(todoInfo) {
            return bmplugin.ajax.post('/web/task/todo/edittodoinfo', todoInfo);
        },
        /**
         * 完成
         */
        doneTodoInfo: function(todoInfo) {
            return bmplugin.ajax.post('/web/task/todo/donetodoinfo', todoInfo);
        },
        /**
         * 删除
         */
        deleteTodoInfo: function(todoInfo) {
            return bmplugin.ajax.post('/web/task/todo/deletetodoinfo', todoInfo);
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
                title: '',
                account_id: '',
                status: ['01']
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
                todo: {
                    title: '新增待办事项',
                    visible: false,
                    todo_info_blank: {
                        id: '0',
                        title: '',
                        content: '',
                        priority: '1'
                    },
                    todo_info: {
                        id: '0',
                        title: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请输入事项标题'}},
                                max_len: {value: 10, err: {err_msg: '事项标题最大长度为10个字符'}}
                            }
                        },
                        content: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请输入事项内容'}}
                            }
                        },
                        priority: '1'
                    }
                }
            },
            action: '/web/common/common/uploadfile',
            imageUrl: ''
        },
        watch: {
        },
        created: function() {
        },
        methods: {
            beforeAvatarUpload: function() {

            },
            handleAvatarSuccess: function(response, file, fileList) {
                if (response.success && response.data && response.data.base64) {
                    console.log(response);
                    this.imageUrl = response.data.base64;
                }
            },
            /**
             * 修改
             */
            showDialogTodo: function(id) {
                //title
                if (id) {
                    app.$data.dialog.todo.title = '编辑待办事项';
                } else {
                    app.$data.dialog.todo.title = '新增待办事项';
                }
                //获取数据
                new Promise(function(resolve) {
                    //1.加载角色信息
                    resolve(todo.loadTodoInfo(id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.todo.visible = true;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 编辑
             */
            editTodoInfo: function(id) {
                //1.数据检查
                var todoInfo = validator.check(app.$data.dialog.todo.todo_info);
                //2.后台请求
                if (todoInfo) {
                    new Promise(function(resolve) {
                        //1.待办
                        resolve(todo.editTodoInfo(todoInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.todo.visible = false
                    }).then(function() {
                        //3.列表刷新
                        todo.loadList();
                    }).catch(function(error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            },
            /**
             * 完成
             */
            doneTodoInfo: function(id) {
                this.$confirm('确定完成待办事项?', {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function() {
                    //1.数据检查
                    var todoInfo = {id: id};
                    //2.后台请求
                    if (todoInfo) {
                        new Promise(function(resolve) {
                            //1.完成
                            resolve(todo.doneTodoInfo(todoInfo));
                        }).then(function() {
                            //2.列表刷新
                            todo.loadList();
                        }).catch(function(error) {
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function() {

                });
            },
            /**
             * 删除
             */
            deleteTodoInfo: function(id) {
                this.$confirm('确定删除待办事项?', {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function() {
                    //1.数据检查
                    var todoInfo = {id: id};
                    //2.后台请求
                    if (todoInfo) {
                        new Promise(function(resolve) {
                            //1.删除
                            resolve(todo.deleteTodoInfo(todoInfo));
                        }).then(function() {
                            //2.列表刷新
                            todo.loadList();
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
                todo.loadList();
            },
            /**
             * table格式化
             * 内容
             */
            formatContent: function(content) {
                return content.replace(/(\r\n)|(\n)/g, '<br/>');
            },
            /**
             * table格式化
             * 状态
             */
            formatStatus: function(status) {
                var dic = {'01': '待办', '02': '已办', '06': '删除'};
                return dic[status];
            },
            /**
             * table分页
             * 页数变化
             */
            pageIndexChange: function(curIndex) {
                if (app.$data.list.page.page_index != curIndex) {
                    app.$data.list.page.page_index = curIndex;
                    todo.loadList();
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                todo.loadAuthButton();
                todo.loadBaseInfo();
                todo.loadList();
            });
        }
    });
}(Vue, window, document));
