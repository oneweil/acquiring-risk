/**
 * 后台通用 UI：确认框、Alert 提示（Tabler）
 * @see https://docs.tabler.io/ui/components/modals
 * @see https://docs.tabler.io/ui/components/alerts
 */
var AdminUi = (function () {
  'use strict';

  var MODAL_ID = 'adminConfirmModal';
  var ALERT_HOST_ID = 'adminAlertHost';
  var instance = null;
  var pendingResolve = null;
  var alertTimers = [];

  function escapeHtml(str) {
    if (window.ListPage && typeof ListPage.escapeHtml === 'function') {
      return ListPage.escapeHtml(str);
    }
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

  function getModalCtor() {
    return (window.bootstrap && window.bootstrap.Modal)
      || (window.tabler && window.tabler.Modal)
      || null;
  }

  function ensureModal() {
    var el = document.getElementById(MODAL_ID);
    if (el) {
      return el;
    }

    el = document.createElement('div');
    el.id = MODAL_ID;
    el.className = 'modal modal-blur fade';
    el.tabIndex = -1;
    el.setAttribute('aria-hidden', 'true');
    el.innerHTML = ''
      + '<div class="modal-dialog modal-sm modal-dialog-centered" role="document">'
      +   '<div class="modal-content">'
      +     '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="关闭"></button>'
      +     '<div class="modal-status" data-role="status"></div>'
      +     '<div class="modal-body text-center py-4">'
      +       '<i class="ti icon mb-2 icon-lg" data-role="icon" aria-hidden="true"></i>'
      +       '<h3 data-role="title"></h3>'
      +       '<div class="text-secondary" data-role="message"></div>'
      +     '</div>'
      +     '<div class="modal-footer">'
      +       '<div class="w-100">'
      +         '<div class="row">'
      +           '<div class="col">'
      +             '<button type="button" class="btn w-100" data-bs-dismiss="modal" data-role="cancel">取消</button>'
      +           '</div>'
      +           '<div class="col">'
      +             '<button type="button" class="btn w-100" data-role="confirm">确定</button>'
      +           '</div>'
      +         '</div>'
      +       '</div>'
      +     '</div>'
      +   '</div>'
      + '</div>';

    document.body.appendChild(el);

    el.querySelector('[data-role="confirm"]').addEventListener('click', function () {
      var resolve = pendingResolve;
      pendingResolve = null;
      var ModalCtor = getModalCtor();
      if (instance && ModalCtor) {
        instance.hide();
      }
      if (typeof resolve === 'function') {
        resolve(true);
      }
    });

    el.addEventListener('hidden.bs.modal', function () {
      if (typeof pendingResolve === 'function') {
        var resolve = pendingResolve;
        pendingResolve = null;
        resolve(false);
      }
    });

    return el;
  }

  /**
   * Tabler 风格确认框（danger / warning / primary）
   *
   * @param {object|string} options 文案对象，或直接传 message 字符串
   * @param {string} [options.title=请确认]
   * @param {string} [options.message]
   * @param {string} [options.confirmText=确定]
   * @param {string} [options.cancelText=取消]
   * @param {string} [options.type=danger] danger|warning|primary
   * @returns {Promise<boolean>} 确认 true / 取消 false
   */
  function confirm(options) {
    var opts = typeof options === 'string'
      ? { message: options }
      : (options || {});

    var type = opts.type || 'danger';
    var theme = {
      danger: { status: 'bg-danger', icon: 'ti-alert-triangle text-danger', btn: 'btn-danger' },
      warning: { status: 'bg-warning', icon: 'ti-alert-circle text-warning', btn: 'btn-warning' },
      primary: { status: 'bg-primary', icon: 'ti-help-circle text-primary', btn: 'btn-primary' }
    }[type] || {
      status: 'bg-danger',
      icon: 'ti-alert-triangle text-danger',
      btn: 'btn-danger'
    };

    var ModalCtor = getModalCtor();
    if (!ModalCtor) {
      return Promise.resolve(window.confirm(opts.message || opts.title || '请确认'));
    }

    var el = ensureModal();
    el.querySelector('[data-role="status"]').className = 'modal-status ' + theme.status;
    el.querySelector('[data-role="icon"]').className = 'ti icon mb-2 icon-lg ' + theme.icon;
    el.querySelector('[data-role="title"]').textContent = opts.title || '请确认';
    el.querySelector('[data-role="message"]').innerHTML = escapeHtml(opts.message || '').replace(/\n/g, '<br>');
    el.querySelector('[data-role="cancel"]').textContent = opts.cancelText || '取消';

    var confirmBtn = el.querySelector('[data-role="confirm"]');
    confirmBtn.className = 'btn w-100 ' + theme.btn;
    confirmBtn.textContent = opts.confirmText || '确定';

    if (pendingResolve) {
      var prev = pendingResolve;
      pendingResolve = null;
      prev(false);
    }

    instance = ModalCtor.getOrCreateInstance(el);

    return new Promise(function (resolve) {
      pendingResolve = resolve;
      instance.show();
    });
  }

  function ensureAlertHost() {
    var host = document.getElementById(ALERT_HOST_ID);
    if (host) {
      return host;
    }
    host = document.createElement('div');
    host.id = ALERT_HOST_ID;
    host.className = 'admin-alert-host';
    host.setAttribute('aria-live', 'polite');
    document.body.appendChild(host);
    return host;
  }

  /**
   * Tabler Alert 浮动提示（可关闭，默认数秒后自动消失）
   *
   * @param {object|string} options 文案对象，或直接传 message
   * @param {string} [options.type=success] success|info|warning|danger
   * @param {string} [options.title]
   * @param {string} [options.message]
   * @param {number} [options.duration=2000] 毫秒；0 表示不自动关闭
   */
  function alert(options) {
    var opts = typeof options === 'string'
      ? { message: options }
      : (options || {});

    var type = opts.type || 'success';
    var icons = {
      success: 'ti-circle-check',
      info: 'ti-info-circle',
      warning: 'ti-alert-triangle',
      danger: 'ti-alert-circle'
    };
    var icon = icons[type] || icons.success;
    var title = opts.title || '';
    var message = opts.message || '';
    if (!title && !message) {
      return;
    }

    var duration = opts.duration;
    if (duration === undefined || duration === null) {
      duration = 2000;
    }

    var host = ensureAlertHost();
    var el = document.createElement('div');
    el.className = 'alert alert-' + type + ' alert-dismissible admin-alert-item';
    el.setAttribute('role', 'alert');

    var body = '';
    body += '<div class="d-flex">';
    body +=   '<div><i class="ti ' + icon + ' alert-icon" aria-hidden="true"></i></div>';
    body +=   '<div>';
    if (title) {
      body += '<h4 class="alert-heading">' + escapeHtml(title) + '</h4>';
    }
    if (message) {
      body += '<div class="' + (title ? 'alert-description' : '') + '">' + escapeHtml(message) + '</div>';
    }
    body +=   '</div>';
    body += '</div>';
    body += '<a href="#" class="btn-close" data-role="close" aria-label="关闭"></a>';
    el.innerHTML = body;
    host.appendChild(el);

    var removed = false;
    function removeAlert() {
      if (removed) return;
      removed = true;
      if (el.parentNode) {
        el.parentNode.removeChild(el);
      }
    }

    el.querySelector('[data-role="close"]').addEventListener('click', function (e) {
      e.preventDefault();
      removeAlert();
    });

    if (duration > 0) {
      var timer = window.setTimeout(removeAlert, duration);
      alertTimers.push(timer);
    }

    return el;
  }

  function success(message, title) {
    return alert({ type: 'success', title: title || '操作成功', message: message || '' });
  }

  function danger(message, title) {
    return alert({ type: 'danger', title: title || '操作失败', message: message || '' });
  }

  return {
    confirm: confirm,
    alert: alert,
    success: success,
    danger: danger
  };
})();
