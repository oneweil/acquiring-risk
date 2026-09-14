---
name: pr-self-review
description: >-
  PR-oriented self-review for doopsun-risk. Reviews branch or uncommitted diff
  for architecture regressions, API/docs drift, security hygiene, and a test
  plan; drafts a PR summary. Use when the user says pr-self-review, PR 自检,
  开 PR 前审查, review my changes for PR, or after ship-check before opening a PR.
disable-model-invocation: true
---

# pr-self-review（开 PR 前自检）

用户显式调用时执行。默认审 **相对默认基线的 branch changes**（含已提交 + 工作区未提交）；若用户只要未提交改动，则只审 working tree。

可与 `/ship-check` 串联：先 ship-check 再本 skill。内置 `/review-bugbot` / `/review-security` 可作为补充，不替代本清单。

## 步骤

1. **收集 diff**  
   - `git status`、`git log`（相对基线的提交列表）、完整 diff  
   - 按文件类型分组：PHP / migration / view+js / docs / config

2. **正确性与回归**  
   - 变更是否完成用户目标；有无半截接口、死路由、未挂 sidebar  
   - evaluate / upsert / alert / blacklist / disposition 行为是否与规则一致  
   - 错误路径：校验失败、未知商户、暂停收单 → 是否仍 `decline` 等预期

3. **架构与数据**（同 ship-check 硬边界，写成 PR 风险项）  
   - 双库、无跨库 JOIN、本地商户投影、预警挂订单、无主站自动回调

4. **API / Admin / 文档**  
   - 对外字段英文枚举 + `*_label`；Admin JSON `code:0` 约定  
   - 路由长短顺序；`pageSize` 仅 10/20/50（若动列表）  
   - `docs/` 是否与实现同步；不同步则要求先补再合入

5. **安全与密钥**  
   - 无 `.env`、密钥、真实卡号/PII 进入 diff  
   - 无危险 raw SQL 拼接；后台写操作有校验场景

6. **可测性**  
   - 列出手工验收步骤（接口路径、后台菜单、关键数据）  
   - 注明未覆盖风险

7. **输出**（固定模板）

```markdown
## PR self-review

**基线**: … → HEAD  
**结论**: Ready | Ready with nits | Not ready

### 发现（按严重度）
1. **[blocker]** …
2. **[major]** …
3. **[nit]** …

### 测试计划
- [ ] …
- [ ] …

### 建议 PR 描述
## Summary
- …

## Test plan
- [ ] …
```

若结论为 Ready 且用户要求创建 PR，再按仓库流程 `gh pr create`；否则只输出审查结果。

## 不要做

- 不把审查写成空泛「LGTM」；每条发现指出文件/行为  
- 不强制改代码（除非用户要求「顺手修」且为明确小修）  
- 未要求时不 push、不开 PR
