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

单笔订单命中规则产生，**不表示订单挂起待审**。物理表逻辑名 `alert`（前缀后 `risk_alert`）。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| alert_no | string(32) | 业务编号，如 AL2026060001（UNIQUE） |
| scope | string | 固定 `order` |
| alerted_at | datetime | 预警时间 |
| merchant_id | string | |
| order_no | string | 关联订单（可空） |
| amount_display | string | 金额展示串，如 `USD 1,250.00` |
| risk_level | enum | 英文：`low` / `mid` / `high` / `critical` |
| rule_name | string | 主命中规则名称快照 |
| measure_code | string | `DECLINE` / `ALERT_ONLY` / `CHARGEBACK_INQUIRY` 等 |
| action_name | string | 策略名称快照 |
| hit_details | json | `[{id,name,risk_level,measure}]` |
| status | enum | 英文：`pending` / `processing` / `closed`（待处理/处理中/已关闭） |
| handle_remark | string | 处理备注 |
| inquiry_desc | text | 调单说明 |
| evaluation_id | bigint | 关联 `order_evaluation.id`（可空） |
| str_report_id | string | 关联 STR 报送编号（可空） |
| operator_id | int | 处置人（可空） |
| handled_at | datetime | |
| created_at / updated_at | datetime | |

#### 2.1.1 调单附件 `risk_alert_attachment`

逻辑表名 `alert_attachment`。调单材料存独立附件表（非 JSON 内嵌）。

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | PK |
| alert_id | bigint | 关联 `alert.id` |
| file_name | string | 原始文件名 |
| file_size | int | 字节 |
| storage_path | string | 相对 public 盘路径，如 `alert/20260909/xxx.pdf` |
| file_type | string | pdf / jpg / png / doc / docx / zip |
| uploaded_at | datetime | |
| created_at / updated_at | datetime | |

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
| enabled | bool | |
| push_str | bool | 命中后是否自动推送 STR |
| sort | int | |

风险等级由关联处置策略带出，非本表字段。

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

## 10. STR 报送

### `risk_str_report`（逻辑表 `str_report`）

| 字段 | 类型 | 空 | 默认 | 注释 |
|------|------|----|------|------|
| id | bigint unsigned PK | NO | — | 主键 |
| report_no | string(32) UNIQUE | NO | — | 业务编号，如 STR20260909001 |
| type | string(8) | NO | — | ltr / str |
| trigger_mode | string(16) | NO | manual | auto / manual |
| merchant_id | string(32) | NO | — | 商户号 |
| merchant_name | string(128) | NO | '' | 商户名称快照 |
| order_no | string(64) | NO | — | 关联订单号 |
| currency | string(8) | NO | USD | USD / EUR / GBP / HKD |
| amount_val | decimal(18,2) | NO | 0 | 交易金额数值 |
| amount_display | string(64) | NO | '' | 金额展示串 |
| trigger_reason | string(512) | NO | '' | 触发原因 |
| suspicious_desc | text | YES | NULL | 可疑描述（type=str 业务必填） |
| status | string(32) | NO | generated | pending_confirm / generated / uploaded / submitted / dismissed / archived / rejected |
| submitter | string(64) | YES | NULL | 报送人 |
| reviewer | string(64) | YES | NULL | 审核人 |
| review_remark | string(1000) | YES | NULL | 确认上报说明 |
| dismiss_reason | string(1000) | YES | NULL | 无需上报理由 |
| reject_reason | string(1000) | YES | NULL | 退回原因 |
| linked_alert_id | string(64) | YES | NULL | 关联预警编号 |
| hit_rule_ids | text/json | YES | NULL | 命中规则编号 JSON 数组 |
| reviewed_at | datetime | YES | NULL | 审核时间 |
| submitted_at | datetime | YES | NULL | 提交监管时间 |
| created_at / updated_at | datetime | NO | — | |

**LTR 默认阈值**（推送配置）：USD 10000，HKD 50000

### `risk_str_attachment`（逻辑表 `str_attachment`）

| 字段 | 类型 | 空 | 默认 | 注释 |
|------|------|----|------|------|
| id | bigint unsigned PK | NO | — | 主键 |
| str_report_id | bigint unsigned | NO | — | 关联 str_report.id |
| file_name | string(255) | NO | — | 原始文件名 |
| file_size | int unsigned | NO | 0 | 字节 |
| storage_path | string(512) | NO | — | public 盘相对路径 |
| file_type | string(16) | NO | xml | xml / pdf / zip / xlsx |
| is_auto | bool | NO | 0 | 系统自动生成 |
| uploaded_at | datetime | NO | — | 上传时间 |
| created_at / updated_at | datetime | NO | — | |

---

## 11. EDD

### `risk_edd_case`（逻辑表 `edd_case`）

| 字段 | 说明 |
|------|------|
| id | bigint PK |
| case_no | 业务编号，如 EDD20260908001（唯一） |
| merchant_id, merchant_name | 商户号；名称创建时快照 |
| trigger | 英文：high_risk_merchant / suspicious_txn / pep_sanction / high_risk_industry / volume_anomaly / str_link |
| risk_level | 快照 low / mid / high |
| status | pending / collecting / reviewing / passed / rejected / expired |
| deadline | date |
| assignee | 负责人，可空 |
| progress | 0–100（按勾选项附件齐备度计算） |
| checklist | json，7 项 key→bool：ubo/source/business/site/bank/pep/visit |
| notes | 备注 |
| linked_str_id | 关联 STR 编号（展示用，可空） |
| review_remark, reviewed_at | 审核备注与时间 |
| created_at, updated_at | |

### `risk_edd_attachment`（逻辑表 `edd_attachment`）

| 字段 | 说明 |
|------|------|
| id | bigint PK |
| edd_case_id | 关联工单 |
| checklist_key | 清单项 key |
| file_name, file_size, storage_path | 原始名、字节数、public 盘相对路径 |
| file_type | pdf / img / doc / zip |
| uploaded_at, created_at, updated_at | |

---

## 12. 用户 / 角色

> 薄 RBAC：无 `sys_permission` 主表；权限码固定于 `app/support/PermissionCatalog.php`，角色权限存 `sys_role_permission.perm_code`。无部门字段。

### 用户 `sys_user`

| 字段 | 说明 |
|------|------|
| id, account, name | account: 小写+数字+下划线 3–32 |
| password | `password_hash` 哈希，不对外返回 |
| title | 岗位 |
| phone, email | |
| status | enabled / disabled / locked（UI：启用/停用/锁定） |
| last_login_at, remark | |
| created_at, updated_at | |

### 角色 `sys_role`

| 字段 | 说明 |
|------|------|
| id, code, name | code: 大写+下划线 |
| type | builtin / custom（内置/自定义） |
| status | enabled / disabled |
| sort, description | |
| created_at, updated_at | |

### 关联表

| 表 | 说明 |
|----|------|
| `sys_role_user` | role_id + user_id |
| `sys_role_permission` | role_id + perm_code（固定权限码字符串） |

内置角色：`SYS_ADMIN`（全权限）、`AUDITOR`（只读集）不可删除。

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

## 15. STR 推送配置 `risk_str_push_config`（单行全局）

按规则开关在 `risk_rule.push_str`，本表仅存全局策略。

| 字段 | 说明 |
|------|------|
| enabled | STR 自动推送总开关 |
| push_by_risk_level | 是否按综合风险等级推送 |
| risk_levels | string[]（英文枚举，如 high/critical） |
| push_ltr | 大额 LTR 自动推送 |
| ltr_threshold_usd / hkd | LTR 金额阈值 |
