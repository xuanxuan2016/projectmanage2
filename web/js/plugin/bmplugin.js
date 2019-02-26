/**
 * 通用组件
 */
window.bmplugin = {};

/**
 * axios配置
 */
(function(Vue, window, axios) {
    /**
     * vue对象
     */
    var vuePlugin = new Vue();
    /**
     * loading对象
     */
    var loading = null;

    /**
     * 请求拦截器
     * 1.显示loading图标
     */
    axios.interceptors.request.use(function(config) {
        var data = config.data || {};
        if (typeof data.is_show_loading === 'undefined' || data.is_show_loading === true) {
            loading = vuePlugin.$loading({
                lock: true,
                text: '拼命加载中',
                spinner: 'el-icon-loading',
                background: 'rgba(0, 0, 0, 0.5)'
            });
        }
        return config;
    }, function(error) {
        // Do something with request error
        return Promise.reject(error);
    });

    /**
     * 请求拦截器
     * 1.关闭loading图标
     */
    axios.interceptors.response.use(function(response) {
        if (loading) {
            loading.close();
        }
        return response;
    }, function(error) {
        // Do something with response error
        return Promise.reject(error);
    });

    /**
     * 请求拦截器
     * 2.检查返回值
     */
    axios.interceptors.response.use(function(response) {
        //从response中获取data
        var data = response.data;
        //检查返回值
        if (data && typeof data === 'object') {
            Object.assign(response, data);
            if (data.success !== 1) {
                //返回数据
                return Promise.reject(data);
            }
        } else {
            return Promise.reject({
                success: 0,
                err_msg: 'error',
                err_code: '0001'
            });
        }
        return response;
    }, function(error) {
        // Do something with response error
        return Promise.reject(error);
    });

    /**
     * 请求拦截器
     * 3.检查status
     */
    axios.interceptors.response.use(function(response) {
        //从response中获取status
        var status = response.status;
        //检查status
        if (status >= 400) {
            return Promise.reject({
                success: 0,
                err_msg: 'error',
                err_code: '0002'
            });
        }
        //返回data
        return response.data;
    }, function(error) {
        // Do something with response error
        return Promise.reject(error);
    });

    /**
     * 添加方法到组件
     */
    window.bmplugin.ajax = axios;
}(Vue, window, axios));

/**
 * 错误信息提示
 */
(function(Vue, window) {
    /**
     * vue对象
     */
    var vueError = new Vue();

    /**
     * 添加方法到组件
     */
    window.bmplugin.showErrMsg = function(error) {
        var errMsg = typeof error === 'string' ? error : (error.err_msg || error.message || '错误提示');
        vueError.$message({
            showClose: false,
            message: errMsg,
            type: 'error'
        });
    };
}(Vue, window));

/**
 * 文件下载
 */
(function(Vue, window) {
    /**
     * 创建下载插件对象
     */
    function createDownloadObj() {
        var downLoadDiv = document.createElement('div');
        downLoadDiv.setAttribute('id', 'BMDownload');
        downLoadDiv.setAttribute('class', 'download');

        var downLoadForm = document.createElement('form');
        downLoadForm.setAttribute('name', 'BMDownload_form');
        downLoadForm.setAttribute('id', 'BMDownload_form');
        downLoadForm.setAttribute('target', 'BMDownload_iframe');
        downLoadForm.setAttribute('method', 'post');
        downLoadForm.setAttribute('encType', 'multipart/form-data');
        downLoadForm.setAttribute('action', '/web/common/common/downloadfile');

        var downLoadInput = document.createElement('input');
        downLoadInput.setAttribute('type', 'text');
        downLoadInput.setAttribute('name', 'attach_id');

        var downLoadIframe = document.createElement('iframe');
        downLoadIframe.setAttribute('id', 'BMDownload_iframe');
        downLoadIframe.setAttribute('name', 'BMDownload_iframe');

        downLoadForm.append(downLoadInput);
        downLoadDiv.append(downLoadForm);
        downLoadDiv.append(downLoadIframe);
        document.getElementsByTagName('body')[0].append(downLoadDiv);
    }

    /**
     * 获取下载插件对象
     */
    function getDownloadObj(attachId) {
        if (!document.getElementById('BMDownload')) {
            createDownloadObj(attachId);
        }
        document.getElementById('BMDownload_form').children[0].setAttribute('value', attachId);
    }

    /**
     * 添加方法到组件
     */
    window.bmplugin.downloadFile = function(attachId) {
        getDownloadObj(attachId);
        document.getElementById('BMDownload_form').submit();
    };
}(Vue, window));


