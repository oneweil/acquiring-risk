# 04 — 接口清单

## 约定

- 后台页面：`/admin/*`，Session + Auth 中间件，HTML 或 JSON
- 开放 API：`/api/v1/*`，API Key（`api_auth`），无 Session
- JSON 响应建议统一：`{ code, msg, data }`（与现有 `ApiBase` 一致）
- 列表分页：`page`, `page_size`（默认 10），返回 `{ items, total }`

---

## 1. 对外 API（doopsun / 网关调用）

### 1.1 风控评估

```
POST /api/v1/risk/evaluate
Auth: API Key
```

**Request**

```json
{
  "order_no": "MO20260658001",
  "merchant_id": "M100001",
  "amount": 1280.50,
  "currency": "USD",
  "card_no": "411111******9012",
  "card_bin": "411111",
  "card_country": "US",
  "ip": "198.51.100.10",
  "ip_country": "US",
  "email": "buyer@mail.com",
  "billing_country": "US",
  "shipping_country": "US",
  "avs_result": "Full Match",
  "cvv_result": "Match",
  "three_ds": "未认证",
  "eci": "07",
  "website": "https://shop.example.com/checkout",
  "device_fingerprint": "fp_xxx",
  "mcc": "5411"
}
```

**Response（目标形态，当前为骨架）**

```json
{
  "code": 0,
  "data": {
    "decision": "decline",
    "risk_level": "中风险",
    "action": "拒绝交易",
    "measure_code": "DECLINE",
    "score": 72,
    "hit_rules": [
      {
        "rule_id": "R013",
        "name": "单笔大额交易",
        "risk_level": "中风险",
        "measure": "人工审核"
      }
    ],
    "alert_id": "AL2026061001",
    "evaluation_id": 10001,
    "message": "ok"
  }
}
```

**decision 枚举（交易级，无 manual_review）**

| 值 | 说明 |
|----|------|
| pass | 通过 |
| decline | 拒绝 |
| challenge_3ds | 强制 3DS |

---

### 1.2 商户状态回调（待实现，doopsun 接收）

风控在**商户人工审核**或**交易量异常**命中后调用。

```
POST {doopsun}/api/risk/callback/merchant
```

**Request（示例：暂停）**

```json
{
  "merchant_id": "M100005",
  "action": "pause_trading_and_settlement",
  "reason": "交易量暴涨：当日为近30日均值620%",
  "merchant_alert_id": "MA2026060001",
  "rule_id": "R026"
}
```

**action 枚举**

| action | 说明 |
|--------|------|
| pause_trading | 暂停交易（新单拒绝） |
| pause_settlement | 暂停结算 |
| pause_trading_and_settlement | 暂停交易 + 暂停结算 |
| resume_trading | 恢复交易 |
| resume_settlement | 恢复结算 |
| resume_all | 审核通过，全部恢复 |

---

### 1.3 商户人工审核回调（待实现）

商户预警处理完成后调用，配合 `resume_*` 或维持暂停。

```json
{
  "merchant_id": "M100005",
  "merchant_alert_id": "MA2026060001",
  "decision": "approve",
  "remark": "促销季正常放量，已核实"
}
```

**decision**：`approve` | `keep_paused` | `false_positive`

---

### 1.4 交易处置说明（已废弃订单人审回调）

~~`POST .../callback/disposition` 用于订单人工审核放行~~ — **不实现**。若需调单跟进，仅更新交易预警状态，不改变历史授权终态。

---

### 1.5 商户同步（可选，P1+）

```
POST /api/v1/merchant/sync
GET  /api/v1/merchant/{merchant_id}
```

CRM 推送入网资料或风控定时拉取。

---

## 2. 后台 JSON 接口（按模块）

### 2.1 已实现

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/admin/order/list` | 订单列表（筛选+分页） |
| GET | `/admin/order/detail` | 订单详情 |

**GET /admin/order/list 参数**

| 参数 | 说明 |
|------|------|
| merchant_id | 商户号 |
| order_no | 商户订单号 |
| currency | 币种 |
| risk_level | 风险等级 |
| status | 订单状态 |
| page | 页码 |

**Item 字段**：见 [03-data-dictionary.md](./03-data-dictionary.md) §1.1

---

### 2.2 监控中心（待实现）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/admin/dashboard/stats` | KPI + 风险分布 |
| GET | `/admin/dashboard/feed` | 实时订单流 |
| GET | `/admin/dashboard/alerts` | 最新预警 Top N |

---

### 2.3 预警中心（P0）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/admin/alert/list` | 交易预警列表 |
| GET | `/admin/merchant_alert/list` | **商户预警**列表（人工审核） |
| GET | `/admin/alert/detail` | 交易预警详情 |
| GET | `/admin/merchant_alert/detail` | 商户预警详情 |
| POST | `/admin/alert/handle` | 交易预警处置（调单/关闭） |
| POST | `/admin/merchant_alert/handle` | **商户人工审核**提交 |

**POST /admin/merchant_alert/handle**

```json
{
  "alert_id": "MA2026060001",
  "decision": "approve",
  "remark": "已核实为正常促销",
  "resume_trading": true,
  "resume_settlement": true
}
```

`decision`: `approve` | `keep_paused` | `false_positive`

---

### 2.4 规则配置（P0）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/admin/rule/list` | 规则列表（分组） |
| POST | `/admin/rule/save` | 批量保存规则 |
| GET | `/admin/disposition/list` | 处置策略列表 |
| POST | `/admin/disposition/save` | 新增/编辑策略 |
| GET | `/admin/disposition/stats` | 今日触发统计 |
| GET | `/admin/blacklist/list` | 黑名单列表 |
| POST | `/admin/blacklist/save` | 新增/编辑 |
| DELETE | `/admin/blacklist/delete` | 删除 |

---

### 2.5 商户管理（P1）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/admin/onboarding/list` | 入网待审列表 |
| POST | `/admin/onboarding/sync` | 触发同步 |
| GET | `/admin/onboarding/detail` | 入网详情 |
| POST | `/admin/onboarding/review` | 审核决策 |
| GET | `/admin/merchant/list` | 商户列表 |
| GET | `/admin/merchant/detail` | 商户详情 |
| POST | `/admin/merchant/reassess` | 单个/批量重评 |
| GET | `/admin/merchant_risk/config` | 评估规则配置 |
| POST | `/admin/merchant_risk/save` | 保存评估规则 |
| POST | `/admin/merchant_risk_level/save` | 保存等级规则（首屏配置由页面 render 注入，无独立 config 接口） |

**POST /admin/onboarding/review**

```json
{
  "app_id": "OB202606001",
  "decision": "approve",
  "remark": "KYC 通过"
}
```

`decision`: `approve` | `conditional` | `reject`

---

### 2.6 合规报送（P2）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/admin/str_report/list` | STR 列表 |
| GET | `/admin/str_report/detail` | 详情 |
| POST | `/admin/str_report/create` | 新建 |
| POST | `/admin/str_report/confirm` | 确认上报 |
| POST | `/admin/str_report/dismiss` | 无需上报 |
| POST | `/admin/str_report/submit` | 提交监管 |
| POST | `/admin/str_report/upload` | 上传附件 |
| GET | `/admin/str_push_rule/config` | 推送规则 |
| POST | `/admin/str_push_rule/save` | 保存推送规则 |
| GET | `/admin/edd/list` | EDD 列表 |
| GET | `/admin/edd/detail` | EDD 详情 |
| POST | `/admin/edd/create` | 新建 |
| POST | `/admin/edd/collect` | 启动资料收集 |
| POST | `/admin/edd/review` | 提交/通过/拒绝 |

---

### 2.7 系统管理（P1）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/admin/user/list` | 用户列表 |
| POST | `/admin/user/save` | 新增/编辑 |
| POST | `/admin/user/reset_password` | 重置密码 |
| POST | `/admin/user/toggle_status` | 启用/停用/解锁 |
| GET | `/admin/role/list` | 角色列表 |
| POST | `/admin/role/save` | 新增/编辑 |
| POST | `/admin/role/permissions` | 保存权限 |
| POST | `/admin/role/users` | 分配用户 |
| GET | `/admin/audit_log/list` | 日志列表 |
| GET | `/admin/audit_log/export` | 导出 |

---

## 3. 页面路由（已实现，仅 HTML）

| GET 路径 | Controller |
|----------|------------|
| `/admin/dashboard` | Dashboard/index |
| `/admin/order` | Order/index |
| `/admin/alert` | Alert/index |
| `/admin/onboarding` | Onboarding/index |
| `/admin/merchant` | Merchant/index |
| `/admin/merchant_risk_level` | MerchantRiskLevel/index |
| `/admin/merchant_risk` | MerchantRisk/index |
| `/admin/str_report` | StrReport/index |
| `/admin/str_push_rule` | StrPushRule/index |
| `/admin/edd` | Edd/index |
| `/admin/rule` | Rule/index |
| `/admin/disposition` | Disposition/index |
| `/admin/blacklist` | Blacklist/index |
| `/admin/user` | User/index |
| `/admin/role` | Role/index |
| `/admin/audit_log` | AuditLog/index |

---

## 4. 认证

| 场景 | 方式 |
|------|------|
| 后台登录 | POST `/login`，Session |
| 后台业务 | Session + `auth` 中间件 |
| 开放 API | Header API Key，`api_auth` 中间件 |

---

## 5. 错误码（建议）

| code | 含义 |
|------|------|
| 0 | 成功 |
| 401 | 未登录 / API Key 无效 |
| 403 | 无权限 |
| 404 | 资源不存在 |
| 422 | 参数校验失败 |
| 500 | 服务器错误 |
