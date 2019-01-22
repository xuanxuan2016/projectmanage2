
/**
 * 时间格式化过滤器
 * @param string val 
 * @param string format 'yyyy-MM-dd (周w) hh:mm:ss'
 */
Vue.filter('timeFormat', function(val, format) {
    var week = ['日', '一', '二', '三', '四', '五', '六']; 
    var value = new Date(val);
    if ($.trim(value) === 'Invalid Date') {
        return val;
    }
    var o = {
        "M+": value.getMonth() + 1,
        "d+": value.getDate(),
        "h+": value.getHours(),
        "m+": value.getMinutes(),
        "s+": value.getSeconds(),
        "w+": week[value.getDay()]
    };
    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (value.getFullYear() + "") . substr(4 - RegExp.$1.length));
    }
    for (var k in o) {
        if (new RegExp("(" + k +")").test(format)) {
            format = format.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length )));
        }
    }
    return format; 
});

Vue.filter('textDefault', function(val, code) {
    if ($.trim(val).length) {
        return val;
    }
    if (code !== 'undefined' && $.inArray(code, Object.keys(emptyconf)) !== -1) {
        return emptyconf[code];
    }
    //默认未填写
    return emptyconf['001'];
});


Vue.filter('newLine2Br', function(str) {
    if ($.trim(str).length === 0) {
        return "";
    }
    return str.replace(/[\r\n]/g, "<br/>");
});