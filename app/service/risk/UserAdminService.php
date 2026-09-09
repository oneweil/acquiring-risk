<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\SysUser;
use app\repository\risk\SysUserRepository;
use app\resource\SysUserResource;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;

class UserAdminService
{
    public function __construct(
        private readonly SysUserRepository $userRepo = new SysUserRepository(),
        private readonly PermissionService $permService = new PermissionService(),
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function search(array $filters, int $page, int $pageSize): array
    {
        $paginator = $this->userRepo->search($filters, $page, $pageSize);
        $ids       = [];
        foreach ($paginator as $model) {
            $ids[] = (int) $model->id;
        }
        $rolesMap = $this->userRepo->rolesMapByUserIds($ids);

        $items = [];
        foreach ($paginator as $model) {
            $id      = (int) $model->id;
            $items[] = SysUserResource::makeWithRoles($model, $rolesMap[$id] ?? [])->toArray();
        }

        $payload         = $paginator->toArray();
        $payload['data'] = $items;

        return $payload;
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return $this->userRepo->stats();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function save(int $id, array $payload): array
    {
        $isCreate = $id <= 0;
        if ($this->userRepo->existsAccount((string) $payload['account'], $isCreate ? null : $id)) {
            throw new ValidateException('登录账号已存在');
        }

        if ($isCreate) {
            if (empty($payload['password'])) {
                throw new ValidateException('请填写密码');
            }
            $model = $this->userRepo->create($payload);
        } else {
            $existing = $this->userRepo->find($id);
            if ($existing === null) {
                throw new ModelNotFoundException('用户不存在');
            }
            $model = $this->userRepo->update($id, $payload);
        }

        return $this->detail((int) $model->id);
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(int $id): array
    {
        $model = $this->userRepo->find($id);
        if ($model === null) {
            throw new ModelNotFoundException('用户不存在');
        }
        $rolesMap = $this->userRepo->rolesMapByUserIds([$id]);

        return SysUserResource::makeWithRoles($model, $rolesMap[$id] ?? [])->toArray();
    }

    public function resetPassword(int $id, string $newPassword): void
    {
        $model = $this->userRepo->find($id);
        if ($model === null) {
            throw new ModelNotFoundException('用户不存在');
        }
        $this->userRepo->update($id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toggleStatus(int $id, string $status): array
    {
        if (!in_array($status, SysUser::STATUSES, true)) {
            throw new ValidateException('状态无效');
        }
        $model = $this->userRepo->find($id);
        if ($model === null) {
            throw new ModelNotFoundException('用户不存在');
        }

        $sessionId = (int) (admin_user()['id'] ?? 0);
        if ($sessionId > 0 && $sessionId === $id && $status !== SysUser::STATUS_ENABLED) {
            throw new ValidateException('不能停用或锁定当前登录账号');
        }

        $this->userRepo->update($id, ['status' => $status]);

        return $this->detail($id);
    }

    /**
     * @param list<int> $roleIds
     * @return array<string, mixed>
     */
    public function assignRoles(int $id, array $roleIds): array
    {
        $model = $this->userRepo->find($id);
        if ($model === null) {
            throw new ModelNotFoundException('用户不存在');
        }
        $this->permService->syncUserRoles($id, $roleIds);

        return $this->detail($id);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function enabledRoleOptions(): array
    {
        $roles = $this->userRepo->enabledRoles();
        $out   = [];
        foreach ($roles as $role) {
            $out[] = [
                'id'   => (int) $role->id,
                'code' => (string) $role->code,
                'name' => (string) $role->name,
            ];
        }

        return $out;
    }
}
