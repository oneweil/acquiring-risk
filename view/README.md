# 视图目录约定

```
view/
  login/                 # 登录页（admin 外，不走 Auth）
  admin/
    layout/              # 后台公共布局（Tabler）
      admin.html
      sidebar.html
      theme-init.html
      list/              # 列表页公共片段（include，勿多层 extend）
        header.html
        footer.html
    page.html            # 占位页
    dashboard/index.html # 风控总览（待办/决策/来源/姿态/合规，Mock）
    order/index.html     # 业务页（直接 extend admin 布局）
```

静态资源：
- UI：`/static/tabler/`（css / js / icons）
- 图表：`/static/apexcharts/apexcharts.min.js`
- 微调：`/static/css/custom.css`
- 列表详情：`/static/js/admin/list-page.js`
- 订单列表：`/static/js/admin/order.js`（AJAX `/admin/order/list`）
- 风控总览：`/static/js/admin/dashboard.js`

模板约定：
- 业务页只 `{extend name="/admin/layout/admin" /}` 一层
- 列表公共结构用 `{include file="/admin/layout/list/header" /}` / `footer`
- 路径统一以 `/` 开头，相对 `view/` 根目录
- 主题：`data-bs-theme` 跟随系统深浅色（storage key: `theme`）
