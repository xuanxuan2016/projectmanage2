(function(Vue, window, document) {
    /**
     * 业务对象
     */
    var tableinfo = {
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
            //2.后台请求
            if (listInfo) {
                bmplugin.ajax.post('/web/common/tableinfo/loadlist', listInfo).then(function(data) {
                    app.$data.list.data = data.list;
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        /**
         * 加载待办数据
         */
        loadDevRuleInfo: function(id) {
            return bmplugin.ajax.post('/web/common/tableinfo/loadtableinfoinfo', {id: id}).then(function(data) {
                app.$data.article = data.info;
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
             * 页面基础信息
             */
            base_info: {
            },
            /**
             * 查询条件
             */
            search: {
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
            },
            /**
             * 文章内容
             */
            article: '<h5>请选择相应规则（↑）进行查看。</h5>',
            /**
             * 当前选中的文章
             */
            active: ''
        },
        watch: {
        },
        created: function() {
        },
        methods: {
            /**
             * 修改
             */
            showArticle: function(id) {
                this.active = id;
                //获取数据
                new Promise(function(resolve) {
                    //1.加载角色信息
                    resolve(tableinfo.loadDevRuleInfo(id));
                }).catch(function(error) {
                    bmplugin.showErrMsg(error);
                });
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                //tableinfo.loadList();
            });
        }
    });
}(Vue, window, document));
