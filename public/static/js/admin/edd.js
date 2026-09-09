/**
 * EDD 强化尽调（KPI + 列表 + 新建 + 详情多模式 + 上传/审核）
 */
var EddPage = (function () {
  'use strict';

  var LIST_URL = '/admin/edd/list';
  var STATS_URL = '/admin/edd/stats';
  var DETAIL_URL = '/admin/edd/detail';
  var CREATE_URL = '/admin/edd/create';
  var COLLECT_URL = '/admin/edd/collect';
  var UPLOAD_URL = '/admin/edd/upload';
  var DELETE_ATTACH_URL = '/admin/edd/attachment/delete';
  var SUBMIT_URL = '/admin/edd/submit';
  var REVIEW_URL = '/admin/edd/review';

  var state = {
    page: 1,
    pageSize: 10,
    loading: false,
    saving: false,
    createModal: null,
    detailModal: null,
    currentId: null,
    currentAction: 'view',
    currentDetail: null,
    uploadKey: null
  };

  var COLSPAN = 9;

  var STATUS_LT = {
    '待启动': 'bg-indigo-lt',
    '资料收集中': 'bg-azure-lt',
    '审核中': 'bg-yellow-lt',
    '已通过': 'bg-green-lt',
    '未通过': 'bg-red-lt',
    '已过期': 'bg-secondary-lt'
  };

  var RISK_LT = {
    '低风险': 'bg-green-lt',
    '中风险': 'bg-yellow-lt',
    '高风险': 'bg-red-lt'
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

  function checklistItems() {
    return Array.isArray(window.EDD_CHECKLIST_ITEMS) ? window.EDD_CHECKLIST_ITEMS : [];
  }

  function badge(label, clsMap) {
    if (!label) return '—';
    var cls = clsMap[label] || 'bg-secondary-lt';
    return '<span class="badge ' + cls + '">' + escapeHtml(label) + '</span>';
  }

  function progressBar(pct) {
    var p = Math.max(0, Math.min(100, parseInt(pct, 10) || 0));
    return ''
      + '<div style="min-width:100px;">'
      +   '<div class="small mb-1">' + p + '%</div>'
      +   '<div class="progress progress-sm">'
      +     '<div class="progress-bar" style="width:' + p + '%" role="progressbar"></div>'
      +   '</div>'
      + '</div>';
  }

  function getFilters() {
    var form = document.getElementById('eddFilterForm');
    var params = new URLSearchParams();
    if (!form) return params;

    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.disabled) return;
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
      if (el.type === 'submit' || el.type === 'button') return;
      var val = String(el.value || '').trim();
      if (val !== '') params.set(el.name, val);
    });

    document.querySelectorAll('[form="eddFilterForm"]').forEach(function (el) {
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
    var form = document.getElementById('eddFilterForm');
    if (form) {
      Array.prototype.forEach.call(form.elements, function (el) {
        if (!el.name) return;
        if (qs.has(el.name)) el.value = qs.get(el.name);
      });
    }
    document.querySelectorAll('[form="eddFilterForm"]').forEach(function (el) {
      if (!el.name) return;
      if (qs.has(el.name)) el.value = qs.get(el.name);
    });

    state.page = Math.max(1, parseInt(qs.get('page') || '1', 10) || 1);
    state.pageSize = ListPage.normalizePageSize(parseInt(qs.get('pageSize') || '10', 10) || 10);

    var mid = qs.get('merchant_id');
    if (mid) {
      var merchantInput = document.getElementById('eddFormMerchantId');
      if (merchantInput) merchantInput.value = mid;
    }
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
    var tbody = document.getElementById('eddTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function renderRows(items) {
    var tbody = document.getElementById('eddTableBody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无 EDD 工单</td></tr>';
      return;
    }

    tbody.innerHTML = items.map(function (row) {
      var id = row.id;
      var status = row.status;
      var docsReady = !!row.docs_ready;
      var actions = ''
        + '<button type="button" class="btn btn-ghost-primary btn-sm js-edd-detail" data-id="' + id + '" data-action="view">详情</button>';

      if (status === 'pending') {
        actions += ' <button type="button" class="btn btn-primary btn-sm js-edd-detail" data-id="' + id + '" data-action="start">启动</button>';
      }
      if (status === 'collecting' && !docsReady) {
        actions += ' <button type="button" class="btn btn-primary btn-sm js-edd-detail" data-id="' + id + '" data-action="upload">上传资料</button>';
      }
      if (status === 'reviewing' || (status === 'collecting' && docsReady)) {
        actions += ' <button type="button" class="btn btn-warning btn-sm js-edd-review" data-id="' + id + '">审核</button>';
      }

      return ''
        + '<tr>'
        +   '<td class="font-monospace">' + dash(row.case_no) + '</td>'
        +   '<td class="font-monospace">' + dash(row.merchant_id) + '</td>'
        +   '<td>' + dash(row.merchant_name) + '</td>'
        +   '<td>' + dash(row.trigger_label) + '</td>'
        +   '<td>' + badge(row.risk_level_label, RISK_LT) + '</td>'
        +   '<td>' + progressBar(row.progress) + '</td>'
        +   '<td class="text-secondary">' + dash(row.deadline) + '</td>'
        +   '<td>' + badge(row.status_label, STATUS_LT) + '</td>'
        +   '<td class="text-nowrap">' + actions + '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var summary = document.getElementById('eddTableSummary');
    var pager = document.getElementById('eddTablePager');
    var sizeWrap = document.getElementById('eddPageSize');
    if (!summary || !pager) return;

    var total = data.total;
    var page = data.current_page;
    var lastPage = data.last_page;
    var perPage = data.per_page || state.pageSize;
    state.pageSize = ListPage.normalizePageSize(perPage);
    if (sizeWrap) {
      sizeWrap.innerHTML = ListPage.renderPageSizeDropdown(state.pageSize);
    }

    if (total <= 0) {
      summary.textContent = '暂无数据';
      pager.innerHTML = '';
      return;
    }

    summary.textContent = '共 ' + total + ' 条记录';
    pager.innerHTML = ListPage.renderPaginationHtml(page, lastPage);
  }

  function renderStats(stats) {
    var map = {
      eddKpiActive: stats.active,
      eddKpiPending: stats.pending,
      eddKpiPassed: stats.passed,
      eddKpiFailed: stats.failed
    };
    Object.keys(map).forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.textContent = map[id] != null ? String(map[id]) : '—';
    });
  }

  function loadStats() {
    return fetch(STATS_URL, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '统计加载失败');
          return;
        }
        renderStats(json.data || {});
      })
      .catch(function () {
        notifyError('统计加载失败');
      });
  }

  function loadList(page) {
    if (typeof page === 'number') state.page = page;
    if (state.loading) return;
    setLoading(true);
    syncUrl();

    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));

    fetch(LIST_URL + '?' + params.toString(), {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.loading = false;
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '列表加载失败');
          var tbody = document.getElementById('eddTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">加载失败</td></tr>';
          }
          return;
        }
        var data = json.data || {};
        state.page = data.current_page || state.page;
        renderRows(data.data || []);
        renderPager(data);
      })
      .catch(function () {
        state.loading = false;
        notifyError('列表加载失败');
      });
  }

  function detailItem(label, valueHtml) {
    return ''
      + '<div class="col-6 col-md-4">'
      +   '<div class="text-secondary small">' + escapeHtml(label) + '</div>'
      +   '<div class="mt-1">' + valueHtml + '</div>'
      + '</div>';
  }

  function setActionButtons(action, detail) {
    var collectBtn = document.getElementById('eddCollectBtn');
    var uploadHintBtn = document.getElementById('eddUploadHintBtn');
    var submitBtn = document.getElementById('eddSubmitBtn');
    var approveBtn = document.getElementById('eddApproveBtn');
    var rejectBtn = document.getElementById('eddRejectBtn');
    var dialog = document.getElementById('eddDetailDialog');

    [collectBtn, uploadHintBtn, submitBtn, approveBtn, rejectBtn].forEach(function (btn) {
      if (btn) btn.classList.add('d-none');
    });

    if (dialog) {
      dialog.classList.toggle('modal-xl', action === 'review');
      dialog.classList.toggle('modal-lg', action !== 'review');
    }

    if (action === 'start' && detail.status === 'pending' && collectBtn) {
      collectBtn.classList.remove('d-none');
    }
    if (action === 'upload' && detail.status === 'collecting') {
      if (!detail.docs_ready && uploadHintBtn) uploadHintBtn.classList.remove('d-none');
      if (detail.docs_ready && submitBtn) submitBtn.classList.remove('d-none');
    }
    if (action === 'review' && detail.status === 'reviewing') {
      if (approveBtn) approveBtn.classList.remove('d-none');
      if (rejectBtn) rejectBtn.classList.remove('d-none');
    }
  }

  function renderFileRows(files, canUpload, canPreview) {
    if (!files || !files.length) {
      return '<div class="text-secondary small mt-2">尚未上传资料</div>';
    }
    return '<div class="mt-2 d-flex flex-column gap-1">' + files.map(function (f) {
      return ''
        + '<div class="d-flex align-items-center gap-2 small border rounded px-2 py-1 bg-light">'
        +   '<span class="text-truncate" style="max-width:220px;" title="' + escapeHtml(f.name) + '">' + escapeHtml(f.name) + '</span>'
        +   '<span class="text-secondary">' + escapeHtml(f.size || '') + '</span>'
        +   '<span class="text-secondary">' + escapeHtml(f.upload_time || '') + '</span>'
        +   '<span class="ms-auto d-flex gap-1 flex-shrink-0">'
        +     (canUpload
          ? '<button type="button" class="btn btn-sm js-edd-del-file" data-attach-id="' + f.id + '">删除</button>'
          : '')
        +     (canPreview
          ? '<a class="btn btn-sm btn-primary" href="' + escapeHtml(f.download_url) + '" target="_blank" rel="noopener">下载查看</a>'
          : '')
        +   '</span>'
        + '</div>';
    }).join('') + '</div>';
  }

  function renderChecklist(detail, action) {
    var selecting = action === 'start' && detail.status === 'pending';
    var canUpload = action === 'upload' && detail.status === 'collecting';
    var reviewing = action === 'review' && detail.status === 'reviewing';
    var checklist = detail.checklist || {};
    var docs = detail.docs || {};
    var items = checklistItems();
    var canPreview = reviewing || action === 'view' || canUpload
      || ['reviewing', 'passed', 'rejected', 'expired', 'collecting'].indexOf(detail.status) >= 0;

    if (reviewing) {
      var rows = [];
      items.forEach(function (c) {
        if (!checklist[c.key]) return;
        var files = docs[c.key] || [];
        if (!files.length) {
          rows.push('<tr><td>' + escapeHtml(c.label) + '</td><td colspan="3" class="text-secondary">未上传</td><td>—</td></tr>');
          return;
        }
        files.forEach(function (f) {
          rows.push(
            '<tr><td>' + escapeHtml(c.label) + '</td>'
            + '<td>' + escapeHtml(f.name) + '</td>'
            + '<td>' + escapeHtml(f.size || '—') + '</td>'
            + '<td>' + escapeHtml(f.upload_time || '—') + '</td>'
            + '<td><a class="btn btn-sm btn-primary" href="' + escapeHtml(f.download_url) + '" target="_blank" rel="noopener">下载查看</a></td></tr>'
          );
        });
      });
      return ''
        + '<div class="table-responsive"><table class="table table-sm table-vcenter">'
        + '<thead><tr><th>材料项</th><th>文件名</th><th>大小</th><th>上传时间</th><th>操作</th></tr></thead>'
        + '<tbody>' + (rows.length ? rows.join('') : '<tr><td colspan="5" class="text-center text-secondary">暂无已上传资料</td></tr>') + '</tbody></table></div>';
    }

    return items.map(function (c) {
      var isNeeded = selecting ? (c.required !== false) : !!checklist[c.key];
      if (selecting) {
        var checked = checklist[c.key] !== undefined ? !!checklist[c.key] : (c.required !== false);
        var statusTag = c.required === false
          ? '<span class="badge bg-green-lt">可选</span>'
          : '<span class="badge bg-azure-lt">建议收集</span>';
        return ''
          + '<div class="border rounded p-3 mb-2">'
          +   '<div class="form-check">'
          +     '<input class="form-check-input js-edd-check" type="checkbox" id="eddReq_' + escapeHtml(c.key) + '" data-key="' + escapeHtml(c.key) + '"' + (checked ? ' checked' : '') + '>'
          +     '<label class="form-check-label" for="eddReq_' + escapeHtml(c.key) + '">'
          +       '<strong>' + escapeHtml(c.label) + '</strong> ' + statusTag
          +       '<div class="text-secondary small">' + escapeHtml(c.desc) + '</div>'
          +     '</label>'
          +   '</div>'
          + '</div>';
      }

      if (!isNeeded && action !== 'view') {
        return '';
      }

      var files = docs[c.key] || [];
      var done = files.length > 0;
      var statusTag = !isNeeded
        ? '<span class="badge bg-secondary-lt">未要求</span>'
        : (done ? '<span class="badge bg-green-lt">已上传</span>' : '<span class="badge bg-azure-lt">待上传</span>');

      return ''
        + '<div class="border rounded p-3 mb-2">'
        +   '<div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">'
        +     '<div>'
        +       '<strong>' + escapeHtml(c.label) + '</strong> ' + statusTag
        +       '<div class="text-secondary small">' + escapeHtml(c.desc) + '</div>'
        +     '</div>'
        +     (canUpload && isNeeded
          ? '<button type="button" class="btn btn-primary btn-sm js-edd-upload" data-key="' + escapeHtml(c.key) + '"><i class="ti ti-upload me-1"></i>上传</button>'
          : '')
        +   '</div>'
        +   (isNeeded ? renderFileRows(files, canUpload, canPreview) : '')
        + '</div>';
    }).join('');
  }

  function renderDetailBody(detail, action) {
    var selecting = action === 'start' && detail.status === 'pending';
    var canUpload = action === 'upload' && detail.status === 'collecting';
    var reviewing = action === 'review' && detail.status === 'reviewing';
    var missing = detail.missing_keys || [];
    var prog = detail.progress || 0;

    var progressHint = '';
    if (selecting) {
      progressHint = '<span class="text-secondary small ms-2">勾选所需资料后确认启动</span>';
    } else if (canUpload && missing.length) {
      progressHint = '<span class="text-warning small ms-2">待上传 ' + missing.length + ' 项</span>';
    } else if (canUpload && !missing.length) {
      progressHint = '<span class="text-green small ms-2">资料已齐备，可提交审核</span>';
    } else if (reviewing) {
      progressHint = '<span class="text-secondary small ms-2">请核验材料并填写审核备注</span>';
    }

    var sectionTitle = selecting
      ? '勾选所需尽调资料'
      : (reviewing ? '已上传尽调资料' : '尽调资料清单');

    var reviewHtml = '';
    if (reviewing) {
      reviewHtml = ''
        + '<div class="card card-sm mb-3"><div class="card-body">'
        +   '<h4 class="card-title">审核备注</h4>'
        +   '<label class="form-label required">审核备注（必填）</label>'
        +   '<textarea class="form-control" id="eddReviewRemark" rows="4" placeholder="请填写材料核验情况、风险判断及通过/不通过依据"></textarea>'
        +   '<div class="form-hint text-warning">审核通过或不通过前必须填写审核备注</div>'
        + '</div></div>';
    } else if (detail.review_remark) {
      reviewHtml = ''
        + '<div class="card card-sm mb-3"><div class="card-body">'
        +   '<h4 class="card-title">审核备注</h4>'
        +   '<div class="row g-3">'
        +     detailItem('审核结论', badge(detail.status_label, STATUS_LT))
        +     detailItem('审核时间', dash(detail.reviewed_at))
        +     '<div class="col-12"><div class="text-secondary small">审核备注</div><div class="mt-1">' + escapeHtml(detail.review_remark) + '</div></div>'
        +   '</div>'
        + '</div></div>';
    }

    return ''
      + '<div class="card card-sm mb-3"><div class="card-body">'
      +   '<h4 class="card-title">工单信息</h4>'
      +   '<div class="row g-3">'
      +     detailItem('EDD 编号', '<span class="font-monospace">' + dash(detail.case_no) + '</span>')
      +     detailItem('状态', badge(detail.status_label, STATUS_LT))
      +     detailItem('商户号', '<span class="font-monospace">' + dash(detail.merchant_id) + '</span>')
      +     detailItem('商户名称', dash(detail.merchant_name))
      +     detailItem('触发原因', dash(detail.trigger_label))
      +     detailItem('风险等级', badge(detail.risk_level_label, RISK_LT))
      +     detailItem('负责人', dash(detail.assignee))
      +     detailItem('截止日期', dash(detail.deadline))
      +     detailItem('创建时间', dash(detail.created_at))
      +     (detail.reviewed_at ? detailItem('审核时间', dash(detail.reviewed_at)) : '')
      +   '</div>'
      +   '<div class="mt-3">资料完成度：<strong class="text-primary">' + prog + '%</strong>' + progressHint
      +     '<div class="progress progress-sm mt-2"><div class="progress-bar" style="width:' + prog + '%"></div></div>'
      +   '</div>'
      + '</div></div>'
      + (detail.notes
        ? '<div class="card card-sm mb-3"><div class="card-body"><h4 class="card-title">备注</h4><p class="mb-0">' + escapeHtml(detail.notes) + '</p></div></div>'
        : '')
      + (detail.linked_str_id
        ? '<div class="card card-sm mb-3"><div class="card-body"><h4 class="card-title">关联 STR</h4><span class="font-monospace">' + escapeHtml(detail.linked_str_id) + '</span></div></div>'
        : '')
      + '<div class="card card-sm mb-3"><div class="card-body">'
      +   '<h4 class="card-title">' + sectionTitle + '</h4>'
      +   renderChecklist(detail, action)
      + '</div></div>'
      + reviewHtml;
  }

  function openDetail(id, action) {
    state.currentId = id;
    state.currentAction = action || 'view';

    var titleMap = { start: '启动资料收集', upload: '上传尽调资料', review: 'EDD 审核', view: 'EDD 详情' };
    var titleEl = document.getElementById('eddDetailModalLabel');
    var body = document.getElementById('eddDetailBody');
    if (body) body.innerHTML = '<div class="text-center text-secondary py-4">加载中…</div>';
    if (state.detailModal) state.detailModal.show();

    fetch(DETAIL_URL + '?id=' + encodeURIComponent(id), {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0 || !json.data) {
          notifyError((json && json.msg) || '详情加载失败');
          if (body) body.innerHTML = '<div class="text-center text-secondary py-4">加载失败</div>';
          return;
        }
        var detail = json.data;
        var act = state.currentAction;
        if (act === 'start' && detail.status !== 'pending') act = 'view';
        if (act === 'upload' && detail.status !== 'collecting') act = 'view';
        if (act === 'review' && detail.status !== 'reviewing') act = 'view';
        state.currentAction = act;
        state.currentDetail = detail;

        if (titleEl) titleEl.textContent = (detail.case_no || '') + ' · ' + (titleMap[act] || 'EDD 详情');
        var sub = document.getElementById('eddDetailSub');
        if (sub) sub.textContent = (detail.merchant_id || '') + ' · ' + (detail.merchant_name || '');
        if (body) body.innerHTML = renderDetailBody(detail, act);
        setActionButtons(act, detail);
      })
      .catch(function () {
        notifyError('详情加载失败');
      });
  }

  function openReview(id) {
    fetch(DETAIL_URL + '?id=' + encodeURIComponent(id), {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0 || !json.data) {
          notifyError((json && json.msg) || '详情加载失败');
          return;
        }
        var detail = json.data;
        if (detail.status === 'collecting') {
          if (!detail.docs_ready) {
            notifyError('请先完成勾选材料上传');
            openDetail(id, 'upload');
            return;
          }
          return postJson(SUBMIT_URL, { id: id }).then(function (ok) {
            if (ok) openDetail(id, 'review');
          });
        }
        if (detail.status !== 'reviewing') {
          notifyError('当前状态不可审核');
          return;
        }
        openDetail(id, 'review');
      })
      .catch(function () {
        notifyError('详情加载失败');
      });
  }

  function postJson(url, data) {
    return fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify(data)
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '操作失败');
          return null;
        }
        notifySuccess(json.msg || '操作成功');
        loadList();
        loadStats();
        return json.data;
      })
      .catch(function () {
        notifyError('网络错误，请稍后重试');
        return null;
      });
  }

  function postForm(url, formData) {
    return fetch(url, {
      method: 'POST',
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
      body: formData
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '操作失败');
          return null;
        }
        notifySuccess(json.msg || '操作成功');
        loadList();
        loadStats();
        return json.data;
      })
      .catch(function () {
        notifyError('网络错误，请稍后重试');
        return null;
      });
  }

  function collectChecklistFromDom() {
    var map = {};
    checklistItems().forEach(function (c) {
      map[c.key] = false;
    });
    document.querySelectorAll('.js-edd-check').forEach(function (el) {
      var key = el.getAttribute('data-key');
      if (key) map[key] = !!el.checked;
    });
    return map;
  }

  function openCreateModal() {
    var form = document.getElementById('eddCreateForm');
    if (form) form.reset();
    var deadline = document.getElementById('eddFormDeadline');
    if (deadline && !deadline.value) {
      var d = new Date();
      d.setDate(d.getDate() + 30);
      deadline.value = d.toISOString().slice(0, 10);
    }
    var qs = new URLSearchParams(window.location.search);
    var mid = qs.get('merchant_id');
    if (mid) {
      var merchantInput = document.getElementById('eddFormMerchantId');
      if (merchantInput) merchantInput.value = mid;
    }
    if (state.createModal) state.createModal.show();
  }

  function saveCreate(e) {
    e.preventDefault();
    if (state.saving) return;
    var form = document.getElementById('eddCreateForm');
    if (!form) return;

    var payload = {
      merchant_id: String(form.merchant_id.value || '').trim(),
      trigger: String(form.trigger.value || '').trim(),
      deadline: String(form.deadline.value || '').trim(),
      assignee: String(form.assignee.value || '').trim(),
      notes: String(form.notes.value || '').trim()
    };

    if (!payload.merchant_id || !payload.deadline) {
      notifyError('请填写商户号与截止日期');
      return;
    }

    state.saving = true;
    var btn = document.getElementById('eddCreateSubmit');
    if (btn) {
      btn.disabled = true;
      btn.textContent = '保存中…';
    }

    postJson(CREATE_URL, payload).then(function (data) {
      state.saving = false;
      if (btn) {
        btn.disabled = false;
        btn.textContent = '保存';
      }
      if (data && state.createModal) state.createModal.hide();
    });
  }

  function bindEvents() {
    var form = document.getElementById('eddFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        loadList(1);
      });
    }

    var resetBtn = document.getElementById('eddFilterReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('[form="eddFilterForm"]').forEach(function (el) {
          if (el.tagName === 'SELECT') el.selectedIndex = 0;
          else if (el.type !== 'submit' && el.type !== 'button') el.value = '';
        });
        if (form) form.reset();
        loadList(1);
      });
    }

    var pager = document.getElementById('eddTablePager');
    if (pager) {
      pager.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-page]');
        if (!link) return;
        e.preventDefault();
        var page = parseInt(link.getAttribute('data-page'), 10);
        if (!isNaN(page)) loadList(page);
      });
    }

    var sizeWrap = document.getElementById('eddPageSize');
    if (sizeWrap) {
      sizeWrap.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-page-size]');
        if (!link) return;
        e.preventDefault();
        var size = ListPage.normalizePageSize(link.getAttribute('data-page-size'));
        if (size === state.pageSize) return;
        state.pageSize = size;
        loadList(1);
      });
    }

    var addBtn = document.getElementById('eddAddBtn');
    if (addBtn) {
      addBtn.addEventListener('click', function (e) {
        e.preventDefault();
        openCreateModal();
      });
    }

    var createForm = document.getElementById('eddCreateForm');
    if (createForm) createForm.addEventListener('submit', saveCreate);

    var tbody = document.getElementById('eddTableBody');
    if (tbody) {
      tbody.addEventListener('click', function (e) {
        var detailBtn = e.target.closest('.js-edd-detail');
        if (detailBtn) {
          e.preventDefault();
          openDetail(parseInt(detailBtn.getAttribute('data-id'), 10), detailBtn.getAttribute('data-action') || 'view');
          return;
        }
        var reviewBtn = e.target.closest('.js-edd-review');
        if (reviewBtn) {
          e.preventDefault();
          openReview(parseInt(reviewBtn.getAttribute('data-id'), 10));
        }
      });
    }

    var detailBody = document.getElementById('eddDetailBody');
    if (detailBody) {
      detailBody.addEventListener('click', function (e) {
        var uploadBtn = e.target.closest('.js-edd-upload');
        if (uploadBtn) {
          e.preventDefault();
          state.uploadKey = uploadBtn.getAttribute('data-key');
          var input = document.getElementById('eddFileInput');
          if (input) input.click();
          return;
        }
        var delBtn = e.target.closest('.js-edd-del-file');
        if (delBtn) {
          e.preventDefault();
          var attachId = parseInt(delBtn.getAttribute('data-attach-id'), 10);
          postJson(DELETE_ATTACH_URL, { attachment_id: attachId }).then(function (data) {
            if (data) openDetail(state.currentId, 'upload');
          });
        }
      });
    }

    var fileInput = document.getElementById('eddFileInput');
    if (fileInput) {
      fileInput.addEventListener('change', function (e) {
        var file = e.target.files && e.target.files[0];
        e.target.value = '';
        if (!file || !state.uploadKey || !state.currentId) return;
        var fd = new FormData();
        fd.append('id', String(state.currentId));
        fd.append('checklist_key', state.uploadKey);
        fd.append('file', file);
        postForm(UPLOAD_URL, fd).then(function (data) {
          state.uploadKey = null;
          if (data) openDetail(state.currentId, 'upload');
        });
      });
    }

    var collectBtn = document.getElementById('eddCollectBtn');
    if (collectBtn) {
      collectBtn.addEventListener('click', function () {
        var checklist = collectChecklistFromDom();
        postJson(COLLECT_URL, { id: state.currentId, checklist: checklist }).then(function (data) {
          if (data && state.detailModal) state.detailModal.hide();
        });
      });
    }

    var uploadHintBtn = document.getElementById('eddUploadHintBtn');
    if (uploadHintBtn) {
      uploadHintBtn.addEventListener('click', function () {
        var detail = state.currentDetail;
        if (!detail || !detail.missing_keys || !detail.missing_keys.length) {
          notifySuccess('资料已齐备，可提交审核');
          return;
        }
        state.uploadKey = detail.missing_keys[0];
        var input = document.getElementById('eddFileInput');
        if (input) input.click();
      });
    }

    var submitBtn = document.getElementById('eddSubmitBtn');
    if (submitBtn) {
      submitBtn.addEventListener('click', function () {
        postJson(SUBMIT_URL, { id: state.currentId }).then(function (data) {
          if (data) openDetail(state.currentId, 'review');
        });
      });
    }

    var approveBtn = document.getElementById('eddApproveBtn');
    if (approveBtn) {
      approveBtn.addEventListener('click', function () {
        var remarkEl = document.getElementById('eddReviewRemark');
        var remark = remarkEl ? String(remarkEl.value || '').trim() : '';
        if (!remark) {
          notifyError('请填写审核备注');
          return;
        }
        postJson(REVIEW_URL, { id: state.currentId, action: 'approve', review_remark: remark }).then(function (data) {
          if (data && state.detailModal) state.detailModal.hide();
        });
      });
    }

    var rejectBtn = document.getElementById('eddRejectBtn');
    if (rejectBtn) {
      rejectBtn.addEventListener('click', function () {
        var remarkEl = document.getElementById('eddReviewRemark');
        var remark = remarkEl ? String(remarkEl.value || '').trim() : '';
        if (!remark) {
          notifyError('请填写审核备注');
          return;
        }
        postJson(REVIEW_URL, { id: state.currentId, action: 'reject', review_remark: remark }).then(function (data) {
          if (data && state.detailModal) state.detailModal.hide();
        });
      });
    }
  }

  function init() {
    var ModalCtor = (window.bootstrap && window.bootstrap.Modal)
      || (window.tabler && window.tabler.Modal);

    var createEl = document.getElementById('eddCreateModal');
    if (createEl && ModalCtor) {
      state.createModal = ModalCtor.getOrCreateInstance(createEl);
    }
    var detailEl = document.getElementById('eddDetailModal');
    if (detailEl && ModalCtor) {
      state.detailModal = ModalCtor.getOrCreateInstance(detailEl);
    }

    applyQueryToForm();
    bindEvents();
    loadStats();
    loadList(state.page);
  }

  return { init: init, loadList: loadList };
})();
