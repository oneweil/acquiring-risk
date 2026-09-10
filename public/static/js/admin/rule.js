/**
 * 风控规则配置（分组卡片 + 批量保存 / 重置）
 */
var RulePage = (function () {
  'use strict';

  var LIST_URL = '/admin/rule/list';
  var SAVE_URL = '/admin/rule/save';
  var RESET_URL = '/admin/rule/reset';

  var state = {
    loading: false,
    saving: false,
    groups: [],
    measures: []
  };

  var RISK_LT = {
    low: 'bg-green-lt',
    mid: 'bg-yellow-lt',
    high: 'bg-red-lt',
    critical: 'bg-purple-lt'
  };

  /** 分组卡片 header 图标（对齐原型 Font Awesome → Tabler） */
  var CATEGORY_ICON = {
    card_velocity: 'ti-credit-card',
    ip_velocity: 'ti-network',
    time_pattern: 'ti-clock',
    avs_cvv_3ds: 'ti-lock',
    amount: 'ti-currency-dollar',
    geo_sanctions: 'ti-world',
    fraud: 'ti-spy',
    merchant: 'ti-building-store',
    blacklist: 'ti-ban'
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
  }

  function measureMap() {
    var map = {};
    (state.measures || []).forEach(function (m) {
      map[m.code] = m;
    });
    return map;
  }

  function riskBadgeHtml(level, label) {
    if (!level) {
      return '<span class="text-secondary">—</span>';
    }
    var cls = RISK_LT[level] || 'bg-secondary-lt';
    return '<span class="badge ' + cls + '">' + escapeHtml(label || level) + '</span>';
  }

  function renderTemplate(template, config, ruleId) {
    var html = '';
    var re = /\{([a-zA-Z0-9_]+)\}/g;
    var last = 0;
    var m;
    var text = String(template || '');
    while ((m = re.exec(text)) !== null) {
      html += escapeHtml(text.slice(last, m.index));
      var key = m[1];
      var val = config && config[key] !== undefined && config[key] !== null ? String(config[key]) : '';
      var width = val.length > 6 ? '5.5rem' : '4rem';
      html += '<input type="text" class="form-control form-control-sm d-inline-block mx-1 js-rule-config" '
        + 'data-rule-id="' + escapeHtml(ruleId) + '" data-key="' + escapeHtml(key) + '" '
        + 'value="' + escapeHtml(val) + '" style="width:' + width + ';vertical-align:baseline;">';
      last = m.index + m[0].length;
    }
    html += escapeHtml(text.slice(last));
    return html || escapeHtml(text);
  }

  function measureOptionsHtml(selected) {
    return (state.measures || []).map(function (m) {
      var sel = m.code === selected ? ' selected' : '';
      return '<option value="' + escapeHtml(m.code) + '"' + sel + '>' + escapeHtml(m.name) + '</option>';
    }).join('');
  }

  function renderGroups(payload) {
    state.groups = payload.groups || [];
    state.measures = payload.measures || [];

    var wrap = document.getElementById('ruleGroups');
    if (!wrap) return;

    if (!state.groups.length) {
      wrap.innerHTML = '<div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5">暂无规则数据</div></div></div>';
      return;
    }

    var map = measureMap();
    wrap.innerHTML = state.groups.map(function (group) {
      var rows = (group.rules || []).map(function (rule) {
        var measure = map[rule.measure_code] || {};
        var riskLevel = measure.risk_level || rule.risk_level || '';
        var riskLabel = measure.risk_level_label || rule.risk_level_label || '';
        var enabled = !!rule.enabled;

        return ''
          + '<div class="list-group-item js-rule-row" data-rule-id="' + escapeHtml(rule.rule_id) + '">'
          +   '<div class="row g-2 align-items-center">'
          +     '<div class="col-12 col-lg">'
          +       '<div class="fw-medium">'
          +         '<span class="text-secondary font-monospace me-2">' + escapeHtml(rule.rule_id) + '</span>'
          +         renderTemplate(rule.content_template, rule.config || {}, rule.rule_id)
          +       '</div>'
          +       (rule.description
            ? '<div class="text-secondary small mt-1">' + escapeHtml(rule.description) + '</div>'
            : '')
          +     '</div>'
          +     '<div class="col-6 col-md-3 col-lg-2">'
          +       '<select class="form-select form-select-sm js-rule-measure" data-rule-id="' + escapeHtml(rule.rule_id) + '">'
          +         measureOptionsHtml(rule.measure_code)
          +       '</select>'
          +     '</div>'
          +     '<div class="col-3 col-md-2 col-lg-1 text-center js-rule-risk" data-rule-id="' + escapeHtml(rule.rule_id) + '">'
          +       riskBadgeHtml(riskLevel, riskLabel)
          +     '</div>'
          +     '<div class="col-3 col-md-2 col-lg-1 text-center">'
          +       '<label class="form-check form-switch m-0 d-inline-flex justify-content-center">'
          +         '<input class="form-check-input js-rule-enabled" type="checkbox" data-rule-id="' + escapeHtml(rule.rule_id) + '"'
          +           (enabled ? ' checked' : '') + '>'
          +       '</label>'
          +     '</div>'
          +   '</div>'
          + '</div>';
      }).join('');

      var icon = CATEGORY_ICON[group.category] || 'ti-adjustments';

      return ''
        + '<div class="col-12">'
        +   '<div class="card">'
        +     '<div class="card-header rule-card-head">'
        +       '<h3 class="card-title">'
        +         '<i class="ti ' + icon + ' me-2 text-primary"></i>'
        +         escapeHtml(group.category_label || group.category)
        +       '</h3>'
        +     '</div>'
        +     '<div class="card-header py-2 d-none d-lg-block">'
        +       '<div class="row g-2 text-secondary small">'
        +         '<div class="col-lg">规则内容</div>'
        +         '<div class="col-lg-2">处置策略</div>'
        +         '<div class="col-lg-1 text-center">风险等级</div>'
        +         '<div class="col-lg-1 text-center">启用</div>'
        +       '</div>'
        +     '</div>'
        +     '<div class="list-group list-group-flush">' + rows + '</div>'
        +   '</div>'
        + '</div>';
    }).join('');
  }

  function setLoading(loading) {
    state.loading = loading;
    var wrap = document.getElementById('ruleGroups');
    if (!wrap || !loading) return;
    wrap.innerHTML = ''
      + '<div class="col-12"><div class="card"><div class="card-body text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</div></div></div>';
  }

  function loadList() {
    if (state.loading) return;
    setLoading(true);

    fetch(LIST_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.loading = false;
        if (json.code !== 0 || !json.data) {
          var wrap = document.getElementById('ruleGroups');
          if (wrap) {
            wrap.innerHTML = '<div class="col-12"><div class="card"><div class="card-body text-center text-danger py-5">'
              + escapeHtml(json.msg || '加载失败') + '</div></div></div>';
          }
          return;
        }
        renderGroups(json.data);
      })
      .catch(function () {
        state.loading = false;
        var wrap = document.getElementById('ruleGroups');
        if (wrap) {
          wrap.innerHTML = '<div class="col-12"><div class="card"><div class="card-body text-center text-danger py-5">网络错误，请稍后重试</div></div></div>';
        }
      });
  }

  function collectItems() {
    var items = [];
    document.querySelectorAll('.js-rule-row').forEach(function (row) {
      var ruleId = row.getAttribute('data-rule-id');
      if (!ruleId) return;

      var config = {};
      row.querySelectorAll('.js-rule-config').forEach(function (input) {
        var key = input.getAttribute('data-key');
        if (!key) return;
        var raw = String(input.value || '').trim();
        if (raw !== '' && /^-?\d+(\.\d+)?$/.test(raw)) {
          config[key] = raw.indexOf('.') >= 0 ? parseFloat(raw) : parseInt(raw, 10);
        } else {
          config[key] = raw;
        }
      });

      var measureEl = row.querySelector('.js-rule-measure');
      var enabledEl = row.querySelector('.js-rule-enabled');

      items.push({
        rule_id: ruleId,
        config: config,
        measure_code: measureEl ? measureEl.value : '',
        enabled: !!(enabledEl && enabledEl.checked)
      });
    });
    return items;
  }

  function updateRiskForRow(ruleId, measureCode) {
    var cell = document.querySelector('.js-rule-risk[data-rule-id="' + ruleId + '"]');
    if (!cell) return;
    var measure = measureMap()[measureCode];
    if (!measure) {
      cell.innerHTML = '<span class="text-secondary">—</span>';
      return;
    }
    cell.innerHTML = riskBadgeHtml(measure.risk_level, measure.risk_level_label);
  }

  function setBusy(busy, label) {
    state.saving = busy;
    var saveBtn = document.getElementById('ruleSaveBtn');
    var resetBtn = document.getElementById('ruleResetBtn');
    if (saveBtn) {
      saveBtn.disabled = busy;
      saveBtn.textContent = busy && label === 'save' ? '保存中…' : '保存配置';
    }
    if (resetBtn) {
      resetBtn.disabled = busy;
      resetBtn.textContent = busy && label === 'reset' ? '重置中…' : '重置默认';
    }
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

  function saveConfig() {
    if (state.saving) return;
    var items = collectItems();
    if (!items.length) {
      notifyError('没有可保存的规则');
      return;
    }

    setBusy(true, 'save');
    fetch(SAVE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ items: items })
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        setBusy(false);
        if (json.code !== 0) {
          notifyError(json.msg || '保存失败');
          return;
        }
        if (json.data) renderGroups(json.data);
        notifySuccess(json.msg || '保存成功');
      })
      .catch(function () {
        setBusy(false);
        notifyError('网络错误，请稍后重试');
      });
  }

  function doReset() {
    setBusy(true, 'reset');
    fetch(RESET_URL, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        setBusy(false);
        if (json.code !== 0) {
          notifyError(json.msg || '重置失败');
          return;
        }
        if (json.data) renderGroups(json.data);
        notifySuccess(json.msg || '已恢复默认规则');
      })
      .catch(function () {
        setBusy(false);
        notifyError('网络错误，请稍后重试');
      });
  }

  function resetConfig() {
    if (state.saving) return;

    var confirmFn = window.AdminUi && typeof AdminUi.confirm === 'function'
      ? AdminUi.confirm.bind(AdminUi)
      : function (opts) {
          return Promise.resolve(window.confirm(opts.message || opts.title || '请确认'));
        };

    confirmFn({
      type: 'danger',
      title: '确定恢复默认规则？',
      message: '将覆盖当前全部规则配置，未保存的修改将丢失。此操作不可撤销。',
      confirmText: '确认重置',
      cancelText: '取消'
    }).then(function (ok) {
      if (ok) doReset();
    });
  }

  function bindEvents() {
    var saveBtn = document.getElementById('ruleSaveBtn');
    if (saveBtn) {
      saveBtn.addEventListener('click', function (e) {
        e.preventDefault();
        saveConfig();
      });
    }

    var resetBtn = document.getElementById('ruleResetBtn');
    if (resetBtn) {
      resetBtn.addEventListener('click', function (e) {
        e.preventDefault();
        resetConfig();
      });
    }

    var wrap = document.getElementById('ruleGroups');
    if (wrap) {
      wrap.addEventListener('change', function (e) {
        var sel = e.target.closest('.js-rule-measure');
        if (!sel) return;
        updateRiskForRow(sel.getAttribute('data-rule-id'), sel.value);
      });
    }
  }

  function init() {
    bindEvents();
    loadList();
  }

  return { init: init, loadList: loadList };
})();
