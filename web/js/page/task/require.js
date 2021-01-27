(function (Vue, window, document) {
    /**
     * 业务对象
     */
    var require = {
        /**
         * 加载按钮权限
         */
        loadAuthButton: function () {
            app.$data.auth_button = JSON.parse((document.getElementsByClassName('auth-button')[0] && document.getElementsByClassName('auth-button')[0].innerText) || '{}');
            app.$data.auth_role = document.getElementsByClassName('auth-role')[0] && document.getElementsByClassName('auth-role')[0].innerText.replace(/\n/g, '').trim() || '';
        },
        /**
         * 加载页面基础数据
         */
        loadBaseInfo: function () {
            bmplugin.ajax.post('/web/task/require/loadbaseinfo', {'is_show_loading': false, 'project_id': app.$data.search.project_id}).then(function (data) {
                app.$data.base_info.account = data.account;
                app.$data.base_info.needer = data.needer;
                app.$data.base_info.module = data.module;
                app.$data.base_info.account_allot = data.account_allot;
            }).catch(function (error) {
                bmplugin.showErrMsg(error);
            });
        },
        /**
         * 加载列表数据
         */
        loadList: function () {
            //1.数据检查
            var listInfo = app.$data.list.page;
            listInfo.search_param = JSON.stringify(app.$data.search);
            listInfo.project_id = app.$data.search.project_id;
            //2.后台请求
            if (listInfo) {
                bmplugin.ajax.post('/web/task/require/loadlist', listInfo).then(function (data) {
                    app.$data.list.data = data.list;
                    app.$data.list.page.total = data.total;
                }).catch(function (error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 设置弹框数据
         */
        initDialogInfo: function (dialogName, dialogInfo) {
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
         * 加载需求数据
         */
        loadRequireInfo: function (id) {
            //数据重置
            require.initDialogInfo('require', app.$data.dialog.require.require_info_blank);
            //后台请求
            if (id) {
                return bmplugin.ajax.post('/web/task/require/loadrequireinfo', {id: id, project_id: app.$data.search.project_id}).then(function (data) {
                    if (typeof data.info.need_tip !== 'undefined') {
                        data.info.need_tip = data.info.need_tip || '01.代码上线前需执行脚本：有\n02.代码上线后需执行脚本：有\n03.代码上线同步执行脚本：有\n04.执行时间较长脚本：有\n05.需新增或修改配置文件节点：有\n06.是否需要杀进程：有\n07.是否涉及多服务器：有\n08.是否涉及文件夹新增及权限问题：有\n09.是否涉及第三方接口：有\n10.上线时间点：未定义\n11.漏洞检查：（sql注入[绑定变量,底层校验]：有 越权漏洞[中间件过滤]：有）';
                    }
                    require.initDialogInfo('require', data.info);
                });
            }
        },
        /**
         * 加载需求分配数据
         */
        loadAllotInfo: function () {
            //数据重置
            require.initDialogInfo('allot', app.$data.dialog.allot.allot_info_blank);
        },
        /**
         * 加载需求重新分配数据
         */
        loadReAllotInfo: function () {
            //数据重置
            require.initDialogInfo('reallot', app.$data.dialog.reallot.reallot_info_blank);
        },
        /**
         * 加载需求送测数据
         */
        loadQaInfo: function () {
            //数据重置
            require.initDialogInfo('qa', app.$data.dialog.qa.qa_info_blank);
        },
        /**
         * 新增需求
         */
        addRequireInfo: function (requireInfo) {
            requireInfo = Object.assign(requireInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/require/addrequireinfo', requireInfo);
        },
        /**
         * 编辑需求
         */
        editRequireInfo: function (requireInfo) {
            requireInfo = Object.assign(requireInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/require/editrequireinfo', requireInfo);
        },
        /**
         * 完成需求
         */
        doneRequireInfo: function (requireInfo) {
            requireInfo = Object.assign(requireInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/require/donerequireinfo', requireInfo);
        },
        /**
         * 删除需求
         */
        deleteRequireInfo: function (requireInfo) {
            requireInfo = Object.assign(requireInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/require/deleterequireinfo', requireInfo);
        },
        /**
         * 分配需求
         */
        allotRequireInfo: function (allotInfo) {
            allotInfo = Object.assign(allotInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/require/allotrequireinfo', allotInfo);
        },
        /**
         * 重新分配需求
         */
        reallotRequireInfo: function (allotInfo) {
            allotInfo = Object.assign(allotInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/require/reallotrequireinfo', allotInfo);
        },
        /**
         * 送测需求
         */
        qaRequireInfo: function (qaInfo) {
            qaInfo = Object.assign(qaInfo, {project_id: app.$data.search.project_id});
            return bmplugin.ajax.post('/web/task/require/qarequireinfo', qaInfo);
        },
        /**
         * 导出需求
         */
        outputRequireInfo: function () {
            var downloadInfo = Object.assign({}, {project_id: app.$data.search.project_id});
            downloadInfo.search_param = JSON.stringify(app.$data.search);
            return bmplugin.ajax.post('/web/task/require/outputrequireinfo', downloadInfo);
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
        initCKEditor: function (type) {
            //删除并重建组件，解决element-ui重复打开不能编辑的问题
            if (CKEDITOR.instances[type]) {
                CKEDITOR.remove(CKEDITOR.instances[type]);
            }
            if (document.querySelector('#cke_' + type)) {
                document.querySelector('#cke_' + type).remove();
            }
            plugin.quilleditor[type] = CKEDITOR.replace(type);
            plugin.quilleditor[type].on('change', function () {
                app.$data.dialog.require.require_info[type].value = plugin.quilleditor[type].getData();
            });
            plugin.quilleditor[type].on('instanceReady', function () {
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
                module: [],
                account_allot: []
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
                        browser_fit: '',
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
                        change_file1: '',
                        change_file2: '',
                        change_file3: '',
                        change_file4: '',
                        change_file5: '',
                        change_file_qa: {}
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
                                required: {value: false, err: {err_msg: '请输入需求提出人'}}
                            }
                        },
                        task_name: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入需求名称'}}
                            }
                        },
                        browser_fit: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入需要支持的浏览器'}}
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
                        dev_dealy_reason: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入开发延迟原因'}}
                            }
                        },
                        change_file1: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入送测修改文件'}}
                            }
                        },
                        change_file2: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入送测修改文件'}}
                            }
                        },
                        change_file3: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入送测修改文件'}}
                            }
                        },
                        change_file4: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入送测修改文件'}}
                            }
                        },
                        change_file5: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入送测修改文件'}}
                            }
                        },
                        change_file_qa: {}
                    }
                },
                allot: {
                    title: '需求分配',
                    visible: false,
                    allot_info_blank: {
                        need_done_date: '',
                        account_id: ''
                    },
                    allot_info: {
                        need_done_date: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请设置期望完成时间'}}
                            }
                        },
                        account_id: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请选择开发人员'}}
                            }
                        },
                        task_id: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请选择需要分配的需求'}}
                            }
                        }
                    }
                },
                reallot: {
                    title: '需求重新分配',
                    visible: false,
                    reallot_info_blank: {
                        account_id: ''
                    },
                    reallot_info: {
                        account_id: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请选择开发人员'}}
                            }
                        },
                        task_id: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请选择需要重新分配的需求'}}
                            }
                        }
                    }
                },
                qa: {
                    title: '需求送测',
                    visible: false,
                    conflict: [],
                    qa_info_blank: {
                        qa_name: ''
                    },
                    qa_info: {
                        qa_name: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请输入送测名称'}}
                            }
                        },
                        qa_tip: '',
                        task_id: {
                            value: '',
                            rules: {
                                required: {value: true, err: {err_msg: '请选择需要送测的需求'}}
                            }
                        },
                        is_force: 0
                    }
                }
            },
            /**
             * 需求分配
             */
            checkListAllot: {
            },
            /**
             * 需求重新分配
             */
            checkListReAllot: {
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
            'search.module_type': function () {
                this.search.module_id = '';
            },
            /**
             * 需求弹框，模块类型切换
             */
            'dialog.require.require_info.module_type': function (newVal, oldVal) {
                if (oldVal == '') {
                    return;
                }
                this.dialog.require.require_info.module_id.value = '';
            },
            /**
             * 需求弹框，送测文件
             */
            'dialog.require.require_info.change_file_qa': {
                handler: function () {
                    //后面的赋值需要兼容undefined，否则value被赋值为undefined，再赋值时就会直接覆盖整个属性(因为value=undefined)，而不是value了
                    this.dialog.require.require_info.change_file1.value = this.dialog.require.require_info.change_file_qa['1'] || '';
                    this.dialog.require.require_info.change_file2.value = this.dialog.require.require_info.change_file_qa['2'] || '';
                    this.dialog.require.require_info.change_file3.value = this.dialog.require.require_info.change_file_qa['3'] || '';
                    this.dialog.require.require_info.change_file4.value = this.dialog.require.require_info.change_file_qa['4'] || '';
                    this.dialog.require.require_info.change_file5.value = this.dialog.require.require_info.change_file_qa['5'] || '';
                },
                deep: true
            },
            /**
             * 送测弹框，是否强制送测
             */
            'qa_dialog_conflict_status': function () {
                this.dialog.qa.qa_info.is_force = this.qa_dialog_conflict_status ? 1 : 0;
            }
        },
        created: function () {
        },
        computed: {
            /**
             * 查询区域
             * 根据类型获取模块名称
             */
            search_module: function () {
                var moduleType = this.search.module_type;
                return this.base_info.module.filter(function (currentValue) {
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
            dialog_module: function () {
                var moduleType = this.dialog.require.require_info.module_type;
                return this.base_info.module.filter(function (currentValue) {
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
            round_arr: function () {
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
            require_dialog_product_status: function () {
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
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
            require_dialog_dev_status: function () {
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
            require_dialog_changefile_status: function () {
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
            require_dialog_btnadd_status: function () {
                return this.auth_button['Task.Require.Add'] && this.dialog.require.require_info.status == '';
            },
            /**
             * 需求弹框
             * 按钮区域的delete状态
             * 由角色权限,角色,需求状态控制
             */
            require_dialog_btndelete_status: function () {
                return this.auth_button['Task.Require.Delete'] && ['01', '02', '03'].indexOf(this.dialog.require.require_info.status) >= 0;
            },
            /**
             * 需求弹框
             * 按钮区域的done状态
             * 由角色权限,角色,需求状态控制
             */
            require_dialog_btndone_status: function () {
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
            require_dialog_btnsave_status: function () {
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
                        return this.auth_button['Task.Require.Edit'] && ['01', '02', '03', '04'].indexOf(this.dialog.require.require_info.status) >= 0;
                        break;
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
            },
            /**
             * 送测弹框
             * 文件冲突状态
             */
            qa_dialog_conflict_status: function () {
                return this.dialog.qa.conflict.length > 0;
            }
        },
        methods: {
            /**
             * 获取编辑需求时的信息
             */
            getRequireInfo: function () {
                //字段信息
                var arrCol = [];
                switch (this.auth_role) {
                    case 'admin':
                    case 'manager':
                        switch (this.dialog.require.require_info.status) {
                            case '':
                                arrCol = ['xingzhi', 'needer', 'task_name', 'browser_fit', 'module_id', 'need_memo', 'need_attach'];
                                break;
                            case '01':
                                arrCol = ['id', 'xingzhi', 'needer', 'task_name', 'browser_fit', 'module_id', 'need_memo', 'need_attach'];
                                break;
                            case '02':
                            case '03':
                                arrCol = ['id', 'xingzhi', 'needer', 'task_name', 'browser_fit', 'module_id', 'need_memo', 'need_attach', 'page_enter', 'dev_memo', 'need_tip', 'change_file', 'sql_attach', 'other_attach'];
                                break;
                            case '04':
                                arrCol = ['id', 'xingzhi', 'needer', 'task_name', 'browser_fit', 'module_id', 'need_memo', 'need_attach', 'page_enter', 'dev_memo', 'need_tip', 'change_file', 'sql_attach', 'other_attach'];
                                //送测文件
                                if (this.dialog.require.require_info.round != '0') {
                                    arrCol.push('change_file' + this.dialog.require.require_info.round);
                                }
                                break;
                            default:
                                arrCol = [];
                                break;
                        }
                        break;
                    case 'product':
                        switch (this.dialog.require.require_info.status) {
                            case '':
                                arrCol = ['xingzhi', 'needer', 'task_name', 'browser_fit', 'module_id', 'need_memo', 'need_attach'];
                                break;
                            case '01':
                            case '02':
                            case '03':
                            case '04':
                                arrCol = ['id', 'xingzhi', 'needer', 'task_name', 'browser_fit', 'module_id', 'need_memo', 'need_attach'];
                                break;
                            default:
                                arrCol = [];
                                break;
                        }
                        break;
                    case 'devloper':
                        switch (this.dialog.require.require_info.status) {
                            case '02':
                                arrCol = ['id', 'page_enter', 'dev_memo', 'need_tip', 'change_file', 'sql_attach', 'other_attach'];
                                break;
                            case '04':
                                arrCol = ['id'];
                                //送测文件
                                if (this.dialog.require.require_info.round != '0') {
                                    arrCol.push('change_file' + this.dialog.require.require_info.round);
                                }
                                break;
                            default:
                                arrCol = [];
                                break;
                        }
                        break;
                    default :
                        break;
                }
                //提取信息
                var arrRequireInfo = [];
                for (var index in arrCol) {
                    arrRequireInfo[arrCol[index]] = this.dialog.require.require_info[arrCol[index]];
                }
                return arrRequireInfo;
            },
            /**
             * 获取完成需求时的信息
             */
            getRequireInfoDone: function () {
                var arrCol = ['id'];
                //延迟原因
                if (this.dialog.require.require_info.is_timeout) {
                    arrCol.push('dev_dealy_reason');
                }
                //提取信息
                var arrRequireInfo = [];
                for (var index in arrCol) {
                    arrRequireInfo[arrCol[index]] = this.dialog.require.require_info[arrCol[index]];
                }
                return arrRequireInfo;
            },
            /**
             * 需求修改(新增，修改，完成，作废)
             */
            showDialogRequire: function (id) {
                //title
                if (id) {
                    app.$data.dialog.require.title = '编辑需求';
                } else {
                    app.$data.dialog.require.title = '新增需求';
                }
                app.$data.dialog.require.tab_index = '0';
                //获取数据
                new Promise(function (resolve) {
                    //1.加载需求信息
                    resolve(require.loadRequireInfo(id));
                }).then(function () {
                    //2.显示弹框
                    app.$data.dialog.require.visible = true;
                }).then(function () {
                    //require.initDialogInfo时包含被watch的属性，会触发vue的更新，所以需要在$nextTick中执行
                    //否则，第一次弹框不能被渲染
                    app.$nextTick(function () {
                        //3.editor需要在dialog生成后才能创建
                        plugin.initCKEditor('need_memo');
                        plugin.initCKEditor('dev_memo');
                    });
                }).catch(function (error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 新增需求
             */
            addRequireInfo: function () {
                //1.数据检查
                var requireInfo = validator.check(this.getRequireInfo());
                //2.后台请求
                if (requireInfo) {
                    new Promise(function (resolve) {
                        //1.保存需求信息
                        resolve(require.addRequireInfo(requireInfo));
                    }).then(function () {
                        //2.关闭弹框
                        app.$data.dialog.require.visible = false
                    }).then(function () {
                        //3.列表刷新
                        require.loadList();
                    }).catch(function (error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            },
            /**
             * 保存需求
             */
            editRequireInfo: function () {
                //1.数据检查
                var requireInfo = validator.check(this.getRequireInfo());
                //2.后台请求
                if (requireInfo) {
                    new Promise(function (resolve) {
                        //1.保存需求信息
                        resolve(require.editRequireInfo(requireInfo));
                    }).then(function () {
                        //2.关闭弹框
                        app.$data.dialog.require.visible = false
                    }).then(function () {
                        //3.列表刷新
                        require.loadList();
                    }).catch(function (error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            },
            /**
             * 完成需求
             */
            doneRequireInfo: function () {
                var browerFit = '';
                if (this.$data.dialog.require.require_info.browser_fit.value != '无') {
                    browerFit = "<p style='color:red;'>请确认已进行【" + this.$data.dialog.require.require_info.browser_fit.value + "】浏览器的兼容性测试。</p>";
                }
                this.$confirm('需求完成后将不能修改开发信息，只能找主管进行修改？' + browerFit, {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function () {
                    //1.数据检查
                    var requireInfo = validator.check(app.getRequireInfo());
                    var requireInfoDone = validator.check(app.getRequireInfoDone());
                    //2.后台请求
                    if (requireInfo && requireInfoDone) {
                        new Promise(function (resolve) {
                            //1.保存需求
                            resolve(require.editRequireInfo(requireInfo));
                        }).then(function () {
                            //2.完成需求
                            return require.doneRequireInfo(requireInfoDone);
                        }).then(function () {
                            //3.关闭弹框
                            app.$data.dialog.require.visible = false
                        }).then(function () {
                            //4.列表刷新
                            require.loadList();
                        }).catch(function (error) {
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function (error) {
                    //bmplugin.showErrMsg(error);
                });
            },
            /**
             * 作废需求
             */
            deleteRequireInfo: function () {
                this.$confirm('确定作废此需求吗?', {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function () {
                    //1.数据检查
                    var requireInfo = {id: app.$data.dialog.require.require_info.id};
                    //2.后台请求
                    if (requireInfo) {
                        new Promise(function (resolve) {
                            //1.保存需求信息
                            resolve(require.deleteRequireInfo(requireInfo));
                        }).then(function () {
                            //2.关闭弹框
                            app.$data.dialog.require.visible = false
                        }).then(function () {
                            //3.列表刷新
                            require.loadList();
                        }).catch(function (error) {
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function () {

                });
            },
            /**
             * 需求分配
             */
            showDialogAllot: function () {
                new Promise(function (resolve, reject) {
                    //1.获取需要分配的需求
                    var arrTaskId = [];
                    for (var taskId in app.$data.checkListAllot) {
                        if (app.$data.checkListAllot[taskId]) {
                            arrTaskId.push(taskId);
                        }
                    }
                    if (arrTaskId.length == 0) {
                        reject(new Error('请选择需要分配的需求'));
                    } else {
                        app.$data.dialog.allot.allot_info.task_id.value = arrTaskId.join();
                        resolve();
                    }
                }).then(function () {
                    //2.加载弹框信息
                    require.loadAllotInfo();
                }).then(function () {
                    //3.显示弹框
                    app.$data.dialog.allot.visible = true;
                }).catch(function (error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 需求分配
             */
            allotRequireInfo: function () {
                //1.数据检查
                var allotInfo = validator.check(app.$data.dialog.allot.allot_info);
                //2.后台请求
                if (allotInfo) {
                    new Promise(function (resolve) {
                        //1.保存需求信息
                        resolve(require.allotRequireInfo(allotInfo));
                    }).then(function () {
                        //2.关闭弹框
                        app.$data.dialog.allot.visible = false
                    }).then(function () {
                        //3.列表刷新
                        require.loadList();
                    }).catch(function (error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            },
            /**
             * 需求重新分配
             */
            showDialogReAllot: function () {
                new Promise(function (resolve, reject) {
                    //1.获取需要分配的需求
                    var arrTaskId = [];
                    for (var taskId in app.$data.checkListReAllot) {
                        if (app.$data.checkListReAllot[taskId]) {
                            arrTaskId.push(taskId);
                        }
                    }
                    if (arrTaskId.length == 0) {
                        reject(new Error('请选择需要重新分配的需求'));
                    } else {
                        app.$data.dialog.reallot.reallot_info.task_id.value = arrTaskId.join();
                        resolve();
                    }
                }).then(function () {
                    //2.加载弹框信息
                    require.loadReAllotInfo();
                }).then(function () {
                    //3.显示弹框
                    app.$data.dialog.reallot.visible = true;
                }).catch(function (error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 需求重新分配
             */
            reallotRequireInfo: function () {
                //1.数据检查
                var allotInfo = validator.check(app.$data.dialog.reallot.reallot_info);
                //2.后台请求
                if (allotInfo) {
                    new Promise(function (resolve) {
                        //1.保存需求信息
                        resolve(require.reallotRequireInfo(allotInfo));
                    }).then(function () {
                        //2.关闭弹框
                        app.$data.dialog.reallot.visible = false
                    }).then(function () {
                        //3.列表刷新
                        require.loadList();
                    }).then(function () {
                        //4.去除选中
                        for (var index in app.$data.checkListReAllot) {
                            app.$data.checkListReAllot[index] = false;
                        }
                    }).catch(function (error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            },
            /**
             * 送测
             */
            showDialogQa: function () {
                new Promise(function (resolve, reject) {
                    //1.获取需要送测的需求
                    var arrTaskId = [];
                    for (var taskId in app.$data.checkListQa) {
                        if (app.$data.checkListQa[taskId]) {
                            arrTaskId.push(taskId);
                        }
                    }
                    if (arrTaskId.length == 0) {
                        reject(new Error('请选择需要送测的需求'));
                    } else {
                        app.$data.dialog.qa.qa_info.task_id.value = arrTaskId.join();
                        app.$data.dialog.qa.conflict = [];
                        resolve();
                    }
                }).then(function () {
                    //2.加载弹框信息
                    require.loadQaInfo();
                }).then(function () {
                    //3.显示弹框
                    app.$data.dialog.qa.visible = true;
                }).catch(function (error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 需求送测
             */
            qaRequireInfo: function () {
                this.$confirm('确定要送测需求吗?', {
                    type: 'warning',
                    dangerouslyUseHTMLString: true
                }).then(function () {
                    //1.数据检查
                    var qaInfo = validator.check(app.$data.dialog.qa.qa_info);
                    //2.后台请求
                    if (qaInfo) {
                        new Promise(function (resolve) {
                            //1.保存需求信息
                            resolve(require.qaRequireInfo(qaInfo));
                        }).then(function () {
                            //2.关闭弹框
                            app.$data.dialog.qa.visible = false
                        }).then(function () {
                            //3.列表刷新
                            require.loadList();
                        }).catch(function (error) {
                            if (error.data && error.data.conflict) {
                                app.$data.dialog.qa.conflict = error.data.conflict;
                            }
                            bmplugin.showErrMsg(error);
                        });
                    }
                }).catch(function () {
                });
            },
            /**
             * 导出
             */
            outputRequireInfo: function () {
                new Promise(function (resolve) {
                    //1.导出需求
                    resolve(require.outputRequireInfo());
                }).then(function (data) {
                    //2.下载
                    bmplugin.downloadFile(data['attach_id']);
                }).catch(function (error) {
                    bmplugin.showErrMsg(error);
                });
            },
            /**
             * 查询条件变化
             */
            searchChange: function () {
                app.$data.list.page.page_index = 1;
                require.loadList();
            },
            /**
             * 行样式
             */
            handleRowClassName: function (row, rowIndex) {
                return row.row.is_timeout == '1' ? 'timeout' : '';
            },
            /**
             * table格式化
             * 需求类型
             */
            formatModuleType: function (row, column, cellValue, index) {
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
            formatStatus: function (row, column, cellValue, index) {
                var status = {'00': '作废', '01': '需求', '02': '开发', '03': '就绪', '04': '送测', '05': '上线'};
                return status[cellValue];
            },
            /**
             * table格式化
             * 需求性质
             */
            formatXingzhi: function (row, column, cellValue, index) {
                var status = {'01': '确定', '02': '待定'};
                return status[cellValue];
            },
            /**
             * table格式化
             * 日期
             */
            formatDate: function (row, column, cellValue, index) {
                return cellValue ? cellValue.substring(0, 10) : '';
            },
            /**
             * table分页
             * 页数变化
             */
            pageIndexChange: function (curIndex) {
                if (app.$data.list.page.page_index != curIndex) {
                    app.$data.list.page.page_index = curIndex;
                    require.loadList();
                }
            },
            /**
             * table分页
             * 每页条数变化
             */
            pageSizeChange: function () {
                app.$data.list.page.page_index = 1;
                require.loadList();
            }
        },
        mounted: function () {
            this.$nextTick(function () {
                require.loadAuthButton();
                require.loadBaseInfo();
                require.loadList();
            });
        }
    });
}(Vue, window, document));