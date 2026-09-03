# 06 — 架构边界与库表草案

## 1. 系统边界

```
┌─────────────────┐     evaluate API      ┌─────────────────┐
│  doopsun 收单    │ ───────────────────► │  doopsun-risk   │
│  (主库)          │ ◄── 人审回调(待建) ── │  (风控库)        │
└─────────────────┘                       └─────────────────┘
        │                                         │
        │ doopsun_order                           │ risk_* 表
        │ doopsun_merchants                       │ 规则/评估/预警
        └─────────── 只读 ────────────────────────┘
```

| 原则 | 说明 |
|------|------|
| 双库 | `mysql` = 风控库；`doopsun` = 主站只读（`config/database.php`） |
| 订单不镜像 | 列表/详情读 `doopsun_order`，评估结果写风控库后 JOIN |
| 历史不回灌 | 仅处理接入 evaluate 后的新单 |
| 决策走 API | 不跨库直写 doopsun 核心表；人审结论回调接口 |
| 商户 | 短期只读 `doopsun_merchants`；入网资料可 CRM 推送或定时同步 |

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

-- 预警工单（交易级）
risk_alert (
  id, scope DEFAULT 'order', order_no, merchant_id, evaluation_id,
  ...
)

-- 商户预警 / 商户人工审核
risk_merchant_alert (
  id, merchant_id, trigger_type, trigger_rule_id,
  trigger_metrics JSON, risk_level, status,
  trading_paused, settlement_paused,
  review_decision, handle_remark,
  operator_id, handled_at, created_at, updated_at
)

-- 风控规则
risk_rule (
  id, rule_id UNIQUE, category, name, description,
  config JSON, measure_code, risk_level,
  enabled, sort, updated_at
)

-- 处置策略
risk_disposition (
  id, code UNIQUE, name, description,
  risk_level, scope, priority,
  is_block, push_alert, extra_config JSON,
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

risk_onboarding (
  id, app_id, merchant_id, sync_data JSON,
  kyc_status, risk_score, risk_level,
  status, review_decision, review_remark,
  reviewed_at, created_at
)
```

### P2 合规

```sql
risk_str_report (...)
risk_str_push_config (...)
risk_edd_case (...)
```

### P1 系统

```sql
sys_user, sys_role, sys_role_user, sys_role_permission
audit_log
```

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

## 4. 列表查询模式（订单监控）

```sql
SELECT o.*, m.name AS merchant_name,
       e.risk_level, e.action, e.measure_code
FROM doopsun.doopsun_order o
LEFT JOIN doopsun.doopsun_merchants m ON m.merchantid = o.merchantid
LEFT JOIN risk_order_evaluation e ON e.order_no = o.orderid
WHERE ...
ORDER BY o.doopsun_orderdate DESC
LIMIT ...
```

- 无评估记录的订单：可不在列表展示，或展示为「未评估」（产品定）
- 筛选 `risk_level` / `status`：评估表 + 订单状态组合条件

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

---

## 7. 与原型差异的处理

| 差异 | 处理 |
|------|------|
| 原型 localStorage 存规则 | 改为风控库 + 后台 save API |
| 原型 Math.random 模拟命中 | 改为真实 check + Redis 计数 |
| 原型订单「人工审核/审核中」 | **不实现**；交易级用 pass/decline/3ds |
| 原型 MANUAL_REVIEW | 拆为交易级策略 + 商户级 MERCHANT_MANUAL_REVIEW |
| 侧栏无入网 | 文档保留；路由已有，按需恢复菜单 |
