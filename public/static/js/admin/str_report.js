/**
 * STR/LTR 报送（KPI + Tab + 列表 + 新建 + 详情状态机）
 */
var StrReportPage = (function () {
  'use strict';

  var LIST_URL = '/admin/str_report/list';
  var STATS_URL = '/admin/str_report/stats';
  var DETAIL_URL = '/admin/str_report/detail';
  var CREATE_URL = '/admin/str_report/create';
  var CONFIRM_URL = '/admin/str_report/confirm';
  var DISMISS_URL = '/admin/str_report/dismiss';
  var UPLOAD_URL = '/admin/str_report/upload';
  var SUBMIT_URL = '/admin/str_report/submit';

  var state = {
    page: 1,
    pageSize: 10,
    tab: 'all',
    loading: false,
    saving: false,
    createModal: null,
    detailModal: null,
    currentId: null,
    currentDetail: null
  };

  var COLSPAN = 11;

  var STATUS_LT = {
    '待确认': 'bg-yellow-lt',
    '已生成': 'bg-azure-lt',
    '已上传': 'bg-indigo-lt',
    '已提交监管': 'bg-green-lt',
    '无需上报': 'bg-secondary-lt',
    '已归档': 'bg-secondary-lt',
    '已退回': 'bg-red-lt'
  };

  var TYPE_LT = {
    '大额交易 (LTR)': 'bg-cyan-lt',
    '可疑交易 (STR)': 'bg-orange-lt'
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

  function askConfirm(options) {
    var confirmFn = window.AdminUi && typeof AdminUi.confirm === 'function'
      ? AdminUi.confirm.bind(AdminUi)
      : function (opts) {
          return Promise.resolve(window.confirm(opts.message || opts.title || '请确认'));
        };
    return confirmFn(options);
  }

  function submitToRegulator(id, afterOk) {
    askConfirm({
      title: '提交监管',
      message: '确认将本笔报送提交监管？提交后不可在本页撤销。',
      confirmText: '确认提交',
      type: 'primary'
    }).then(function (ok) {
      if (!ok) return;
      postJson(SUBMIT_URL, { id: id })
        .then(function (json) {
          if (!json || json.code !== 0) {
            notifyError((json && json.msg) || '提交失败');
            return;
          }
          notifySuccess(json.msg || '已提交监管');
          if (typeof afterOk === 'function') afterOk();
          refreshAll();
        })
        .catch(function () { notifyError('提交失败'); });
    });
  }

  function badge(label, clsMap) {
    if (!label) return '—';
    var cls = clsMap[label] || 'bg-secondary-lt';
    return '<span class="badge ' + cls + '">' + escapeHtml(label) + '</span>';
  }

  function getFilters() {
    var form = document.getElementById('strFilterForm');
    var params = new URLSearchParams();
    if (!form) return params;

    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.disabled) return;
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
      if (el.type === 'submit' || el.type === 'button') return;
      var val = String(el.value || '').trim();
      if (val !== '') params.set(el.name, val);
    });

    document.querySelectorAll('[form="strFilterForm"]').forEach(function (el) {
      if (!el.name || el.disabled) return;
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
      if (el.type === 'submit' || el.type === 'button') return;
      var val = String(el.value || '').trim();
      if (val !== '') params.set(el.name, val);
    });

    params.set('tab', state.tab || 'all');
    return params;
  }

  function applyQueryToForm() {
    var qs = new URLSearchParams(window.location.search);
    var form = document.getElementById('strFilterForm');
    if (form) {
      Array.prototype.forEach.call(form.elements, function (el) {
        if (!el.name) return;
        if (qs.has(el.name)) el.value = qs.get(el.name);
      });
    }
    document.querySelectorAll('[form="strFilterForm"]').forEach(function (el) {
      if (!el.name) return;
      if (qs.has(el.name)) el.value = qs.get(el.name);
    });

    state.page = Math.max(1, parseInt(qs.get('page') || '1', 10) || 1);
    state.pageSize = ListPage.normalizePageSize(parseInt(qs.get('pageSize') || '10', 10) || 10);
    state.tab = qs.get('tab') || 'all';
    var tabInput = document.getElementById('strTabInput');
    if (tabInput) tabInput.value = state.tab;
    syncTabUi();
  }

  function syncTabUi() {
    document.querySelectorAll('#strSubTabs [data-str-tab]').forEach(function (btn) {
      btn.classList.toggle('active', btn.getAttribute('data-str-tab') === state.tab);
    });
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
    var tbody = document.getElementById('strTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function renderRows(items) {
    var tbody = document.getElementById('strTableBody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无 STR 报送记录</td></tr>';
      return;
    }

    tbody.innerHTML = items.map(function (row) {
      var id = row.id;
      var status = row.status;
      var highlight = status === 'pending_confirm' ? ' class="table-warning"' : '';
      var actions = ''
        + '<button type="button" class="btn btn-ghost-primary btn-sm js-str-detail" data-id="' + id + '">详情</button>';

      if (status === 'pending_confirm') {
        actions += ' <button type="button" class="btn btn-success btn-sm js-str-detail" data-id="' + id + '">审核</button>';
      }
      if (status === 'generated' || status === 'rejected') {
        actions += ' <button type="button" class="btn btn-primary btn-sm js-str-detail" data-id="' + id + '">报送</button>';
      }
      if (status === 'uploaded') {
        actions += ' <button type="button" class="btn btn-success btn-sm js-str-submit" data-id="' + id + '">提交</button>';
      }

      var attachText = row.attachment_count > 0 ? (row.attachment_count + ' 个') : '—';

      return ''
        + '<tr' + highlight + '>'
        +   '<td class="font-monospace">' + dash(row.report_no) + '</td>'
        +   '<td>' + badge(row.type_label, TYPE_LT) + '</td>'
        +   '<td>' + dash(row.trigger_mode_label) + '</td>'
        +   '<td class="font-monospace">' + dash(row.merchant_id) + '</td>'
        +   '<td class="font-monospace">' + dash(row.order_no) + '</td>'
        +   '<td>' + dash(row.amount_display) + '</td>'
        +   '<td class="text-secondary text-wrap" style="max-width:220px;" title="'
        +     escapeHtml(row.trigger_reason || '') + '">' + dash(row.trigger_reason) + '</td>'
        +   '<td class="text-secondary text-nowrap">' + dash(row.created_at) + '</td>'
        +   '<td>' + badge(row.status_label, STATUS_LT) + '</td>'
        +   '<td>' + escapeHtml(attachText) + '</td>'
        +   '<td class="text-nowrap">' + actions + '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var summary = document.getElementById('strTableSummary');
    var pager = document.getElementById('strTablePager');
    var sizeWrap = document.getElementById('strPageSize');
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
      strKpiPending: stats.pending,
      strKpiLtr: stats.ltr_month,
      strKpiStr: stats.str_month,
      strKpiSubmitted: stats.submitted_quarter
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
          var tbody = document.getElementById('strTableBody');
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

  function setActionButtons(detail) {
    var dismissBtn = document.getElementById('strDismissBtn');
    var confirmBtn = document.getElementById('strConfirmBtn');
    var uploadBtn = document.getElementById('strUploadBtn');
    var submitBtn = document.getElementById('strSubmitBtn');

    [dismissBtn, confirmBtn, uploadBtn, submitBtn].forEach(function (btn) {
      if (btn) btn.classList.add('d-none');
    });

    if (!detail) return;
    var status = detail.status;
    if (status === 'pending_confirm') {
      if (dismissBtn) dismissBtn.classList.remove('d-none');
      if (confirmBtn) confirmBtn.classList.remove('d-none');
    }
    if (status === 'generated' || status === 'rejected') {
      if (uploadBtn) uploadBtn.classList.remove('d-none');
    }
    if (status === 'uploaded') {
      if (submitBtn) submitBtn.classList.remove('d-none');
    }
  }

  function renderAttachments(list) {
    if (!list || !list.length) {
      return '<div class="text-secondary small py-2">暂无附件</div>';
    }
    return ''
      + '<div class="table-responsive"><table class="table table-sm table-vcenter">'
      + '<thead><tr><th>文件名</th><th>大小</th><th>上传时间</th><th></th></tr></thead><tbody>'
      + list.map(function (a) {
        return '<tr>'
          + '<td>' + escapeHtml(a.name) + (a.is_auto ? ' <span class="badge bg-azure-lt">自动</span>' : '') + '</td>'
          + '<td>' + escapeHtml(a.size || '—') + '</td>'
          + '<td>' + escapeHtml(a.upload_time || '—') + '</td>'
          + '<td><a class="btn btn-sm btn-primary" href="' + escapeHtml(a.download_url) + '" target="_blank" rel="noopener">下载</a></td>'
          + '</tr>';
      }).join('')
      + '</tbody></table></div>';
  }

  function renderDetailBody(detail) {
    var pending = detail.status === 'pending_confirm';
    var reviewBlock = '';
    if (pending) {
      reviewBlock = ''
        + '<div class="card card-sm mb-3 border-warning"><div class="card-body">'
        +   '<h4 class="card-title">人工确认 — 是否上报？</h4>'
        +   '<p class="text-secondary small mb-2">系统已检测到该笔可疑交易并生成记录，<strong>尚未上报监管</strong>。请审核可疑依据后选择「确认上报」或「无需上报」。</p>'
        +   '<label class="form-label required">审核说明（必填）</label>'
        +   '<textarea class="form-control" id="strReviewRemark" rows="3" placeholder="确认上报：说明可疑特征及判断依据；无需上报：说明排除理由"></textarea>'
        + '</div></div>';
    }

    var eddBlock = '';
    if (detail.linked_edd) {
      eddBlock = ''
        + '<div class="card card-sm mb-3"><div class="card-body">'
        +   '<h4 class="card-title">关联 EDD</h4>'
        +   '<div class="row g-3">'
        +     detailItem('EDD 编号', '<span class="font-monospace">' + dash(detail.linked_edd.case_no) + '</span>')
        +     detailItem('EDD 状态', badge(detail.linked_edd.status_label, {
          '待启动': 'bg-indigo-lt',
          '资料收集中': 'bg-azure-lt',
          '审核中': 'bg-yellow-lt',
          '已通过': 'bg-green-lt',
          '未通过': 'bg-red-lt',
          '已过期': 'bg-secondary-lt'
        }))
        +   '</div>'
        +   '<a class="btn btn-ghost-primary btn-sm mt-2" href="/admin/edd?keyword=' + encodeURIComponent(detail.linked_edd.case_no) + '">查看 EDD →</a>'
        + '</div></div>';
    }

    return ''
      + '<div class="card card-sm mb-3"><div class="card-body">'
      +   '<h4 class="card-title">报送信息</h4>'
      +   '<div class="row g-3">'
      +     detailItem('报送编号', '<span class="font-monospace">' + dash(detail.report_no) + '</span>')
      +     detailItem('报送类型', badge(detail.type_label, TYPE_LT))
      +     detailItem('触发方式', dash(detail.trigger_mode_label))
      +     detailItem('商户号', '<span class="font-monospace">' + dash(detail.merchant_id) + '</span>')
      +     detailItem('商户名称', dash(detail.merchant_name))
      +     detailItem('订单号', '<span class="font-monospace">' + dash(detail.order_no) + '</span>')
      +     detailItem('交易金额', dash(detail.amount_display))
      +     detailItem('触发原因', dash(detail.trigger_reason))
      +     detailItem('报送状态', badge(detail.status_label, STATUS_LT))
      +     detailItem('创建时间', dash(detail.created_at))
      +     detailItem('报送人', dash(detail.submitter))
      +     (detail.reviewer ? detailItem('审核人', dash(detail.reviewer)) : '')
      +     (detail.reviewed_at ? detailItem('审核时间', dash(detail.reviewed_at)) : '')
      +     (detail.review_remark ? detailItem('确认说明', dash(detail.review_remark)) : '')
      +     (detail.dismiss_reason ? detailItem('排除理由', dash(detail.dismiss_reason)) : '')
      +     (detail.submitted_at ? detailItem('提交时间', dash(detail.submitted_at)) : '')
      +     (detail.reject_reason ? detailItem('退回原因', dash(detail.reject_reason)) : '')
      +   '</div>'
      + '</div></div>'
      + (detail.suspicious_desc
        ? '<div class="card card-sm mb-3"><div class="card-body"><h4 class="card-title">可疑交易描述</h4><p class="mb-0">' + escapeHtml(detail.suspicious_desc) + '</p></div></div>'
        : '')
      + reviewBlock
      + '<div class="card card-sm mb-3"><div class="card-body"><h4 class="card-title">报送附件</h4>'
      +   renderAttachments(detail.attachments || [])
      + '</div></div>'
      + eddBlock;
  }

  function openDetail(id) {
    state.currentId = id;
    var body = document.getElementById('strDetailBody');
    var title = document.getElementById('strDetailModalLabel');
    var sub = document.getElementById('strDetailSub');
    if (body) body.innerHTML = '<div class="text-center text-secondary py-4">加载中…</div>';
    setActionButtons(null);
    if (state.detailModal) state.detailModal.show();

    fetch(DETAIL_URL + '?id=' + encodeURIComponent(id), {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '详情加载失败');
          if (body) body.innerHTML = '<div class="text-danger text-center py-4">加载失败</div>';
          return;
        }
        var detail = json.data || {};
        state.currentDetail = detail;
        if (title) {
          title.textContent = detail.report_no + ' · ' + (detail.type === 'ltr' ? '大额交易报送' : '可疑交易报送');
        }
        if (sub) {
          sub.textContent = [detail.merchant_id, detail.merchant_name, detail.order_no].filter(Boolean).join(' · ');
        }
        if (body) body.innerHTML = renderDetailBody(detail);
        setActionButtons(detail);
      })
      .catch(function () {
        notifyError('详情加载失败');
      });
  }

  function updateSuspiciousVisibility() {
    var type = document.getElementById('strFormType');
    var wrap = document.getElementById('strSuspiciousWrap');
    if (!type || !wrap) return;
    wrap.style.display = type.value === 'str' ? '' : 'none';
  }

  function postJson(url, payload) {
    return fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json'
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    }).then(function (res) { return res.json(); });
  }

  function refreshAll() {
    loadStats();
    loadList();
  }

  function bindEvents() {
    var filterForm = document.getElementById('strFilterForm');
    if (filterForm) {
      filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        state.page = 1;
        loadList(1);
      });
    }

    var resetBtn = document.getElementById('strFilterReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        var form = document.getElementById('strFilterForm');
        if (form) form.reset();
        state.tab = 'all';
        var tabInput = document.getElementById('strTabInput');
        if (tabInput) tabInput.value = 'all';
        syncTabUi();
        state.page = 1;
        loadList(1);
      });
    }

    document.querySelectorAll('#strSubTabs [data-str-tab]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        state.tab = btn.getAttribute('data-str-tab') || 'all';
        var tabInput = document.getElementById('strTabInput');
        if (tabInput) tabInput.value = state.tab;
        syncTabUi();
        state.page = 1;
        loadList(1);
      });
    });

    var tbody = document.getElementById('strTableBody');
    if (tbody) {
      tbody.addEventListener('click', function (e) {
        var detailBtn = e.target.closest('.js-str-detail');
        if (detailBtn) {
          openDetail(parseInt(detailBtn.getAttribute('data-id'), 10));
          return;
        }
        var submitBtn = e.target.closest('.js-str-submit');
        if (submitBtn) {
          var sid = parseInt(submitBtn.getAttribute('data-id'), 10);
          submitToRegulator(sid);
        }
      });
    }

    var footer = document.getElementById('strTableFooter');
    if (footer) {
      footer.addEventListener('click', function (e) {
        var pageBtn = e.target.closest('[data-page]');
        if (pageBtn) {
          var p = parseInt(pageBtn.getAttribute('data-page'), 10);
          if (!isNaN(p)) loadList(p);
          return;
        }
        var sizeBtn = e.target.closest('[data-page-size]');
        if (sizeBtn) {
          state.pageSize = ListPage.normalizePageSize(parseInt(sizeBtn.getAttribute('data-page-size'), 10) || 10);
          state.page = 1;
          loadList(1);
        }
      });
    }

    var addBtn = document.getElementById('strAddBtn');
    if (addBtn) {
      addBtn.addEventListener('click', function () {
        if (!state.createModal) {
          notifyError('弹窗组件未就绪，请刷新页面重试');
          return;
        }
        var form = document.getElementById('strCreateForm');
        if (form) form.reset();
        updateSuspiciousVisibility();
        state.createModal.show();
      });
    }

    var typeSel = document.getElementById('strFormType');
    if (typeSel) {
      typeSel.addEventListener('change', updateSuspiciousVisibility);
    }

    var createForm = document.getElementById('strCreateForm');
    if (createForm) {
      createForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (state.saving) return;

        var fd = new FormData(createForm);
        if (fd.get('type') === 'str' && !String(fd.get('suspicious_desc') || '').trim()) {
          notifyError('可疑交易 STR 需填写可疑描述');
          return;
        }

        state.saving = true;
        var submitBtn = document.getElementById('strCreateSubmit');
        if (submitBtn) submitBtn.disabled = true;

        fetch(CREATE_URL, {
          method: 'POST',
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
          body: fd
        })
          .then(function (res) { return res.json(); })
          .then(function (json) {
            state.saving = false;
            if (submitBtn) submitBtn.disabled = false;
            if (!json || json.code !== 0) {
              notifyError((json && json.msg) || '创建失败');
              return;
            }
            notifySuccess(json.msg || '创建成功');
            if (state.createModal) state.createModal.hide();
            refreshAll();
          })
          .catch(function () {
            state.saving = false;
            if (submitBtn) submitBtn.disabled = false;
            notifyError('创建失败');
          });
      });
    }

    var confirmBtn = document.getElementById('strConfirmBtn');
    if (confirmBtn) {
      confirmBtn.addEventListener('click', function () {
        var remarkEl = document.getElementById('strReviewRemark');
        var remark = remarkEl ? String(remarkEl.value || '').trim() : '';
        if (!remark) {
          notifyError('请填写审核说明');
          return;
        }
        postJson(CONFIRM_URL, { id: state.currentId, review_remark: remark })
          .then(function (json) {
            if (!json || json.code !== 0) {
              notifyError((json && json.msg) || '确认失败');
              return;
            }
            notifySuccess(json.msg || '已确认需上报');
            if (state.detailModal) state.detailModal.hide();
            refreshAll();
          })
          .catch(function () { notifyError('确认失败'); });
      });
    }

    var dismissBtn = document.getElementById('strDismissBtn');
    if (dismissBtn) {
      dismissBtn.addEventListener('click', function () {
        var remarkEl = document.getElementById('strReviewRemark');
        var remark = remarkEl ? String(remarkEl.value || '').trim() : '';
        if (!remark) {
          notifyError('请填写排除理由');
          return;
        }
        postJson(DISMISS_URL, { id: state.currentId, dismiss_reason: remark })
          .then(function (json) {
            if (!json || json.code !== 0) {
              notifyError((json && json.msg) || '操作失败');
              return;
            }
            notifySuccess(json.msg || '已标记为无需上报');
            if (state.detailModal) state.detailModal.hide();
            refreshAll();
          })
          .catch(function () { notifyError('操作失败'); });
      });
    }

    var uploadBtn = document.getElementById('strUploadBtn');
    var fileInput = document.getElementById('strFileInput');
    if (uploadBtn && fileInput) {
      uploadBtn.addEventListener('click', function () {
        fileInput.value = '';
        fileInput.click();
      });
      fileInput.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0];
        if (!file || !state.currentId) return;
        var fd = new FormData();
        fd.append('id', String(state.currentId));
        fd.append('file', file);
        fetch(UPLOAD_URL, {
          method: 'POST',
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
          body: fd
        })
          .then(function (res) { return res.json(); })
          .then(function (json) {
            if (!json || json.code !== 0) {
              notifyError((json && json.msg) || '上传失败');
              return;
            }
            notifySuccess(json.msg || '上传成功');
            openDetail(state.currentId);
            refreshAll();
          })
          .catch(function () { notifyError('上传失败'); });
      });
    }

    var submitFooterBtn = document.getElementById('strSubmitBtn');
    if (submitFooterBtn) {
      submitFooterBtn.addEventListener('click', function () {
        if (!state.currentId) return;
        submitToRegulator(state.currentId, function () {
          if (state.detailModal) state.detailModal.hide();
        });
      });
    }
  }

  function initModals() {
    var ModalCtor = (window.bootstrap && window.bootstrap.Modal)
      || (window.tabler && window.tabler.Modal)
      || null;
    var createEl = document.getElementById('strCreateModal');
    var detailEl = document.getElementById('strDetailModal');
    if (createEl && ModalCtor) {
      state.createModal = ModalCtor.getOrCreateInstance(createEl);
    }
    if (detailEl && ModalCtor) {
      state.detailModal = ModalCtor.getOrCreateInstance(detailEl);
    }
  }

  function init() {
    applyQueryToForm();
    initModals();
    bindEvents();
    updateSuspiciousVisibility();
    loadStats();
    loadList();
  }

  return { init: init };
})();
