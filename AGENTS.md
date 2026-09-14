# AGENTS.md — doopsun-risk 开工缰绳

业务真相在 `docs/`，硬边界在 `.cursor/rules/risk-architecture.mdc`，可复用流程在 `.cursor/skills/`。根 `README.md` 勿当业务说明。

## 先读什么（按任务选，勿整仓灌进上下文）

| 你要做的事 | 先打开 |
|------------|--------|
| 任意改动（默认） | 本文件 + `risk-architecture`（自动注入） |
| 页面 / 路由 | `docs/01-pages.md` |
| 状态机 / evaluate / 预警 | `docs/02-workflows.md` |
| 字段与枚举 | `docs/03-data-dictionary.md` |
| API | `docs/04-apis.md` |
| 交易规则目录 | `docs/05-rules-catalog.md` |
| 双库与表 | `docs/06-architecture.md`（冲突以 `risk-architecture` 为准） |
| 竖切节奏 | `docs/07-solo-dev-roadmap.md` |
| ThinkPHP | `@docs/thinkphp`，勿整本粘贴 |
| 后台列表 CRUD | `.cursor/skills/admin-list-crud` |
| 交付 / PR | `/ship-check` · `/pr-self-review` |

## 工作方式

1. **任务卡先于代码**（目标 / 范围 / 不碰 / 验收；不确定标「先查再改」）。
2. **先地图再手术**：只读摸清改动面 → 方案与风险 → 用户选定后再改。
3. **一次竖切一条链路**（表 → 接口 → 必要页面）；禁止顺便铺大盘。
4. **最短有效 diff**：YAGNI；能复用不重写；不扩抽象；不擅自大重构；bug 修共享根因一处。
5. **分层**：Controller 薄 → Service 编排 → Repository 读写；后台列表对齐 Admin CRUD。
6. **行为变更同步 `docs/0x-*.md`**；交卷前显式 `/ship-check`。

硬禁止（违反即停）：见 `.cursor/rules/risk-architecture.mdc`，勿在对话里重复粘贴全文。

## 任务卡模板

```text
目标：<一句话可演示结果>
范围：<路径 / 模块>
不碰：<明确排除>
约束：双库边界；Controller→Service→Repository；订单无挂起待人审
验收：<Postman / 页面字段 / 命令>
先查：<不确定点；查清再改>
```
