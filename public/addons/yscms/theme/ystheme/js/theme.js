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
        alert((res && res.msg) || '?§Ý????');
        $btn.data('loading', 0);
      })
      .fail(function () {
        alert('?§Ý????');
        $btn.data('loading', 0);
      });
  });
})(jQuery);
