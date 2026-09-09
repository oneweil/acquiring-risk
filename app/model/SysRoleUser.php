<?php

declare(strict_types=1);

namespace app\model;

use think\model\Pivot;

/**
 * 角色-用户关联（物理表 risk_sys_role_user）
 *
 * @property int    $id
 * @property int    $role_id
 * @property int    $user_id
 * @property string $created_at
 */
class SysRoleUser extends Pivot
{
    protected $name = 'sys_role_user';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = false;
}
