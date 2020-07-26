# 目录

* [命名规范](#1)
* [常用快捷方法](#2)
* [公用方法](#3)
* [业务处理vue](#4)
* [业务处理小程序](#6)
* [代码格式](#5)

---

### <div id="1">命名规范</div>

变量命名规则，包含布尔型、整型、小数型、字符串型、数组型、对象型、日期型、伪类型。

总体原则是【类型】+【变量名】，类型可以是上面类型外的其他类型，必须是可读的。

```
布尔型变量(boolean)：
$blnVar;
整型变量(integer)：
$intVar;
小数型变量(decimal)：
$decVar;
字符串型变量(string)：
$strVar;
数组型变量(array)：
$arrVar;
对象型变量(object)：
$objVar;
日期型变量(datetime)：
$dateVar;
伪类型变量(mix)（可以接受不同的类型）：
$mixVar;
```

对象名、对象名的方法名、参数名、局部变量都统一使用lowerCamelCase风格，必须遵从驼峰形式。
> localValue / getHttpMessage() / inputUserId

Vue对象data中属性全部小写，单词间用下划线隔开，力求语义表达完整清楚，不要嫌名字长。
> 如使用max_stock_count，而不要使用max_count。

常量命名全部大写，单词间用下划线隔开，力求语义表达完整清楚，不要嫌名字长。
> 如使用MAX_STOCK_COUNT，而不要使用MAX_COUNT。

---

### <div id="2">常用快捷方法</div>

在开发需求时，常用的一些方法。更多其它方法，请参见【公用方法】里的内容。

```
获取页面参数：
    获取单个参数：bmcommonjs.getparam('参数名');
```

---

### <div id="3">公用方法</div>

bmplugin

> 包含bmplugin.ajax、bmplugin.showErrMsg、bmplugin.downloadFile方法。

bmplugin.ajax用于执行http时使用。

```
bmplugin.ajax.post('/web/teacherbook/teacherbook/loadlist', listInfo).then(function(data) {
    app.$data.list.data = data.list;
}).catch(function(error) {
    bmplugin.showErrMsg(error);
});
```    

bmplugin.showErrMsg用于显示错误信息时使用。

```
bmplugin.showErrMsg(error);
```

bmplugin.downloadFile用于下载文件时使用。

```
bmplugin.downloadFile(data['attach_id'], data['timestamp'], data['signkey']);
```

---
validator

>通用的数据检查方法，用于在需要提交数据到后台时，将数据对象按照配置的规则进行检查。检查成功返回用于提交后台的数据对象；检查失败弹出错误提示框，返回null值，结束函数运行。

```
#数据对象配置
//参课人数
actual_number: {
    title: '学员人数（实际）',
    visible: false,
    actual_number_info_blank: {
        reservation_id: "",
        actual_number: ""
    },
    actual_number_info: {
        reservation_id: "",
        actual_number: {
            value: '',
            rules: {
                trim: {value: true},
                required: {value: true, err: {err_msg: '请输入参课人数'}},
                posint: {value: true, err: {err_msg: '参课人数需要为正整数'}}
            }
        }

    }
}

#数据检查
var objRequireInfo = validator.check(app.$data.dialog['actual_number']['actual_number_info']);
//检查成功，后台请求
//成功数据格式为：{'reservation_id':'1','actual_number':'6'}
if (objRequireInfo) {
    //http请求
}
```  
---
mainconfig

>用于配置系统中通用的一些属性参数。

---
bmcommonjs

>用于存放系统中通用的一些方法。

```
bmcommonjs.getparam('参数名');
```

---

### <div id="4">业务处理vue</div>

<p>
Tips：此为格式说明，实际业务中请按需处理。
</p>

##### 页面代码基本格式

<p>
一个页面js一般包含2个对象，一个业务对象，一个Vue对象。2个对象处理逻辑的侧重点不同。
</p>

```
(function(Vue, window, document) {
    /**
     * 业务对象，用来执行业务相关的处理
     * 1.http请求
     * 2.设置弹框数据
     */
    var book = {
        /**
         * 加载页面基础数据
         */
        loadBaseInfo: function() {
            bmplugin.ajax.post('/web/mybook/book/loadbaseinfo', {'is_show_loading': false, 'lecturer_id': app.$data.search.lecturer_id}).then(function(data) {
                app.$data.base_info.province = data.province;
                app.$data.base_info.city = data.city;
                app.$data.base_info.customer_name_list = data.customer_name_list;
            }).catch(function(error) {
                bmplugin.showErrMsg(error);
            });
        },
        /**
         * 设置弹框数据
         */
        initDialogInfo: function(dialogName, dialogInfo) {
            for (var objKey in dialogInfo) {
                if (typeof app.$data.dialog[dialogName][dialogName + '_info'][objKey] === 'undefined') {
                    continue;
                }
                if (app.$data.dialog[dialogName][dialogName + '_info'][objKey] !== null && typeof app.$data.dialog[dialogName][dialogName + '_info'][objKey].value !== 'undefined') {
                    app.$data.dialog[dialogName][dialogName + '_info'][objKey].value = dialogInfo[objKey];
                } else {
                    app.$data.dialog[dialogName][dialogName + '_info'][objKey] = dialogInfo[objKey];
                }
            }
        }
    };
    /**
     * vue对象
     */
    var app = new Vue({
        el: '#app',
        data: {
            /**
             * 页面基础信息
             */
            base_info: {
                province: [], //省
                city: [], //市
            },
            /**
             * table信息
             */
            list: {
                data: [],
                page: {
                    total: 0,
                    page_size: 10,
                    page_start: 1
                }
            },
            /**
             * 查询条件
             */
            search: {
                status: '', //预约状态
            },
            /**
             * 弹窗信息
             */
            dialog: {
                actual_number: {//参课人数
                    title: '学员人数（实际）',
                    visible: false,
                    actual_number_info_blank: {
                        reservation_id: "",
                        actual_number: ""
                    },
                    actual_number_info: {
                        reservation_id: "",
                        actual_number: {
                            value: '',
                            rules: {
                                trim: {value: true},
                                required: {value: true, err: {err_msg: '请输入参课人数'}},
                                posint: {value: true, err: {err_msg: '参课人数需要为正整数'}}
                            }
                        }
                    }
                }
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                book.loadBaseInfo();
                book.loadList();
            });
        },
        /**
         * 计算属性
         */
        computed: {
        },
        /**
         * data监听
         */
        watch: {
        },
        methods: {
            /**
             * 弹窗
             * 分类显示弹窗
             */
            showDialogRequire: function(row, dialogName) {
                switch (dialogName) {
                    case 'actual_number'://参课人数
                        this.getDialogDate(dialogName, row, function() {
                            book.initDialogInfo('actual_number', app.$data.dialog['actual_number']['actual_number_info_blank']);
                        });
                        break;
                }
            },
            /**
             * 参课人数
             * 确定
             */
            actualNumberOperate: function() {
                //1.数据检查
                var requireInfo = validator.check(app.$data.dialog['actual_number']['actual_number_info']);
                //2.后台请求
                if (requireInfo) {
                    new Promise(function(resolve) {
                        //1.保存需求信息
                        resolve(book.postActualNumber(requireInfo));
                    }).then(function() {
                        //2.关闭弹框
                        app.$data.dialog.actual_number.visible = false;
                    }).then(function() {
                        //3.列表刷新
                        book.loadList();
                    }).catch(function(error) {
                        bmplugin.showErrMsg(error);
                    });
                }
            }
        }
    });
}(Vue, window, document));
```

##### http请求代码模板

```
//1.数据检查
var requireInfo = validator.check(app.$data.dialog['actual_number']['actual_number_info']);
//2.后台请求
if (requireInfo) {
    new Promise(function(resolve) {
        //1.保存需求信息
        resolve(book.postActualNumber(requireInfo));
    }).then(function() {
        //2.关闭弹框
        app.$data.dialog.actual_number.visible = false;
    }).then(function() {
        //3.列表刷新
        book.loadList();
    }).catch(function(error) {
        bmplugin.showErrMsg(error);
    });
}
```

```
/**
 * 业务对象
 */
var book = {
    postActualNumber: function(info) {
        info = Object.assign(info, {});
        return bmplugin.ajax.post('/web/mybook/book/updatecoursemembers', info);
    }
}
```

##### 弹框代码模板

```
showDialogTodo: function(id) {
    //title
    if (id) {
        app.$data.dialog.todo.title = '编辑待办事项';
    } else {
        app.$data.dialog.todo.title = '新增待办事项';
    }
    //获取数据
    new Promise(function(resolve) {
        //1.加载信息
        resolve(todo.loadTodoInfo(id));
    }).then(function() {
        //2.显示弹框
        app.$data.dialog.todo.visible = true;
    }).catch(function(error) {
        bmplugin.showErrMsg(error);
    });
}
```

```
/**
 * 业务对象
 */
var todo = {
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
   }
}
```

---

### <div id="6">业务处理小程序</div>

<p>
Tips：此为格式说明，实际业务中请按需处理。
</p>

##### 页面代码基本格式

```
(import common from '../../../utils/common.js';

/**
 * 外部信息
 */
const app = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {

  },
  /**
   * 页面tap事件主入口
   */
  bindtap: function (event) {
    var strEventname = event.target.dataset.eventname || '';
    if (this.methods[strEventname]) {
      this.methods[strEventname](event);
    }
  },
  /**
   * 页面input事件主入口
   */
  bindinput: function (event) {
    var strEventname = (event.target.dataset.eventname || '').trim();
    if (strEventname != '') {
      strEventname = 'input' + strEventname[0].toUpperCase() + strEventname.substr(1);
    }
    if (this.methods[strEventname]) {
      this.methods[strEventname](event);
    }
  },
  /**
   * 页面focus事件主入口
   */
  bindfocus: function (event) {
    var strEventname = (event.target.dataset.eventname || '').trim();
    if (strEventname != '') {
      strEventname = 'focus' + strEventname[0].toUpperCase() + strEventname.substr(1);
    }
    if (this.methods[strEventname]) {
      this.methods[strEventname](event);
    }
  },
  /**
   * 页面blur事件主入口
   */
  bindblur: function (event) {
    var strEventname = (event.target.dataset.eventname || '').trim();
    if (strEventname != '') {
      strEventname = 'blur' + strEventname[0].toUpperCase() + strEventname.substr(1);
    }
    if (this.methods[strEventname]) {
      this.methods[strEventname](event);
    }
  },
  /**
   * 业务方法集合
   */
  methods: {
    test: function (event) {
      console.log('test', event);
    },
    inputTest: function (event) {
      console.log('inputTest', event);
    },
    focusTest: function (event) {
      console.log('focusTest', event);
    },
    blurTest: function (event) {
      console.log('blurTest', event);
    },
  },
  /**
   * http请求集合
   */
  http: {

  },
  /**
   * 加载页面
   * 每个页面都需要此方法，用于如下调用
   * 1.onShow
   */
  pageLoad: function () {
    var that = this;
    app.ready(() => {
    });
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.pageLoad();
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})
```

##### http请求代码模板

```
/**
* 注册
*/
register: function (that, e) {
 //1.数据检查
 var objRegisterInfo = common.validator.check(that.data.register_info);
 //2.后台请求
 if (objRegisterInfo) {
   var objParam = Object.assign(JSON.parse(JSON.stringify(that.data.url_param)), objRegisterInfo);
   new Promise((resolve, reject) => {
     resolve(that.http.register(objParam));
   }).then(res => {
     that.methods.pageBack(that, res);
   }).catch(err => {
     wx.showToast({
       title: err.err_msg,
       icon: 'none'
     });
   });
 }
}
```

```
/**
* http请求集合
*/
http: {
 /**
  * 注册
  */
 register: function (param) {
   return common.http.post('/miniapi/laravelframework/common/login/register', param);
 }
}
```

---

### <div id="5">代码格式</div>

为了使代码阅读起来流畅，请注意代码格式。

> 使用NetBeans开发工具时，可通过【右键->格式】来方便的格式化代码。使用其他IDE时，也请注意格式化代码。
