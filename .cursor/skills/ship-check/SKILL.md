---
name: ship-check
description: >-
  Pre-merge / pre-handoff quality gate for doopsun-risk. Scans the current diff
  against risk architecture boundaries, docs sync, layering, and cs-check.
  Use when the user says ship-check, 发版检查, 交付检查, 改完自检, ready to merge,
  or after finishing a feature slice before commit/PR.
disable-model-invocation: true
---

# ship-check（交付前质量闸）

仅在用户显式调用 `/ship-check`（或同义指令）时执行。对照**当前工作区 diff**做一次短报告，能修的小问题直接修，大问题只列阻塞项。

权威边界见 `.cursor/rules/risk-architecture.mdc`（勿整份复制进本 skill）。相关：`admin-ui`、`db-migration-seed`、`thinkphp`。

## 步骤

1. **定范围**  
   - `git status` + `git diff`（含 unstaged）+ 相对默认基线的 `git diff main...HEAD`（基线名按仓库实际调整）  
   - 用 1–2 句概括「改了什么 / 未改什么」

2. **架构硬边界（任一命中 → 阻塞）**  
   - 跨库 SQL JOIN，或运行时直读 `doopsun_merchants`  
   - evaluate 引入 `manual_review` / 订单挂起 / 入库 `MANUAL_REVIEW`  
   - 同步决策超出 `pass` / `decline` / `challenge_3ds`  
   - 新建卡号/IP/邮箱/商户独立预警表，或 `risk_merchant_alert`  
   - 预警不挂 `order_no`，或用预警参与同步放行  
   - 自动回调主站暂停/恢复收单  
   - 入网「批准/拒绝」队列做在风控侧

3. **分层与模块约定**  
   - Controller 无新增业务 private 方法；列表/保存走 Validate scene → Repo → Resource  
   - 禁止跨库 JOIN；关联在应用层组装  
   - Admin CRUD 对齐 `admin-list-crud`（若本次动了后台列表）  
   - Migration 逻辑表名 + `risk_` 前缀约定；有 schema 变更则有 migration

4. **文档**  
   - API/页面/表结构有行为变化 → 对照更新 `docs/04-apis.md` / `docs/01-pages.md` / 相关 docs（缺则记为阻塞或「待补」）

5. **可执行检查**  
   - 若改了 PHP：在仓库根执行 `composer cs-check`（失败则列文件；用户未要求则勿擅自 `cs-fix` 大范围改动）  
   - 有针对性测试则跑相关用例；没有则注明「无自动化覆盖」与手工验收点

6. **输出报告**（固定模板，保持短）

```markdown
## ship-check

**范围**: …
**结论**: PASS | PASS_WITH_WARNINGS | BLOCKED

### 阻塞
- …

### 警告
- …

### 已核对
- [ ] 架构边界
- [ ] 分层 / CRUD
- [ ] docs
- [ ] cs-check / 手工验收

### 建议下一步
- `/pr-self-review` 或开 PR / 补测 …
```

## 不要做

- 不顺手大重构、不扩 scope  
- 不把整份 `docs/` 贴进回复  
- 用户未要求时不 `git commit` / `push`
