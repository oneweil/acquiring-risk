/**
 * STR 推送规则配置页
 * 首屏全局 config 由服务端注入；规则表 AJAX 分页；toggle 改草稿，保存时提交全量 push_str
 */
var StrPushRulePage = (function () {
  'use strict';

  var LIST_URL = '/admin/str_push_rule/list';
  var SAVE_URL = '/admin/str_push_rule/save';
  var RESET_URL = '/admin/str_push_rule/reset';
  var COLSPAN = 7;

  var RISK_LT = {
    '低风险': 'bg-green-lt',
    '中风险': 'bg-yellow-lt',
    '高风险': 'bg-orange-lt',
    '极高风险': 'bg-red-lt'
  };

  /** 风险等级由低到高；单选表示「该等级及以上」 */
  var RISK_ORDER = ['low', 'medium', 'high', 'critical'];

  var state = {
    page: 1,
    pageSize: 20,
    loading: false,
    saving: false,
    draftPush: {},
    draftReady: false
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
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

  function readBootConfig() {
    var el = document.getElementById('strPushConfigBoot');
    if (!el) return null;
    try {
      return JSON.parse(el.textContent || '{}');
    } catch (e) {
      return null;
    }
  }

  function applyConfigToForm(cfg) {
    if (!cfg) return;
    var setVal = function (id, val) {
      var el = document.getElementById(id);
      if (el) el.value = val;
    };
    setVal('strPushEnabled', cfg.enabled ? '1' : '0');
    setVal('strPushByRisk', cfg.push_by_risk_level ? '1' : '0');
    setVal('strPushLtr', cfg.push_ltr ? '1' : '0');
    setVal('strPushLtrUsd', cfg.ltr_threshold_usd);
    setVal('strPushLtrHkd', cfg.ltr_threshold_hkd);

    var riskSel = document.getElementById('strPushRiskLevels');
    if (riskSel) {
      riskSel.value = minRiskLevel(cfg.risk_levels || []) || 'high';
    }

    updateCounts(cfg.enabled_count, cfg.total_count);
  }

  function minRiskLevel(levels) {
    var minIdx = -1;
    (levels || []).forEach(function (lv) {
      var idx = RISK_ORDER.indexOf(String(lv));
      if (idx !== -1 && (minIdx === -1 || idx < minIdx)) {
        minIdx = idx;
      }
    });
    return minIdx === -1 ? '' : RISK_ORDER[minIdx];
  }

  /** 选中等级及以上 → 入库数组（与 Matcher in_array 语义一致） */
  function levelsFromThreshold(selected) {
    var idx = RISK_ORDER.indexOf(String(selected || ''));
    if (idx === -1) return [];
    return RISK_ORDER.slice(idx);
  }

  function updateCounts(enabled, total) {
    var elCount = document.getElementById('strPushEnabledCount');
    var elTotal = document.getElementById('strPushTotalCount');
    if (typeof enabled === 'number' && elCount) elCount.textContent = String(enabled);
    if (typeof total === 'number' && elTotal) elTotal.textContent = String(total);

    if (typeof enabled !== 'number') {
      var n = 0;
      Object.keys(state.draftPush).forEach(function (id) {
        if (state.draftPush[id]) n += 1;
      });
      if (elCount) elCount.textContent = String(n);
      if (elTotal) elTotal.textContent = String(Object.keys(state.draftPush).length);
    }
  }

  function recountDraft() {
    updateCounts();
  }

  function getFilters() {
    var form = document.getElementById('strPushFilterForm');
    var params = new URLSearchParams();
    if (!form) return params;

    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.disabled) return;
      if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) return;
      if (el.type === 'submit' || el.type === 'button') return;
      var val = String(el.value || '').trim();
      if (val !== '') params.set(el.name, val);
    });

    return params;
  }

  function syncUrl(params) {
    var qs = params.toString();
    var url = window.location.pathname + (qs ? '?' + qs : '');
    window.history.replaceState(null, '', url);
  }

  function setLoading(loading) {
    state.loading = loading;
    var tbody = document.getElementById('strPushTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function riskBadge(label) {
    if (!label) return '<span class="text-secondary">—</span>';
    var cls = RISK_LT[label] || 'bg-secondary-lt';
    return '<span class="badge ' + cls + '">' + escapeHtml(label) + '</span>';
  }

  function renderRows(items) {
    var tbody = document.getElementById('strPushTableBody');
    if (!tbody) return;

    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无规则</td></tr>';
      return;
    }

    tbody.innerHTML = items.map(function (row) {
      var ruleId = row.rule_id;
      var on = Object.prototype.hasOwnProperty.call(state.draftPush, ruleId)
        ? !!state.draftPush[ruleId]
        : !!row.push_str;
      state.draftPush[ruleId] = on;

      return ''
        + '<tr data-rule-id="' + escapeHtml(ruleId) + '">'
        +   '<td class="font-monospace">' + escapeHtml(ruleId) + '</td>'
        +   '<td>' + escapeHtml(row.name) + '</td>'
        +   '<td class="text-secondary">' + escapeHtml(row.category_label || row.category) + '</td>'
        +   '<td>' + riskBadge(row.risk_level_label) + '</td>'
        +   '<td>' + escapeHtml(row.measure_name || '—') + '</td>'
        +   '<td>'
        +     '<label class="form-check form-switch mb-0">'
        +       '<input class="form-check-input js-str-push-toggle" type="checkbox" data-rule-id="'
        +         escapeHtml(ruleId) + '"' + (on ? ' checked' : '') + '>'
        +     '</label>'
        +   '</td>'
        +   '<td class="text-secondary text-wrap" style="max-width:260px;" title="'
        +     escapeHtml(row.description || '') + '">'
        +     escapeHtml(row.description || '—')
        +   '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var footer = document.getElementById('strPushTableFooter');
    var summary = document.getElementById('strPushTableSummary');
    var pager = document.getElementById('strPushTablePager');
    var sizeWrap = document.getElementById('strPushPageSize');
    if (!footer || !summary || !pager) return;

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

  function mergeDraftFromItems(items) {
    (items || []).forEach(function (row) {
      var id = row.rule_id;
      if (!Object.prototype.hasOwnProperty.call(state.draftPush, id)) {
        state.draftPush[id] = !!row.push_str;
      }
    });
  }

  function fetchListPage(page, pageSize, filterParams) {
    var params = filterParams ? new URLSearchParams(filterParams.toString()) : new URLSearchParams();
    params.set('page', String(page));
    params.set('pageSize', String(pageSize));
    return fetch(LIST_URL + '?' + params.toString(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (res) { return res.json(); });
  }

  function seedDraftFromServer() {
    var empty = new URLSearchParams();
    return fetchListPage(1, 50, empty).then(function (json) {
      if (!json || json.code !== 0 || !json.data) {
        throw new Error((json && json.msg) || '加载规则失败');
      }
      mergeDraftFromItems(json.data.data || []);
      var last = json.data.last_page || 1;
      var chain = Promise.resolve();
      for (var p = 2; p <= last; p += 1) {
        (function (page) {
          chain = chain.then(function () {
            return fetchListPage(page, 50, empty).then(function (j2) {
              if (j2 && j2.code === 0 && j2.data) {
                mergeDraftFromItems(j2.data.data || []);
              }
            });
          });
        })(p);
      }
      return chain.then(function () {
        state.draftReady = true;
        recountDraft();
      });
    });
  }

  function loadList(page) {
    if (state.loading) return;
    state.page = Math.max(1, page || 1);
    setLoading(true);

    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));
    syncUrl(params);

    fetchListPage(state.page, state.pageSize, getFilters())
      .then(function (json) {
        state.loading = false;
        if (!json || json.code !== 0) {
          var tbody = document.getElementById('strPushTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">'
              + escapeHtml((json && json.msg) || '加载失败') + '</td></tr>';
          }
          return;
        }
        mergeDraftFromItems(json.data.data || []);
        renderRows(json.data.data || []);
        renderPager(json.data);
        recountDraft();
      })
      .catch(function () {
        state.loading = false;
        var tbody = document.getElementById('strPushTableBody');
        if (tbody) {
          tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">网络错误，请稍后重试</td></tr>';
        }
      });
  }

  function collectGlobalPayload() {
    var riskSel = document.getElementById('strPushRiskLevels');
    var levels = levelsFromThreshold(riskSel ? riskSel.value : '');

    var items = Object.keys(state.draftPush).map(function (ruleId) {
      return { rule_id: ruleId, push_str: !!state.draftPush[ruleId] };
    });

    return {
      enabled: document.getElementById('strPushEnabled').value === '1',
      push_by_risk_level: document.getElementById('strPushByRisk').value === '1',
      push_ltr: document.getElementById('strPushLtr').value === '1',
      risk_levels: levels,
      ltr_threshold_usd: parseInt(document.getElementById('strPushLtrUsd').value, 10) || 0,
      ltr_threshold_hkd: parseInt(document.getElementById('strPushLtrHkd').value, 10) || 0,
      items: items
    };
  }

  function saveConfig() {
    if (state.saving) return;
    if (!state.draftReady) {
      notifyError('规则尚未加载完成，请稍候再保存');
      return;
    }
    var payload = collectGlobalPayload();
    if (!payload.risk_levels.length) {
      notifyError('请选择触发 STR 的风险等级');
      return;
    }
    if (!payload.items.length) {
      notifyError('没有可保存的规则开关');
      return;
    }

    state.saving = true;
    fetch(SAVE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.saving = false;
        if (!json || json.code !== 0) {
          notifyError((json && json.msg) || '保存失败');
          return;
        }
        applyConfigToForm(json.data);
        notifySuccess(json.msg || '保存成功');
        loadList(state.page);
      })
      .catch(function () {
        state.saving = false;
        notifyError('网络错误，请稍后重试');
      });
  }

  function resetConfig() {
    var confirmFn = window.AdminUi && typeof AdminUi.confirm === 'function'
      ? AdminUi.confirm.bind(AdminUi)
      : function (opts) {
          return Promise.resolve(window.confirm(opts && opts.message ? opts.message : '确认恢复默认？'));
        };

    confirmFn({
      title: '恢复默认',
      message: '将恢复全局设置与全部规则的默认推送开关，是否继续？'
    }).then(function (ok) {
      if (!ok) return;
      state.saving = true;
      fetch(RESET_URL, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          state.saving = false;
          if (!json || json.code !== 0) {
            notifyError((json && json.msg) || '重置失败');
            return;
          }
          state.draftPush = {};
          state.draftReady = false;
          applyConfigToForm(json.data);
          notifySuccess(json.msg || '已恢复默认');
          return seedDraftFromServer().then(function () {
            loadList(1);
          });
        })
        .catch(function () {
          state.saving = false;
          notifyError('网络错误，请稍后重试');
        });
    });
  }

  function setAllDraft(enabled) {
    Object.keys(state.draftPush).forEach(function (id) {
      state.draftPush[id] = !!enabled;
    });
    recountDraft();
    loadList(state.page);
    notifySuccess(enabled ? '已全部启用 STR 推送（未保存）' : '已全部停用 STR 推送（未保存）');
  }

  function bindEvents() {
    var form = document.getElementById('strPushFilterForm');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        loadList(1);
      });
    }

    var resetFilter = document.getElementById('strPushFilterReset');
    if (resetFilter) {
      resetFilter.addEventListener('click', function () {
        if (form) form.reset();
        loadList(1);
      });
    }

    var saveBtn = document.getElementById('strPushSaveBtn');
    if (saveBtn) saveBtn.addEventListener('click', saveConfig);

    var resetBtn = document.getElementById('strPushResetBtn');
    if (resetBtn) resetBtn.addEventListener('click', resetConfig);

    var enableAll = document.getElementById('strPushEnableAllBtn');
    if (enableAll) enableAll.addEventListener('click', function () { setAllDraft(true); });

    var disableAll = document.getElementById('strPushDisableAllBtn');
    if (disableAll) disableAll.addEventListener('click', function () { setAllDraft(false); });

    var tbody = document.getElementById('strPushTableBody');
    if (tbody) {
      tbody.addEventListener('change', function (e) {
        var el = e.target;
        if (!el || !el.classList.contains('js-str-push-toggle')) return;
        var id = el.getAttribute('data-rule-id');
        if (!id) return;
        state.draftPush[id] = !!el.checked;
        recountDraft();
      });
    }

    var footer = document.getElementById('strPushTableFooter');
    if (footer) {
      footer.addEventListener('click', function (e) {
        var pageLink = e.target.closest('[data-page]');
        if (pageLink) {
          e.preventDefault();
          var p = parseInt(pageLink.getAttribute('data-page'), 10);
          if (!isNaN(p)) loadList(p);
          return;
        }
        var sizeLink = e.target.closest('[data-page-size]');
        if (sizeLink) {
          e.preventDefault();
          var size = ListPage.normalizePageSize(sizeLink.getAttribute('data-page-size'));
          state.pageSize = size;
          loadList(1);
        }
      });
    }
  }

  function applyQueryToFilters() {
    var params = new URLSearchParams(window.location.search);
    var form = document.getElementById('strPushFilterForm');
    if (!form) return;
    ['category', 'push_str', 'keyword'].forEach(function (name) {
      var el = form.elements.namedItem(name);
      if (el && params.has(name)) el.value = params.get(name);
    });
    if (params.has('page')) {
      state.page = Math.max(1, parseInt(params.get('page'), 10) || 1);
    }
    if (params.has('pageSize')) {
      state.pageSize = ListPage.normalizePageSize(params.get('pageSize'));
    }
  }

  function init() {
    applyConfigToForm(readBootConfig());
    applyQueryToFilters();
    bindEvents();
    seedDraftFromServer()
      .then(function () {
        loadList(state.page);
      })
      .catch(function (err) {
        notifyError((err && err.message) || '加载规则失败');
        loadList(state.page);
      });
  }

  return { init: init };
})();
