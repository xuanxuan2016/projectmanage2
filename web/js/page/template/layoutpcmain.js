(function(Vue, window, document) {

    /**
     * 业务对象
     */
    var layout = {
        /**
         * 退出
         */
        logout: function() {
            bmplugin.ajax.post('/web/common/login/logout', {}).then(function(data) {
                window.location.href = '/web/common/login';
            }).catch(function(error) {
                bmplugin.showErrMsg(error);
            });
        },
        /**
         * 菜单切换
         */
        selectMenu: function(index) {
            window.location.href = index;
        },
        /**
         * 修改密码
         */
        changePwd: function() {
            window.location.href = '/web/auth/password';
        }
    };

    /**
     * vue对象
     */
    var appMenu = new Vue({
        el: '#menu',
        data: {
            menu_collapse: false
        },
        created: function() {
        },
        methods: {
            /**
             * 菜单切换
             */
            selectMenu: function(index) {
                layout.selectMenu(index);
            }
        },
        mounted: function() {
            this.$nextTick(function() {
            });
        }
    });

    var appNav = new Vue({
        el: '#nav',
        data: {
        },
        created: function() {
        },
        methods: {
        },
        mounted: function() {
            this.$nextTick(function() {
            });
        }
    });

    var appHeader = new Vue({
        el: '#header',
        data: {
            menu_collapse: false
        },
        created: function() {
        },
        methods: {
            menuCollapse: function() {
                this.menu_collapse = !this.menu_collapse;
            },
            handleCommand: function(command) {
                switch (command) {
                    case 'logout':
                        layout.logout();
                        break;
                    case 'changepwd':
                        layout.changePwd();
                        break;
                }
            }
        },
        watch: {
            'menu_collapse': function(isCollapse) {
                appMenu.$data.menu_collapse = isCollapse;
                document.getElementsByClassName('content')[0].style.left = isCollapse ? '64px' : '';
            }
        },
        mounted: function() {
            this.$nextTick(function() {
            });
        }
    });

}(Vue, window, document));


