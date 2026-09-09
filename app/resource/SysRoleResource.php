<?php

declare(strict_types=1);

namespace app\resource;

use app\model\SysRole;

/**
 * 后台角色列表 / 详情对外形状
 */
class SysRoleResource extends JsonResource
{
    /**
     * @param list<string> $permCodes
     */
    public function __construct(
        mixed $resource,
        private readonly int $userCount = 0,
        private readonly array $permCodes = [],
    ) {
        parent::__construct($resource);
    }

    /**
     * @param list<string> $permCodes
     */
    public static function makeDetail(mixed $resource, int $userCount = 0, array $permCodes = []): static
    {
        return new static($resource, $userCount, $permCodes);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $type   = (string) $this->type;
        $status = (string) $this->status;

        return [
            'id'            => (int) $this->id,
            'code'          => (string) $this->code,
            'name'          => (string) $this->name,
            'type'          => $type,
            'type_label'    => SysRole::TYPE_LABELS[$type] ?? $type,
            'status'        => $status,
            'status_label'  => SysRole::STATUS_LABELS[$status] ?? $status,
            'sort'          => (int) $this->sort,
            'description'   => $this->description !== null && $this->description !== ''
                ? (string) $this->description
                : null,
            'user_count'    => $this->userCount,
            'perm_codes'    => $this->permCodes,
            'perm_count'    => count($this->permCodes),
            'is_builtin'    => $type === SysRole::TYPE_BUILTIN,
            'created_at'    => (string) $this->created_at,
            'updated_at'    => (string) $this->updated_at,
        ];
    }
}
