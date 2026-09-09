<?php

declare(strict_types=1);

namespace app\model;

use think\Model;
use think\model\relation\BelongsToMany;
use think\model\relation\HasMany;

/**
 * 系统角色（物理表 risk_sys_role）
 *
 * @property int         $id
 * @property string      $code
 * @property string      $name
 * @property string      $type
 * @property string      $status
 * @property int         $sort
 * @property string|null $description
 * @property string      $created_at
 * @property string      $updated_at
 */
class SysRole extends Model
{
    protected $name = 'sys_role';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    public const TYPE_BUILTIN = 'builtin';
    public const TYPE_CUSTOM  = 'custom';

    /** @var list<string> */
    public const TYPES = [self::TYPE_BUILTIN, self::TYPE_CUSTOM];

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        self::TYPE_BUILTIN => '内置',
        self::TYPE_CUSTOM  => '自定义',
    ];

    public const STATUS_ENABLED  = 'enabled';
    public const STATUS_DISABLED = 'disabled';

    /** @var list<string> */
    public const STATUSES = [self::STATUS_ENABLED, self::STATUS_DISABLED];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_ENABLED  => '启用',
        self::STATUS_DISABLED => '停用',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            SysUser::class,
            SysRoleUser::class,
            'user_id',
            'role_id'
        );
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(SysRolePermission::class, 'role_id', 'id');
    }
}
