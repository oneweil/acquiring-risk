<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use think\response\View;

/**
 * 后台受保护区基类（鉴权由路由组 middleware 挂载，此处不重复声明）
 */
abstract class AdminBase extends BaseController
{
    protected string $menuKey = '';

    protected string $pageTitle = '';

    /**
     * 占位页（尚未实现的菜单）
     */
    protected function renderPage(): View
    {
        return view('/admin/page', $this->layoutData());
    }

    /**
     * 标准列表页渲染（路径相对 view/ 根目录，如 /admin/order/index）
     *
     * @param array<string, mixed> $data
     */
    protected function renderList(string $view, array $data = []): View
    {
        return view($view, array_merge($this->layoutData(), $data));
    }

    /**
     * @return array<string, mixed>
     */
    protected function layoutData(): array
    {
        return [
            'menu'     => $this->menuKey,
            'title'    => $this->pageTitle,
            'username' => session('admin_user.username') ?: '管理员',
        ];
    }

    /**
     * 从 GET 读取列表筛选参数（标准命名）
     *
     * @param list<string> $keys
     * @return array<string, string>
     */
    protected function listFilters(array $keys): array
    {
        $filters = [];
        foreach ($keys as $key) {
            $filters[$key] = trim((string) $this->request->get($key, ''));
        }

        return $filters;
    }

    protected function listPage(): int
    {
        return max(1, (int) $this->request->get('page', 1));
    }

    /**
     * 假数据分页（无 DB 时用 Paginator::make；有 DB 后改为 Model::paginate(['query' => $filters])）
     *
     * @param list<array<string, mixed>> $items
     * @param array<string, string>      $query
     */
    protected function paginateList(array $items, int $total, string $path, array $query = [], ?int $listRows = null): \think\Paginator
    {
        $listRows = $listRows ?? (int) config('paginate.list_rows', 10);
        $page     = $this->listPage();
        $class    = (string) config('paginate.type', \app\paginator\Bootstrap5::class);

        $query = array_filter($query, static fn ($value): bool => $value !== null && $value !== '');

        return $class::make($items, $listRows, $page, $total, false, [
            'path'     => $path,
            'query'    => $query,
            'var_page' => (string) config('paginate.var_page', 'page'),
        ]);
    }
}
