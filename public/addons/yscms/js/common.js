$(function () {
    window.isMobile = !!("ontouchstart" in window);

    function AddFavorite(sURL, sTitle) {
        if (/firefox/i.test(navigator.userAgent)) {
            return false;
        } else if (window.external && window.external.addFavorite) {
            window.external.addFavorite(sURL, sTitle);
            return true;
        } else if (window.sidebar && window.sidebar.addPanel) {
            window.sidebar.addPanel(sTitle, sURL, "");
            return true;
        } else {
            var touch = (navigator.userAgent.toLowerCase().indexOf('mac') != -1 ? 'Command' : 'CTRL');
            layer.msg('请使用 ' + touch + ' + D 添加到收藏夹.');
            return false;
        }
    }

    var len = function (str) {
        if (!str)
            return 0;
        var length = 0;
        for (var i = 0; i < str.length; i++) {
            if (str.charCodeAt(i) >= 0x4e00 && str.charCodeAt(i) <= 0x9fa5) {
                length += 2;
            } else {
                length++;
            }
        }
        return length;
    };

    //new LazyLoad({elements_selector: ".lazy"});

    layer.config({focusBtn: false});

    //栏目高亮（子项选中时父级一并高亮）
    var nav = $("header.header .navbar-nav").first();
    if ($("li.active", nav).length === 0) {
        var current = nav.data("current");
        var currentNav = $("a[href='" + location.href + "']", nav)[0] || $("a[href='" + location.pathname + "']", nav)[0] || $("li[value='" + current + "'] > a", nav)[0];
        currentNav && $(currentNav).parents("li").addClass("active");
    }
    $("li.active", nav).parents("li").addClass("active");

    $(".collapse.navbar-collapse.active").on("mousedown click", ".dropdown-menu .dropdown-submenu a[data-toggle='dropdown']", function (e) {
        e.stopPropagation();
    });

    $("header.header .navbar-right").insertBefore($("header.header .navbar-right").prev());

    //移动浏览器左右滑动
    $(document).on('touchstart', '.carousel', function (event) {
        const xClick = event.originalEvent.touches[0].pageX;
        $(this).one('touchmove', function (event) {
            const xMove = event.originalEvent.touches[0].pageX;
            const sensitivityInPx = 5;

            if (Math.floor(xClick - xMove) > sensitivityInPx) {
                $(this).carousel('next');
            } else if (Math.floor(xClick - xMove) < -sensitivityInPx) {
                $(this).carousel('prev');
            }
        });
        $(this).on('touchend', function () {
            $(this).off('touchmove');
        });
    });

    // 点击收藏
    $(".addbookbark").attr("rel", "sidebar").click(function () {
        //使用数据库收藏
        CMS.api.ajax({
            url: $(this).data("action"),
            data: {type: $(this).data("type"), aid: $(this).data("aid")}
        });
        //使用浏览器收藏
        //return !AddFavorite(window.location.href, $(this).attr("title"));
    });

    // 点赞
    $(document).on("click", ".btn-like", function () {
        var that = this;
        var id = $(this).data("id");
        var type = $(this).data("type");
        if (CMS.api.storage(type + "vote." + id)) {
            layer.msg("你已经点过赞了");
            return false;
        }
        CMS.api.ajax({
            data: $(this).data()
        }, function (data, ret) {
            $("span", that).text(type === 'like' ? ret.data.likes : ret.data.dislikes);
            CMS.api.storage(type + "vote." + id, true);
            return false;
        }, function () {
            return false;
        });
    });

    // 加载更多
    $(document).on("click", ".btn-loadmore", function () {
        var that = this;
        var page = parseInt($(this).data("page"));
        var container = $(this).data("container");
        container = container ? $(container) : $(".article-list,.product-list");
        var loadmoreText = $(this).text();
        $(that).text("正在加载").prop("disabled", true);
        CMS.api.ajax({
            url: $(that).attr("href"),
        }, function (data, ret) {
            if (data) {
                $(data).appendTo(container);
                page++;
                $(that).attr("href", $(that).data("url").replace("__page__", page)).data("page", page);
                $(that).text(loadmoreText).prop("disabled", false);
            } else {
                $(that).replaceWith('<div class="loadmore loadmore-line loadmore-nodata"><span class="loadmore-tips">暂无更多数据</span></div>');
            }
            return false;
        }, function (data) {
            $(that).text(loadmoreText).prop("disabled", false);
        });
        return false;
    });

    //滚动加载更多
    $(window).scroll(function () {
        var loadmore = $(".btn-loadmore");
        if (loadmore.length > 0 && !loadmore.prop("disabled") && (loadmore.data("autoload") === undefined || loadmore.data("autoload"))) {
            if ($(window).scrollTop() - loadmore.height() > loadmore.offset().top - $(window).height()) {
                loadmore.trigger("click");
            }
        }
    });
    setTimeout(function () {
        if ($(window).scrollTop() > 0) {
            $(window).trigger("scroll");
        }
    }, 500);

    //评论列表
    if ($("#comment-container").length > 0) {
        var ci, si;
        $("#commentlist dl dd div,#commentlist dl dd dl dd").on({
            mouseenter: function () {
                clearTimeout(ci);
                var _this = this;
                ci = setTimeout(function () {
                    $(_this).find("small:first").find("a").stop(true, true).css('display', 'inline-block');
                }, 100);
            },
            mouseleave: function () {
                clearTimeout(ci);
                $(this).find("small:first").find("a").stop(true, true).fadeOut();
            }
        });
        $(".reply").on("click", function () {
            $("#pid").val($(this).data("id"));
            $(this).parent().parent().append($("div#postcomment").detach());
            $("#postcomment h3 a").show();
            $("#commentcontent").focus().val($(this).attr("title"));
        });
        $("#postcomment h3 a").bind("click", function () {
            $("#comment-container").append($("div#postcomment").detach());
            $(this).hide();
        });
        $(".expandall a").on("click", function () {
            $(this).parent().parent().find("dl.hide").fadeIn();
            $(this).fadeOut();
        });

        $(document).on("click", "#submit", function () {
            var btn = $(this);
            var tips = $("#actiontips");
            tips.removeClass();
            var content = $("#commentcontent").val();
            if (len(content) < 3) {
                tips.addClass("text-danger").html("评论内容长度不正确！最少3个字符").fadeIn().change();
                return false;
            }
            if (btn.prop("disabled")) {
                return false;
            }
            var form = $("#postform");
            btn.attr("disabled", "disabled");
            tips.html('正在提交...');
            $.ajax({
                url: form.prop("action"),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function (json) {
                    btn.removeAttr("disabled");
                    if (json.code == 1) {
                        $("#pid").val(0);
                        tips.addClass("text-success").html(json.msg || "评论成功！").fadeIn(300).change();
                        $("#commentcontent").val('');
                        $("#commentcount").text(parseInt($("#commentcount").text()) + 1);
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    } else {
                        tips.addClass("text-danger").html(json.msg).fadeIn();
                    }
                    if (json.data && json.data.token) {
                        $("#postform input[name='__token__']").val(json.data.token);
                    }
                },
                error: function () {
                    btn.removeAttr("disabled");
                    tips.addClass("text-danger").html("评论失败！请刷新页面重试！").fadeIn();
                }
            });
            return false;
        });
        $("#commentcontent").on("keydown", function (e) {
            if ((e.metaKey || e.ctrlKey) && (e.keyCode == 13 || e.keyCode == 10)) {
                $("#submit").trigger('click');
                return false;
            }
        });
        $("#actiontips").on("change", function () {
            clearTimeout(si);
            si = setTimeout(function () {
                $("#actiontips").fadeOut();
            }, 8000);
        });
        $(document).on("keyup change", "#commentcontent", function (e) {
            if (e.metaKey || e.ctrlKey || [13, 10, 18, 91].indexOf(e.keyCode) > -1) {
                return false;
            }
            var max = 1000;
            var c = $(this).val();
            var length = len(c);
            var t = $("#actiontips");
            if (max >= length) {
                t.removeClass().show().addClass("loading").html("你还可以输入 <font color=green>" + (Math.floor((max - length) / 2)) + "</font> 字");
                $("#submit").removeAttr("disabled");
            } else {
                t.removeClass().show().addClass("loading").html("你已经超出 <font color=red>" + (Math.ceil((length - max) / 2)) + "</font> 字");
                $("#submit").attr("disabled", "disabled");
            }
        });
    }
    // 余额支付提示
    $(document).on('click', '.btn-balance', function (e) {
        var that = this;
        layer.confirm("确认支付￥" + $(this).data("price") + "元用于购买？", function () {
            CMS.api.ajax({
                url: $(that).attr("href") || $(that).attr("data-url"),
                data: {
                    paytype: "balance",
                    id: $(that).data("id")
                }
            }, function (data, ret) {
                CMS.api.msg(ret.msg, ret.url);
            }, function (data, ret) {
                if (ret.msg && ret.msg.indexOf("登录") > -1) {
                    layer.alert(ret.msg, {btn: ["去登录", "暂不登录"]}, function () {
                        location.href = ret.url;
                    });
                    return false;
                }
            });
        });
        return false;
    });
    // 回到顶部
    $('#back-to-top').on('click', function (e) {
        e.preventDefault();
        $('html,body').animate({
            scrollTop: 0
        }, 700);
    });

    //如果是PC则移除navbar的dropdown点击事件（会员下拉仍可 hover）
    if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobi/i.test(navigator.userAgent)) {
        $("#navbar-collapse [data-toggle='dropdown']").removeAttr("data-toggle");
    } else {
        $(".navbar-nav ul li:not(.dropdown-submenu):not(.dropdown) a").removeAttr("data-toggle");
    }

    // 有子级时顶级可跳转；移动端点 caret 展开/收起子菜单
    $(document).on('click', '#navbar-collapse .navbar-nav > li.dropdown > a > .caret', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $li = $(this).closest('li.dropdown');
        $li.toggleClass('open').siblings('li.dropdown.open').removeClass('open');
    });

    if (!isMobile) {
        var search = $("#searchinput");
        var form = search.closest("form");
        search.autoComplete({
            minChars: 1,
            cache: false,
            menuClass: 'autocomplete-searchmenu',
            header: '',
            footer: '',
            source: function (term, response) {
                try {
                    xhr.abort();
                } catch (e) {
                }
                xhr = $.getJSON(search.data("suggestion-url"), {q: term}, function (data) {
                    response(data);
                });
            },
            onSelect: function (e, term, item) {
                if (typeof callback === 'function') {
                    callback.call(elem, term, item);
                } else {
                    form.trigger("submit");
                }
            }
        });
    }

    // 手机端左右滑动切换菜单栏
    if (isMobile && 'ontouchstart' in document.documentElement) {
        var startX, startY, moveEndX, moveEndY, relativeX, relativeY, element;
        element = $('#navbar-collapse');
        $("body").on("touchstart", function (e) {
            startX = e.originalEvent.changedTouches[0].pageX;
            startY = e.originalEvent.changedTouches[0].pageY;
        });
        $("body").on("touchend", function (e) {
            moveEndX = e.originalEvent.changedTouches[0].pageX;
            moveEndY = e.originalEvent.changedTouches[0].pageY;
            relativeX = moveEndX - startX;
            relativeY = moveEndY - startY;

            //右滑
            if (relativeX > 45) {
                if ((Math.abs(relativeX) - Math.abs(relativeY)) > 50 && !element.hasClass("active") && startX < ($(window).width() / 4)) {
                    $(".sidebar-toggle").trigger("click");
                }
            }
            //左滑
            else if (relativeX < -45) {
                if ((Math.abs(relativeX) - Math.abs(relativeY)) > 50 && element.hasClass("active")) {
                    $(".sidebar-toggle").trigger("click");
                }
            }
        });
    }

    // 打赏
    $(".btn-donate").popover({
        trigger: 'hover',
        placement: 'top',
        html: true,
        content: function () {
            return "<img src='" + $(this).data("image") + "' width='250' height='250'/>";
        }
    });
    $(document).on("click", ".btn-paynow", function () {
        var paytype = $(this).data("paytype");
        var price = $(this).data("price");
        var nameArr = {wechat: "微信", alipay: "支付宝", balance: "余额"};
        var that = this;
        var tips = function () {
            layer.confirm("请根据支付状态选择下面的操作按钮", {title: "温馨提示", icon: 0, btn: ["支付成功", "支付失败"]}, function () {
                location.reload();
            });
        };
        if (paytype) {
            layer.confirm("确认使用" + (typeof nameArr[paytype] !== 'undefined' ? nameArr[paytype] : "未知") + "进行支付?<br>支付金额：￥" + price + "元", {title: "温馨提示", icon: 3, focusBtn: false, btn: ["立即支付", "取消支付"]}, function (index, layero) {
                $(".layui-layer-btn0", layero).attr("href", $(that).attr("href")).attr("target", "_blank");
                tips();
            });
            return false;
        } else {
            tips();
        }
    });

    //点击切换
    $(document).on("click", ".sidebar-toggle", function () {
        var collapse = $("#navbar-collapse");
        if (collapse.hasClass("active")) {
            $(".navbar-collapse-bg").remove();
        } else {
            $("<div />").addClass("navbar-collapse-bg").insertAfter(collapse).on("click", function () {
                $(".sidebar-toggle").trigger("click");
            });
        }
        collapse.toggleClass("active");
        $(this).toggleClass("active");
    });

    //内容中的图片点击事件
    $(document).on("click", ".article-text img", function () {
        if ($(this).closest("a").length > 0) {
            return;
        }
        var that = this;
        var data = [];
        var index = 0;
        $(".article-text img").each(function (i, j) {
            if (that == this) {
                index = i;
            }
            data.push({
                "src": $(this).attr("src") //原图地址
            });
        });
        layer.photos({
            photos: {
                "start": index, "data": data
            },
            // scrollbar: true,
            // full: true,
            // closeBtn: 1
        });
        return false;
    });

    //分享参数配置
    var shareConfig = {
        title: $("meta[property='og:title']").attr("content") || document.title,
        description: $("meta[property='og:description']").attr("content") || $("meta[name='description']").attr("content") || "",
        url: $("meta[property='og:url']").attr("content") || location.href,
        image: $("meta[property='og:image']").attr("content") || ""
    };

    //微信公众号内分享
    if (typeof wx != 'undefined') {
        shareConfig.url = location.href;
        CMS.api.ajax({
                url: "/addons/cms/ajax/share",
                data: {url: shareConfig.url},
                loading: false
            }, function (data, ret) {
                try {
                    wx.config({
                        appId: data.appId,
                        timestamp: data.timestamp,
                        nonceStr: data.nonceStr,
                        signature: data.signature,
                        jsApiList: [
                            'checkJsApi',
                            'updateAppMessageShareData',
                            'updateTimelineShareData',
                        ]
                    });
                    var shareData = {
                        title: shareConfig.title,
                        desc: shareConfig.description,
                        link: shareConfig.url,
                        imgUrl: shareConfig.image,
                        success: function () {
                            layer.closeAll();
                        },
                        cancel: function () {
                            layer.closeAll();
                        }
                    };
                    wx.ready(function () {
                        wx.updateAppMessageShareData(shareData);
                        wx.updateTimelineShareData(shareData);
                    });

                } catch (e) {
                    console.log(e);
                }
                return false;
            }, function () {
                return false;
            }
        );

        $(".social-share").on("click", ".icon-wechat", function () {
            layer.msg("请点击右上角的●●●进行分享");
            return false;
        }).find(".wechat-qrcode").remove();
    }
});
