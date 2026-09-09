<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\SysRole;
use app\repository\risk\SysRoleRepository;
use app\resource\SysRoleResource;
use app\support\PermissionCatalog;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;

class RoleAdminService
{
    public function __construct(
        private readonly SysRoleRepository $roleRepo = new SysRoleRepository(),
        private readonly PermissionService $permService = new PermissionService(),
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function search(array $filters, int $page, int $pageSize): array
    {
        $paginator = $this->roleRepo->search($filters, $page, $pageSize);
        $ids       = [];
        foreach ($paginator as $model) {
            $ids[] = (int) $model->id;
        }
        $userCounts = $this->roleRepo->userCountsByRoleIds($ids);
        $permMap    = $this->roleRepo->permCodesByRoleIds($ids);

        $items = [];
        foreach ($paginator as $model) {
            $id      = (int) $model->id;
            $items[] = SysRoleResource::makeDetail(
                $model,
                $userCounts[$id] ?? 0,
                $permMap[$id] ?? []
            )->toArray();
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
        return $this->roleRepo->stats();
    }

    /**
     * @return list<array{id: string, name: string, perms: list<array{id: string, name: string}>}>
     */
    public function permissionCatalog(): array
    {
        return PermissionCatalog::modules();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function save(int $id, array $payload): array
    {
        $isCreate = $id <= 0;

        if ($isCreate) {
            if ($this->roleRepo->existsCode((string) $payload['code'])) {
                throw new ValidateException('角色编码已存在');
            }
            $payload['type'] = SysRole::TYPE_CUSTOM;
            $model           = $this->roleRepo->create($payload);
        } else {
            $existing = $this->roleRepo->find($id);
            if ($existing === null) {
                throw new ModelNotFoundException('角色不存在');
            }
            // 内置角色禁止改 code / type
            if ((string) $existing->type === SysRole::TYPE_BUILTIN) {
                unset($payload['code']);
            } elseif ($this->roleRepo->existsCode((string) $payload['code'], $id)) {
                throw new ValidateException('角色编码已存在');
            }
            $model = $this->roleRepo->update($id, $payload);
        }

        return $this->detail((int) $model->id);
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(int $id): array
    {
        $model = $this->roleRepo->find($id);
        if ($model === null) {
            throw new ModelNotFoundException('角色不存在');
        }

        return SysRoleResource::makeDetail(
            $model,
            $this->roleRepo->userCount($id),
            $this->roleRepo->permCodesOfRole($id)
        )->toArray();
    }

    public function delete(int $id): void
    {
        $model = $this->roleRepo->find($id);
        if ($model === null) {
            throw new ModelNotFoundException('角色不存在');
        }
        if ((string) $model->type === SysRole::TYPE_BUILTIN) {
            throw new ValidateException('内置角色不可删除');
        }
        if ($this->roleRepo->userCount($id) > 0) {
            throw new ValidateException('角色仍绑定用户，无法删除');
        }
        $this->permService->syncRolePermissions($id, []);
        $this->roleRepo->delete($id);
    }

    /**
     * @param list<string> $permCodes
     * @return array<string, mixed>
     */
    public function savePermissions(int $id, array $permCodes): array
    {
        $model = $this->roleRepo->find($id);
        if ($model === null) {
            throw new ModelNotFoundException('角色不存在');
        }
        $this->permService->syncRolePermissions($id, $permCodes);

        return $this->detail($id);
    }

    /**
     * @param list<int> $userIds
     * @return array<string, mixed>
     */
    public function assignUsers(int $id, array $userIds): array
    {
        $model = $this->roleRepo->find($id);
        if ($model === null) {
            throw new ModelNotFoundException('角色不存在');
        }
        $this->permService->syncRoleUsers($id, $userIds);

        return $this->detail($id);
    }

    /**
     * @return array{role: array<string, mixed>, user_ids: list<int>, users: list<array<string, mixed>>}
     */
    public function usersAssignPayload(int $id): array
    {
        $role = $this->detail($id);
        $users = [];
        foreach ($this->roleRepo->allUsersBrief() as $user) {
            $users[] = [
                'id'      => (int) $user->id,
                'account' => (string) $user->account,
                'name'    => (string) $user->name,
                'title'   => $user->title ? (string) $user->title : null,
                'status'  => (string) $user->status,
                'status_label' => \app\model\SysUser::STATUS_LABELS[(string) $user->status] ?? (string) $user->status,
            ];
        }

        return [
            'role'     => $role,
            'user_ids' => $this->roleRepo->userIdsOfRole($id),
            'users'    => $users,
        ];
    }
}
