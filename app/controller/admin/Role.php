<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\SysRole as SysRoleModel;
use app\service\risk\RoleAdminService;
use app\validate\Role as RoleValidate;
use think\db\exception\ModelNotFoundException;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class Role extends AdminBase
{
    protected string $menuKey = 'role';

    protected string $pageTitle = '角色权限';

    public function index(): View
    {
        return $this->renderList('/admin/role/index', [
            'filter_options' => [
                'status' => SysRoleModel::STATUS_LABELS,
                'type'   => SysRoleModel::TYPE_LABELS,
            ],
            'list_card_title' => '角色管理',
        ]);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(RoleValidate::class)->scene('list')->failException(true)->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $service = new RoleAdminService();
        $filters = RoleValidate::toListFilters($params);
        $page    = (int) ($params['page'] ?? 1);
        $size    = RoleValidate::toListPageSize($params);

        return $this->success([
            'list'  => $service->search($filters, $page, $size),
            'stats' => $service->stats(),
        ]);
    }

    public function permissionCatalog(): Json
    {
        return $this->success((new RoleAdminService())->permissionCatalog());
    }

    public function save(): Json
    {
        $id   = (int) $this->request->post('id', 0);
        $post = $this->request->post();

        try {
            validate(RoleValidate::class)->scene('save')->failException(true)->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $payload = RoleValidate::toSaveData($post);

        try {
            $row = (new RoleAdminService())->save($id, $payload);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        } catch (\Throwable $e) {
            return $this->fail('保存失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '保存成功');
    }

    public function permissions(): Json
    {
        $post = $this->request->post();
        if (!isset($post['perm_codes'])) {
            $post['perm_codes'] = [];
        }

        try {
            validate(RoleValidate::class)->scene('permissions')->failException(true)->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $codes = $post['perm_codes'] ?? [];
        if (!is_array($codes)) {
            return $this->fail('权限列表无效', 422, null, 422);
        }

        try {
            $row = (new RoleAdminService())->savePermissions((int) $post['id'], $codes);
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        } catch (\Throwable $e) {
            return $this->fail('保存失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '权限已保存');
    }

    public function users(): Json
    {
        $post = $this->request->post();
        if (!isset($post['user_ids'])) {
            $post['user_ids'] = [];
        }

        try {
            validate(RoleValidate::class)->scene('users')->failException(true)->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $userIds = $post['user_ids'] ?? [];
        if (!is_array($userIds)) {
            return $this->fail('用户列表无效', 422, null, 422);
        }
        $userIds = array_map('intval', $userIds);

        try {
            $row = (new RoleAdminService())->assignUsers((int) $post['id'], $userIds);
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        } catch (\Throwable $e) {
            return $this->fail('分配失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '用户已更新');
    }

    public function usersAssign(): Json
    {
        $id = (int) $this->request->get('id', 0);
        if ($id <= 0) {
            return $this->fail('角色 ID 无效', 422, null, 422);
        }

        try {
            $payload = (new RoleAdminService())->usersAssignPayload($id);
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        }

        return $this->success($payload);
    }

    public function delete(): Json
    {
        $post = $this->request->post();

        try {
            validate(RoleValidate::class)->scene('delete')->failException(true)->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        try {
            (new RoleAdminService())->delete((int) $post['id']);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (ModelNotFoundException $e) {
            return $this->fail($e->getMessage(), 404, null, 404);
        } catch (\Throwable $e) {
            return $this->fail('删除失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success(null, '已删除');
    }
}
