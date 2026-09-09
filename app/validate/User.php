<?php

declare(strict_types=1);

namespace app\validate;

use app\model\SysUser as SysUserModel;
use think\Validate;

class User extends Validate
{
    protected $rule = [
        'account'      => 'require|alphaDash|length:3,32',
        'name'         => 'require|max:64',
        'password'     => 'checkPassword',
        'title'        => 'max:64',
        'phone'        => 'max:32',
        'email'        => 'email|max:128',
        'status'       => 'require|checkStatus',
        'remark'       => 'max:255',
        'keyword'      => 'max:128',
        'role_id'      => 'integer|gt:0',
        'page'         => 'integer|gt:0',
        'pageSize'     => 'integer|in:10,20,50',
        'id'           => 'require|integer|gt:0',
        'new_password' => 'require|length:6,64',
        'role_ids'     => 'array',
    ];

    protected $message = [
        'account.require'      => '请填写登录账号',
        'account.alphaDash'    => '账号仅支持字母、数字、下划线',
        'account.length'       => '账号长度 3–32',
        'name.require'         => '请填写姓名',
        'name.max'             => '姓名最长 64 字符',
        'title.max'            => '岗位最长 64 字符',
        'phone.max'            => '手机最长 32 字符',
        'email.email'          => '邮箱格式无效',
        'email.max'            => '邮箱最长 128 字符',
        'status.require'       => '状态无效',
        'remark.max'           => '备注最长 255 字符',
        'keyword.max'          => '关键词最长 128 字符',
        'page.integer'         => '页码无效',
        'page.gt'              => '页码无效',
        'pageSize.integer'     => '每页条数无效',
        'pageSize.in'          => '每页条数无效',
        'id.require'           => '用户 ID 无效',
        'id.integer'           => '用户 ID 无效',
        'id.gt'                => '用户 ID 无效',
        'new_password.require' => '请填写新密码',
        'new_password.length'  => '新密码长度 6–64',
        'role_ids.array'       => '角色列表无效',
    ];

    protected $scene = [
        'list'           => ['keyword', 'status', 'role_id', 'page', 'pageSize'],
        'save'           => ['account', 'name', 'password', 'title', 'phone', 'email', 'status', 'remark'],
        'reset_password' => ['id', 'new_password'],
        'toggle_status'  => ['id', 'status'],
        'roles'          => ['id', 'role_ids'],
    ];

    public function sceneList()
    {
        return $this->only(['keyword', 'status', 'role_id', 'page', 'pageSize'])
            ->remove('status', 'require');
    }

    public function sceneSave()
    {
        return $this->only(['account', 'name', 'password', 'title', 'phone', 'email', 'status', 'remark']);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{keyword?: string, status?: string, role_id?: int}
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
        $roleId = (int) ($data['role_id'] ?? 0);
        if ($roleId > 0) {
            $filters['role_id'] = $roleId;
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
    public static function toSaveData(array $data, bool $isCreate): array
    {
        $payload = [
            'account' => strtolower(trim((string) ($data['account'] ?? ''))),
            'name'    => trim((string) ($data['name'] ?? '')),
            'title'   => trim((string) ($data['title'] ?? '')) ?: null,
            'phone'   => trim((string) ($data['phone'] ?? '')) ?: null,
            'email'   => trim((string) ($data['email'] ?? '')) ?: null,
            'status'  => trim((string) ($data['status'] ?? SysUserModel::STATUS_ENABLED)),
            'remark'  => trim((string) ($data['remark'] ?? '')) ?: null,
        ];

        $password = (string) ($data['password'] ?? '');
        if ($isCreate || $password !== '') {
            $payload['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $payload;
    }

    protected function checkStatus(mixed $value): bool|string
    {
        $status = trim((string) $value);
        if ($status === '') {
            return true;
        }

        return in_array($status, SysUserModel::STATUSES, true) ? true : '状态无效';
    }

    protected function checkPassword(mixed $value, mixed $rule, array $data = []): bool|string
    {
        $id  = (int) ($data['id'] ?? request()->post('id', 0));
        $pwd = (string) $value;
        if ($id > 0 && $pwd === '') {
            return true;
        }
        if ($pwd === '') {
            return '请填写密码';
        }
        $len = strlen($pwd);
        if ($len < 6 || $len > 64) {
            return '密码长度 6–64';
        }

        return true;
    }
}
