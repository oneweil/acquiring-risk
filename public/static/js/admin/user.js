/**
 * 用户管理（AJAX 列表 + 新增/编辑 / 分配角色 / 重置密码 / 状态）
 */
var UserPage = (function () {
  'use strict';

  var LIST_URL = '/admin/user/list';
  var SAVE_URL = '/admin/user/save';
  var ROLES_URL = '/admin/user/roles';
  var ROLE_OPTS_URL = '/admin/user/role_options';
  var RESET_PWD_URL = '/admin/user/reset_password';
  var TOGGLE_URL = '/admin/user/toggle_status';

  var state = {
    page: 1,
    pageSize: 10,
    loading: false,
    saving: false,
    modal: null,
    roleModal: null,
    pwdModal: null,
    roleOptions: []
  };

  var COLSPAN = 9;

  var STATUS_LT = {
    '启用': 'bg-green-lt',
    '停用': 'bg-secondary-lt',
    '锁定': 'bg-yellow-lt'
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
  }

  function getFilters() {
    var form = document.getElementById('userFilterForm');
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

  function setLoading(loading) {
    state.loading = loading;
    var tbody = document.getElementById('userTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function renderKpi(stats) {
    if (!stats) return;
    var map = {
      userKpiTotal: stats.total,
      userKpiEnabled: stats.enabled,
      userKpiBound: stats.with_role,
      userKpiInactive: stats.inactive
    };
    Object.keys(map).forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.textContent = map[id] != null ? String(map[id]) : '—';
    });
  }

  function renderRoles(roles) {
    if (!roles || !roles.length) return '<span class="text-secondary">—</span>';
    return roles.map(function (r) {
      return '<span class="badge bg-blue-lt me-1">' + escapeHtml(r.name) + '</span>';
    }).join('');
  }

  function renderRows(items) {
    var tbody = document.getElementById('userTableBody');
    if (!tbody) return;
    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无用户数据</td></tr>';
      return;
    }
    tbody.innerHTML = items.map(function (row) {
      var statusCls = STATUS_LT[row.status_label] || 'bg-secondary-lt';
      var payload = encodeURIComponent(JSON.stringify(row));
      var ops = ''
        + '<div class="btn-list flex-nowrap">'
        +   '<button type="button" class="btn btn-ghost-primary btn-sm js-user-edit" data-row="' + payload + '">编辑</button>'
        +   '<button type="button" class="btn btn-ghost-primary btn-sm js-user-roles" data-row="' + payload + '">角色</button>'
        +   '<button type="button" class="btn btn-ghost-primary btn-sm js-user-pwd" data-row="' + payload + '">密码</button>';
      if (row.status === 'enabled') {
        ops += '<button type="button" class="btn btn-ghost-secondary btn-sm js-user-status" data-id="' + row.id + '" data-status="disabled">停用</button>';
      } else {
        ops += '<button type="button" class="btn btn-ghost-success btn-sm js-user-status" data-id="' + row.id + '" data-status="enabled">启用</button>';
      }
      if (row.status === 'locked') {
        ops += '<button type="button" class="btn btn-ghost-success btn-sm js-user-status" data-id="' + row.id + '" data-status="enabled">解锁</button>';
      }
      ops += '</div>';

      return ''
        + '<tr>'
        +   '<td class="font-monospace">' + escapeHtml(row.account) + '</td>'
        +   '<td>' + escapeHtml(row.name) + '</td>'
        +   '<td class="text-secondary">' + escapeHtml(row.title || '—') + '</td>'
        +   '<td class="text-secondary">' + escapeHtml(row.phone || '—') + '</td>'
        +   '<td>' + renderRoles(row.roles) + '</td>'
        +   '<td><span class="badge ' + statusCls + '">' + escapeHtml(row.status_label) + '</span></td>'
        +   '<td class="text-secondary">' + escapeHtml(row.last_login_at || '—') + '</td>'
        +   '<td class="text-secondary">' + escapeHtml(row.updated_at || '—') + '</td>'
        +   '<td>' + ops + '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var footer = document.getElementById('userTableFooter');
    var summary = document.getElementById('userTableSummary');
    var pager = document.getElementById('userTablePager');
    var sizeWrap = document.getElementById('userPageSize');
    if (!footer || !summary || !pager) return;

    var total = data.total;
    var page = data.current_page;
    var lastPage = data.last_page;
    var perPage = data.per_page || state.pageSize;
    state.pageSize = ListPage.normalizePageSize(perPage);
    if (sizeWrap) sizeWrap.innerHTML = ListPage.renderPageSizeDropdown(state.pageSize);

    if (total <= 0) {
      summary.textContent = '暂无数据';
      pager.innerHTML = '';
      return;
    }
    summary.textContent = '共 ' + total + ' 条记录';
    pager.innerHTML = ListPage.renderPaginationHtml(page, lastPage);
  }

  function loadList(page) {
    if (state.loading) return;
    state.page = Math.max(1, page || 1);
    setLoading(true);

    var params = getFilters();
    params.set('page', String(state.page));
    params.set('pageSize', String(state.pageSize));

    var qs = params.toString();
    var nextUrl = qs ? (window.location.pathname + '?' + qs) : window.location.pathname;
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, '', nextUrl);
    }

    fetch(LIST_URL + '?' + qs, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        state.loading = false;
        if (json.code !== 0 || !json.data) {
          var tbody = document.getElementById('userTableBody');
          if (tbody) {
            tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">'
              + escapeHtml(json.msg || '加载失败') + '</td></tr>';
          }
          return;
        }
        renderKpi(json.data.stats);
        var data = json.data.list || {};
        state.page = data.current_page || 1;
        renderRows(data.data || []);
        renderPager(data);
      })
      .catch(function () {
        state.loading = false;
        var tbody = document.getElementById('userTableBody');
        if (tbody) {
          tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">网络错误，请稍后重试</td></tr>';
        }
      });
  }

  function resetForm() {
    var form = document.getElementById('userForm');
    if (form) form.reset();
    var idEl = document.getElementById('userId');
    if (idEl) idEl.value = '';
    var status = document.getElementById('userStatus');
    if (status) status.value = 'enabled';
    var pwd = document.getElementById('userPassword');
    if (pwd) {
      pwd.value = '';
      pwd.required = true;
    }
    var account = document.getElementById('userAccount');
    if (account) account.readOnly = false;
  }

  function openAddModal() {
    resetForm();
    var title = document.getElementById('userModalLabel');
    if (title) title.textContent = '新增用户';
    if (state.modal) state.modal.show();
  }

  function openEditModal(row) {
    resetForm();
    var title = document.getElementById('userModalLabel');
    if (title) title.textContent = '编辑用户';
    document.getElementById('userId').value = row.id || '';
    document.getElementById('userAccount').value = row.account || '';
    document.getElementById('userAccount').readOnly = true;
    document.getElementById('userName').value = row.name || '';
    document.getElementById('userTitle').value = row.title || '';
    document.getElementById('userPhone').value = row.phone || '';
    document.getElementById('userEmail').value = row.email || '';
    document.getElementById('userStatus').value = row.status || 'enabled';
    document.getElementById('userRemark').value = row.remark || '';
    var pwd = document.getElementById('userPassword');
    if (pwd) pwd.required = false;
    if (state.modal) state.modal.show();
  }

  function postForm(url, data) {
    var fd = new FormData();
    Object.keys(data).forEach(function (k) {
      var v = data[k];
      if (Array.isArray(v)) {
        v.forEach(function (item) { fd.append(k + '[]', item); });
      } else if (v !== undefined && v !== null) {
        fd.append(k, v);
      }
    });
    return fetch(url, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: fd
    }).then(function (res) { return res.json(); });
  }

  function saveForm(e) {
    e.preventDefault();
    if (state.saving) return;
    var form = document.getElementById('userForm');
    if (!form) return;
    if (typeof form.reportValidity === 'function' && !form.reportValidity()) return;

    var id = parseInt(document.getElementById('userId').value || '0', 10) || 0;
    var password = document.getElementById('userPassword').value;
    if (id <= 0 && !password) {
      alert('请填写密码');
      return;
    }

    var payload = {
      id: id,
      account: document.getElementById('userAccount').value.trim(),
      name: document.getElementById('userName').value.trim(),
      title: document.getElementById('userTitle').value.trim(),
      phone: document.getElementById('userPhone').value.trim(),
      email: document.getElementById('userEmail').value.trim(),
      status: document.getElementById('userStatus').value,
      remark: document.getElementById('userRemark').value.trim()
    };
    if (password) payload.password = password;

    state.saving = true;
    postForm(SAVE_URL, payload)
      .then(function (json) {
        state.saving = false;
        if (json.code !== 0) {
          alert(json.msg || '保存失败');
          return;
        }
        if (state.modal) state.modal.hide();
        loadList(id > 0 ? state.page : 1);
      })
      .catch(function () {
        state.saving = false;
        alert('网络错误，请稍后重试');
      });
  }

  function loadRoleOptions() {
    return fetch(ROLE_OPTS_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (json.code === 0 && Array.isArray(json.data)) {
          state.roleOptions = json.data;
        }
      });
  }

  function openRoleModal(row) {
    document.getElementById('userRoleUserId').value = row.id;
    document.getElementById('userRoleModalSub').textContent = row.name + '（' + row.account + '）';
    var selected = {};
    (row.role_ids || []).forEach(function (id) { selected[id] = true; });

    var list = document.getElementById('userRoleList');
    list.innerHTML = state.roleOptions.map(function (r) {
      var checked = selected[r.id] ? ' checked' : '';
      return ''
        + '<label class="list-group-item">'
        +   '<input class="form-check-input me-2 js-user-role-cb" type="checkbox" value="' + r.id + '"' + checked + '>'
        +   '<span class="fw-bold">' + escapeHtml(r.name) + '</span>'
        +   '<span class="text-secondary ms-2 font-monospace">' + escapeHtml(r.code) + '</span>'
        + '</label>';
    }).join('') || '<div class="text-secondary p-3">暂无启用角色</div>';

    if (state.roleModal) state.roleModal.show();
  }

  function saveRoles() {
    var userId = parseInt(document.getElementById('userRoleUserId').value || '0', 10);
    var roleIds = [];
    Array.prototype.forEach.call(document.querySelectorAll('.js-user-role-cb:checked'), function (el) {
      roleIds.push(parseInt(el.value, 10));
    });
    postForm(ROLES_URL, { id: userId, role_ids: roleIds })
      .then(function (json) {
        if (json.code !== 0) {
          alert(json.msg || '保存失败');
          return;
        }
        if (state.roleModal) state.roleModal.hide();
        loadList(state.page);
      })
      .catch(function () { alert('网络错误，请稍后重试'); });
  }

  function openPwdModal(row) {
    document.getElementById('userPwdUserId').value = row.id;
    document.getElementById('userPwdModalSub').textContent = row.name + '（' + row.account + '）';
    document.getElementById('userNewPassword').value = '';
    if (state.pwdModal) state.pwdModal.show();
  }

  function savePwd(e) {
    e.preventDefault();
    var id = parseInt(document.getElementById('userPwdUserId').value || '0', 10);
    var pwd = document.getElementById('userNewPassword').value;
    if (!pwd || pwd.length < 6) {
      alert('新密码至少 6 位');
      return;
    }
    postForm(RESET_PWD_URL, { id: id, new_password: pwd })
      .then(function (json) {
        if (json.code !== 0) {
          alert(json.msg || '重置失败');
          return;
        }
        if (state.pwdModal) state.pwdModal.hide();
        alert('密码已重置');
      })
      .catch(function () { alert('网络错误，请稍后重试'); });
  }

  function toggleStatus(id, status) {
    if (!confirm(status === 'enabled' ? '确认启用该用户？' : '确认停用该用户？')) return;
    postForm(TOGGLE_URL, { id: id, status: status })
      .then(function (json) {
        if (json.code !== 0) {
          alert(json.msg || '操作失败');
          return;
        }
        loadList(state.page);
      })
      .catch(function () { alert('网络错误，请稍后重试'); });
  }

  function bindEvents() {
    var filterForm = document.getElementById('userFilterForm');
    if (filterForm) {
      filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loadList(1);
      });
    }
    var resetBtn = document.getElementById('userFilterReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        if (filterForm) filterForm.reset();
        loadList(1);
      });
    }
    var addBtn = document.getElementById('userAddBtn');
    if (addBtn) addBtn.addEventListener('click', openAddModal);

    var form = document.getElementById('userForm');
    if (form) form.addEventListener('submit', saveForm);

    var pwdForm = document.getElementById('userPwdForm');
    if (pwdForm) pwdForm.addEventListener('submit', savePwd);

    var roleSave = document.getElementById('userRoleSaveBtn');
    if (roleSave) roleSave.addEventListener('click', saveRoles);

    document.addEventListener('click', function (e) {
      var t = e.target;
      if (!(t instanceof Element)) return;
      var editBtn = t.closest('.js-user-edit');
      if (editBtn) {
        try { openEditModal(JSON.parse(decodeURIComponent(editBtn.getAttribute('data-row')))); } catch (err) {}
        return;
      }
      var roleBtn = t.closest('.js-user-roles');
      if (roleBtn) {
        try { openRoleModal(JSON.parse(decodeURIComponent(roleBtn.getAttribute('data-row')))); } catch (err) {}
        return;
      }
      var pwdBtn = t.closest('.js-user-pwd');
      if (pwdBtn) {
        try { openPwdModal(JSON.parse(decodeURIComponent(pwdBtn.getAttribute('data-row')))); } catch (err) {}
        return;
      }
      var stBtn = t.closest('.js-user-status');
      if (stBtn) {
        toggleStatus(parseInt(stBtn.getAttribute('data-id'), 10), stBtn.getAttribute('data-status'));
        return;
      }
      var pageBtn = t.closest('#userTablePager [data-page]');
      if (pageBtn) {
        e.preventDefault();
        loadList(parseInt(pageBtn.getAttribute('data-page'), 10));
        return;
      }
      var sizeBtn = t.closest('#userPageSize [data-page-size]');
      if (sizeBtn) {
        e.preventDefault();
        state.pageSize = ListPage.normalizePageSize(parseInt(sizeBtn.getAttribute('data-page-size'), 10));
        loadList(1);
      }
    });
  }

  function applyQueryToFilters() {
    var params = new URLSearchParams(window.location.search);
    var form = document.getElementById('userFilterForm');
    if (!form) return;
    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name) return;
      if (params.has(el.name)) el.value = params.get(el.name);
    });
    if (params.has('page')) state.page = Math.max(1, parseInt(params.get('page'), 10) || 1);
    if (params.has('pageSize')) state.pageSize = ListPage.normalizePageSize(parseInt(params.get('pageSize'), 10));
  }

  function init() {
    var modalEl = document.getElementById('userModal');
    if (modalEl && window.bootstrap) state.modal = new bootstrap.Modal(modalEl);
    var roleEl = document.getElementById('userRoleModal');
    if (roleEl && window.bootstrap) state.roleModal = new bootstrap.Modal(roleEl);
    var pwdEl = document.getElementById('userPwdModal');
    if (pwdEl && window.bootstrap) state.pwdModal = new bootstrap.Modal(pwdEl);

    bindEvents();
    applyQueryToFilters();
    loadRoleOptions().finally(function () { loadList(state.page); });
  }

  return { init: init };
})();
