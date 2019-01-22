var bmcommonjs = {
    /**
     * 刷新页面
     */
    refresh: function(data) {
        //ajax没有登录
        if (data && data["refresh"] && data["refresh"] == "1") {
            var href = window.location.pathname.indexOf('/jobseek/') >= 0 ? '/jobseek/login' : '/interview/login';
            window.location.href = href + window.location.search;
            return false;
        }
        return true;
    },
    /**
     * 去除字符串左边空格
     */
    ltrim: function(str) {
        return typeof (str) !== 'string' ? '' : str.replace(/(^\s*)/g, "");
    },
    /**
     * 去除字符串右边空格
     */
    rtrim: function(str) {
        return typeof (str) !== 'string' ? '' : str.replace(/(\s*$)/g, "");
    },
    /**
     * 去除字符串两边空格
     */
    trim: function(str) {
        return typeof (str) !== 'string' ? '' : str.replace(/(^\s*)|(\s*$)/g, "");
    },
    /**
     * 获取访问链接中的指定参数
     */
    getparam: function(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) {
            return  unescape(r[2]);
        }
        return '';
    },
    /*
     *   < ; > ; \r\n  转义
     */
    htmlEncode: function(str) {
        var retstr = "";
        if ($.trim(str).length === 0) {
            return "";
        }
        retstr = str.replace(/</g, "&lt;");
        retstr = retstr.replace(/>/g, "&gt;");
        retstr = retstr.replace(/[\r\n]/g, "<br/>");
        return retstr;
    },
    /**
     * 是否手机浏览器
     */
    isMobile: function() {
        var sUserAgent = navigator.userAgent.toLowerCase(),
                bIsIpad = sUserAgent.match(/ipad/i) == "ipad",
                bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os",
                bIsMidp = sUserAgent.match(/midp/i) == "midp",
                bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4",
                bIsUc = sUserAgent.match(/ucweb/i) == "ucweb",
                bIsAndroid = sUserAgent.match(/android/i) == "android",
                bIsCE = sUserAgent.match(/windows ce/i) == "windows ce",
                bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile",
                bIsWebview = sUserAgent.match(/webview/i) == "webview";
        return (bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM);
    },
    /**
     * 是否苹果浏览器
     */
    isIOS: function() {
        var useragent = navigator.userAgent.toLowerCase();
        if (/(iphone|ipad|ipod|ios)/i.test(useragent) || (/(qb)/i.test(useragent) && !/(micromessenger)/i.test(useragent))) {
            return true;
        } else {
            return false;
        }
    },
    /**
     * 处理表情符号
     */
    dealEmoji: function(str) {
        //微信浏览器字符转换
        str = unescape(escape(str).replace(/%uE[0-5][0-5][0-9A-F](%20)*/g, ''));
        //标准字符转换      
        str = str.replace(/[\ud800-\udbff][\udc00-\udfff]/ig, '');
        //非标准字符转换
        var emojis = [
            '(%uFE0F(%u[0-9A-Z][0-9A-Z][0-9A-Z][0-9A-Z])*)', '%u203C', '%u200D', '%u2049', '%u270A', '%u270C', '%u26F3', '%u26BE', '%u26BD', '%u26F5', '%u2708', '%u2753', '%u2757', '%u2764', '%u26EA', '%u26FD', '%u2615', '%u26C4', '%u2601', '%u2600', '%u2614', '%u26F2', '%u26FA', '%u2668', '%u303D', '%u26A1', '%u2734', '%u2733', '%u267F', '%u2665', '%u2666', '%u2660', '%u2663', '%23%u20E3', '%u27BF', '%u2B1C', '1%u20E3', '2%u20E3', '3%u20E3', '4%u20E3', '5%u20E3', '6%u20E3', '7%u20E3', '8%u20E3', '9%u20E3', '0%u20E3', '%u2B06', '%u2B07', '%u27A1', '%u2B05', '%u2197', '%u2196', '%u2198', '%u2199', '%u25B6', '%u25C0', '%u23E9', '%u23EA', '%u2648', '%u2649', '%u264A', '%u264B', '%u264C', '%u264D', '%u264E', '%u264F', '%u2650', '%u2651', '%u2652', '%u2653', '%u26CE', '%A9', '%AE', '%u26A0', '%u3297', '%u2702', '%u3299', '%u2728', '%u2B50', '%u2B55', '%u274C', '%u2754', '%u2755', '%u263A', '%uD83C%uDDEF%uD83C%uDDF5', '%uD83C%uDDFA%uD83C%uDDF8', '%uD83C%uDDEB%uD83C%uDDF7', '%uD83C%uDDE9%uD83C%uDDEA', '%uD83C%uDDEE%uD83C%uDDF9', '%uD83C%uDDEC%uD83C%uDDE7', '%uD83C%uDDE8%uD83C%uDDF3', '%uD83C%uDDF0%uD83C%uDDF7', '%u2122'
        ];
        var pattstr = '';
        for (var i = 0; i < emojis.length; i++) {
            pattstr = pattstr + (i == 0 ? '(' + emojis[i] + ')' : '|(' + emojis[i] + ')');
        }
        eval("var patt = /" + pattstr + "/ig;");
        str = unescape(escape(str).replace(patt, ''));
        //苹果手机
        str = str.replace(/[\uD83C|\uD83D|\uD83E][\uDC00-\uDFFF][\u200D|\uFE0F]|[\uD83C|\uD83D|\uD83E][\uDC00-\uDFFF]|[0-9|*|#]\uFE0F\u20E3|[0-9|#]\u20E3|[\u203C-\u3299]\uFE0F\u200D|[\u203C-\u3299]\uFE0F|[\u2122-\u2B55]|\u303D|\uA9|\uAE|\u3030/ig, '');
        return str;
    },
    /**
     * 设置LocalStorage数据，需要浏览器支持
     * key:键
     * value:值
     */
    setLocalStorage: function(key, value) {
        try {
            //判断浏览器是否支持localStorge （针对苹果safari的无痕模式）
            if (window.sessionStorage) {
                var storage = window.sessionStorage;
                storage.setItem(key, value);
            }
        } catch (error) {
        }
    },
    /**
     * 获取LocalStorage数据，需要浏览器支持
     * key:键
     */
    getLocalStorage: function(key) {
        try {
            if (window.sessionStorage) {
                var storage = window.sessionStorage;
                return storage.getItem(key) == null ? '' : storage.getItem(key);
            } else {
                return '';
            }
        } catch (error) {
            return '';
        }

    },
    getlength: function(str) {
        //去除空格（全角或半角），回车计算字数
//        str = unescape(escape(str).replace(/%20/g, '').replace(/%u3000/g, '').replace(/%0A/g, ''));
        //mysql
        return str.length;
        //mssql
        var l = str.length;
        var blen = 0;
        for (var i = 0; i < l; i++) {
            if ((str.charCodeAt(i) & 0xff00) != 0) {
                blen++;
            }
            blen++;
        }
        return blen;
    },
    /*
     * 星星评价
     * 参数
     *  id:父元素id值
     *  rank:评价等级值
     *  isclick:如已有评价值，是否再编辑 ture为可以，false则不可编辑，缺省为不可编辑
     * 方法
     *   bmcommonjs.initStar(id)
     *   bmcommonjs.initStar(id，rank,isclick)
     */
    renderStar: function(id, num) {
        var oli = $("#" + id).find("ul li");
        $.each(oli, function(index) {
            if (index < num) {
                $(oli[index]).find('a').css('background-position', '-1.5rem 0rem');
            } else {
                $(oli[index]).find('a').css('background-position', '0rem 0rem');
            }
        });
    },
    /**
     * 跳转到空白页面
     */
    jumpBlankPage: function() {
        var userAgent = navigator.userAgent;
        if (userAgent.indexOf("Firefox") !== -1 || userAgent.indexOf("Chrome") !== -1) {
            window.location.href = "about:blank";
        } else {
            window.opener = null;
            window.open("", "_self");
            window.close();
        }
    }
};
