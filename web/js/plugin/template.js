/* 
 * 面试详情页
 */
(function($, Vue, window, document) {
    /**
     * 业务对象
     */
    var invite = {
        //加载改期权限
        loadData: function() {
            $.bmajax.ajax({
                url: '/web/interview/singlepage/invite/loaddata',
                data: Object.assign(param, app.url_param),
                success: function(data) {
                    if (data && typeof(data.success) !== "undefined") {
                        if (data.success === 1) {
                            dealData.dealLoadData(data);
                        } else {
                            
                        }
                    }
                }
            });
        },
        //初始化
        init: function() {
            this.loadPostponeAuth();
            this.loadResumeInfo();
            this.loadInviteInfo();
            this.loadEvalList();
            this.loadAvatar();
        }

    };
    var dealData = {
        dealLoadData: function(data) {
            //一般都是赋值或修改 vue对象内的data值
        }
        
    };
    /**
     * jquery插件
     */
    var jqplugin = {
        init: function() {}
    };
    jqplugin.init();

    /**
     * document
     */
    var dom = {
        //初始化
        init: function() {}
    };
    dom.init();

    /**
     * vue对象
     */
    var app = new Vue({
        el: '#invite',
        data: {
            //url 参数
            url_param: {
                invite_id: bmcommonjs.getparam('invite_id'),
                jobseek_id: bmcommonjs.getparam('jobseek_id'),
                resume_id: bmcommonjs.getparam('resume_id'),
                hr_id: bmcommonjs.getparam('hr_id')
            },
            //往后台提交的数据
            form_param: {
                //提交评价参数
                submit: {
                    //需要校验的数据
                    submit1: {
                        value: '',
                        rules: {
                            trim: { value: true },
                            optional: { value: ['', '01', '02', '03', '04', '05', '06'], err: { err_msg: '评价标签无效' } }
                        }
                    },
                    //需要校验的数据
                    submit12: {
                        value: '',
                        rules: {
                            trim: { value: true },
                            max_len: { value: 200, err: { err_msg: '评价内容长度超过限制' } }
                        }
                    },
                    //不需要校验的数据
                    submit13: ''
                }
            }

        },
        //局部过滤器
        filters: {
            //统一处理页面上的一些文本等
            filter1: function() {  
            }
        },
        created: function() {
            // 可以再此处请求数据
        },
        methods: {
            //一般是页面的事件
            submit: function() {
                //统一校验方法
                var param = validator.check(this.form_param.submit);
            }
        },
        updated: function() {
            
        },
        mounted: function() {
            this.$nextTick(function() {
                invite.init();
            });
        }
    });
}(jQuery, Vue, window, document));