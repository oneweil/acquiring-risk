<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\BelongsToMany;

/**
 * 系统用户（物理表 risk_sys_user）
 *
 * @property int         $id
 * @property string      $account
 * @property string      $name
 * @property string      $password
 * @property string|null $title
 * @property string|null $phone
 * @property string|null $email
 * @property string      $status
 * @property string|null $last_login_at
 * @property string|null $remark
 * @property string      $created_at
 * @property string      $updated_at
 */
class SysUser extends Model
{
    protected $name = 'sys_user';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    protected $hidden = ['password'];

    public const STATUS_ENABLED  = 'enabled';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_LOCKED   = 'locked';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_ENABLED,
        self::STATUS_DISABLED,
        self::STATUS_LOCKED,
    ];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_ENABLED  => '启用',
        self::STATUS_DISABLED => '停用',
        self::STATUS_LOCKED   => '锁定',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            SysRole::class,
            SysRoleUser::class,
            'role_id',
            'user_id'
        );
    }
}
