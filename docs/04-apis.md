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
    "risk_level": "high",
    "action": "拒绝交易",
    "measure_code": "DECLINE",
    "hit_rules": [
      {
        "rule_id": "R013",
        "name": "单笔大额交易",
        "risk_level": "high",
        "measure": "DECLINE"
      }
    ],
    "alert_id": "AL2026061001",
    "evaluation_id": 10001,
    "message": "ok"
  }
}
```

**decision 枚举（交易级，无挂起 / 无 manual_review）**

| 值 | 说明 |
|----|------|
| pass | 通过 |
| decline | 拒绝 |
| challenge_3ds | 强制 3DS |

---

### 1.2 （不做）风控 → 主站商户状态回调

~~`POST {doopsun}/api/risk/callback/merchant`~~ — **不实现**。

暂停/恢复收单与结算由**人工在主站处理**；风控侧只做发现、本地投影/预警记录与 evaluate 决策，**不自动回调主站改状态**。

---

### 1.3 （已取消）商户人工审核结案回调

~~独立 `merchant_alert` 结案回调~~ — **不实现**。

---

### 1.4 交易处置说明（已废弃订单人审回调）

~~`POST .../callback/disposition` 用于订单人工审核放行~~ — **不实现**。若需调单跟进，仅更新交易预警状态，不改变历史授权终态。

---

### 1.5 商户投影 Upsert（主站推送）

```
POST /api/v1/merchant/upsert
POST /api/v1/merchant/upsert_batch
GET  /api/v1/merchant/:merchant_id
```

主站/CRM **主动推送**商户档案到风控本地投影；风控**不**直连主站商户库、**不**做入网审核队列。

**POST /api/v1/merchant/upsert** body：

```json
{
  "merchant_id": "M100001",
  "name": "GlobalShop Inc.",
  "status": "normal",
  "industry": "跨境电商",
  "country": "US",
  "register_at": "2020-06-12",
  "onboard_at": "2024-03-15",
  "website": "https://example.com",
  "website_status": "compliant",
  "compliance_hits": 0,
  "review_status": "approved",
  "source_version": 1710000000,
  "assess": false,
  "extra": {}
}
```

- 必填：`merchant_id`, `name`, `status`, `source_version`（单调；旧版本不覆盖，`skipped=true`）
- `status`：`normal` / `watch` / `restricted` / `suspended` / `not_opened`
- **新建投影**：自动执行入网型评估并写 `risk_merchant_assessment`
- **更新投影**：默认不重评；`assess=true` 可强制再跑入网型评估
- 响应：投影摘要 + `created` / `skipped`；若有评估含 `risk_score` / `risk_level` / `assess_type` / `assessed_at`

**POST /api/v1/merchant/upsert_batch**：`{ "items": [ /* upsert 对象 */ ] }`，逐条幂等（每条同样：新建自动评估）。

~~`POST /api/v1/merchant/assess`~~ — **已去除**；重评用 upsert 的 `assess=true`，或后台重评接口。

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

### 2.2 监控中心（待实现；总览当前为前端 Mock）

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/admin/dashboard/stats` | 待办 + 决策三态 + Top 规则/商户 + 商户姿态 + 合规摘要 |
| GET | `/admin/dashboard/alerts` | 最新待处理预警 Top N |
| GET | `/admin/dashboard/feed` | 实时订单流（当前前端 Mock，约 4s 轮询占位） |

---

### 2.3 预警中心（P0）

**仅交易预警**（`risk_alert`，`order_no` 必填）。**不实现** `merchant_alert` 接口。

| 方法 | 路径 | 说明 | 状态 |
|------|------|------|------|
| GET | `/admin/alert` | 交易预警列表页 | 已实现 |
| GET | `/admin/alert/list` | 交易预警列表（AJAX） | 已实现 |
| GET | `/admin/alert/detail` | 交易预警详情 | 已实现 |
| POST | `/admin/alert/handle` | 交易预警处置（调单/关闭） | 已实现 |
| POST | `/admin/alert/upload` | 调单附件上传 | 已实现 |
| GET | `/admin/alert/attachment/download` | 附件下载（`attachment_id`） | 已实现 |

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
| GET | `/admin/merchant/list` | 商户列表（读本地投影） |
| GET | `/admin/merchant/detail` | 商户详情 |
| GET | `/admin/merchant/stats` | KPI |
| POST | `/admin/merchant/reassess` | 单个/批量重评（引擎未就绪可 501） |
| GET | `/admin/merchant_risk/config` | 评估规则配置 |
| POST | `/admin/merchant_risk/save` | 保存评估规则 |
| POST | `/admin/merchant_risk_level/save` | 保存等级规则（首屏配置由页面 render 注入，无独立 config 接口） |

~~`/admin/onboarding/*`（list/sync/detail/review）~~ — **不实现**；入网审核在主站。

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
| `/admin/dashboard` | Dashboard/index | UI + Mock（待办/决策/来源/姿态/合规） |
| `/admin/order` | Order/index | 已实现列表 |
| `/admin/alert` | Alert/index | 已实现 |
| `/admin/merchant` | Merchant/index | 已实现列表（本地投影） |
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
