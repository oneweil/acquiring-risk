<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\SysRole;
use app\model\SysRolePermission;
use app\model\SysRoleUser;
use app\model\SysUser;
use app\support\PermissionCatalog;
use think\facade\Db;

/**
 * 薄 RBAC：加载角色/权限、判定 can、组装 Session
 */
class PermissionService
{
    /**
     * @return array{role_codes: list<string>, perm_codes: list<string>}
     */
    public function loadAuthzForUser(int $userId): array
    {
        $roleIds = SysRoleUser::where('user_id', $userId)->column('role_id');
        $roleIds = array_map('intval', $roleIds);
        if ($roleIds === []) {
            return ['role_codes' => [], 'perm_codes' => []];
        }

        $roles = SysRole::whereIn('id', $roleIds)
            ->where('status', SysRole::STATUS_ENABLED)
            ->field('id,code')
            ->select();

        $roleCodes = [];
        $enabledRoleIds = [];
        foreach ($roles as $role) {
            /** @var SysRole $role */
            $roleCodes[] = (string) $role->code;
            $enabledRoleIds[] = (int) $role->id;
        }

        if (in_array(PermissionCatalog::ROLE_SYS_ADMIN, $roleCodes, true)) {
            return [
                'role_codes' => $roleCodes,
                'perm_codes' => PermissionCatalog::allCodes(),
            ];
        }

        if ($enabledRoleIds === []) {
            return ['role_codes' => $roleCodes, 'perm_codes' => []];
        }

        $permCodes = SysRolePermission::whereIn('role_id', $enabledRoleIds)
            ->column('perm_code');
        $permCodes = array_values(array_unique(array_map('strval', $permCodes)));

        return [
            'role_codes' => $roleCodes,
            'perm_codes' => $permCodes,
        ];
    }

    /**
     * @param array<string, mixed>|null $sessionUser
     */
    public function can(?array $sessionUser, string $permCode): bool
    {
        if ($sessionUser === null || $sessionUser === []) {
            return false;
        }

        $roleCodes = $sessionUser['role_codes'] ?? [];
        if (!is_array($roleCodes)) {
            $roleCodes = [];
        }
        if (in_array(PermissionCatalog::ROLE_SYS_ADMIN, $roleCodes, true)) {
            return true;
        }

        $permCodes = $sessionUser['perm_codes'] ?? [];
        if (!is_array($permCodes)) {
            $permCodes = [];
        }

        return in_array($permCode, $permCodes, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSessionPayload(SysUser $user): array
    {
        $authz = $this->loadAuthzForUser((int) $user->id);

        return [
            'id'         => (int) $user->id,
            'username'   => (string) $user->account,
            'name'       => (string) $user->name,
            'role_codes' => $authz['role_codes'],
            'perm_codes' => $authz['perm_codes'],
            'login_at'   => date('Y-m-d H:i:s'),
        ];
    }

    public function touchLastLogin(int $userId): void
    {
        SysUser::where('id', $userId)->update([
            'last_login_at' => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param list<int> $roleIds
     */
    public function syncUserRoles(int $userId, array $roleIds): void
    {
        $roleIds = array_values(array_unique(array_map('intval', $roleIds)));
        $now     = date('Y-m-d H:i:s');

        Db::transaction(function () use ($userId, $roleIds, $now): void {
            SysRoleUser::where('user_id', $userId)->delete();
            if ($roleIds === []) {
                return;
            }
            $rows = [];
            foreach ($roleIds as $roleId) {
                if ($roleId <= 0) {
                    continue;
                }
                $rows[] = [
                    'role_id'    => $roleId,
                    'user_id'    => $userId,
                    'created_at' => $now,
                ];
            }
            if ($rows !== []) {
                Db::name('sys_role_user')->insertAll($rows);
            }
        });
    }

    /**
     * @param list<int> $userIds
     */
    public function syncRoleUsers(int $roleId, array $userIds): void
    {
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $now     = date('Y-m-d H:i:s');

        Db::transaction(function () use ($roleId, $userIds, $now): void {
            SysRoleUser::where('role_id', $roleId)->delete();
            if ($userIds === []) {
                return;
            }
            $rows = [];
            foreach ($userIds as $userId) {
                if ($userId <= 0) {
                    continue;
                }
                $rows[] = [
                    'role_id'    => $roleId,
                    'user_id'    => $userId,
                    'created_at' => $now,
                ];
            }
            if ($rows !== []) {
                Db::name('sys_role_user')->insertAll($rows);
            }
        });
    }

    /**
     * @param list<string> $permCodes
     */
    public function syncRolePermissions(int $roleId, array $permCodes): void
    {
        $valid = [];
        foreach ($permCodes as $code) {
            $code = trim((string) $code);
            if ($code !== '' && PermissionCatalog::isValid($code)) {
                $valid[$code] = true;
            }
        }
        $codes = array_keys($valid);
        $now   = date('Y-m-d H:i:s');

        Db::transaction(function () use ($roleId, $codes, $now): void {
            SysRolePermission::where('role_id', $roleId)->delete();
            if ($codes === []) {
                return;
            }
            $rows = [];
            foreach ($codes as $code) {
                $rows[] = [
                    'role_id'    => $roleId,
                    'perm_code'  => $code,
                    'created_at' => $now,
                ];
            }
            Db::name('sys_role_permission')->insertAll($rows);
        });
    }
}
