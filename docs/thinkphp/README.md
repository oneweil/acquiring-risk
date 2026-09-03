# ThinkPHP 8 文档知识库（本地）

> **权威来源**：[ThinkPHP 8 官方手册](https://doc.thinkphp.cn/v8_0/preface.html)（`doc.thinkphp.cn`）  
> **本目录作用**：把手册变成仓库内可索引 Markdown，供 Cursor Agent / `@docs/thinkphp` 检索。官方站点多为前端渲染，直接抓取网页不稳定。

## 来源说明

| 项 | 说明 |
|----|------|
| 官方 | https://doc.thinkphp.cn/v8_0/ |
| 本地正文 | [`v8/`](./v8/) 共约 117 篇 Markdown |
| 镜像来源 | 社区提取稿 [MacAndTea/ThinkPHP8xDoc](https://github.com/MacAndTea/ThinkPHP8xDoc)（非官方仓库，内容来自官网） |
| 项目版本 | 本仓库使用 **ThinkPHP 8.x**（以 `composer.lock` 为准） |

有冲突时：**以官方在线手册为准**；本地用于 Agent 检索与离线对照。

## Agent / 开发者怎么用

1. 涉及框架 API 时先 `@docs/thinkphp` 或打开下表对应文件，再写代码。  
2. 不要整本贴进 `.cursor/rules`；rules 只保留短约束（见 `.cursor/rules/thinkphp-docs.mdc`）。  
3. 业务约定仍在上级 [`docs/README.md`](../README.md)（01–07），与框架手册分开。

## 常用章节速查（本仓库高频）

| 主题 | 本地文件 | 官方入口（同名语义） |
|------|----------|----------------------|
| 序言 / 总览 | — | [preface](https://doc.thinkphp.cn/v8_0/preface.html) |
| 开发规范 | [v8/DevelopmentSpecification.md](./v8/DevelopmentSpecification.md) | development_specifications |
| 目录结构 | [v8/DirectoryStructure.md](./v8/DirectoryStructure.md) | |
| 配置 | [v8/Configuration.md](./v8/Configuration.md) | |
| 路由 | [v8/RouteDefinition.md](./v8/RouteDefinition.md)、[ResourceRouting.md](./v8/ResourceRouting.md) | |
| 控制器 | [v8/ControllerDefinition.md](./v8/ControllerDefinition.md)、[BasicController.md](./v8/BasicController.md) | |
| 请求 / 响应 | [v8/RequestObject.md](./v8/RequestObject.md)、[ResponseOutput.md](./v8/ResponseOutput.md) | |
| 数据库连接 | [v8/ConnectToTheDatabase.md](./v8/ConnectToTheDatabase.md) | |
| 查询构造器 | [v8/QueryConstructor.md](./v8/QueryConstructor.md)、[QueryBuilder*.md](./v8/) | |
| **分页查询** | [v8/QueryBuilderPagedQuery.md](./v8/QueryBuilderPagedQuery.md) | [pagination_query](https://doc.thinkphp.cn/v8_0/pagination_query.html) |
| 模型 | [v8/Model.md](./v8/Model.md)、[ModelQuery.md](./v8/ModelQuery.md)、[ModelModelOutput.md](./v8/ModelModelOutput.md) | |
| 验证 | [v8/Verification.md](./v8/Verification.md) | |
| 数据库迁移 | [v8/DatabaseMigrationTool.md](./v8/DatabaseMigrationTool.md) | [think-migration](https://doc.thinkphp.cn/v8_0/think-migration.html) |
| 命令行 | [v8/CommandLine.md](./v8/CommandLine.md) | |
| 中间件 | [v8/Middleware.md](./v8/Middleware.md) | |
| 异常 | [v8/ExceptionHandling.md](./v8/ExceptionHandling.md) | |

完整文件列表见 `v8/` 目录；文件名多为英文驼峰，对应官网中文章节。

## 更新方式

官方改版后可重新同步镜像，例如：

```bash
git clone --depth 1 https://github.com/MacAndTea/ThinkPHP8xDoc.git /tmp/tp8doc
# 仅复制 *.md 覆盖 docs/thinkphp/v8/
```

或手工对照 [官方手册](https://doc.thinkphp.cn/v8_0/preface.html) 增补本目录摘要。
