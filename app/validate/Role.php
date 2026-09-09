<?php

declare(strict_types=1);

namespace app\validate;

use app\model\SysRole as SysRoleModel;
use think\Validate;

class Role extends Validate
{
    protected $rule = [
        'code'        => 'require|checkCode|length:2,64',
        'name'        => 'require|max:64',
        'status'      => 'require|checkStatus',
        'type'        => 'checkType',
        'sort'        => 'integer|egt:0',
        'description' => 'max:255',
        'keyword'     => 'max:128',
        'page'        => 'integer|gt:0',
        'pageSize'    => 'integer|in:10,20,50',
        'id'          => 'require|integer|gt:0',
        'perm_codes'  => 'array',
        'user_ids'    => 'array',
    ];

    protected $message = [
        'code.require'     => '请填写角色编码',
        'code.length'      => '角色编码长度 2–64',
        'name.require'     => '请填写角色名称',
        'name.max'         => '角色名称最长 64 字符',
        'status.require'   => '状态无效',
        'sort.integer'     => '排序无效',
        'sort.egt'         => '排序无效',
        'description.max'  => '描述最长 255 字符',
        'keyword.max'      => '关键词最长 128 字符',
        'page.integer'     => '页码无效',
        'page.gt'          => '页码无效',
        'pageSize.integer' => '每页条数无效',
        'pageSize.in'      => '每页条数无效',
        'id.require'       => '角色 ID 无效',
        'id.integer'       => '角色 ID 无效',
        'id.gt'            => '角色 ID 无效',
        'perm_codes.array' => '权限列表无效',
        'user_ids.array'   => '用户列表无效',
    ];

    protected $scene = [
        'list'        => ['keyword', 'status', 'type', 'page', 'pageSize'],
        'save'        => ['code', 'name', 'status', 'sort', 'description'],
        'permissions' => ['id', 'perm_codes'],
        'users'       => ['id', 'user_ids'],
        'delete'      => ['id'],
    ];

    public function sceneList()
    {
        return $this->only(['keyword', 'status', 'type', 'page', 'pageSize'])
            ->remove('status', 'require');
    }

    /**
     * @param array<string, mixed> $data
     * @return array{keyword?: string, status?: string, type?: string}
     */
    public static function toListFilters(array $data): array
    {
        $filters = [];
        $keyword = trim((string) ($data['keyword'] ?? ''));
        if ($keyword !== '') {
            $filters['keyword'] = $keyword;
        }
        $status = trim((string) ($data['status'] ?? ''));
        if ($status !== '') {
            $filters['status'] = $status;
        }
        $type = trim((string) ($data['type'] ?? ''));
        if ($type !== '') {
            $filters['type'] = $type;
        }

        return $filters;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function toListPageSize(array $data): int
    {
        $size = (int) ($data['pageSize'] ?? 0);

        return in_array($size, [10, 20, 50], true)
            ? $size
            : (int) config('paginate.list_rows', 10);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function toSaveData(array $data): array
    {
        return [
            'code'        => strtoupper(trim((string) ($data['code'] ?? ''))),
            'name'        => trim((string) ($data['name'] ?? '')),
            'status'      => trim((string) ($data['status'] ?? SysRoleModel::STATUS_ENABLED)),
            'sort'        => (int) ($data['sort'] ?? 100),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
        ];
    }

    protected function checkCode(mixed $value): bool|string
    {
        $code = strtoupper(trim((string) $value));
        if ($code === '') {
            return true;
        }

        return preg_match('/^[A-Z][A-Z0-9_]*$/', $code) === 1
            ? true
            : '角色编码须为大写字母开头，仅含大写字母/数字/下划线';
    }

    protected function checkStatus(mixed $value): bool|string
    {
        $status = trim((string) $value);
        if ($status === '') {
            return true;
        }

        return in_array($status, SysRoleModel::STATUSES, true) ? true : '状态无效';
    }

    protected function checkType(mixed $value): bool|string
    {
        $type = trim((string) $value);
        if ($type === '') {
            return true;
        }

        return in_array($type, SysRoleModel::TYPES, true) ? true : '类型无效';
    }
}
