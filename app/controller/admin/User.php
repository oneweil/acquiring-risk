<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\SysUser as SysUserModel;
use app\repository\risk\SysRoleRepository;
use app\service\risk\UserAdminService;
use app\validate\User as UserValidate;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class User extends AdminBase
{
    protected string $menuKey = 'user';

    protected string $pageTitle = '用户管理';

    public function index(): View
    {
        $roleOpts = [];
        foreach ((new SysRoleRepository())->search([], 1, 200) as $role) {
            $roleOpts[(int) $role->id] = (string) $role->name . ' (' . (string) $role->code . ')';
        }

        return $this->renderList('/admin/user/index', [
            'filter_options' => [
                'status' => SysUserModel::STATUS_LABELS,
                'role'   => $roleOpts,
            ],
            'list_card_title' => '用户管理',
        ]);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(UserValidate::class)->scene('list')->failException(true)->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $service = new UserAdminService();
        $filters = UserValidate::toListFilters($params);
        $page    = (int) ($params['page'] ?? 1);
        $size    = UserValidate::toListPageSize($params);

        return $this->success([
            'list'  => $service->search($filters, $page, $size),
            'stats' => $service->stats(),
        ]);
    }

    public function save(): Json
    {
        $id   = (int) $this->request->post('id', 0);
        $post = $this->request->post();

        try {
            validate(UserValidate::class)->scene('save')->failException(true)->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $payload = UserValidate::toSaveData($post, $id <= 0);

        try {
            $row = (new UserAdminService())->save($id, $payload);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        } catch (\Throwable $e) {
            return $this->fail('保存失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '保存成功');
    }

    public function resetPassword(): Json
    {
        $post = $this->request->post();

        try {
            validate(UserValidate::class)->scene('reset_password')->failException(true)->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        try {
            (new UserAdminService())->resetPassword(
                (int) $post['id'],
                (string) $post['new_password']
            );
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        } catch (\Throwable $e) {
            return $this->fail('重置失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success(null, '密码已重置');
    }

    public function toggleStatus(): Json
    {
        $post = $this->request->post();

        try {
            validate(UserValidate::class)->scene('toggle_status')->failException(true)->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        try {
            $row = (new UserAdminService())->toggleStatus(
                (int) $post['id'],
                trim((string) $post['status'])
            );
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        } catch (\Throwable $e) {
            return $this->fail('操作失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '状态已更新');
    }

    public function roles(): Json
    {
        $post = $this->request->post();
        if (!isset($post['role_ids'])) {
            $post['role_ids'] = [];
        }
        if (is_string($post['role_ids'])) {
            $post['role_ids'] = array_filter(explode(',', $post['role_ids']));
        }

        try {
            validate(UserValidate::class)->scene('roles')->failException(true)->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $roleIds = $post['role_ids'] ?? [];
        if (!is_array($roleIds)) {
            return $this->fail('角色列表无效', 422, null, 422);
        }
        $roleIds = array_values(array_map('intval', $roleIds));

        try {
            $row = (new UserAdminService())->assignRoles((int) $post['id'], $roleIds);
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        } catch (\Throwable $e) {
            return $this->fail('分配失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '角色已更新');
    }

    public function roleOptions(): Json
    {
        return $this->success((new UserAdminService())->enabledRoleOptions());
    }
}
