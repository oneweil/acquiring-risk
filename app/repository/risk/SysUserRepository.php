<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\SysRole;
use app\model\SysRoleUser;
use app\model\SysUser;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Paginator;

class SysUserRepository
{
    /**
     * @param array{keyword?: string, status?: string, role_id?: int} $filters
     *
     * @throws DbException
     */
    public function search(array $filters, ?int $page = null, ?int $pageSize = null): Paginator
    {
        $query = SysUser::order('id', 'desc');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['keyword'])) {
            $like = '%' . addcslashes((string) $filters['keyword'], '%_\\') . '%';
            $query->where(function ($q) use ($like): void {
                $q->whereLike('account', $like)
                    ->whereLike('name', $like, 'OR')
                    ->whereLike('phone', $like, 'OR')
                    ->whereLike('email', $like, 'OR');
            });
        }

        if (isset($filters['role_id'])) {
            $userIds = SysRoleUser::where('role_id', (int) $filters['role_id'])->column('user_id');
            $userIds = array_map('intval', $userIds);
            if ($userIds === []) {
                $query->whereRaw('1=0');
            } else {
                $query->whereIn('id', $userIds);
            }
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
     * @return array{total: int, enabled: int, with_role: int, inactive: int}
     *
     * @throws DbException
     */
    public function stats(): array
    {
        $total    = (int) SysUser::count();
        $enabled  = (int) SysUser::where('status', SysUser::STATUS_ENABLED)->count();
        $inactive = (int) SysUser::whereIn('status', [SysUser::STATUS_DISABLED, SysUser::STATUS_LOCKED])->count();
        $withRole = (int) \think\facade\Db::name('sys_role_user')
            ->group('user_id')
            ->count();

        return [
            'total'     => $total,
            'enabled'   => $enabled,
            'with_role' => $withRole,
            'inactive'  => $inactive,
        ];
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?SysUser
    {
        return SysUser::find($id);
    }

    /**
     * @throws DbException
     */
    public function findByAccount(string $account): ?SysUser
    {
        return SysUser::where('account', $account)->find();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): SysUser
    {
        return SysUser::create($data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): SysUser
    {
        $model = SysUser::find($id);
        if ($model === null) {
            throw new ModelNotFoundException('用户不存在');
        }
        $model->save($data);

        return $model;
    }

    /**
     * @throws DbException
     */
    public function existsAccount(string $account, ?int $excludeId = null): bool
    {
        $query = SysUser::where('account', $account);
        if ($excludeId !== null) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->count() > 0;
    }

    /**
     * @param list<int> $userIds
     * @return array<int, list<array{id: int, code: string, name: string}>>
     *
     * @throws DbException
     */
    public function rolesMapByUserIds(array $userIds): array
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if ($userIds === []) {
            return [];
        }

        $links = SysRoleUser::whereIn('user_id', $userIds)->select();
        $roleIds = [];
        foreach ($links as $link) {
            $roleIds[] = (int) $link->role_id;
        }
        $roleIds = array_values(array_unique($roleIds));
        if ($roleIds === []) {
            return [];
        }

        $roles = SysRole::whereIn('id', $roleIds)->field('id,code,name')->select();
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[(int) $role->id] = [
                'id'   => (int) $role->id,
                'code' => (string) $role->code,
                'name' => (string) $role->name,
            ];
        }

        $result = [];
        foreach ($links as $link) {
            $uid = (int) $link->user_id;
            $rid = (int) $link->role_id;
            if (!isset($roleMap[$rid])) {
                continue;
            }
            $result[$uid][] = $roleMap[$rid];
        }

        return $result;
    }

    /**
     * @return list<int>
     *
     * @throws DbException
     */
    public function roleIdsOfUser(int $userId): array
    {
        return array_map('intval', SysRoleUser::where('user_id', $userId)->column('role_id'));
    }

    /**
     * 分配角色弹窗：启用中的角色列表
     *
     * @return list<SysRole>
     *
     * @throws DbException
     */
    public function enabledRoles(): array
    {
        return SysRole::where('status', SysRole::STATUS_ENABLED)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->all();
    }
}
