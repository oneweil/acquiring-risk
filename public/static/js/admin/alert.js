/**
 * 交易预警中心（列表 + 处置弹窗 + 调单上传）
 */
var AlertPage = (function () {
  'use strict';

  var LIST_URL = '/admin/alert/list';
  var DETAIL_URL = '/admin/alert/detail';
  var HANDLE_URL = '/admin/alert/handle';
  var UPLOAD_URL = '/admin/alert/upload';

  var state = {
    page: 1,
    pageSize: 10,
    loading: false,
    saving: false,
    handleModal: null,
    currentId: null,
    currentDetail: null
  };

  var COLSPAN = 11;

  var STATUS_LT = {
    '待处理': 'bg-indigo-lt',
    '处理中': 'bg-yellow-lt',
    '已关闭': 'bg-secondary-lt'
  };

  var RISK_LT = {
    '低风险': 'bg-green-lt',
    '中风险': 'bg-yellow-lt',
    '高风险': 'bg-orange-lt',
    '极高风险': 'bg-red-lt'
  };

  var MEASURE_LT = {
    '拒绝交易': 'bg-red-lt',
    '3DS强验': 'bg-orange-lt',
    '仅预警': 'bg-green-lt',
    '调单': 'bg-azure-lt',
    '延迟结算': 'bg-cyan-lt',
    '限制单笔额度': 'bg-purple-lt',
    '加入观察': 'bg-indigo-lt'
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
  }

  function dash(val) {
    if (val === null || val === undefined || val === '') return '—';
    return escapeHtml(String(val));
  }

  function notifySuccess(message) {
    if (window.AdminUi && typeof AdminUi.success === 'function') {
      AdminUi.success(message);
      return;
    }
    window.alert(message);
  }

  function notifyError(message) {
    if (window.AdminUi && typeof AdminUi.danger === 'function') {
      AdminUi.danger(message);
      return;
    }
    window.alert(message);
  }

  function badge(label, clsMap) {
    if (!label) return '—';
    var cls = clsMap[label] || 'bg-secondary-lt';
    return '<span class="badge ' + cls + '">' + escapeHtml(label) + '</span>';
  }

  function handleActions() {
    return window.ALERT_HANDLE_ACTIONS || { default: {}, inquiry: {} };
  }

  function getFilters() {
    var form = document.getElementById('alertFilterForm');
    var params = new URLSearchParams();
    if (!form) return params;

    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.disabled) return;
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
      if (el.type === 'submit' || el.type === 'button') return;
      var val = String(el.value || '').trim();
      if (val !== '') params.set(el.name, val);
    });

    document.querySelectorAll('[form="alertFilterForm"]').forEach(function (el) {
      if (!el.name || el.disabled) return;
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
      if (el.type === 'submit' || el.type === 'button') return;
      var val = String(el.value || '').trim();
      if (val !== '') params.set(el.name, val);
    });

    return params;
  }

  function applyQueryToForm() {
    var qs = new URLSearchParams(window.location.search);
    var form = document.getElementById('alertFilterForm');
    if (form) {
      Array.prototype.forEach.call(form.elements, function (el) {
        if (!el.name) return;
        if (qs.has(el.name)) el.value = qs.get(el.name);
      });
    }
    document.querySelectorAll('[form="alertFilterForm"]').forEach(function (el) {
      if (!el.name) return;
      if (qs.has(el.name)) el.value = qs.get(el.name);
    });

    state.page = Math.max(1, parseInt(qs.get('page') || '1', 10) || 1);
    state.pageSize = ListPage.normalizePageSize(parseInt(qs.get('pageSize') || '10', 10) || 10);
  }

  function syncUrl() {
    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));
    var qs = params.toString();
    var url = window.location.pathname + (qs ? '?' + qs : '');
    window.history.replaceState(null, '', url);
  }

  function setLoading(loading) {
    state.loading = loading;
    var tbody = document.getElementById('alertTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function strCell(row) {
    var s = row.str_summary;
    if (!s || !s.report_no) return '—';
    var label = s.status_label ? badge(s.status_label, {
      '待确认': 'bg-yellow-lt',
      '已生成': 'bg-azure-lt',
      '已上传': 'bg-indigo-lt',
      '已提交监管': 'bg-green-lt',
      '无需上报': 'bg-secondary-lt',
      '已归档': 'bg-secondary-lt',
      '已退回': 'bg-red-lt'
    }) + ' ' : '';
    return label + '<a href="/admin/str_report" class="font-monospace">' + escapeHtml(s.report_no) + '</a>';
  }

  function actionButtons(row) {
    var id = row.id;
    var viewBtn = '<button type="button" class="btn btn-ghost-primary btn-sm js-alert-open" data-id="'
      + id + '" data-mode="view">查看</button>';
    if (!row.can_handle) return viewBtn;
    var label = row.is_inquiry ? '调单' : '处理';
    return viewBtn + ' <button type="button" class="btn btn-primary btn-sm js-alert-open" data-id="'
      + id + '" data-mode="handle">' + label + '</button>';
  }

  function renderRows(items) {
    var tbody = document.getElementById('alertTableBody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无预警</td></tr>';
      return;
    }

    tbody.innerHTML = items.map(function (row) {
      return ''
        + '<tr>'
        +   '<td class="font-monospace">' + dash(row.alert_no) + '</td>'
        +   '<td class="text-secondary">' + dash(row.alerted_at) + '</td>'
        +   '<td class="font-monospace">' + dash(row.merchant_id) + '</td>'
        +   '<td class="font-monospace">' + dash(row.order_no) + '</td>'
        +   '<td>' + dash(row.amount_display) + '</td>'
        +   '<td>' + badge(row.risk_level_label, RISK_LT) + '</td>'
        +   '<td>' + dash(row.rule_name) + '</td>'
        +   '<td>' + badge(row.measure_label, MEASURE_LT) + '</td>'
        +   '<td>' + badge(row.status_label, STATUS_LT) + '</td>'
        +   '<td>' + strCell(row) + '</td>'
        +   '<td class="text-nowrap">' + actionButtons(row) + '</td>'
        + '</tr>';
    }).join('');
  }

  function renderFooter(payload) {
    var total = payload.total || 0;
    var current = payload.current_page || state.page;
    var last = payload.last_page || 1;
    var perPage = payload.per_page || state.pageSize;

    var summary = document.getElementById('alertTableSummary');
    if (summary) summary.textContent = '共 ' + total + ' 条记录';

    var pageSizeEl = document.getElementById('alertPageSize');
    if (pageSizeEl) pageSizeEl.innerHTML = ListPage.renderPageSizeDropdown(perPage);

    var pager = document.getElementById('alertTablePager');
    if (pager) pager.innerHTML = ListPage.renderPaginationHtml(current, last);
  }

  function loadList() {
    if (state.loading) return;
    setLoading(true);
    syncUrl();

    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));

    fetch(LIST_URL + '?' + params.toString(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.loading = false;
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '加载失败');
          var tbody = document.getElementById('alertTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">'
              + escapeHtml((json && json.msg) || '加载失败') + '</td></tr>';
          }
          return;
        }
        var payload = json.data || {};
        state.page = payload.current_page || state.page;
        state.pageSize = ListPage.normalizePageSize(payload.per_page || state.pageSize);
        renderRows(payload.data || []);
        renderFooter(payload);
      })
      .catch(function () {
        state.loading = false;
        notifyError('网络错误');
      });
  }

  function hitDetailsHtml(detail) {
    var hits = Array.isArray(detail.hit_details) ? detail.hit_details : [];
    if (!hits.length) return '';
    var rows = hits.map(function (h) {
      return '<tr><td class="font-monospace">' + dash(h.id) + '</td><td>' + dash(h.name) + '</td>'
        + '<td>' + dash(h.measure || h.measure_code) + '</td><td>' + dash(h.risk_level) + '</td></tr>';
    }).join('');
    return ''
      + '<div class="mb-3">'
      +   '<div class="fw-bold mb-2">命中规则及配置处置策略</div>'
      +   '<div class="table-responsive">'
      +     '<table class="table table-sm table-bordered mb-0">'
      +       '<thead><tr><th>规则</th><th>名称</th><th>配置策略</th><th>风险等级</th></tr></thead>'
      +       '<tbody>' + rows + '</tbody>'
      +     '</table>'
      +   '</div>'
      + '</div>';
  }

  function attachmentsHtml(detail, editable) {
    var list = Array.isArray(detail.attachments) ? detail.attachments : [];
    var items = list.length
      ? list.map(function (f) {
          return '<div class="d-flex align-items-center justify-content-between border rounded px-2 py-1 mb-1">'
            + '<span class="text-truncate me-2">' + dash(f.file_name)
            + ' <span class="text-secondary small">(' + Math.round((f.file_size || 0) / 1024) + ' KB)</span></span>'
            + '<a class="btn btn-ghost-primary btn-sm" href="' + escapeHtml(f.download_url || '#') + '">下载</a>'
            + '</div>';
        }).join('')
      : '<div class="text-secondary small mb-2">暂无附件</div>';

    var upload = editable
      ? '<button type="button" class="btn btn-outline-primary btn-sm" id="alertUploadBtn">'
        + '<i class="ti ti-upload me-1"></i>上传调单材料</button>'
      : '';

    return ''
      + '<div class="mb-3" id="alertInquirySection">'
      +   '<div class="fw-bold mb-2">调单材料</div>'
      +   '<p class="text-secondary small">调单（Retrieval Request）已发起，请上传调单函、交易凭证、商户回复、物流/签收证明等相关材料。</p>'
      +   items
      +   upload
      +   '<div class="mt-2">'
      +     '<label class="form-label" for="alertInquiryDesc">调单说明</label>'
      +     '<textarea class="form-control" id="alertInquiryDesc" rows="2" '
      +       + (editable ? '' : 'readonly ')
      +       + 'placeholder="填写调单原因、要求商户补充的材料、调单编号等">'
      +       + escapeHtml(detail.inquiry_desc || '') + '</textarea>'
      +   '</div>'
      + '</div>';
  }

  function actionOptionsHtml(detail) {
    var map = detail.is_inquiry ? (handleActions().inquiry || {}) : (handleActions().default || {});
    var keys = Object.keys(map);
    if (!keys.length) return '';
    return keys.map(function (k) {
      return '<option value="' + escapeHtml(k) + '">' + escapeHtml(map[k]) + '</option>';
    }).join('');
  }

  function renderHandleBody(detail, mode) {
    var editable = mode === 'handle' && !!detail.can_handle;
    var notice = ''
      + '<div class="alert alert-info" role="alert">'
      +   '<div><b>说明：</b>处置策略「' + escapeHtml(detail.measure_label || '') + '」已记录，预警状态为 <b>'
      +   escapeHtml(detail.status_label || '') + '</b>。'
      +   '订单授权终态不受本处置影响，本操作仅更新预警跟进状态'
      +   + (detail.is_inquiry ? '与调单材料。' : '。')
      +   '</div>'
      + '</div>';

    var form = '';
    if (editable) {
      form = ''
        + '<div class="mb-3">'
        +   '<div class="mb-2">当前策略 ' + badge(detail.measure_label, MEASURE_LT) + '</div>'
        +   '<label class="form-label required" for="alertHandleAction">后续处理</label>'
        +   '<select class="form-select" id="alertHandleAction">' + actionOptionsHtml(detail) + '</select>'
        +   '<div class="form-hint">' + (detail.is_inquiry
          ? '提交调单材料后维持调单状态；材料齐全并核实后可关闭预警'
          : '确认记录后关闭预警，或标记误报关闭') + '</div>'
        +   '<label class="form-label mt-3" for="alertHandleRemark">处理备注</label>'
        +   '<textarea class="form-control" id="alertHandleRemark" rows="2" placeholder="请输入处理说明">'
        +     escapeHtml(detail.handle_remark || '') + '</textarea>'
        + '</div>';
    } else {
      form = ''
        + '<div class="mb-2">当前策略 ' + badge(detail.measure_label, MEASURE_LT) + '</div>'
        + (detail.handle_remark
          ? '<div class="text-secondary small mb-2">处理备注：' + escapeHtml(detail.handle_remark) + '</div>'
          : '');
    }

    return hitDetailsHtml(detail)
      + notice
      + (detail.is_inquiry ? attachmentsHtml(detail, editable) : '')
      + form;
  }

  function ensureModal() {
    if (state.handleModal) return state.handleModal;
    var ModalCtor = (window.bootstrap && window.bootstrap.Modal)
      || (window.tabler && window.tabler.Modal);
    var modalEl = document.getElementById('alertHandleModal');
    if (modalEl && ModalCtor) {
      state.handleModal = ModalCtor.getOrCreateInstance(modalEl);
    }
    return state.handleModal;
  }

  function openHandle(id, mode) {
    state.currentId = id;
    state.currentDetail = null;
    var body = document.getElementById('alertHandleBody');
    var submitBtn = document.getElementById('alertHandleSubmit');
    if (body) body.innerHTML = '<div class="text-center text-secondary py-4">加载中…</div>';
    if (submitBtn) submitBtn.classList.add('d-none');

    var modal = ensureModal();
    if (modal) {
      modal.show();
    } else {
      notifyError('弹窗组件未加载，请刷新页面重试');
      return;
    }

    fetch(DETAIL_URL + '?id=' + encodeURIComponent(id), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '加载详情失败');
          if (body) body.innerHTML = '<div class="alert alert-danger mb-0">' + escapeHtml((json && json.msg) || '加载失败') + '</div>';
          return;
        }
        var detail = json.data || {};
        state.currentDetail = detail;
        var title = document.getElementById('alertHandleModalLabel');
        var sub = document.getElementById('alertHandleSub');
        var editable = mode === 'handle' && !!detail.can_handle;
        if (title) {
          title.textContent = editable
            ? (detail.is_inquiry ? '调单处理' : '预警处理')
            : '预警详情';
        }
        if (sub) {
          sub.textContent = [detail.order_no || '', detail.rule_name || ''].filter(Boolean).join(' · ');
        }
        if (body) body.innerHTML = renderHandleBody(detail, mode);
        if (submitBtn) {
          if (editable) {
            submitBtn.classList.remove('d-none');
            submitBtn.textContent = detail.is_inquiry ? '提交调单' : '提交处理';
          } else {
            submitBtn.classList.add('d-none');
          }
        }
      })
      .catch(function () {
        notifyError('网络错误');
      });
  }

  function refreshDetailInModal() {
    if (!state.currentId || !state.handleModal) return;
    openHandle(state.currentId, 'handle');
  }

  function submitHandle() {
    if (state.saving || !state.currentId || !state.currentDetail) return;
    var actionEl = document.getElementById('alertHandleAction');
    var remarkEl = document.getElementById('alertHandleRemark');
    var descEl = document.getElementById('alertInquiryDesc');
    if (!actionEl) return;

    var payload = {
      id: state.currentId,
      action: actionEl.value,
      remark: remarkEl ? remarkEl.value : ''
    };
    if (state.currentDetail.is_inquiry && descEl) {
      payload.inquiry_desc = descEl.value;
    }

    state.saving = true;
    var submitBtn = document.getElementById('alertHandleSubmit');
    if (submitBtn) submitBtn.disabled = true;

    fetch(HANDLE_URL, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.saving = false;
        if (submitBtn) submitBtn.disabled = false;
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '处置失败');
          return;
        }
        notifySuccess(json.msg || '处置成功');
        if (state.handleModal) state.handleModal.hide();
        loadList();
      })
      .catch(function () {
        state.saving = false;
        if (submitBtn) submitBtn.disabled = false;
        notifyError('网络错误');
      });
  }

  function uploadFiles(files) {
    if (!state.currentId || !files || !files.length) return;
    var chain = Promise.resolve();
    Array.prototype.forEach.call(files, function (file) {
      chain = chain.then(function () {
        var fd = new FormData();
        fd.append('id', String(state.currentId));
        fd.append('file', file);
        return fetch(UPLOAD_URL, {
          method: 'POST',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
          body: fd
        }).then(function (res) { return res.json(); }).then(function (json) {
          if (!json || json.code !== 0) {
            throw new Error((json && json.msg) || '上传失败');
          }
          state.currentDetail = json.data;
        });
      });
    });

    chain
      .then(function () {
        notifySuccess('上传成功');
        refreshDetailInModal();
        loadList();
      })
      .catch(function (err) {
        notifyError(err.message || '上传失败');
      });
  }

  function bindEvents() {
    var form = document.getElementById('alertFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        state.page = 1;
        loadList();
      });
    }

    var resetBtn = document.getElementById('alertFilterReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        document.querySelectorAll('[form="alertFilterForm"]').forEach(function (el) {
          if (el.name) el.value = '';
        });
        if (form) {
          Array.prototype.forEach.call(form.elements, function (el) {
            if (el.name) el.value = '';
          });
        }
        state.page = 1;
        loadList();
      });
    }

    var footer = document.getElementById('alertTableFooter');
    if (footer) {
      footer.addEventListener('click', function (e) {
        var sizeBtn = e.target.closest('[data-page-size]');
        if (sizeBtn) {
          e.preventDefault();
          state.pageSize = ListPage.normalizePageSize(parseInt(sizeBtn.getAttribute('data-page-size'), 10) || 10);
          state.page = 1;
          loadList();
          return;
        }
        var pageLink = e.target.closest('[data-page]');
        if (pageLink) {
          e.preventDefault();
          var p = parseInt(pageLink.getAttribute('data-page'), 10);
          if (!isNaN(p) && p > 0) {
            state.page = p;
            loadList();
          }
        }
      });
    }

    var tbody = document.getElementById('alertTableBody');
    if (tbody) {
      tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-alert-open');
        if (!btn) return;
        openHandle(parseInt(btn.getAttribute('data-id'), 10), btn.getAttribute('data-mode') || 'view');
      });
    }

    var submitBtn = document.getElementById('alertHandleSubmit');
    if (submitBtn) {
      submitBtn.addEventListener('click', function () {
        submitHandle();
      });
    }

    var modalBody = document.getElementById('alertHandleBody');
    if (modalBody) {
      modalBody.addEventListener('click', function (e) {
        if (e.target.closest('#alertUploadBtn')) {
          var input = document.getElementById('alertFileInput');
          if (input) input.click();
        }
      });
    }

    var fileInput = document.getElementById('alertFileInput');
    if (fileInput) {
      fileInput.addEventListener('change', function () {
        uploadFiles(fileInput.files);
        fileInput.value = '';
      });
    }
  }

  function init() {
    ensureModal();
    applyQueryToForm();
    bindEvents();
    loadList();
  }

  return { init: init };
})();
