(function ($) {
  'use strict';

  var $win = $(window);
  var $header = $('.yst-header');
  var $drawer = $('#yst-drawer');
  var $mask = $('#yst-drawer-mask');
  var $search = $('#yst-search-panel');
  var $back = $('#yst-backtop');

  function onScroll() {
    var y = $win.scrollTop();
    $header.toggleClass('is-scrolled', y > 8);
    $back.toggleClass('is-show', y > 320);
  }

  $win.on('scroll', onScroll);
  onScroll();

  $('#yst-burger').on('click', function () {
    $drawer.addClass('is-open');
    $mask.addClass('is-open');
  });

  $mask.on('click', function () {
    $drawer.removeClass('is-open');
    $mask.removeClass('is-open');
  });

  $('#yst-search-toggle').on('click', function (e) {
    e.preventDefault();
    $search.toggleClass('is-open');
    if ($search.hasClass('is-open')) {
      $search.find('input[name="q"]').trigger('focus');
    }
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#yst-search-panel, #yst-search-toggle').length) {
      $search.removeClass('is-open');
    }
  });

  $back.on('click', function (e) {
    e.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, 420);
  });

  // Home layered banner: left content / right image, transform stagger
  (function initYstSlider() {
    var $slider = $('#yst-focus.yst-slider');
    if (!$slider.length) {
      return;
    }

    var root = $slider[0];
    var $slides = $slider.find('.yst-slider-stage > .yst-slide');
    var total = $slides.length;
    if (total < 1) {
      return;
    }

    // 内容区：按行拆成段落（兼容纯文本 / br / 已有 p）
    $slides.each(function () {
      var $box = $(this).find('.yst-slide-content');
      if (!$box.length) {
        return;
      }
      if ($box.children('p,h1,h2,h3,h4').length) {
        return;
      }
      var html = ($box.html() || '').replace(/<br\s*\/?>/gi, '\n');
      var tmp = document.createElement('div');
      tmp.innerHTML = html;
      var text = (tmp.textContent || tmp.innerText || '').replace(/\r/g, '');
      var lines = text.split('\n').map(function (s) { return $.trim(s); }).filter(Boolean);
      if (!lines.length) {
        return;
      }
      $box.empty();
      lines.forEach(function (line) {
        $box.append($('<p class="yst-slide-line"/>').text(line));
      });
    });

    var current = Math.max(0, $slides.index($slides.filter('.is-active')));
    if (current < 0) {
      current = 0;
      $slides.eq(0).addClass('is-active');
    }
    var animating = false;
    var autoplay = $slider.data('autoplay') == 1 || $slider.data('autoplay') === true;
    var interval = parseInt($slider.data('interval'), 10) || 5000;
    var duration = parseInt($slider.data('duration'), 10) || 1200;
    var timer = null;
    var rafId = 0;
    var textFactor = parseFloat(getComputedStyle(root).getPropertyValue('--yst-text-factor')) || 1;
    var mediaFactor = parseFloat(getComputedStyle(root).getPropertyValue('--yst-media-factor')) || 0.72;

    var $dots = $('<nav class="yst-slider-dots" aria-label="轮播指示"/>');
    for (var i = 0; i < total; i++) {
      $dots.append('<span' + (i === current ? ' class="is-active"' : '') + '/>');
    }
    $slider.append($dots);
    var $pages = $dots.children('span');

    function clamp01(v) {
      return v < 0 ? 0 : (v > 1 ? 1 : v);
    }

    function easeInOutCubic(t) {
      return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
    }

    function delayed(t, delay) {
      return easeInOutCubic(clamp01((t - delay) / (1 - delay)));
    }

    function setLayer($el, xPercent, opacity) {
      if (!$el.length) {
        return;
      }
      $el[0].style.transform = 'translate3d(' + xPercent + '%,0,0)';
      $el[0].style.opacity = String(opacity);
    }

    function resetResting($slide, visible) {
      setLayer($slide.find('.yst-layer-text'), 0, visible ? 1 : 0);
      setLayer($slide.find('.yst-layer-media'), 0, visible ? 1 : 0);
    }

    function updateDots() {
      $pages.removeClass('is-active').eq(current).addClass('is-active');
    }

    function stopAuto() {
      if (timer) {
        clearTimeout(timer);
        timer = null;
      }
    }

    function startAuto() {
      stopAuto();
      if (!autoplay || total < 2) {
        return;
      }
      timer = setTimeout(function () {
        goTo(current + 1, 'next');
        startAuto();
      }, interval);
    }

    function paint(progress, dir, $from, $to) {
      var outText = delayed(progress, 0);
      var outMedia = delayed(progress, 0.2);
      var inText = delayed(progress, 0.22);
      var inMedia = delayed(progress, 0.4);
      var sign = dir === 'next' ? 1 : -1;
      var textDist = 110 * textFactor;
      var mediaDist = 110 * mediaFactor;

      // 文字先滑走，图片跟踪；入场左右铺开
      setLayer($from.find('.yst-layer-text'), -sign * textDist * outText, 1 - outText);
      setLayer($from.find('.yst-layer-media'), sign * mediaDist * outMedia, 1 - outMedia);
      setLayer($to.find('.yst-layer-text'), -sign * textDist * (1 - inText), inText);
      setLayer($to.find('.yst-layer-media'), sign * mediaDist * (1 - inMedia), inMedia);
    }

    function goTo(page, dir) {
      if (total < 2 || animating) {
        return;
      }
      page = ((page % total) + total) % total;
      if (page === current) {
        return;
      }
      if (!dir) {
        dir = page > current ? 'next' : 'prev';
        if (current === total - 1 && page === 0) {
          dir = 'next';
        } else if (current === 0 && page === total - 1) {
          dir = 'prev';
        }
      }

      animating = true;
      var $from = $slides.eq(current);
      var $to = $slides.eq(page);
      $from.addClass('is-animating').removeClass('is-active');
      $to.addClass('is-animating is-active');

      var start = 0;
      if (rafId) {
        cancelAnimationFrame(rafId);
      }

      function frame(ts) {
        if (!start) {
          start = ts;
        }
        var t = clamp01((ts - start) / duration);
        paint(t, dir, $from, $to);
        if (t < 1) {
          rafId = requestAnimationFrame(frame);
          return;
        }
        resetResting($from, false);
        resetResting($to, true);
        $from.removeClass('is-animating is-active');
        $to.removeClass('is-animating').addClass('is-active');
        current = page;
        updateDots();
        animating = false;
        rafId = 0;
      }

      paint(0, dir, $from, $to);
      rafId = requestAnimationFrame(frame);
    }

    $slides.each(function (idx) {
      resetResting($(this), idx === current);
    });

    $pages.on('click', function () {
      autoplay = false;
      stopAuto();
      goTo($(this).index());
    });

    $slider.find('.yst-slider-next').on('click', function () {
      autoplay = false;
      stopAuto();
      goTo(current + 1, 'next');
    });

    $slider.find('.yst-slider-prev').on('click', function () {
      autoplay = false;
      stopAuto();
      goTo(current - 1, 'prev');
    });

    var touchX = 0;
    $slider.on('touchstart', function (e) {
      touchX = e.originalEvent.touches[0].clientX;
    }).on('touchend', function (e) {
      var dx = e.originalEvent.changedTouches[0].clientX - touchX;
      if (Math.abs(dx) < 50) {
        return;
      }
      autoplay = false;
      stopAuto();
      if (dx < 0) {
        goTo(current + 1, 'next');
      } else {
        goTo(current - 1, 'prev');
      }
    });

    startAuto();
  })();

  // Product gallery (Swiper if present)
  if (typeof Swiper !== 'undefined' && document.querySelector('.yst-gallery-top')) {
    var thumbs = new Swiper('.yst-gallery-thumbs', {
      spaceBetween: 10,
      slidesPerView: 5,
      freeMode: true,
      watchSlidesVisibility: true,
      watchSlidesProgress: true
    });
    new Swiper('.yst-gallery-top', {
      spaceBetween: 10,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev'
      },
      thumbs: { swiper: thumbs }
    });
  }

  // Vote / collect (reuse portal endpoints if cms.js not loaded)
  function yscmsIndex() {
    return (window.YSCMS && YSCMS.index) ? String(YSCMS.index).replace(/\/?$/, '') : '';
  }

  $(document).on('click', '.yscms-vote', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var id = $btn.data('id');
    var type = $btn.data('type') || 'like';
    var tag = $btn.data('tag') || 'archives';
    var url = tag === 'page' ? (yscmsIndex() + '/p/vote') : (yscmsIndex() + '/a/vote');
    $.post(url, { id: id, type: type, tag: tag }).done(function (res) {
      if (res && res.data && (res.code === 0 || res.code === '0' || res.code === 200 || res.code === 1 || res.code === '1')) {
        $btn.find('span').first().text(res.data.likes || 0);
      }
    });
  });

  $(document).on('click', '.yscms-collect', function (e) {
    e.preventDefault();
    var $btn = $(this);
    $.post(yscmsIndex() + '/ajax/collection', {
      type: $btn.data('type') || 'archives',
      aid: $btn.data('aid')
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
    $.post(yscmsIndex() + '/ajax/theme', { theme: theme })
      .done(function (res) {
        if (res && (res.code === 0 || res.code === '0')) {
          location.reload();
          return;
        }
        alert((res && res.msg) || '?��????');
        $btn.data('loading', 0);
      })
      .fail(function () {
        alert('?��????');
        $btn.data('loading', 0);
      });
  });
})(jQuery);
