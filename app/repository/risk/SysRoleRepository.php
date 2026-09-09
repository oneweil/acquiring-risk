<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\SysRole;
use app\model\SysRolePermission;
use app\model\SysRoleUser;
use app\model\SysUser;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Paginator;

class SysRoleRepository
{
    /**
     * @param array{keyword?: string, status?: string, type?: string} $filters
     *
     * @throws DbException
     */
    public function search(array $filters, ?int $page = null, ?int $pageSize = null): Paginator
    {
        $query = SysRole::order('sort', 'asc')->order('id', 'asc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['keyword'])) {
            $like = '%' . addcslashes((string) $filters['keyword'], '%_\\') . '%';
            $query->where(function ($q) use ($like): void {
                $q->whereLike('code', $like)
                    ->whereLike('name', $like, 'OR')
                    ->whereLike('description', $like, 'OR');
            });
        }

        $pageSize = $pageSize ?? (int) config('paginate.list_rows', 10);
        $page     = $page ?? max(1, (int) request()->param((string) config('paginate.var_page', 'page'), 1));

        return $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
            'query'     => array_filter($filters, static fn ($value): bool => $value !== null && $value !== ''),
        ]);
    }

    /**
     * @return array{total: int, enabled: int, users: int, perms: int}
     *
     * @throws DbException
     */
    public function stats(): array
    {
        return [
            'total'   => (int) SysRole::count(),
            'enabled' => (int) SysRole::where('status', SysRole::STATUS_ENABLED)->count(),
            'users'   => (int) SysRoleUser::count(),
            'perms'   => count(\app\support\PermissionCatalog::allCodes()),
        ];
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?SysRole
    {
        return SysRole::find($id);
    }

    /**
     * @throws DbException
     */
    public function findByCode(string $code): ?SysRole
    {
        return SysRole::where('code', $code)->find();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): SysRole
    {
        return SysRole::create($data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): SysRole
    {
        $model = SysRole::find($id);
        if ($model === null) {
            throw new ModelNotFoundException('角色不存在');
        }
        $model->save($data);

        return $model;
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function delete(int $id): void
    {
        $model = SysRole::find($id);
        if ($model === null) {
            throw new ModelNotFoundException('角色不存在');
        }
        $model->delete();
    }

    /**
     * @throws DbException
     */
    public function existsCode(string $code, ?int $excludeId = null): bool
    {
        $query = SysRole::where('code', $code);
        if ($excludeId !== null) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->count() > 0;
    }

    /**
     * @throws DbException
     */
    public function userCount(int $roleId): int
    {
        return (int) SysRoleUser::where('role_id', $roleId)->count();
    }

    /**
     * @param list<int> $roleIds
     * @return array<int, int>
     *
     * @throws DbException
     */
    public function userCountsByRoleIds(array $roleIds): array
    {
        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
        if ($roleIds === []) {
            return [];
        }

        $rows = SysRoleUser::whereIn('role_id', $roleIds)
            ->field('role_id, COUNT(*) AS cnt')
            ->group('role_id')
            ->select();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->role_id] = (int) $row->getAttr('cnt');
        }

        return $map;
    }

    /**
     * @param list<int> $roleIds
     * @return array<int, list<string>>
     *
     * @throws DbException
     */
    public function permCodesByRoleIds(array $roleIds): array
    {
        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
        if ($roleIds === []) {
            return [];
        }

        $rows = SysRolePermission::whereIn('role_id', $roleIds)->select();
        $map  = [];
        foreach ($rows as $row) {
            $map[(int) $row->role_id][] = (string) $row->perm_code;
        }

        return $map;
    }

    /**
     * @return list<int>
     *
     * @throws DbException
     */
    public function userIdsOfRole(int $roleId): array
    {
        return array_map('intval', SysRoleUser::where('role_id', $roleId)->column('user_id'));
    }

    /**
     * @return list<string>
     *
     * @throws DbException
     */
    public function permCodesOfRole(int $roleId): array
    {
        return array_map('strval', SysRolePermission::where('role_id', $roleId)->column('perm_code'));
    }

    /**
     * 分配用户弹窗：全部用户简表
     *
     * @return list<SysUser>
     *
     * @throws DbException
     */
    public function allUsersBrief(): array
    {
        return SysUser::field('id,account,name,title,status')
            ->order('id', 'asc')
            ->select()
            ->all();
    }
}
