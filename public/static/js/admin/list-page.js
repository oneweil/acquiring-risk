/**
 * 后台标准列表页：详情 Modal（AJAX 拉取 JSON 后渲染）+ 分页条数
 */
var ListPage = (function () {
  'use strict';

  var PAGE_SIZES = [10, 20, 50];

  function escapeHtml(str) {
    if (str === null || str === undefined) {
      return '';
    }
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function normalizePageSize(value) {
    var size = parseInt(value, 10);
    if (PAGE_SIZES.indexOf(size) === -1) {
      return PAGE_SIZES[0];
    }
    return size;
  }

  /**
   * Tabler 风格每页条数下拉（参考 preview.tabler.io/tables.html）
   */
  function renderPageSizeDropdown(currentSize) {
    var size = normalizePageSize(currentSize);
    return ''
      + '<div class="dropdown dropup">'
      +   '<a href="#" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">'
      +     escapeHtml(String(size)) + ' 条/页'
      +   '</a>'
      +   '<div class="dropdown-menu">'
      +     PAGE_SIZES.map(function (n) {
            return '<a class="dropdown-item' + (n === size ? ' active' : '')
              + '" href="#" data-page-size="' + n + '">' + n + ' 条/页</a>';
          }).join('')
      +   '</div>'
      + '</div>';
  }

  /**
   * Tabler 风格数字分页（左右为 chevron 箭头）
   */
  function renderPaginationHtml(page, lastPage) {
    if (lastPage <= 1) {
      return '';
    }

    function pageItem(targetPage, label, disabled, active) {
      if (disabled) {
        return '<li class="page-item disabled"><span class="page-link">' + escapeHtml(label) + '</span></li>';
      }
      if (active) {
        return '<li class="page-item active" aria-current="page"><span class="page-link">'
          + escapeHtml(label) + '</span></li>';
      }
      return '<li class="page-item"><a class="page-link" href="#" data-page="'
        + targetPage + '">' + escapeHtml(label) + '</a></li>';
    }

    function navItem(targetPage, dir, disabled) {
      var icon = dir === 'prev'
        ? '<i class="ti ti-chevron-left"></i>'
        : '<i class="ti ti-chevron-right"></i>';
      var label = dir === 'prev' ? '上一页' : '下一页';
      if (disabled) {
        return '<li class="page-item disabled">'
          + '<span class="page-link" aria-disabled="true" aria-label="' + label + '">' + icon + '</span>'
          + '</li>';
      }
      return '<li class="page-item">'
        + '<a class="page-link" href="#" data-page="' + targetPage + '" aria-label="' + label + '">'
        + icon + '</a></li>';
    }

    var html = '<ul class="pagination m-0">';
    html += navItem(page - 1, 'prev', page <= 1);

    var windowSize = 2;
    var start = Math.max(1, page - windowSize);
    var end = Math.min(lastPage, page + windowSize);
    if (start > 1) {
      html += pageItem(1, '1', false, page === 1);
      if (start > 2) {
        html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
      }
    }
    for (var p = start; p <= end; p++) {
      html += pageItem(p, String(p), false, p === page);
    }
    if (end < lastPage) {
      if (end < lastPage - 1) {
        html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
      }
      html += pageItem(lastPage, String(lastPage), false, page === lastPage);
    }

    html += navItem(page + 1, 'next', page >= lastPage);
    html += '</ul>';
    return html;
  }

  function init(options) {
    var detailUrl = options.detailUrl;
    var renderDetail = options.renderDetail;
    var mapDetail = options.mapDetail || function (data) { return data; };
    var modalEl = document.getElementById('listDetailModal');
    var bodyEl = document.getElementById('listDetailModalBody');

    if (!modalEl || !bodyEl || !detailUrl || typeof renderDetail !== 'function') {
      return;
    }

    var ModalCtor = (window.bootstrap && window.bootstrap.Modal)
      || (window.tabler && window.tabler.Modal);
    if (!ModalCtor) {
      return;
    }
    var modal = ModalCtor.getOrCreateInstance(modalEl);

    document.addEventListener('click', function (event) {
      var btn = event.target.closest('.js-list-detail');
      if (!btn) {
        return;
      }

      var id = btn.getAttribute('data-id');
      if (!id) {
        return;
      }

      bodyEl.innerHTML = '<div class="text-center text-secondary py-4">加载中…</div>';
      modal.show();

      fetch(detailUrl + '?id=' + encodeURIComponent(id), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (json.code !== 0 || !json.data) {
            bodyEl.innerHTML = '<div class="alert alert-danger mb-0">' + escapeHtml(json.msg || '加载失败') + '</div>';
            return;
          }
          bodyEl.innerHTML = renderDetail(mapDetail(json.data));
        })
        .catch(function () {
          bodyEl.innerHTML = '<div class="alert alert-danger mb-0">网络错误，请稍后重试</div>';
        });
    });
  }

  return {
    init: init,
    escapeHtml: escapeHtml,
    PAGE_SIZES: PAGE_SIZES,
    normalizePageSize: normalizePageSize,
    renderPageSizeDropdown: renderPageSizeDropdown,
    renderPaginationHtml: renderPaginationHtml
  };
})();
