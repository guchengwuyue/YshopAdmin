var CMS = {

    events: {
        //请求成功的回调
        onAjaxSuccess: function (ret, onAjaxSuccess) {
            var data = typeof ret.data !== 'undefined' ? ret.data : null;
            var msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : '操作成功';

            if (typeof onAjaxSuccess === 'function') {
                var result = onAjaxSuccess.call(this, data, ret);
                if (result === false)
                    return;
            }
            layer.msg(msg, {icon: 1});
        },
        //请求错误的回调
        onAjaxError: function (ret, onAjaxError) {
            var data = typeof ret.data !== 'undefined' ? ret.data : null;
            var msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : '操作失败';
            if (typeof onAjaxError === 'function') {
                var result = onAjaxError.call(this, data, ret);
                if (result === false) {
                    return;
                }
            }
            layer.msg(msg, {icon: 2});
        },
        //服务器响应数据后
        onAjaxResponse: function (response) {
            try {
                var ret = typeof response === 'object' ? response : JSON.parse(response);
                if (!ret.hasOwnProperty('code')) {
                    $.extend(ret, {code: -2, msg: response, data: null});
                }
            } catch (e) {
                var ret = {code: -1, msg: e.message, data: null};
            }
            return ret;
        }
    },
    api: {
        //获取修复后可访问的cdn链接
        cdnurl: function (url) {
            return /^(?:[a-z]+:)?\/\//i.test(url) ? url : Config.upload.cdnurl + url;
        },
        //发送Ajax请求
        ajax: function (options, success, error) {
            options = typeof options === 'string' ? {url: options} : options;
            var st, index = 0;
            st = setTimeout(function () {
                index = layer.load();
            }, 150);
            options = $.extend({
                type: "POST",
                dataType: "json",
                xhrFields: {
                    withCredentials: true
                },
                success: function (ret) {
                    clearTimeout(st);
                    index && layer.close(index);
                    ret = CMS.events.onAjaxResponse(ret);
                    if (ret.code === 1) {
                        CMS.events.onAjaxSuccess(ret, success);
                    } else {
                        CMS.events.onAjaxError(ret, error);
                    }
                },
                error: function (xhr) {
                    clearTimeout(st);
                    index && layer.close(index);
                    var ret = {code: xhr.status, msg: xhr.statusText, data: null};
                    CMS.events.onAjaxError(ret, error);
                }
            }, options);
            return $.ajax(options);
        },
        //提示并跳转
        msg: function (message, url) {
            var callback = typeof url === 'function' ? url : function () {
                if (typeof url !== 'undefined' && url) {
                    location.href = url;
                }
            };
            layer.msg(message, {
                icon: 1,
                time: 2000
            }, callback);
        },
        //表单提交事件
        form: function (elem, success, error, submit) {
            var delegation = typeof elem === 'object' && typeof elem.prevObject !== 'undefined' ? elem.prevObject : document;
            $(delegation).on("submit", elem, function (e) {
                var form = $(e.target);
                if (typeof submit === 'function') {
                    if (false === submit.call(form, success, error)) {
                        return false;
                    }
                }
                $("[type=submit]", form).prop("disabled", true);
                CMS.api.ajax({
                    url: form.attr("action"),
                    data: form.serialize(),
                    complete: function (xhr) {
                        var token = xhr.getResponseHeader('__token__');
                        if (token) {
                            $("input[name='__token__']").val(token);
                        }
                        $("[type=submit]", form).prop("disabled", false);
                    }
                }, function (data, ret) {
                    //刷新客户端token
                    if (data && typeof data.token !== 'undefined') {
                        $("input[name='__token__']").val(data.token);
                    }
                    //自动保存草稿设置
                    var autosaveKey = $("textarea[data-autosave-key]", form).data("autosave-key");
                    if (autosaveKey && localStorage) {
                        localStorage.removeItem("autosave-" + autosaveKey);
                        $(".md-autosave", form).addClass("hidden");
                    }
                    if (typeof success === 'function') {
                        if (false === success.call(form, data, ret)) {
                            return false;
                        }
                    }
                }, function (data, ret) {
                    //刷新客户端token
                    if (data && typeof data.token !== 'undefined') {
                        $("input[name='__token__']").val(data.token);
                    }
                    if (typeof error === 'function') {
                        if (false === error.call(form, data, ret)) {
                            return false;
                        }
                    }
                });
                return false;
            });
        },
        //localStorage存储
        storage: function (key, value) {
            key = key.split('.');

            var _key = key[0];
            var o = JSON.parse(localStorage.getItem(_key));

            if (typeof value === 'undefined') {
                if (o == null)
                    return null;
                if (key.length === 1) {
                    return o;
                }
                _key = key[1];
                return typeof o[_key] !== 'undefined' ? o[_key] : null;
            } else {
                if (key.length === 1) {
                    o = value;
                } else {
                    if (o && typeof o === 'object') {
                        o[key[1]] = value;
                    } else {
                        o = {};
                        o[key[1]] = value;
                    }
                }
                localStorage.setItem(_key, JSON.stringify(o));
            }
        }
    }
}