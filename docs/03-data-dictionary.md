# 03 — 数据字典

字段命名：后台 JSON 建议 **snake_case**（与现有 `OrderMockService` 一致）；原型 JS 为 camelCase，实现时做映射。

**数据来源图例**

- `[D]` doopsun 库只读
- `[R]` 风控库
- `[E]` 外部同步（CRM/入网）
- `[C]` 计算/引擎产出

---

## 1. 订单（交易）

### 1.1 列表 / 详情 — 展示字段

| 字段 | 类型 | 来源 | 原型字段 | doopsun 映射（建议） |
|------|------|------|----------|----------------------|
| order_no | string | [D] | id | `doopsun_order.orderid` |
| channel_no | string | [D] | channelNo | `doopsun_order.doopsun_orderid` |
| merchant_id | string | [D] | merchantId | `doopsun_order.merchantid` |
| merchant_name | string | [D] | merchantName | 联表 `doopsun_merchants` |
| trade_time | datetime | [D] | time / ts | `doopsun_order.doopsun_orderdate` |
| currency | string | [D] | currency | `doopsun_order.currency` |
| amount | decimal | [D] | amount | `doopsun_order.orderamount` |
| card_type | string | [D] | cardType | `doopsun_order.cardtype` |
| card_country | string | [D] | cardCountry | 发卡国解析 |
| ip_country | string | [D] | ipCountry | IP 地理库 |
| three_ds | string | [D] | threeDS | 3DS 结果；`del=3` 等 |
| status | enum | [D+R] | status | 成功 / 拦截 / 失败（**无「审核中-待人工」**） |
| risk_level | enum | [R] | riskLevel | 极低/低/中/高/极高 |
| hit_rule | string | [R] | hitRule | 命中规则名拼接 |
| action | string | [R] | action | 最终处置策略名 |

### 1.2 详情扩展字段

| 字段 | 来源 | 说明 |
|------|------|------|
| website | [D] | 交易 URL / accessurl |
| card_no | [D] | 脱敏卡号 cardnum |
| card_bin | [D] | 前 6 位 |
| avs_result | [D] | AVS 结果 |
| cvv_result | [D] | CVV 结果 |
| eci | [D] | 3DS ECI |
| email | [D] | 持卡人邮箱 |
| ip | [D] | 交易 IP |
| billing_country | [D] | 账单国 |
| shipping_country | [D] | 收货国 |
| mcc | [D] | 商户类别码 |
| is_proxy | [C] | VPN/Proxy 检测 |
| blacklist_hit | [C] | 是否命中黑名单 |
| disposable_email | [C] | 一次性邮箱 |
| hit_details | array | [{id,name,risk_level,measure}] [R] |
| alert_id | string | 关联预警 [R] |
| str_report_id | string | 关联 STR [R] |

### 1.3 evaluate 入参（网关 → 风控）

| 字段 | 必填 | 说明 |
|------|------|------|
| order_no | 是 | 商户订单号 |
| merchant_id | 是 | |
| amount | 是 | |
| currency | 是 | |
| card_no / card_bin | 是 | 卡号或 BIN |
| card_country | 建议 | |
| ip / ip_country | 建议 | |
| email | 建议 | |
| billing_country / shipping_country | 可选 | |
| avs_result / cvv_result | 可选 | |
| three_ds / eci | 可选 | |
| website | 可选 | |
| device_fingerprint | 可选 | |
| mcc | 可选 | |

### 1.4 枚举

**risk_level**：`低风险` | `中风险` | `高风险` | `极高风险`

**order status**：`成功` | `拦截` | `失败`

> 3DS 进行中由**网关**处理，风控 evaluate 返回 `challenge_3ds`；订单列表可不单独展示「审核中」。若 doopsun 侧有 3DS 中间态，仅作通道状态，**不等同于人工审核挂起**。

---

## 2. 预警（分类型）

### 2.1 交易预警 `risk_alert`（scope = order）

单笔订单命中规则产生，**不表示订单挂起待审**。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | string | 如 AL2026060001 |
| scope | string | 固定 `order` |
| time | datetime | 预警时间 |
| merchant_id | string | |
| order_no | string | 关联订单（可空仅预警场景） |
| amount | string | 展示用 |
| risk_level | enum | |
| rule_name | string | |
| action | string | 实际执行的**交易级**策略名 |
| measure_code | string | DECLINE / ALERT_ONLY / CHARGEBACK_INQUIRY 等 |
| hit_details | json | |
| status | enum | 待处理 / 处理中 / 已关闭 |
| handle_remark | text | |
| inquiry_desc | text | 调单说明 |
| inquiry_attachments | json | |
| str_report_id | string | |
| operator_id | int | |
| handled_at | datetime | |

### 2.2 商户预警 `risk_merchant_alert`（scope = merchant）

商户级异常与**商户人工审核**工单（交易量暴涨/暴跌、存续风险等）。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | string | 如 MA2026060001 |
| scope | string | 固定 `merchant` |
| merchant_id | string | |
| merchant_name | string | |
| trigger_type | enum | 交易量暴涨 / 交易量暴跌 / 拒付超标 / 入网审核 / … |
| trigger_rule_id | string | 如 R026 |
| trigger_metrics | json | `{today_count, avg_30d, ratio_pct, today_amount…}` |
| risk_level | enum | |
| status | enum | 待处理 / 处理中 / 已关闭 |
| trading_paused | bool | 是否已暂停交易 |
| settlement_paused | bool | 是否已暂停结算 |
| review_decision | enum | 审核通过 / 维持暂停 / 误报关闭 |
| handle_remark | text | |
| operator_id | int | |
| handled_at | datetime | |
| created_at | datetime | |

**展示**：预警中心需分 Tab 或类型筛选「交易预警 / 商户预警」，或商户预警独立入口在商户列表。

---

## 3. 评估结果 `risk_order_evaluation`

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | PK |
| order_no | string | 商户订单号 |
| doopsun_order_id | string | 通道单号，可选 |
| merchant_id | string | |
| risk_level | enum | |
| action | string | 策略名 |
| measure_code | string | |
| decision | string | pass / decline / challenge_3ds（**无 manual_review**） |
| evaluated_at | datetime | |

## 4. 命中明细 `risk_order_hit`

| 字段 | 类型 | 说明 |
|------|------|------|
| evaluation_id | bigint | FK |
| rule_id | string | R001… |
| rule_name | string | |
| risk_level | enum | |
| measure | string | |

---

## 5. 风控规则 `risk_rule`

| 字段 | 类型 | 说明 |
|------|------|------|
| rule_id | string | R001，唯一 |
| category | string | 卡号频率、地理与制裁… |
| name | string | |
| description | text | |
| config | json | 阈值、时间窗等 |
| measure_code | string | 关联处置策略 |
| risk_level | enum | 可由策略带出 |
| enabled | bool | |
| sort | int | |

---

## 6. 处置策略 `risk_disposition`

| 字段 | 类型 | 说明 |
|------|------|------|
| code | string | DECLINE… |
| name | string | 中文名 |
| description | text | |
| risk_level | enum | |
| scope | enum | **交易级** / **商户级** |
| priority | int | 越小越优先 |
| is_block | bool | |
| push_alert | bool | |
| extra_config | json | 限额、结算天数等 |
| status | enum | 启用 / 停用 |

---

## 7. 黑名单 `risk_blacklist`

| 字段 | 类型 | 说明 |
|------|------|------|
| id | string | BL001 |
| type | enum | IP/邮箱/卡号/国家/网站/手机号（库内英文：ip/email/card/country/website/phone） |
| value | string | |
| reason | text | |
| risk_level | enum | |
| effective_date | date | |
| expiry | date \| null | null=长期 |
| status | enum | 生效中 / 已失效 |

---

## 8. 商户 `merchant`（列表）

| 字段 | 来源 | 说明 |
|------|------|------|
| merchant_id | [D]/[E] | |
| name | [D]/[E] | |
| industry | [E] | 跨境电商、虚拟商品… |
| country | [E] | 注册国家 |
| onboard_date | [D] | 入网时间 |
| risk_score | [R] | 0–100 |
| risk_level | [R] | |
| chargeback_rate | [D] | % |
| fraud_rate | [D] | % |
| refund_rate | [D] | % |
| last_assess_time | [R] | |
| today_count / today_amount | [D] | 当日统计 |
| review_status | [R] | 批准入驻/条件通过/拒绝入驻 |
| status | [D] | 正常/观察/受限/暂停/未开通 |
| trading_paused | [R] | 是否暂停交易（doopsun 同步） |
| settlement_paused | [R] | 是否暂停结算 |
| kyc_score | [E] | |
| website_status | [E] | compliant/unverified/mismatch |
| pci_level | [E] | L1/L2/SAQ-A/NONE |
| compliance_hits | [E] | 筛查命中数 |

---

## 9. 入网申请 `onboarding`

| 字段 | 说明 |
|------|------|
| app_id | OB202606001 |
| merchant_id | 预分配或同步 |
| sync_time | 同步时间 |
| name, industry, country, website | 基本信息 |
| kyc_status | PASS/REJECT/REVIEW/PENDING |
| kyc_score | |
| risk_score, risk_level | 评估结果 |
| status | 待审核/已通过/已拒绝 |
| assess_time, review_time | |
| attachments[] | id, category, name, size, upload_time, status, type |
| assess_details[] | 维度评分明细 |

---

## 10. STR 报送 `risk_str_report`

| 字段 | 说明 |
|------|------|
| id | STR202606001 |
| type | LTR / STR |
| trigger_mode | auto / manual |
| merchant_id, merchant_name | |
| order_no | |
| currency, amount_val, amount | |
| trigger_reason | |
| suspicious_desc | STR 必填 |
| create_time, submit_time | |
| status | 待确认/已生成/已上传/已提交监管/无需上报/已归档/已退回 |
| submitter, reviewer | |
| review_remark, dismiss_reason | |
| attachments[] | name, size, upload_time, type, auto |
| linked_alert_id | |
| hit_rule_ids | json |

**LTR 默认阈值**：USD 10000，HKD 50000

---

## 11. EDD `risk_edd_case`

| 字段 | 说明 |
|------|------|
| id | EDD202606001 |
| merchant_id, merchant_name | |
| trigger | 触发原因 enum |
| risk_level | |
| status | 待启动/资料收集中/审核中/已通过/未通过/已过期 |
| deadline | |
| assignee | |
| progress | 0–100 |
| checklist | json，7 项 key→bool |
| notes | |
| linked_str_id | |

---

## 12. 用户 / 角色

### 用户 `sys_user`

| 字段 | 说明 |
|------|------|
| id, account, name | account: 小写+数字+下划线 3–32 |
| dept | 风控管理部、合规部… |
| title | 岗位 |
| phone, email | |
| status | 启用/停用/锁定 |
| last_login, updated | |
| remark | |

### 角色 `sys_role`

| 字段 | 说明 |
|------|------|
| id, code, name | code: 大写+下划线 |
| type | 内置/自定义 |
| status | 启用/停用 |
| sort, desc | |
| perms | string[] 权限 ID |
| user_ids | 关联用户 |

### 权限点 ID 清单

**监控中心**

- `dashboard:view`
- `orders:view`, `orders:export`, `orders:release`
- `alerts:view`, `alerts:handle`

**商户管理**

- `onboarding:view`, `onboarding:review`
- `merchants:view`, `merchants:edit`
- `merchant-risk:view`, `merchant-risk:edit`
- `merchant-risk-level:edit`

**合规报送**

- `str:view`, `str:confirm`, `str:push-rules`
- `edd:view`, `edd:manage`

**规则配置**

- `rules:view`, `rules:edit`
- `disposition:view`, `disposition:edit`
- `blacklist:view`, `blacklist:edit`

**系统管理**

- `users:view`, `users:edit`
- `roles:view`, `roles:edit`
- `audit-logs:view`, `audit-logs:export`

---

## 13. 审计日志 `audit_log`

| 字段 | 说明 |
|------|------|
| id | LOG20260622001 |
| time, ts | |
| module | 登录认证、订单监控、预警处置… |
| action | 登录、新增、修改、删除、审核、处置、导出、配置变更、系统事件 |
| level | INFO / WARN / ERROR |
| operator | 用户名或 SYSTEM |
| target | 对象 ID |
| summary | 摘要 |
| result | 成功 / 失败 |
| ip | |
| detail | JSON |

---

## 14. 商户评估配置 `merchant_assess_config`（JSON 存储）

| 键 | 说明 |
|----|------|
| thresholds | lowMax, midMax, chargebackWarn, chargebackId, fraudWarn, kycLow, refundWarn |
| dimensions[] | key, name, weight |
| industryScores | 行业→维度分 |
| geoScores | 国家→维度分 |
| levelPolicies | low/mid/high 权益 |
| chargebackMul, fraudMul, refundMul | 公式系数 |
| tenureTiers, volumeAnomalyTiers | 分档 |

**入网排除维度**：chargeback, fraud, refund, volumeAnomaly

---

## 15. STR 推送配置 `str_push_config`（JSON）

| 键 | 说明 |
|----|------|
| enabled | 总开关 |
| push_by_risk_level | |
| risk_levels | string[] |
| push_ltr | |
| ltr_threshold_usd / hkd | |
| rule_push | { R001: true, … } |
