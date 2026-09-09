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

**本阶段已实现交易预警**；`merchant_alert` 接口尚未实现。

| 方法 | 路径 | 说明 | 状态 |
|------|------|------|------|
| GET | `/admin/alert` | 交易预警列表页 | 已实现 |
| GET | `/admin/alert/list` | 交易预警列表（AJAX） | 已实现 |
| GET | `/admin/alert/detail` | 交易预警详情 | 已实现 |
| POST | `/admin/alert/handle` | 交易预警处置（调单/关闭） | 已实现 |
| POST | `/admin/alert/upload` | 调单附件上传 | 已实现 |
| GET | `/admin/alert/attachment/download` | 附件下载（`attachment_id`） | 已实现 |
| GET | `/admin/merchant_alert/list` | **商户预警**列表（人工审核） | 未实现 |
| GET | `/admin/merchant_alert/detail` | 商户预警详情 | 未实现 |
| POST | `/admin/merchant_alert/handle` | **商户人工审核**提交 | 未实现 |

**GET /admin/alert/list** query：`risk_level` / `status` / `measure_code` / `page` / `pageSize`(10\|20\|50)。无 `status` 时默认排除 `closed`。

**POST /admin/alert/handle**

```json
{
  "id": 1,
  "action": "close",
  "remark": "已阅关闭",
  "inquiry_desc": "可选，调单说明"
}
```

`action`：`close` | `false_positive` | `submit_materials` | `complete`  
- `submit_materials` / `complete` 仅 `measure_code=CHARGEBACK_INQUIRY`；非误报须已有附件  
- 处置**不改变**订单授权终态

**POST /admin/merchant_alert/handle**（规划）

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
| GET | `/admin/str_report/stats` | KPI：待确认 / 本月 LTR / 本月 STR / 本季已提交（**已实现**） |
| GET | `/admin/str_report/list` | 列表（tab/merchant_id/order_no/status + page/pageSize）（**已实现**） |
| GET | `/admin/str_report/detail` | 详情（含 attachments + linked_edd）（**已实现**） |
| POST | `/admin/str_report/create` | 新建（可选 multipart file；STR 必填 suspicious_desc）（**已实现**） |
| POST | `/admin/str_report/confirm` | 确认上报（pending_confirm → uploaded + 自动附件；STR 联动 EDD）（**已实现**） |
| POST | `/admin/str_report/dismiss` | 无需上报（**已实现**） |
| POST | `/admin/str_report/upload` | 上传附件（generated/rejected → uploaded）（**已实现**） |
| GET | `/admin/str_report/attachment/download` | 下载附件（**已实现**） |
| POST | `/admin/str_report/submit` | 提交监管（uploaded → submitted）（**已实现**） |
| GET | `/admin/str_push_rule/list` | 规则表分页（category/push_str/keyword） |
| POST | `/admin/str_push_rule/save` | 保存全局配置 + 批量 push_str |
| POST | `/admin/str_push_rule/reset` | 恢复默认全局配置与 push_str |
| GET | `/admin/edd/stats` | KPI：进行中 / 待启动 / 已通过 / 未通过过期 |
| GET | `/admin/edd/list` | EDD 列表（keyword/trigger/status + page/pageSize） |
| GET | `/admin/edd/detail` | EDD 详情（含 docs 分组附件） |
| POST | `/admin/edd/create` | 新建（pending） |
| POST | `/admin/edd/collect` | 启动资料收集（勾选 checklist） |
| POST | `/admin/edd/upload` | 上传附件（multipart：id/checklist_key/file） |
| POST | `/admin/edd/attachment/delete` | 删除附件 |
| GET | `/admin/edd/attachment/download` | 下载附件 |
| POST | `/admin/edd/submit` | 资料齐备后进入审核中 |
| POST | `/admin/edd/review` | 审核通过/拒绝（action=approve\|reject + review_remark） |

---

### 2.7 系统管理（P1）

| 方法 | 路径 | 说明 | 权限码 |
|------|------|------|--------|
| GET | `/admin/user/list` | 用户列表（含 stats） | `users:view` |
| GET | `/admin/user/role_options` | 启用角色选项 | `users:view` |
| POST | `/admin/user/save` | 新增/编辑 | `users:edit` |
| POST | `/admin/user/reset_password` | 重置密码 | `users:edit` |
| POST | `/admin/user/toggle_status` | 启用/停用/解锁 | `users:edit` |
| POST | `/admin/user/roles` | 分配角色 | `users:edit` |
| GET | `/admin/role/list` | 角色列表（含 stats） | `roles:view` |
| GET | `/admin/role/permission_catalog` | 权限树目录 | `roles:view` |
| GET | `/admin/role/users_assign` | 角色分配用户弹窗数据 | `roles:view` |
| POST | `/admin/role/save` | 新增/编辑 | `roles:edit` |
| POST | `/admin/role/permissions` | 保存权限 | `roles:edit` |
| POST | `/admin/role/users` | 分配用户 | `roles:edit` |
| POST | `/admin/role/delete` | 删除自定义空角色 | `roles:edit` |
| GET | `/admin/audit_log/list` | 日志列表 | 未实现 |
| GET | `/admin/audit_log/export` | 导出 | 未实现 |

本轮仅对 user/role 路由强制权限中间件；其它业务模块写操作暂未挂权限码。`SYS_ADMIN` 全放行。角色/权限变更后需重新登录生效。

---

## 3. 页面路由

| GET 路径 | Controller | 说明 |
|----------|------------|------|
| `/admin/dashboard` | Dashboard/index | 占位/部分实现 |
| `/admin/order` | Order/index | 已实现列表 |
| `/admin/alert` | Alert/index | 已实现 |
| `/admin/onboarding` | Onboarding/index | 占位 |
| `/admin/merchant` | Merchant/index | 已实现列表 |
| `/admin/merchant_risk_level` | MerchantRiskLevel/index | 已实现 |
| `/admin/merchant_risk` | MerchantRisk/index | 已实现 |
| `/admin/str_report` | StrReport/index | 已实现 KPI+列表+状态机+附件 |
| `/admin/str_push_rule` | StrPushRule/index | 已实现 |
| `/admin/edd` | Edd/index | 已实现 KPI+列表+工作流 |
| `/admin/rule` | Rule/index | 已实现 |
| `/admin/disposition` | Disposition/index | 已实现 |
| `/admin/blacklist` | Blacklist/index | 已实现 |
| `/admin/user` | User/index | 已实现 |
| `/admin/role` | Role/index | 已实现 |
| `/admin/audit_log` | AuditLog/index | 占位 |

---

## 4. 认证

| 场景 | 方式 |
|------|------|
| 后台登录 | POST `/login`，校验 `sys_user`（仅 `enabled`），Session |
| Session | `admin_user`：`id` / `username` / `name` / `role_codes` / `perm_codes` / `login_at` |
| 后台业务 | Session + `auth`；user/role 另挂 `permission` 权限码 |
| 开放 API | Header API Key，`api_auth` 中间件 |
| 种子账号 | `admin` / `admin123`（`SYS_ADMIN`） |

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
