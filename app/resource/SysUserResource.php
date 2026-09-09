<?php

declare(strict_types=1);

namespace app\resource;

use app\model\SysUser;

/**
 * 后台用户列表 / 详情对外形状
 */
class SysUserResource extends JsonResource
{
    /**
     * @param list<array{id: int, code: string, name: string}> $roles
     */
    public function __construct(mixed $resource, private readonly array $roles = [])
    {
        parent::__construct($resource);
    }

    /**
     * @param list<array{id: int, code: string, name: string}> $roles
     */
    public static function makeWithRoles(mixed $resource, array $roles = []): static
    {
        return new static($resource, $roles);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $status = (string) $this->status;

        return [
            'id'              => (int) $this->id,
            'account'         => (string) $this->account,
            'name'            => (string) $this->name,
            'title'           => $this->title !== null && $this->title !== '' ? (string) $this->title : null,
            'phone'           => $this->phone !== null && $this->phone !== '' ? (string) $this->phone : null,
            'email'           => $this->email !== null && $this->email !== '' ? (string) $this->email : null,
            'status'          => $status,
            'status_label'    => SysUser::STATUS_LABELS[$status] ?? $status,
            'roles'           => $this->roles,
            'role_ids'        => array_map(static fn (array $r): int => $r['id'], $this->roles),
            'last_login_at'   => $this->last_login_at ? (string) $this->last_login_at : null,
            'remark'          => $this->remark !== null && $this->remark !== '' ? (string) $this->remark : null,
            'created_at'      => (string) $this->created_at,
            'updated_at'      => (string) $this->updated_at,
        ];
    }
}
