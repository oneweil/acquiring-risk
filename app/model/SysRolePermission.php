<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 角色-权限关联（物理表 risk_sys_role_permission）
 *
 * @property int    $id
 * @property int    $role_id
 * @property string $perm_code
 * @property string $created_at
 */
class SysRolePermission extends Model
{
    protected $name = 'sys_role_permission';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = false;
}
