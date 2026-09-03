<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\Disposition as DispositionModel;
use app\repository\risk\DispositionRepository;
use app\resource\DispositionResource;
use app\validate\Disposition as DispositionValidate;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class Disposition extends AdminBase
{
    protected string $menuKey = 'disposition';

    protected string $pageTitle = '处置策略';

    public function index(): View
    {
        return $this->renderList('/admin/disposition/index', [
            'filter_options' => [
                'risk_level' => DispositionModel::RISK_LEVEL_LABELS,
                'scope'      => DispositionModel::SCOPE_LABELS,
            ],
            'list_card_title' => '处置策略库',
        ]);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(DispositionValidate::class)
                ->scene('list')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $filters   = DispositionValidate::toListFilters($params);
        $page      = (int) ($params['page'] ?? 1);
        $paginator = (new DispositionRepository())->search($filters, $page);

        return $this->success(DispositionResource::paginate($paginator));
    }

    public function save(): Json
    {
        $id   = (int) $this->request->post('id', 0);
        $post = $this->request->post();

        try {
            validate(DispositionValidate::class)
                ->scene('save')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $payload = DispositionValidate::toSaveData($post);
        $repo    = new DispositionRepository();

        if ($repo->existsCode($payload['code'], $id > 0 ? $id : null)) {
            return $this->fail('策略编码已存在', 422, null, 422);
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
            $row ? DispositionResource::make($row)->toArray() : null,
            '保存成功'
        );
    }
}
