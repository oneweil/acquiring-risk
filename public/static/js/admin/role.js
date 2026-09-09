/**
 * 角色权限（AJAX 列表 + 保存 / 权限树 / 分配用户 / 删除）
 */
var RolePage = (function () {
  'use strict';

  var LIST_URL = '/admin/role/list';
  var SAVE_URL = '/admin/role/save';
  var PERMS_URL = '/admin/role/permissions';
  var USERS_URL = '/admin/role/users';
  var USERS_ASSIGN_URL = '/admin/role/users_assign';
  var CATALOG_URL = '/admin/role/permission_catalog';
  var DELETE_URL = '/admin/role/delete';

  var state = {
    page: 1,
    pageSize: 10,
    loading: false,
    saving: false,
    modal: null,
    permModal: null,
    userModal: null,
    catalog: [],
    assignUsers: [],
    selectedUserIds: {}
  };

  var COLSPAN = 8;

  var STATUS_LT = {
    '启用': 'bg-green-lt',
    '停用': 'bg-secondary-lt'
  };

  function escapeHtml(str) {
    return ListPage.escapeHtml(str);
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

  function getFilters() {
    var form = document.getElementById('roleFilterForm');
    var params = new URLSearchParams();
    if (!form) return params;
    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name || el.disabled) return;
      if (el.type === 'submit' || el.type === 'button') return;
      var val = String(el.value || '').trim();
      if (val !== '') params.set(el.name, val);
    });
    return params;
  }

  function setLoading(loading) {
    state.loading = loading;
    var tbody = document.getElementById('roleTableBody');
    if (!tbody || !loading) return;
    tbody.innerHTML =
      '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">'
      + '<div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>加载中…'
      + '</td></tr>';
  }

  function renderKpi(stats) {
    if (!stats) return;
    var map = {
      roleKpiTotal: stats.total,
      roleKpiEnabled: stats.enabled,
      roleKpiUsers: stats.users,
      roleKpiPerms: stats.perms
    };
    Object.keys(map).forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.textContent = map[id] != null ? String(map[id]) : '—';
    });
  }

  function renderRows(items) {
    var tbody = document.getElementById('roleTableBody');
    if (!tbody) return;
    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-secondary py-5">暂无角色数据</td></tr>';
      return;
    }
    tbody.innerHTML = items.map(function (row) {
      var statusCls = STATUS_LT[row.status_label] || 'bg-secondary-lt';
      var payload = encodeURIComponent(JSON.stringify(row));
      var typeBadge = row.is_builtin
        ? '<span class="badge bg-azure-lt">内置</span>'
        : '<span class="badge bg-secondary-lt">自定义</span>';
      var ops = ''
        + '<div class="btn-list flex-nowrap">'
        +   '<button type="button" class="btn btn-ghost-primary btn-sm js-role-edit" data-row="' + payload + '">编辑</button>'
        +   '<button type="button" class="btn btn-ghost-primary btn-sm js-role-perms" data-row="' + payload + '">权限</button>'
        +   '<button type="button" class="btn btn-ghost-primary btn-sm js-role-users" data-row="' + payload + '">用户</button>';
      if (!row.is_builtin) {
        ops += '<button type="button" class="btn btn-ghost-danger btn-sm js-role-delete" data-id="' + row.id + '">删除</button>';
      }
      ops += '</div>';

      return ''
        + '<tr>'
        +   '<td class="font-monospace">' + escapeHtml(row.code) + '</td>'
        +   '<td>' + escapeHtml(row.name) + '</td>'
        +   '<td>' + typeBadge + '</td>'
        +   '<td><span class="badge ' + statusCls + '">' + escapeHtml(row.status_label) + '</span></td>'
        +   '<td>' + (row.user_count || 0) + '</td>'
        +   '<td>' + (row.perm_count || 0) + '</td>'
        +   '<td class="text-secondary">' + escapeHtml(row.updated_at || '—') + '</td>'
        +   '<td>' + ops + '</td>'
        + '</tr>';
    }).join('');
  }

  function renderPager(data) {
    var footer = document.getElementById('roleTableFooter');
    var summary = document.getElementById('roleTableSummary');
    var pager = document.getElementById('roleTablePager');
    var sizeWrap = document.getElementById('rolePageSize');
    if (!footer || !summary || !pager) return;

    var total = data.total;
    state.pageSize = ListPage.normalizePageSize(data.per_page || state.pageSize);
    if (sizeWrap) sizeWrap.innerHTML = ListPage.renderPageSizeDropdown(state.pageSize);

    if (total <= 0) {
      summary.textContent = '暂无数据';
      pager.innerHTML = '';
      return;
    }
    summary.textContent = '共 ' + total + ' 条记录';
    pager.innerHTML = ListPage.renderPaginationHtml(data.current_page, data.last_page);
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
          var tbody = document.getElementById('roleTableBody');
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
        var tbody = document.getElementById('roleTableBody');
        if (tbody) {
          tbody.innerHTML = '<tr><td colspan="' + COLSPAN + '" class="text-center text-danger py-5">网络错误，请稍后重试</td></tr>';
        }
      });
  }

  function resetForm() {
    var form = document.getElementById('roleForm');
    if (form) form.reset();
    document.getElementById('roleId').value = '';
    document.getElementById('roleStatus').value = 'enabled';
    document.getElementById('roleSort').value = '100';
    document.getElementById('roleCode').readOnly = false;
  }

  function openAddModal() {
    resetForm();
    document.getElementById('roleModalLabel').textContent = '新增角色';
    if (state.modal) state.modal.show();
  }

  function openEditModal(row) {
    resetForm();
    document.getElementById('roleModalLabel').textContent = '编辑角色';
    document.getElementById('roleId').value = row.id || '';
    document.getElementById('roleCode').value = row.code || '';
    document.getElementById('roleCode').readOnly = !!row.is_builtin;
    document.getElementById('roleName').value = row.name || '';
    document.getElementById('roleStatus').value = row.status || 'enabled';
    document.getElementById('roleSort').value = row.sort != null ? row.sort : 100;
    document.getElementById('roleDesc').value = row.description || '';
    if (state.modal) state.modal.show();
  }

  function saveForm(e) {
    e.preventDefault();
    if (state.saving) return;
    var form = document.getElementById('roleForm');
    if (!form) return;
    if (typeof form.reportValidity === 'function' && !form.reportValidity()) return;

    var id = parseInt(document.getElementById('roleId').value || '0', 10) || 0;
    var payload = {
      id: id,
      code: document.getElementById('roleCode').value.trim().toUpperCase(),
      name: document.getElementById('roleName').value.trim(),
      status: document.getElementById('roleStatus').value,
      sort: document.getElementById('roleSort').value || 100,
      description: document.getElementById('roleDesc').value.trim()
    };

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

  function updatePermHint() {
    var n = document.querySelectorAll('#rolePermTree .js-role-perm-cb:checked').length;
    var el = document.getElementById('rolePermSelectedHint');
    if (el) el.textContent = '已选 ' + n + ' 项';
  }

  function renderPermTree(selectedCodes) {
    var selected = {};
    (selectedCodes || []).forEach(function (c) { selected[c] = true; });
    var html = state.catalog.map(function (mod) {
      var items = (mod.perms || []).map(function (p) {
        var checked = selected[p.id] ? ' checked' : '';
        return ''
          + '<label class="form-check mb-1">'
          +   '<input class="form-check-input js-role-perm-cb" type="checkbox" value="' + escapeHtml(p.id) + '"' + checked + '>'
          +   '<span class="form-check-label">' + escapeHtml(p.name)
          +   ' <span class="text-secondary font-monospace small">(' + escapeHtml(p.id) + ')</span></span>'
          + '</label>';
      }).join('');
      return '<div class="mb-3"><div class="fw-bold mb-2">' + escapeHtml(mod.name) + '</div>' + items + '</div>';
    }).join('');
    document.getElementById('rolePermTree').innerHTML = html || '<div class="text-secondary">无权限目录</div>';
    updatePermHint();
  }

  function openPermModal(row) {
    document.getElementById('rolePermRoleId').value = row.id;
    document.getElementById('rolePermModalSub').textContent = row.name + '（' + row.code + '）';
    renderPermTree(row.perm_codes || []);
    if (state.permModal) state.permModal.show();
  }

  function savePerms() {
    var roleId = parseInt(document.getElementById('rolePermRoleId').value || '0', 10);
    var codes = [];
    Array.prototype.forEach.call(document.querySelectorAll('#rolePermTree .js-role-perm-cb:checked'), function (el) {
      codes.push(el.value);
    });
    postForm(PERMS_URL, { id: roleId, perm_codes: codes })
      .then(function (json) {
        if (json.code !== 0) {
          alert(json.msg || '保存失败');
          return;
        }
        if (state.permModal) state.permModal.hide();
        loadList(state.page);
      })
      .catch(function () { alert('网络错误，请稍后重试'); });
  }

  function renderUserAssignList(filter) {
    var q = (filter || '').trim().toLowerCase();
    var list = document.getElementById('roleUserList');
    var rows = state.assignUsers.filter(function (u) {
      if (!q) return true;
      return String(u.name || '').toLowerCase().indexOf(q) >= 0
        || String(u.account || '').toLowerCase().indexOf(q) >= 0
        || String(u.title || '').toLowerCase().indexOf(q) >= 0;
    });
    list.innerHTML = rows.map(function (u) {
      var checked = state.selectedUserIds[u.id] ? ' checked' : '';
      return ''
        + '<label class="list-group-item">'
        +   '<input class="form-check-input me-2 js-role-user-cb" type="checkbox" value="' + u.id + '"' + checked + '>'
        +   '<span class="fw-bold">' + escapeHtml(u.name) + '</span>'
        +   '<span class="text-secondary ms-2 font-monospace">' + escapeHtml(u.account) + '</span>'
        +   (u.title ? '<span class="badge bg-secondary-lt ms-2">' + escapeHtml(u.title) + '</span>' : '')
        + '</label>';
    }).join('') || '<div class="text-secondary p-3">无匹配用户</div>';
  }

  function openUserModal(row) {
    document.getElementById('roleUserRoleId').value = row.id;
    document.getElementById('roleUserModalSub').textContent = row.name + '（' + row.code + '）';
    document.getElementById('roleUserFilter').value = '';
    fetch(USERS_ASSIGN_URL + '?id=' + encodeURIComponent(row.id), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (json.code !== 0 || !json.data) {
          alert(json.msg || '加载失败');
          return;
        }
        state.assignUsers = json.data.users || [];
        state.selectedUserIds = {};
        (json.data.user_ids || []).forEach(function (id) { state.selectedUserIds[id] = true; });
        renderUserAssignList('');
        if (state.userModal) state.userModal.show();
      })
      .catch(function () { alert('网络错误，请稍后重试'); });
  }

  function saveUsers() {
    var roleId = parseInt(document.getElementById('roleUserRoleId').value || '0', 10);
    var userIds = Object.keys(state.selectedUserIds)
      .filter(function (id) { return state.selectedUserIds[id]; })
      .map(function (id) { return parseInt(id, 10); });
    postForm(USERS_URL, { id: roleId, user_ids: userIds })
      .then(function (json) {
        if (json.code !== 0) {
          alert(json.msg || '保存失败');
          return;
        }
        if (state.userModal) state.userModal.hide();
        loadList(state.page);
      })
      .catch(function () { alert('网络错误，请稍后重试'); });
  }

  function deleteRole(id) {
    if (!confirm('确认删除该角色？仅未绑定用户的自定义角色可删。')) return;
    postForm(DELETE_URL, { id: id })
      .then(function (json) {
        if (json.code !== 0) {
          alert(json.msg || '删除失败');
          return;
        }
        loadList(1);
      })
      .catch(function () { alert('网络错误，请稍后重试'); });
  }

  function loadCatalog() {
    return fetch(CATALOG_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (json.code === 0 && Array.isArray(json.data)) {
          state.catalog = json.data;
        }
      });
  }

  function bindEvents() {
    var filterForm = document.getElementById('roleFilterForm');
    if (filterForm) {
      filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loadList(1);
      });
    }
    var resetBtn = document.getElementById('roleFilterReset');
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        if (filterForm) filterForm.reset();
        loadList(1);
      });
    }
    var addBtn = document.getElementById('roleAddBtn');
    if (addBtn) addBtn.addEventListener('click', openAddModal);
    var form = document.getElementById('roleForm');
    if (form) form.addEventListener('submit', saveForm);

    var selectAll = document.getElementById('rolePermSelectAll');
    if (selectAll) {
      selectAll.addEventListener('click', function () {
        document.querySelectorAll('#rolePermTree .js-role-perm-cb').forEach(function (el) { el.checked = true; });
        updatePermHint();
      });
    }
    var clearAll = document.getElementById('rolePermClearAll');
    if (clearAll) {
      clearAll.addEventListener('click', function () {
        document.querySelectorAll('#rolePermTree .js-role-perm-cb').forEach(function (el) { el.checked = false; });
        updatePermHint();
      });
    }
    var permSave = document.getElementById('rolePermSaveBtn');
    if (permSave) permSave.addEventListener('click', savePerms);
    var userSave = document.getElementById('roleUserSaveBtn');
    if (userSave) userSave.addEventListener('click', saveUsers);

    var userFilter = document.getElementById('roleUserFilter');
    if (userFilter) {
      userFilter.addEventListener('input', function () {
        renderUserAssignList(userFilter.value);
      });
    }

    document.addEventListener('change', function (e) {
      var t = e.target;
      if (!(t instanceof Element)) return;
      if (t.classList.contains('js-role-perm-cb')) {
        updatePermHint();
        return;
      }
      if (t.classList.contains('js-role-user-cb')) {
        var id = parseInt(t.value, 10);
        if (t.checked) state.selectedUserIds[id] = true;
        else delete state.selectedUserIds[id];
      }
    });

    document.addEventListener('click', function (e) {
      var t = e.target;
      if (!(t instanceof Element)) return;
      var editBtn = t.closest('.js-role-edit');
      if (editBtn) {
        try { openEditModal(JSON.parse(decodeURIComponent(editBtn.getAttribute('data-row')))); } catch (err) {}
        return;
      }
      var permBtn = t.closest('.js-role-perms');
      if (permBtn) {
        try { openPermModal(JSON.parse(decodeURIComponent(permBtn.getAttribute('data-row')))); } catch (err) {}
        return;
      }
      var userBtn = t.closest('.js-role-users');
      if (userBtn) {
        try { openUserModal(JSON.parse(decodeURIComponent(userBtn.getAttribute('data-row')))); } catch (err) {}
        return;
      }
      var delBtn = t.closest('.js-role-delete');
      if (delBtn) {
        deleteRole(parseInt(delBtn.getAttribute('data-id'), 10));
        return;
      }
      var pageBtn = t.closest('#roleTablePager [data-page]');
      if (pageBtn) {
        e.preventDefault();
        loadList(parseInt(pageBtn.getAttribute('data-page'), 10));
        return;
      }
      var sizeBtn = t.closest('#rolePageSize [data-page-size]');
      if (sizeBtn) {
        e.preventDefault();
        state.pageSize = ListPage.normalizePageSize(parseInt(sizeBtn.getAttribute('data-page-size'), 10));
        loadList(1);
      }
    });
  }

  function applyQueryToFilters() {
    var params = new URLSearchParams(window.location.search);
    var form = document.getElementById('roleFilterForm');
    if (!form) return;
    Array.prototype.forEach.call(form.elements, function (el) {
      if (!el.name) return;
      if (params.has(el.name)) el.value = params.get(el.name);
    });
    if (params.has('page')) state.page = Math.max(1, parseInt(params.get('page'), 10) || 1);
    if (params.has('pageSize')) state.pageSize = ListPage.normalizePageSize(parseInt(params.get('pageSize'), 10));
  }

  function init() {
    var modalEl = document.getElementById('roleModal');
    if (modalEl && window.bootstrap) state.modal = new bootstrap.Modal(modalEl);
    var permEl = document.getElementById('rolePermModal');
    if (permEl && window.bootstrap) state.permModal = new bootstrap.Modal(permEl);
    var userEl = document.getElementById('roleUserModal');
    if (userEl && window.bootstrap) state.userModal = new bootstrap.Modal(userEl);

    bindEvents();
    applyQueryToFilters();
    loadCatalog().finally(function () { loadList(state.page); });
  }

  return { init: init };
})();
