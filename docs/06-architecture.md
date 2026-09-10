# 06 — 架构边界与库表草案

## 1. 系统边界

```
┌─────────────────┐  evaluate / merchant upsert  ┌─────────────────┐
│  收单主站 / CRM  │ ───────────────────────────► │  doopsun-risk   │
│  (主数据)        │   （无自动暂停回调）          │  (风控库)        │
└─────────────────┘                               └─────────────────┘
        │                                                   │
        │ 订单（过渡期可只读）                               │ risk_merchant 投影
        │                                                   │ risk_* 评估/预警
        └─────────── 默认不直连商户表 ──────────────────────┘
```

| 原则 | 说明 |
|------|------|
| 双库 | `mysql` = 风控库；`doopsun_db` = 过渡期订单只读（`config/database.php`） |
| 商户投影 | 主站 `POST /api/v1/merchant/upsert` → `risk_merchant`；**禁止**直读 `doopsun_merchants` |
| 入网审核 | 主站负责；风控只评分，不建 `risk_onboarding` 审核表 |
| 订单不镜像 | 列表/详情过渡期读 `doopsun_order`，评估结果写风控库后应用层组装 |
| 历史不回灌 | 仅处理接入 evaluate 后的新单 |
| 决策走 API | 不跨库直写主站；暂停/恢复由人工在主站处理，风控不自动回调 |

---

## 2. 风控库表（最小集 → 完整）

### P0 必须

```sql
-- 订单评估主表
risk_order_evaluation (
  id, order_no, doopsun_order_id, merchant_id,
  risk_level, action, measure_code, decision,
  evaluated_at, created_at
)

-- 命中明细
risk_order_hit (
  id, evaluation_id, rule_id, rule_name,
  risk_level, measure, created_at
)

-- 预警（交易级；order_no 必填；不建 merchant_alert）
risk_alert (
  id, alert_no UNIQUE, scope DEFAULT 'order', alerted_at,
  merchant_id, order_no NOT NULL, amount_display, risk_level,
  rule_name, measure_code, action_name, hit_details JSON,
  status, handle_remark, inquiry_desc, evaluation_id,
  str_report_id, operator_id, handled_at, created_at, updated_at
)
risk_alert_attachment (
  id, alert_id FK→alert CASCADE, file_name, file_size, storage_path, file_type, uploaded_at, ...
)

-- 风控规则
risk_rule (
  id, rule_id UNIQUE, category, name, description,
  config JSON, measure_code,
  enabled, push_str, sort, updated_at
)

-- 处置策略（无 MANUAL_REVIEW；risk_level 用 mid 非 medium）
risk_disposition (
  id, code UNIQUE, name, description,
  risk_level, scope, priority,
  is_block, push_alert,
  status, updated_at
)

-- 黑名单
risk_blacklist (
  id, type, value, reason,
  effective_date, expiry, status,
  created_at, updated_at
)
```

### P1 商户

```sql
-- 主站推送投影（逻辑表 merchant → risk_merchant）
risk_merchant (
  id, merchant_id UNIQUE, name, status,
  industry, country, register_at, onboard_at,
  website, email, mobile, address,
  website_status, compliance_hits, review_status,
  source_version, extra JSON, synced_at,
  created_at, updated_at
)

risk_merchant_assessment (
  id, merchant_id, risk_score, risk_level,
  assess_type, -- onboarding / periodic
  assess_details JSON, assessed_at
)

risk_merchant_assess_config ( -- 或 JSON 文件/单表 key-value
  config_key, config_json, updated_at
)

risk_merchant_level_config (
  config_json, updated_at
)
```

~~`risk_onboarding`~~ — **不建**；入网审核在主站。

### P2 合规

```sql
risk_str_report (report_no, type, trigger_mode, merchant_*, order_no, amount_*, status, …)
risk_str_attachment (str_report_id, file_*, is_auto, …)
risk_str_push_config (enabled, push_by_risk_level, risk_levels JSON, push_ltr, ltr_threshold_*)
risk_edd_case (...)
```

### P1 系统

```sql
sys_user, sys_role, sys_role_user, sys_role_permission
-- audit_log（未落地）
```

权限码目录在代码 `PermissionCatalog`（无权限主表）。后台登录查 `sys_user`；user/role 路由挂 `permission` 中间件。
---

## 3. doopsun 订单字段映射（建议）

| 风控字段 | doopsun_order 字段 | 备注 |
|----------|-------------------|------|
| order_no | orderid | 商户订单号 |
| channel_no | doopsun_orderid | 通道订单号 |
| merchant_id | merchantid | |
| trade_time | doopsun_orderdate | |
| currency | currency | |
| amount | orderamount | |
| card_type | cardtype | |
| card_no | cardnum | 脱敏 |
| email | email | |
| ip | ip | |
| website | accessurl | |
| status | status | 需与风控状态映射 |
| three_ds | del 等 | 业务确认（如 del=3 表示 3DS） |

风控字段 **risk_level / hit_rule / action** 仅存在于 `risk_order_evaluation`。

---

## 4. 列表查询模式（订单监控，过渡期）

应用层分查组装（禁止跨库 SQL JOIN）：

1. 读 `doopsun_order`（过渡）
2. 读本地 `risk_merchant` 取商户名（`MerchantRepository::mapNamesByIds`）
3. 读 `risk_order_evaluation` 取风险结果

商户列表**只读** `risk_merchant` + `risk_merchant_assessment`，不访问主站商户表。

---

## 5. 服务分层（建议）

```
Controller (admin/*, api/Risk)
    ↓
Service (RiskEvaluateService, AlertService, RuleService…)
    ↓
Repository
    ├── RiskDb*     风控库
    └── DoopsunDb*  只读主库
```

现有可复用：

- `OrderMockService` → 替换为 `OrderRepository`
- `app/common.php` 徽章函数 → 继续用于视图
- `list-page.js` / `order.js` → 列表+详情 Modal 范本

---

## 6. 配置项

| 配置 | 位置 | 说明 |
|------|------|------|
| 数据库双连接 | `config/database.php` | mysql + doopsun |
| 分页 | `config/paginate.php` | list_rows = 10 |
| API Key | env / config | api_auth 中间件 |
| 权限码目录 | `app/support/PermissionCatalog.php` | 固定权限点；无权限主表 |
| 权限中间件 | `permission` 别名 | 仅 user/role 路由强制校验 |

---

## 7. 与原型差异的处理

| 差异 | 处理 |
|------|------|
| 原型 localStorage 存规则 | 改为风控库 + 后台 save API |
| 原型 Math.random 模拟命中 | 改为真实 check + Redis 计数 |
| 原型订单「人工审核/审核中」 | **不实现**；交易级用 pass/decline/3ds（`is_block` 映射） |
| 原型 MANUAL_REVIEW | **不入库**；规则映射为 ALERT_ONLY / DECLINE / 3DS |
| 商户预警人审表 | **不建** `risk_merchant_alert`；商户级策略只做状态动作 |
| 侧栏/入网页 | **不实现**入网审核；主站推送投影 + 风控评分 |
