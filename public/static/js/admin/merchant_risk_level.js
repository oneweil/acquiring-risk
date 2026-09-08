/**
 * 商户风险等级规则配置页（首屏由服务端 renderList 注入，仅 save/reassess 走 AJAX）
 */
var MerchantRiskLevelPage = (function () {
  'use strict';

  var SAVE_URL = '/admin/merchant_risk_level/save';
  var REASSESS_URL = '/admin/merchant_risk_level/reassess';

  var LEVELS = ['low', 'mid', 'high'];
  var state = { saving: false };

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

  function parseIntSafe(value, fallback) {
    var n = parseInt(value, 10);
    return isNaN(n) ? fallback : n;
  }

  function updateRanges() {
    var lowEl = document.getElementById('mrlLowMax');
    var midEl = document.getElementById('mrlMidMax');
    if (!lowEl || !midEl) return;

    var low = parseIntSafe(lowEl.value, 40);
    var mid = parseIntSafe(midEl.value, 70);

    var setText = function (id, text) {
      var el = document.getElementById(id);
      if (el) el.textContent = String(text);
    };

    setText('mrlLowRange', low);
    setText('mrlMidRuleLo', low);
    setText('mrlMidRangeLo', low + 1);
    setText('mrlMidRangeHi', mid);
    setText('mrlHighRuleLo', mid);
    setText('mrlHighRange', mid);
  }

  function setField(level, field, value) {
    var el = document.querySelector('.js-mrl-field[data-level="' + level + '"][data-field="' + field + '"]');
    if (el) el.value = value;
  }

  function applyConfig(data) {
    if (!data) return;

    var lowEl = document.getElementById('mrlLowMax');
    var midEl = document.getElementById('mrlMidMax');
    if (lowEl) lowEl.value = data.low_max;
    if (midEl) midEl.value = data.mid_max;
    updateRanges();

    var policies = data.policies || {};
    LEVELS.forEach(function (level) {
      var row = policies[level];
      if (!row) return;
      setField(level, 'settle_days', row.settle_days);
      setField(level, 'margin_rate', row.margin_rate);
      setField(level, 'single_limit', row.single_limit);
      setField(level, 'daily_limit', row.daily_limit);
      setField(level, 'review_cycle', row.review_cycle);

      var adviceEl = document.querySelector('.js-mrl-advice[data-level="' + level + '"]');
      if (adviceEl) adviceEl.textContent = row.advice || '—';
    });
  }

  function collectPayload() {
    var policies = {};
    LEVELS.forEach(function (level) {
      policies[level] = {
        settle_days: parseIntSafe(
          document.querySelector('.js-mrl-field[data-level="' + level + '"][data-field="settle_days"]').value,
          0
        ),
        margin_rate: parseIntSafe(
          document.querySelector('.js-mrl-field[data-level="' + level + '"][data-field="margin_rate"]').value,
          0
        ),
        single_limit: parseIntSafe(
          document.querySelector('.js-mrl-field[data-level="' + level + '"][data-field="single_limit"]').value,
          0
        ),
        daily_limit: parseIntSafe(
          document.querySelector('.js-mrl-field[data-level="' + level + '"][data-field="daily_limit"]').value,
          0
        ),
        review_cycle: document.querySelector(
          '.js-mrl-field[data-level="' + level + '"][data-field="review_cycle"]'
        ).value
      };
    });

    return {
      low_max: parseIntSafe(document.getElementById('mrlLowMax').value, 0),
      mid_max: parseIntSafe(document.getElementById('mrlMidMax').value, 0),
      policies: policies
    };
  }

  function setBusy(busy) {
    state.saving = busy;
    var saveBtn = document.getElementById('mrlSaveBtn');
    var reassessBtn = document.getElementById('mrlReassessBtn');
    if (saveBtn) {
      saveBtn.disabled = busy;
      saveBtn.textContent = busy ? '保存中…' : '保存等级规则';
    }
    if (reassessBtn) {
      reassessBtn.disabled = busy;
    }
  }

  function saveConfig() {
    if (state.saving) return;
    var payload = collectPayload();

    setBusy(true);
    fetch(SAVE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        setBusy(false);
        if (json.code !== 0) {
          notifyError(json.msg || '保存失败');
          return;
        }
        if (json.data) applyConfig(json.data);
        notifySuccess(json.msg || '保存成功');
      })
      .catch(function () {
        setBusy(false);
        notifyError('网络错误，请稍后重试');
      });
  }

  function reassessAll() {
    if (state.saving) return;

    var confirmFn = window.AdminUi && typeof AdminUi.confirm === 'function'
      ? AdminUi.confirm.bind(AdminUi)
      : function (opts) {
          return Promise.resolve(window.confirm(opts.message || opts.title || '请确认'));
        };

    confirmFn({
      type: 'warning',
      title: '重新评估全部商户？',
      message: '将按当前已保存的等级规则重新计算全部商户风险等级。若尚未保存修改，请先保存。',
      confirmText: '确认重评',
      cancelText: '取消'
    }).then(function (ok) {
      if (!ok) return;

      setBusy(true);
      fetch(REASSESS_URL, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          setBusy(false);
          if (json.code !== 0) {
            notifyError(json.msg || '评估引擎未就绪');
            return;
          }
          notifySuccess(json.msg || '已触发重新评估');
        })
        .catch(function () {
          setBusy(false);
          notifyError('网络错误，请稍后重试');
        });
    });
  }

  function bindEvents() {
    var lowEl = document.getElementById('mrlLowMax');
    var midEl = document.getElementById('mrlMidMax');
    if (lowEl) lowEl.addEventListener('input', updateRanges);
    if (midEl) midEl.addEventListener('input', updateRanges);

    var saveBtn = document.getElementById('mrlSaveBtn');
    if (saveBtn) {
      saveBtn.addEventListener('click', function (e) {
        e.preventDefault();
        saveConfig();
      });
    }

    var reassessBtn = document.getElementById('mrlReassessBtn');
    if (reassessBtn) {
      reassessBtn.addEventListener('click', function (e) {
        e.preventDefault();
        reassessAll();
      });
    }
  }

  function init() {
    bindEvents();
    updateRanges();
  }

  return { init: init };
})();
