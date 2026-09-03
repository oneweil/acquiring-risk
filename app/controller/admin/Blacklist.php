<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\Blacklist as BlacklistModel;
use app\repository\risk\BlacklistRepository;
use app\resource\BlacklistResource;
use app\validate\Blacklist as BlacklistValidate;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class Blacklist extends AdminBase
{
    protected string $menuKey = 'blacklist';

    protected string $pageTitle = '黑名单';

    public function index(): View
    {
        return $this->renderList('/admin/blacklist/index', [
            'filter_options' => [
                'type'       => BlacklistModel::TYPE_LABELS,
                'risk_level' => BlacklistModel::RISK_LEVEL_LABELS,
            ],
            'list_card_title' => '黑名单管理',
        ]);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(BlacklistValidate::class)
                ->scene('list')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $filters   = BlacklistValidate::toListFilters($params);
        $page      = (int) ($params['page'] ?? 1);
        $paginator = (new BlacklistRepository())->search($filters, $page);

        return $this->success(BlacklistResource::paginate($paginator));
    }

    public function save(): Json
    {
        $id   = (int) $this->request->post('id', 0);
        $post = $this->request->post();

        try {
            validate(BlacklistValidate::class)
                ->scene('save')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $payload = BlacklistValidate::toSaveData($post);
        $repo    = new BlacklistRepository();

        if ($payload['status'] && $repo->existsActiveDuplicate($payload['type'], $payload['value'], $id > 0 ? $id : null)) {
            return $this->fail('已存在相同类型且生效中的黑名单值', 422, null, 422);
        }

        try {
            if ($id > 0) {
                $model = $repo->update($id, $payload);
            } else {
                $model = $repo->create($payload);
            }
        } catch (\Throwable $e) {
            return $this->fail('保存失败：' . $e->getMessage(), 500, null, 500);
        }

        $row = $repo->find((int) $model->id);

        return $this->success(
            $row ? BlacklistResource::make($row)->toArray() : null,
            '保存成功'
        );
    }
}
