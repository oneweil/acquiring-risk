---
name: admin-list-crud
description: >-
  Scaffolds ThinkPHP 8 admin AJAX list + save CRUD in doopsun-risk (Tabler,
  Validate scenes, Repository, JsonResource, migration/seed). Use when adding a
  new admin list page, 后台列表, CRUD 模块, or aligning a module with the
  blacklist reference implementation.
---

# Admin 列表 CRUD（对齐黑名单）

## 何时使用

用户要新增/对齐一个后台管理列表页（筛选 + 分页 + 同页 Modal 保存）时执行本流程。  
权威参考：`app/controller/admin/Blacklist.php` 及同名 validate/repository/resource/view/js。

相关短规则（勿整份复制进本 skill）：`.cursor/rules/admin-ui.mdc`、`db-migration-seed.mdc`、`thinkphp-naming.mdc`、`thinkphp-docs.mdc`。

## 交付清单

把 `{module}` 换成逻辑名（如 `blacklist`），`{Module}` 换成类名（如 `Blacklist`）：

- [ ] Migration（逻辑表名，前缀自动成 `risk_*`）+ 必要时 Factory/Seeder
- [ ] Model（英文枚举常量 + `*_LABELS`；勿用 append 塞展示字段）
- [ ] Repository（查询/分页/增改；禁止跨库 JOIN）
- [ ] Validate：`scene('list')` / `scene('save')`；`toListFilters()` / `toSaveData()`
- [ ] Resource：`toArray()` 出 `*_label`；列表用 `XxxResource::paginate($paginator)`
- [ ] Controller：`index` / `list` / `save`；JSON 只用 `AdminBase::success` / `fail`
- [ ] 路由：更长路径写在短路径前
- [ ] `view/admin/{module}/index.html` + `public/static/js/admin/{module}.js`
- [ ] Sidebar：`menuKey` 与 `view/admin/layout/sidebar.html` 一致

## 分层约定

| 层 | 职责 | 不要做 |
|---|---|---|
| Controller | 取参、校验、调 repo、包 Resource、返回 JSON/View | 私有 normalize；直接拼列表展示字段 |
| Validate | 规则 + 场景；通过后的 `toListFilters` / `toSaveData` | 写库 |
| Repository | DB 查询与写入；`paginate([...])` | 返回带中文 label 的展示 DTO |
| Resource | API 对外形状与 `*_label` | 业务写库 |
| Model | 表映射、枚举常量 | 把 label 塞进 `$append`（除非确有必要） |

## 接口与响应

- 页面：`GET /admin/{module}` → `AdminBase::renderList('/admin/{module}/index', …)`
- 列表：`GET /admin/{module}/list` → `{ code: 0, msg, data }`  
  `data` = ThinkPHP 分页器数组，且 `data` 键为 Resource 集合（见 `JsonResource::paginate`）：至少含 `data`、`total`、`per_page`、`current_page`、`last_page`
- 保存：`POST /admin/{module}/save` → 成功 `success(row, '保存成功')`；校验失败 `fail(msg, 422, null, 422)`
- 枚举：库内/接口 value **英文**；UI 用 label

路由示例：

```php
Route::get('{module}/list', 'admin.{Module}/list');
Route::post('{module}/save', 'admin.{Module}/save');
Route::get('{module}', 'admin.{Module}/index');
```

## Controller 模板要点

```php
// list
$params = $this->request->get();
// validate → scene('list') → fail 422
$filters = {Module}Validate::toListFilters($params);
$page    = (int) ($params['page'] ?? 1); // page 已校验 integer|gt:0，勿再 max(1,…)
return $this->success({Module}Resource::paginate($repo->search($filters, $page)));

// save
// validate → scene('save') → toSaveData → create/update → Resource::make
```

- 控制器**不要**留 `normalizePayload` 一类私有整理方法（放 Validate）
- 列表 `page` 可空；缺省 `(int) ($params['page'] ?? 1)` 即可

## Validate

- `list`：筛选字段可空；枚举用自定义 `check*`；`page` => `integer|gt:0`（无 require）
- `save`：业务必填；`requireIf` 等按字段来
- 用 `sceneList()` / `sceneSave()` 或 `$scene` + `remove`，保证 list 去掉不该 require 的规则

## 前端

对照 `view/admin/blacklist/index.html`、`public/static/js/admin/blacklist.js`：

- Tabler；筛选 form + 表格 + footer 分页 + Modal 表单同页
- JS 读分页用 `current_page` / `last_page` / `data`（行数组），勿用旧的 `items` / `page_size`
- HTML 转义：`ListPage.escapeHtml`
- 筛选/分页同步 URL query
- option **value=英文**；无硬删时用状态失效

## 数据层

- 表结构只走 `think-migration`；模拟数据用 Factory + Seeder，**迁移里禁止 insert 业务数据**
- 逻辑表名不要带 `risk_` 前缀（`DB_PREFIX` 会加）
- 布尔状态 `TINYINT(1)`；原因等文案设合理 `max` 与 comment

## 自检

- [ ] 非法枚举 / 缺必填 → 422 且文案可读
- [ ] 列表空筛选、翻页、刷新后 query 仍在
- [ ] 新增/编辑保存后列表可见；Resource 含 `*_label`
- [ ] 无跨库 SQL JOIN；命名符合 ThinkPHP 规范

## 参考路径（黑名单）

- `app/controller/admin/Blacklist.php`
- `app/validate/Blacklist.php`
- `app/repository/risk/BlacklistRepository.php`
- `app/resource/BlacklistResource.php`、`app/resource/JsonResource.php`
- `app/model/Blacklist.php`
- `view/admin/blacklist/index.html`
- `public/static/js/admin/blacklist.js`
- `route/app.php`（blacklist 段）
