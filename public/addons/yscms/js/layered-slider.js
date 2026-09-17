(function ($) {
  'use strict';

  // ×óÎÄÓÒÍ¼·Ö²ãÂÖ²¥£ºÎÄ×ÖÏÈ»¬¡¢Í¼Æ¬¸ú½ø
  function initYstSlider() {
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

    var $dots = $('<nav class="yst-slider-dots" aria-label="ÂÖ²¥Ö¸Ê¾"/>');
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
  }

  $(initYstSlider);
})(window.jQuery);
