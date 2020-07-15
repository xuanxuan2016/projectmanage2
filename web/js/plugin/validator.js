/**
 * 数据检查
 */
validator = {
    /**
     * 校验多条数据
     * 校验不通过返回 false
     * 校验通过 返回数据
     */
    check: function(obj) {
        //1.obj检查
        if (typeof obj !== 'object') {
            return {};
        }
        //2.遍历检查
        var objRet = {};
        for (var objKey in obj) {
            var objValue = obj[objKey];
            //2.1.记录返回值
            objRet[objKey] = typeof objValue.value === 'undefined' ? objValue : objValue.value;
            //2.2.遍历检查规则
            if (objValue.rules && typeof objValue.value !== 'undefined') {
                if (validator.checkOne(objValue) === false) {
                    return false;
                }
            }
        }
        //3.返回数据对象
        return objRet;
    },
    /**
     * 校验单条数据
     */
    checkOne: function(obj) {
        var val = obj.value;
        if (obj.rules.hasOwnProperty('trim')) {
            val = validator.trim(val);
        }
        for (var ruleKey in obj.rules) {
            if (ruleKey === 'trim') {
                continue;
            }
            var rule = obj.rules[ruleKey];
            if (rule.value !== false && typeof eval(validator[ruleKey]) === 'function') {
                if (eval(validator[ruleKey])(val, rule) === false) {
                    validator.showErrMsg(rule.err);
                    return false;
                }
            }
        }
        return val;
    },
    //去除空格
    trim: function(data) {
        if (data === null) {
            return '';
        }
        if (typeof data === 'string' || typeof data === 'number') {
            return data.replace(/(^\s*)|(\s*$)/g, "");
        }
        if (typeof data === 'object') {
            for (var i in data) {
                data[i] = typeof data[i] !== 'string' && typeof data !== 'number' ? '' : data[i].replace(/(^\s*)|(\s*$)/g, "");
            }
            return data;
        }
        return '';
    },
    //不空
    required: function(data, rule) {
        if (data.length === 0) {
            return false;
        }
    },
    //最小长度
    min_len: function(data, rule) {
        if (data.length < rule.value) {
            return false;
        }
    },
    //最大长度
    max_len: function(data, rule) {
        if (data.length > rule.value) {
            return false;
        }
    },
    //是否在某个选项内
    optional: function(data, rule) {
        rule.value = rule.value || [];
        if (rule.value.indexOf(data) === -1) {
            return false;
        }
    },
    //校验时间
    datetime: function(data, rule) {
        if (new Date(data) === 'Invalid Date') {
            return false;
        }
    },
    //是否有特殊字符
    hasother: function(data, rule) {
        if (!reg.hasother.test(data)) {
            return false;
        }
    },
    //手机号
    phone: function(data, rule) {
        if (!reg.phone.test(data)) {
            return false;
        }
    },
    //手机号+固话/传真
    telephonerich: function(data, rule) {
        if (!reg.telephonerich.test(data)) {
            return false;
        }
    },
    //邮箱
    email: function(data, rule) {
        if (!reg.email.test(data)) {
            return false;
        }
    },
    //数字
    number: function(data, rule) {
        if (!reg.number.test(data)) {
            return false;
        }
    },
    /*---------------------------------------- 联合校验 -------------------------------------------*/
    /**
     * @param array data [1, 2] 数组中仅有两个值
     * @param object rule rule.value=运算符(> == < ...)
     */
    ucompare: function(data, rule) {
        switch (rule.value) {
            case "==":
            case "===":
                return eval('"' + data[0] + '"' + rule.value + '"' + data[1] + '"');
                break;
            default:
                return eval(data[0] + rule.value + data[1]);
                break;
        }
    },
    /**
     * @param array data ['张三', '李四', '', '王五' ...]
     */
    urequired: function(data, rule) {
        var empty = $.grep(data, function(value) {
            return value === '';
        });
        if (empty.length === data.length) {
            return false;
        }
    },
    /**
     * 显示错误信息
     */
    showErrMsg: function(err) {
        var errMsg = "";
        if (validator.trim(err.err_msg).length && err.err_msg !== 'undefined') {
            errMsg += err.err_msg;
        }
        if (validator.trim(err.err_code).length && err.err_code !== 'undefined') {
            errMsg += (tipsconf[err.err_code] !== 'undefined' ? tipsconf[err.err_code] : "");
        }
        bmplugin.showErrMsg(errMsg);
    }

};

var reg = {
    //邮箱
    email: /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/i,
    //国号+手机
    phonerich: /(^(([+]{0,1}\d{2,4}|\d{2,4})-?)?1[34578]\d{9}$)/,
    //国号+电话/传真
    telephonerich: /(^(([+]{0,1}\d{2,4}|\d{2,4})-?)?((\d{3,4})-?)?(\d{6,8})(-?(\d{1,6}))?$)/,
    //手机
    phone: /^1[3456789][0-9]{9}$/,
    //电话/传真
    telephone: /^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?(\(0\d{2,3}\)|0\d{2,3})?(\(0\d{2,3}\)|0\d{2,3})?[1-9]\d{6,7}(\-\d{1,5})?$/,
    //qq号
    qq: /[1-9][0-9]{4,}$/,
    //微信号（中英文下划线数字）
    weixin: /^([0-9a-zA-Z]+[_0-9a-zA-Z]*)$/,
    //超链接
    url: /^http[s]?:\/\/([\w-]+\.)+[\w-]+([\w-./?%&=]*)?$/,
    //判断是否有大写字母
    hascapital: /^.*[A-Z]+.*$/,
    //判断是否有小写字母
    haslowercase: /^.*[a-z]+.*$/,
    //判断是否有数字
    hasnumber: /^.*[0-9]+.*$/,
    //判断是否含有其它字符
    hasother: /^.*[^0-9A-Za-z]+.*$/,
    //判断是否为数字
    number: /^[0-9]+$/,
    //判断是否是数字或字母
    ischarornum: /^([0-9]+|\w+)$/,
    //判断是否是正数，包括小数点
    posnum: /^(0|([1-9]\d*))(\.\d+)?$/,
    //判断是否是整数
    intnum: /^-?[0-9]+$/,
    //邮编
    postcode: /^[1-9][0-9]{5}$/,
    //实数
    realnum: /^[-]?(0|([1-9]\d*))(\.\d+)?$/,
    //英文字母
    english: /^[a-zA-Z]+$/
};

