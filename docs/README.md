# 国际信用卡收单风控系统 — 可开发文档

> 由原型 `xsdfk.html` 翻译整理，供 `doopsun-risk` 项目开发使用。  
> 原型为单页 HTML + Mock 数据，本文档提取其中的**页面、流程、字段、接口**约定，并标注与当前代码库的对应关系。

## 文档索引

| 文档 | 说明 |
|------|------|
| [01-pages.md](./01-pages.md) | 页面清单、路由、优先级、实现状态 |
| [02-workflows.md](./02-workflows.md) | 核心业务流程与状态机 |
| [03-data-dictionary.md](./03-data-dictionary.md) | 各模块字段、枚举、数据来源 |
| [04-apis.md](./04-apis.md) | 对内/对外 API 与后台 JSON 接口清单 |
| [05-rules-catalog.md](./05-rules-catalog.md) | 交易风控规则 R001–R042 目录 |
| [06-architecture.md](./06-architecture.md) | 架构边界与库表草案（与 doopsun 关系） |
| [07-solo-dev-roadmap.md](./07-solo-dev-roadmap.md) | **单人开发路线图**（按周任务清单） |
| [thinkphp/](./thinkphp/README.md) | **ThinkPHP 8 本地手册知识库**（供 Cursor 索引；权威见 [官网](https://doc.thinkphp.cn/v8_0/preface.html)） |

## 系统定位

- **产品**：国际信用卡收单风控后台（Visa VAMP / PSD2 SCA / AML 合规场景）
- **技术栈**：ThinkPHP + Tabler + AJAX 列表
- **关联系统**：`doopsun-master` 收单主系统；风控独立部署、独立库
- **决策入口**：收单网关调用 `POST /api/v1/risk/evaluate`；订单**无挂起**，按处置策略 `is_block` 同步返回 `pass` / `decline` / `challenge_3ds`
- **预警**：一律挂订单（`risk_alert`，`order_no` 必填）；是决策后的记录/调单，不参与放行；不按卡号/IP/邮箱/商户拆人审队列
- **商户侧动作**：`SUSPEND_MERCHANT` 等改商户收单状态/回调主站；**不建** `risk_merchant_alert` 人审表
- **入网审核 / 开通收单在主站**；风控只做评分/定级与投影接收
- **商户档案**：主站主动 `POST /api/v1/merchant/upsert` 推送投影；禁止直读 `doopsun_merchants`

## 开发优先级（建议）

| 阶段 | 范围 | 交付标准 |
|------|------|----------|
| **P0** | evaluate → 订单监控 → 交易预警 → 规则/黑名单 | evaluate 可调、订单见风险结果与预警 |
| **P1** | 商户列表、评估规则、真实用户登录 / 用户角色 | 商户可评；用户+角色薄 RBAC 已落地，审计日志可后置 |
| **P2** | STR / EDD / 总览真实 KPI | 合规工作流可跑通 |

## 当前实现进度（截至文档生成时）

| 模块 | 路由 | 状态 |
|------|------|------|
| 风控总览 | `/admin/dashboard` | UI 完成，数据 Mock |
| 订单监控 | `/admin/order` | UI + AJAX 列表，Mock 数据 |
| 预警中心 | `/admin/alert` | 交易预警已实现（Seed 演示）；预警一律挂订单 |
| 规则/处置/黑名单等 | 各 `/admin/*` | 占位页 |
| 评估 API | `POST /api/v1/risk/evaluate` | 骨架，未接规则引擎 |

## 业务规则约定（与原型差异，以本表为准）

| 规则 | 说明 |
|------|------|
| **订单无挂起** | evaluate 只同步返回能否继续；无「挂起 → 人审 → 放行」 |
| **阻断看处置策略** | `is_block=1` → `decline`；`3DS_CHALLENGE` → `challenge_3ds`；其余非阻断 → `pass`（可写预警） |
| **预警一律挂订单** | 仅 `risk_alert`；`push_alert` 决定是否写入；调单/关闭不改变历史授权终态 |
| **不建 merchant_alert** | 商户级策略只做状态动作；卡号/IP/邮箱用黑名单 + 订单规则，不另建人审表 |
| **商户数据** | 主站推送 `risk_merchant`；评估写入 `risk_merchant_assessment` |
| **交易量异常** | 风控发现并记录/预警；**暂停收单结算由人工在主站处理**（无自动 callback） |
| 原型 `MANUAL_REVIEW` | **不采用**；种子/规则映射为拒绝、3DS 或仅预警 |

## 原型与项目差异

| 项 | 原型 | 当前项目 |
|----|------|----------|
| 商户入网菜单 | 有 `onboarding` | **不实现**；入网审核在主站，风控仅接收档案并评分 |
| 订单「人工审核/审核中」 | 订单挂起待审 | **不做**；无挂起，同步 pass/decline/3ds |
| 订单 3DS 枚举 | 通过 / 未认证 / 未验证 | Mock 使用 通过 / 未通过 / 未参与 |
| 分页大小 | 10 条/页 | 与 config 一致，默认 10 |

## 使用方式

1. 做某一页面前，先读 **01-pages** 确认范围，再读 **03-data-dictionary** 对齐字段。
2. 做 evaluate / 预警 / STR 时，对照 **02-workflows** 状态流转。
3. 前后端联调时，按 **04-apis** 定 URL 与 JSON 结构。
4. 规则引擎实现时，以 **05-rules-catalog** 为初始规则集。
5. 建表与读 doopsun 时，参考 **06-architecture**。
6. **单人开发**按 **[07-solo-dev-roadmap](./07-solo-dev-roadmap.md)** 执行；用户/角色已可用，勿再优先做审计日志全套。
7. 写 ThinkPHP 框架代码时，先查 **[thinkphp/](./thinkphp/README.md)**（或对话里 `@docs/thinkphp`），细则不要塞进 `.cursor/rules`。
