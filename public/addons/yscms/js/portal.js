(function ($) {
  if (!window.jQuery) return;
  var base = (window.YSCMS && YSCMS.index) ? YSCMS.index.replace(/\/?$/, '') : '';

  // mobile nav
  $(document).on('click', '.navbar-toggle.sidebar-toggle', function () {
    var collapse = $('#navbar-collapse');
    collapse.toggleClass('active');
    if (collapse.hasClass('active')) {
      if (!$('.navbar-collapse-bg').length) {
        $('<div class="navbar-collapse-bg"></div>').insertAfter(collapse);
      }
    } else {
      $('.navbar-collapse-bg').remove();
    }
  });
  $(document).on('click', '.navbar-collapse-bg', function () {
    $('#navbar-collapse').removeClass('active');
    $(this).remove();
  });
  $(document).on('click', '.member-sidebar-toggle', function () {
    $('body').toggleClass('member-sidebar-open');
  });

  // load more
  $(document).on('click', '.btn-loadmore', function (e) {
    e.preventDefault();
    var that = this;
    var page = parseInt($(this).data('page'), 10) || 2;
    var container = $(this).data('container');
    container = container ? $(container) : $('.article-list,.product-list');
    var loadmoreText = $(this).text();
    var url = $(that).attr('href');
    $(that).text('正在加载').prop('disabled', true);

    var done = function (html) {
      if (html && $.trim(html)) {
        $(html).appendTo(container);
        page++;
        var next = ($(that).data('url') || '').toString().replace('__page__', page);
        $(that).attr('href', next).data('page', page);
        $(that).text(loadmoreText).prop('disabled', false);
      } else {
        $(that).replaceWith('<div class="loadmore loadmore-line loadmore-nodata"><span class="loadmore-tips">暂无更多数据</span></div>');
      }
    };

    if (window.CMS && CMS.api && typeof CMS.api.ajax === 'function') {
      CMS.api.ajax({ url: url, type: 'GET' }, function (data) {
        done(data);
        return false;
      }, function () {
        $(that).text(loadmoreText).prop('disabled', false);
        return false;
      });
    } else {
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).done(function (ret) {
        var html = (ret && (ret.data || ret.code === 1)) ? (ret.data || '') : '';
        if (typeof ret === 'string') html = ret;
        done(html);
      }).fail(function () {
        $(that).text(loadmoreText).prop('disabled', false);
      });
    }
    return false;
  });

  // back to top
  $('#back-to-top').on('click', function (e) {
    e.preventDefault();
    $('html,body').animate({ scrollTop: 0 }, 700);
  });

  // comment form
  $(document).on('submit', '#yscms-comment-form', function (e) {
    e.preventDefault();
    var box = $('#yscms-comment');
    $.post(base + '/comment/post', {
      type: box.data('type'),
      aid: box.data('aid'),
      content: $(this).find('[name=content]').val()
    }).done(function (res) {
      if (res.code === 200) {
        alert(res.msg || '评论成功');
        location.reload();
      } else {
        alert(res.msg || '失败');
      }
    });
  });

  $(document).on('click', '.yscms-vote', function () {
    var btn = $(this);
    var tag = btn.data('tag') || 'archives';
    var url = tag === 'page' ? (base + '/p/vote') : (base + '/a/vote');
    $.post(url, { id: btn.data('id'), type: btn.data('type') || 'like', tag: tag })
      .done(function (res) {
        if (res.code === 200 && res.data) {
          btn.find('span').text(res.data.likes);
          $('.js-like-num').text(' ' + res.data.likes + ' 点赞');
        } else {
          alert(res.msg || '失败');
        }
      });
  });

  $(document).on('click', '.yscms-collect', function () {
    var btn = $(this);
    $.post(base + '/ajax/collection', { type: btn.data('type'), aid: btn.data('aid') })
      .done(function (res) {
        alert(res.msg || (res.code === 200 ? '成功' : '失败'));
      });
  });

  $(document).on('click', '.yscms-switch-theme', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var theme = String($btn.data('theme') || '');
    if (!theme || $btn.data('loading')) {
      return;
    }
    $btn.data('loading', 1);
    $.post(base + '/ajax/theme', { theme: theme })
      .done(function (res) {
        if (res && (res.code === 200)) {
          location.reload();
          return;
        }
        alert((res && res.msg) || '切换失败');
        $btn.data('loading', 0);
      })
      .fail(function () {
        alert('切换失败');
        $btn.data('loading', 0);
      });
  });

  $(document).on('click', '.btn-download-now', function (e) {
    e.preventDefault();
    var target = $('#download');
    if (target.length) {
      $('html,body').animate({ scrollTop: target.offset().top - 60 }, 700);
    }
    return false;
  });

  // donate popover
  if ($.fn.popover) {
    $('.btn-donate').popover({
      trigger: 'hover',
      placement: 'top',
      html: true,
      content: function () {
        var img = $(this).data('image');
        return img ? "<img src='" + img + "' width='250' height='250'/>" : '暂未配置打赏二维码';
      }
    });
  }

  // Server-rendered #commentlist: skip AJAX list fill
  var box = $('#yscms-comment');
  if (box.length && !$('#commentlist').length) {
    var emptyHtml = '<div class="loadmore loadmore-line loadmore-nodata"><span class="loadmore-tips">暂无评论</span></div>';
    $.get(base + '/comment/list', { type: box.data('type'), aid: box.data('aid') })
      .done(function (res) {
        if (res.code !== 200 || !res.data) {
          $('#yscms-comment-list').html(emptyHtml);
          return;
        }
        var html = '';
        (res.data.rows || []).forEach(function (row) {
          var name = (row.user && row.user.nickname) ? row.user.nickname : '用户';
          html += '<div class="well well-sm"><strong>' + $('<div>').text(name).html() + '</strong>：'
            + $('<div>').text(row.content || '').html()
            + '<div class="text-muted small">' + (row.create_time || '') + '</div></div>';
        });
        $('#yscms-comment-list').html(html || emptyHtml);
      })
      .fail(function () {
        $('#yscms-comment-list').html(emptyHtml);
      });
  }
})(window.jQuery);
