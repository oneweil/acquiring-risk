# 07 — 单人开发路线图

> 适用场景：一人全栈，演示登录即可，**不做完整 RBAC**，按竖切主链路交付。

## 原则（单人必守）

1. **一次只竖切一条链路**：表 → 接口 → 页面，跑通再扩。
2. **不铺 16 个页面**：占位页保持占位，别先做 UI 全套。
3. **登录用现有 demo**：`admin/admin123` 够用；操作人可先写 Session 用户名，审计日志后补。
4. **订单不挂起待人审**：evaluate 只返回 pass / decline / challenge_3ds；商户异常走商户预警。
5. **能 Mock 就 Mock**：doopsun 联调不通时，评估结果可 Postman 写入，列表先 JOIN 风控库。

---

## 里程碑总览

| 阶段 | 目标 | 大约工期（参考） |
|------|------|------------------|
| M1 | evaluate 能跑 + 订单见评估结果 | 3～5 天 |
| M2 | 规则/黑名单可配 + evaluate 读库 | 3～4 天 |
| M3 | 交易预警 + 调单（无订单人审） | 2～3 天 |
| M4 | 商户放量 job + 商户预警 + 暂停回调（可先 Mock doopsun） | 3～5 天 |
| M5 | 总览接真 KPI | 1～2 天 |
| 以后 | 用户角色、STR/EDD、商户评估 | 按需 |

---

## M1：evaluate + 订单监控真数据（最先做）

**交付标准**：Postman 调 `POST /api/v1/risk/evaluate` 后，订单监控列表能看到 `risk_level`、`hit_rule`、`action`。

### 任务清单

- [ ] **1.1** 风控库建表（最少 2 张）
  - `risk_order_evaluation`
  - `risk_order_hit`
  - SQL 见 [06-architecture.md](./06-architecture.md)

- [ ] **1.2** 实现 `RiskEvaluateService`（硬编码即可）
  - 黑名单：IP / 卡号 / 邮箱（可先查 `risk_blacklist` 或内存数组）
  - 规则 3～5 条：R001 制裁、R002 黑名单、R003 CVV、R013 大额、R016 IP 不一致
  - 输出：decision、risk_level、measure_code、hit_rules
  - **不要** `manual_review`

- [ ] **1.3** 接好 `app/controller/api/Risk.php` evaluate

- [ ] **1.4** `OrderRepository`（或 Service）替代 `OrderMockService`
  - 读 `doopsun.doopsun_order` + 联 `doopsun_merchants`
  - LEFT JOIN `risk_order_evaluation`
  - 联调前：评估表手工 INSERT 也能验证列表

- [ ] **1.5** 订单页小调整
  - 筛选/状态：**去掉「审核中」**（或仅保留网关 3DS 中间态映射，不做人审）
  - Mock 数据与文档对齐

- [ ] **1.6** 自测脚本
  - Postman Collection：evaluate × 3 场景（通过、拒绝、3DS）
  - 刷新订单列表核对

**本阶段不做**：用户管理、角色权限、预警中心 UI、16 页面前端。

---

## M2：规则 + 黑名单 + 处置策略（配置化）

**交付标准**：改后台规则/黑名单后，下一次 evaluate 行为变化。

- [ ] **2.1** 表：`risk_rule`、`risk_disposition`、`risk_blacklist`
- [ ] **2.2** 种子数据：9 种处置策略（scope 区分交易级/商户级）
- [ ] **2.3** 后台「黑名单」页：第一版 CRUD（可最简单：列表 + 新增弹窗）
- [ ] **2.4** 后台「风控规则」页：列表 + 启用开关 + 改处置策略（不必一次还原原型 42 条 UI，DB 里 seed 42 条即可）
- [ ] **2.5** evaluate 改读库，不再硬编码

**本阶段仍不做**：完整处置策略编辑弹窗、STR、EDD。

---

## M3：交易预警（非订单人审）

**交付标准**：命中「仅预警/调单」时产生预警；预警中心能列表 + 关闭/调单。

- [ ] **3.1** 表：`risk_alert`（scope=order）
- [ ] **3.2** evaluate 命中且 `push_alert=true` 时写预警
- [ ] **3.3** `/admin/alert/list` + 页面（复用 order 的 AJAX 模式）
- [ ] **3.4** 处理接口：关闭、调单备注/附件（附件可先存本地目录）

**明确不做**：订单「审核通过/拒绝」类选项。

---

## M4：商户交易量异常 + 商户人工审核

**交付标准**：job 检测到暴涨/暴跌 → 写 `risk_merchant_alert` → 后台可审核 → Mock 回调恢复。

- [ ] **4.1** 表：`risk_merchant_alert`
- [ ] **4.2** 定时命令 `php think merchant:volume-check`（ThinkPHP command）
  - 从 doopsun 聚合商户日交易量 vs 近 30 日均值
  - 暴涨 ≥500% / 暴跌 ≤20%（阈值可配置）
- [ ] **4.3** 命中：写商户预警 + 调 doopsun 回调（或先写日志 Mock）
- [ ] **4.4** 预警中心 Tab「商户预警」或独立列表
- [ ] **4.5** 审核：通过 → resume_trading + resume_settlement

---

## M5：风控总览（有数据后再做）

- [ ] KPI 从 evaluate + 订单 + 预警聚合
- [ ] 实时流：最近 N 笔评估结果
- [ ] 顶栏待处理数：交易预警 + 商户预警合计

---

## 明确后置（单人阶段跳过）

| 模块 | 何时做 |
|------|--------|
| 用户管理 / 角色权限 | 多人使用或上线前；最多先做 `sys_user` 替换 demo 登录 |
| 商户列表 / 入网 / 评估规则 | 主链路稳定后（P1） |
| STR / EDD | 合规要求明确后（P2） |
| Redis Velocity 规则 | M2 之后按命中频率加 |
| 完整 42 条规则引擎 | 分批加 check 函数，不必 Day1 全上 |

---

## 每周节奏建议（参考）

### 第 1 周：M1

| 天 |  focus |
|----|--------|
| 1 | 建表 + evaluate 硬编码 + Postman 通 |
| 2 | OrderRepository 读 doopsun + JOIN |
| 3 | 订单 list/detail 接真数据，修状态枚举 |
| 4～5 | 联调、修字段映射、写 README 自测说明 |

### 第 2 周：M2 + M3 开头

| 天 | focus |
|----|--------|
| 1～2 | 规则/黑名单/处置策略表 + seed |
| 3 | 黑名单页 CRUD |
| 4 | evaluate 读库 |
| 5 | 交易预警表 + evaluate 写预警 |

### 第 3 周：M3 收尾 + M4

| 天 | focus |
|----|--------|
| 1～2 | 预警中心页面 + handle API |
| 3～5 | 商户放量 job + 商户预警 |

---

## 每天开工前 3 问

1. 今天交付的**可演示结果**是什么？（能截图/能 Postman 的那种）
2. 有没有在铺新页面而没接后端？
3. 有没有在做用户/角色/STR 而 M1 还没通？

---

## 相关文档

- 业务流程：[02-workflows.md](./02-workflows.md)
- 表结构：[06-architecture.md](./06-architecture.md)
- 接口：[04-apis.md](./04-apis.md)
