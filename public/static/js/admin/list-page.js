/**
 * 后台标准列表页：详情 Modal（AJAX 拉取 JSON 后渲染）
 */
var ListPage = (function () {
  'use strict';

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
    escapeHtml: escapeHtml
  };
})();
