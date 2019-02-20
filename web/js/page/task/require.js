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
            app.$data.auth_role = document.getElementsByClassName('auth-role')[0] && document.getElementsByClassName('auth-role')[0].innerText.replace(/\n/g, '').trim() || '';
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
                if (typeof app.$data.dialog.require.require_info[objKey] === 'undefined') {
                    continue;
                }
                if (typeof app.$data.dialog.require.require_info[objKey].value !== 'undefined') {
                    app.$data.dialog.require.require_info[objKey].value = require_info[objKey];
                } else {
                    app.$data.dialog.require.require_info[objKey] = require_info[objKey];
                }
            }
        },
        /**
         * 加载需求数据
         */
        loadRequireInfo: function(id) {
            //数据重置
            require.initRequireInfo(app.$data.dialog.require.require_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/task/require/loadrequireinfo', {id: id, project_id: app.$data.search.project_id}).then(function(data) {
                    require.initRequireInfo(data.info);
                });
            }
        },
        /**
         * 保存需求数据
         */
        saveRequireInfo: function(saveType, requireInfo) {
            requireInfo = Object.assign(requireInfo, {project_id: app.$data.search.project_id});
            switch (saveType) {
                case 'add':
                    return bmplugin.ajax.post('/web/task/require/addRequireInfo', requireInfo);
                    break;
                case 'edit':
                    return bmplugin.ajax.post('/web/task/require/editRequireInfo', requireInfo);
                    break;
                case 'done':
                    return bmplugin.ajax.post('/web/task/require/doneRequireInfo', requireInfo);
                    break;
                case 'delete':
                    return bmplugin.ajax.post('/web/task/require/deleteRequireInfo', requireInfo);
                    break;
            }
        },
        /**
         * 删除需求数据
         */
        deleteRequireInfo: function(requireInfo) {
            return bmplugin.ajax.post('/web/task/require/deleterequireinfo', requireInfo);
        }
    };

    /**
     * 其它组件
     */
    var plugin = {
        quilleditor: {
            need_memo: null,
            dev_memo: null
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
                app.$data.dialog.require.require_info[type].value = plugin.quilleditor[type].getData();
            });
            plugin.quilleditor[type].on('instanceReady', function() {
                //https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_editor.html#property-status
                //放在外部会报setReadOnly不存在，放在实例初始化完成后执行
                plugin.quilleditor[type].setReadOnly(!(type == 'need_memo' ? app.require_dialog_product_status : app.require_dialog_dev_status));
                plugin.quilleditor[type].setData(type == 'need_memo' ? app.$data.dialog.require.require_info.need_memo.value : app.$data.dialog.require.require_info.dev_memo.value);
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
                require: {
                    title: '新增需求',
                    visible: false,
                    tab_index: '0',
                    require_info_blank: {
                        id: '0',
                        is_self: 0,
                        status: '',
                        is_timeout: 0,
                        round: 0,
                        xingzhi: '02',
                        needer: '',
                        task_name: '',
                        module_type: '',
                        module_id: '',
                        need_memo: '',
                        need_attach: '',
                        page_enter: '',
                        dev_memo: '',
                        need_tip: '',
                        change_file: '',
                        sql_attach: '',
                        other_attach: '',
                        dev_dealy_reason: '',
                        change_file_qa: [],
                    },
                    require_info: {
                        id: '0',
                        is_self: 0,
                        status: '',
                        is_timeout: 0,
                        round: 0,
                        xingzhi: '',
                        needer: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入需求提出人'}}
                            }
                        },
                        task_name: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入需求名称'}}
                            }
                        },
                        module_type: '',
                        module_id: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请选择需求模块'}}
                            }
                        },
                        need_memo: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入需求明细'}}
                            }
                        },
                        need_attach: '',
                        page_enter: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入页面入口'}}
                            }
                        },
                        dev_memo: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入逻辑说明'}}
                            }
                        },
                        need_tip: '',
                        change_file: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入修改文件'}}
                            }
                        },
                        sql_attach: '',
                        other_attach: '',
                        dev_dealy_reason: '',
                        change_file_qa: []
                    }
                },
                allot: {

                },
                qa: {

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
            /**
             * 查询区域，模块类型切换
             */
            'search.module_type': function() {
                this.search.module_id = '';
            },
            /**
             * 需求弹框，模块类型切换
             */
            'dialog.require.require_info.module_type': function() {
                this.dialog.require.require_info.module_id.value = '';
            }
        },
        created: function() {
        },
        computed: {
            /**
             * 查询区域
             * 根据类型获取模块名称
             */
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
            },
            /**
             * 需求弹框
             * 根据类型获取模块名称
             */
            dialog_module: function() {
                var moduleType = this.dialog.require.require_info.module_type;
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
            },
            /**
             * 需求弹框
             * 根据送测轮次获取可遍历数组
             */
            round_arr: function() {
                var arrTmp = [];
                for (var i = 1; i <= this.dialog.require.require_info.round && i <= 5; i++) {
                    arrTmp[i - 1] = i;
                }
                return arrTmp;
            },
            /**
             * 需求弹框
             * 产品区域的form可编辑状态
             * 由角色与需求状态控制
             */
            require_dialog_product_status: function() {
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
                    case 'product':
                        switch (this.dialog.require.require_info.status) {
                            case '':
                            case '01':
                            case '02':
                            case '03':
                            case '04':
                                return true;
                                break;
                            default:
                                return false;
                                break;
                        }
                        break;
                    case 'devloper':
                        return false;
                        break;
                    default :
                        return false;
                        break
                }
            },
            /**
             * 需求弹框
             * 开发区域的form可编辑状态
             * 由角色与需求状态控制
             */
            require_dialog_dev_status: function() {
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
                        switch (this.dialog.require.require_info.status) {
                            case '02':
                            case '03':
                            case '04':
                                return true;
                                break;
                            default:
                                return false;
                                break;
                        }
                        break;
                    case 'devloper':
                        switch (this.dialog.require.require_info.status) {
                            case '02':
                            case '03':
                                return this.dialog.require.require_info.is_self == 1;
                                break;
                            default:
                                return false;
                                break;
                        }
                        break;
                    case 'product':
                        return false;
                        break;
                    default :
                        return false;
                        break
                }
            },
            /**
             * 需求弹框
             * 送测区域的form可编辑状态
             * 由角色与需求状态控制
             */
            require_dialog_changefile_status: function() {
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
                        switch (this.dialog.require.require_info.status) {
                            case '04':
                                return true;
                                break;
                            default:
                                return false;
                                break;
                        }
                        break;
                    case 'devloper':
                        switch (this.dialog.require.require_info.status) {
                            case '04':
                                return this.dialog.require.require_info.is_self == 1;
                                break;
                            default:
                                return false;
                                break;
                        }
                        break;
                    case 'product':
                        return false;
                        break;
                    default :
                        return false;
                        break
                }
            },
            /**
             * 需求弹框
             * 按钮区域的add状态
             * 由角色权限,角色,需求状态控制
             */
            require_dialog_btnadd_status: function() {
                return this.auth_button['Task.Require.Add'] && this.dialog.require.require_info.status == '';
            },
            /**
             * 需求弹框
             * 按钮区域的delete状态
             * 由角色权限,角色,需求状态控制
             */
            require_dialog_btndelete_status: function() {
                return this.auth_button['Task.Require.Delete'] && ['01', '02', '03'].indexOf(this.dialog.require.require_info.status) >= 0;
            },
            /**
             * 需求弹框
             * 按钮区域的done状态
             * 由角色权限,角色,需求状态控制
             */
            require_dialog_btndone_status: function() {
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
                        return this.auth_button['Task.Require.Done'] && ['02'].indexOf(this.dialog.require.require_info.status) >= 0;
                        break;
                    case 'devloper':
                        return this.auth_button['Task.Require.Done'] && ['02'].indexOf(this.dialog.require.require_info.status) >= 0 && this.dialog.require.require_info.is_self == 1;
                        break;
                    default :
                        return false;
                        break
                }
            },
            /**
             * 需求弹框
             * 按钮区域的save状态
             * 由角色权限,角色,需求状态控制
             */
            require_dialog_btnsave_status: function() {
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
                    case 'product':
                        return this.auth_button['Task.Require.Edit'] && ['01', '02', '03', '04'].indexOf(this.dialog.require.require_info.status) >= 0;
                        break;
                    case 'devloper':
                        return this.auth_button['Task.Require.Edit'] && ['02', '03', '04'].indexOf(this.dialog.require.require_info.status) >= 0 && this.dialog.require.require_info.is_self == 1;
                        break;
                    default :
                        return false;
                        break
                }
            }
        },
        methods: {
            /**
             * 获取需要保存的信息
             * 由角色与需求状态控制
             */
            getRequireInfo: function() {
                //字段信息
                var arrCol = [];
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
                    case 'product':
                        switch (this.dialog.require.require_info.status) {
                            case '':
                                arrCol = ['xingzhi', 'needer', 'task_name', 'module_id', 'need_memo', 'need_attach'];
                                break;
                            case '01':
                                arrCol = ['id', 'xingzhi', 'needer', 'task_name', 'module_id', 'need_memo', 'need_attach'];
                                break;
                            case '02':
                            case '03':
                            case '04':
                                break;
                            default:
                                break;
                        }
                        break;
                    case 'devloper':
                        break;
                    default :
                        break
                }
                //提取信息
                var arrRequireInfo = []
                for (var index in arrCol) {
                    arrRequireInfo[arrCol[index]] = this.dialog.require.require_info[arrCol[index]];
                }
                return arrRequireInfo;
            },
            /**
             * 需求修改(新增，修改，完成，作废)
             */
            showDialogRequire: function(id) {
                //title
                if (id) {
                    app.$data.dialog.require.title = '编辑需求';
                } else {
                    app.$data.dialog.require.title = '新增需求';
                }
                app.$data.dialog.require.tab_index = '0';
                //获取数据
                new Promise(function(resolve) {
                    //1.加载需求信息
                    resolve(require.loadRequireInfo(id));
                }).then(function() {
                    //2.显示弹框
                    app.$data.dialog.require.visible = true;
                }).then(function() {
                    //require.initRequireInfo时包含被watch的属性，会触发vue的更新，所以需要在$nextTick中执行
                    //否则，第一次弹框不能被渲染
                    app.$nextTick(function() {
                        //3.editor需要在dialog生成后才能创建
                        plugin.initCKEditor('need_memo');
                        plugin.initCKEditor('dev_memo');
                    });
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 保存需求信息
             */
            saveRequireInfo: function(saveType) {
                //1.数据检查
                var requireInfo = validator.check(this.getRequireInfo());
                //2.后台请求
                if (requireInfo) {
                    new Promise(function(resolve) {
                        //1.保存需求信息
                        resolve(require.saveRequireInfo(saveType, requireInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.require.visible = false
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
                return row.row.is_timeout == '1' ? 'timeout' : '';
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