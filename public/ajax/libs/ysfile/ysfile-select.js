/**
 * ysfile material picker
 * Usage:
 *   $.ysfileSelect.open({ input:'#image', mimetype:'image/*', multiple:true, maxcount:20, callback:fn })
 *   <button data-ysfile-select="#image" data-mimetype="image/*" data-multiple="true">Select</button>
 */
(function (window, $) {
  'use strict';
  if (!$ || $.ysfileSelect) {
    return;
  }
  var seq = 0;

  function ensureBag() {
    if (!window.top.__ysfileSelectCallbacks) {
      window.top.__ysfileSelectCallbacks = {};
    }
    return window.top.__ysfileSelectCallbacks;
  }

  function ensurePreviewCss() {
    if (document.getElementById('ysfile-preview-css')) {
      return;
    }
    var css = document.createElement('style');
    css.id = 'ysfile-preview-css';
    css.textContent =
      '.ysfile-preview{padding:6px 0 0;margin:0;list-style:none;}' +
      '.ysfile-preview li{display:inline-block;margin:0 8px 8px 0;vertical-align:top;position:relative;}' +
      '.ysfile-preview .thumbnail{margin:0;padding:4px;display:block;}' +
      '.ysfile-preview .thumbnail img{height:80px;width:auto;max-width:120px;object-fit:cover;display:block;}' +
      '.ysfile-preview .ysfile-preview-remove{position:absolute;top:-6px;right:-6px;width:20px;height:20px;' +
      'line-height:18px;text-align:center;border-radius:50%;background:#ed5565;color:#fff;cursor:pointer;' +
      'font-size:12px;border:0;padding:0;}';
    document.head.appendChild(css);
  }

  function parseUrls(val) {
    return String(val || '')
      .split(',')
      .map(function (s) {
        return $.trim(s);
      })
      .filter(Boolean);
  }

  function isTruthy(v) {
    return v === true || v === 1 || v === '1' || v === 'true';
  }

  $.ysfileSelect = {
    open: function (options) {
      options = options || {};
      var token = 'ysfs_' + (++seq) + '_' + Date.now();
      ensureBag()[token] = {
        opener: window,
        input: options.input || '',
        multiple: !!options.multiple,
        maxcount: parseInt(options.maxcount, 10) || 0,
        callback: options.callback
      };
      var base = typeof ctx !== 'undefined' ? ctx : '/';
      var url = base + 'ysfile/file/select?token=' + encodeURIComponent(token);
      if (options.mimetype) {
        url += '&mimetype=' + encodeURIComponent(options.mimetype);
      }
      if (options.multiple) {
        url += '&multiple=1';
      }
      if (options.maxcount) {
        url += '&maxcount=' + encodeURIComponent(String(options.maxcount));
      }
      $.modal.openOptions({
        title: options.title || '\u9009\u62e9\u7d20\u6750',
        url: url,
        width: String(options.width || 900),
        height: String(options.height || 620),
        btn: ['<i class="fa fa-close"></i> \u5173\u95ed']
      });
    },
    bind: function (root) {
      $(root || document)
        .off('click.ysfileSelect', '[data-ysfile-select]')
        .on('click.ysfileSelect', '[data-ysfile-select]', function (e) {
          e.preventDefault();
          var $btn = $(this);
          $.ysfileSelect.open({
            input: $btn.attr('data-ysfile-select'),
            mimetype: $btn.attr('data-mimetype') || '',
            title: $btn.attr('data-title') || '\u9009\u62e9\u7d20\u6750',
            multiple: isTruthy($btn.attr('data-multiple')),
            maxcount: parseInt($btn.attr('data-maxcount'), 10) || 0
          });
        });
      $(root || document)
        .off('click.ysfilePreview', '.ysfile-preview-remove')
        .on('click.ysfilePreview', '.ysfile-preview-remove', function (e) {
          e.preventDefault();
          var $li = $(this).closest('li');
          var inputSel = $li.closest('.ysfile-preview').attr('data-input');
          var url = $li.attr('data-url') || '';
          $li.remove();
          if (inputSel) {
            var $input = $(inputSel);
            var urls = parseUrls($input.val()).filter(function (u) {
              return u !== url;
            });
            $input.val(urls.join(',')).trigger('change');
          }
        })
        .off('change.ysfilePreview', '[data-ysfile-preview]')
        .on('change.ysfilePreview', '[data-ysfile-preview]', function () {
          $.ysfileSelect.refreshPreview(this);
        });
    },
    refreshPreview: function (input) {
      ensurePreviewCss();
      var $input = $(input);
      var previewSel = $input.attr('data-ysfile-preview');
      if (!previewSel) {
        return;
      }
      var $preview = $(previewSel);
      if (!$preview.length) {
        return;
      }
      var inputId = $input.attr('id');
      $preview.attr('data-input', inputId ? '#' + inputId : '');
      var urls = parseUrls($input.val());
      var html = '';
      for (var i = 0; i < urls.length; i++) {
        var u = urls[i];
        var esc = $('<div/>').text(u).html();
        html +=
          '<li data-url="' +
          esc +
          '">' +
          '<button type="button" class="ysfile-preview-remove" title="\u79fb\u9664">&times;</button>' +
          '<a href="' +
          esc +
          '" target="_blank" class="thumbnail"><img src="' +
          esc +
          '" onerror="this.style.display=\'none\'"></a></li>';
      }
      $preview.html(html);
    },
    refreshPreviews: function (scope) {
      ensurePreviewCss();
      $(scope || document)
        .find('[data-ysfile-preview]')
        .each(function () {
          $.ysfileSelect.refreshPreview(this);
        });
    }
  };

  $(function () {
    $.ysfileSelect.bind(document);
    $.ysfileSelect.refreshPreviews(document);
  });
})(window, jQuery);
