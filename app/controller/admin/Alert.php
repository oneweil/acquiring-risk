<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\Alert as AlertModel;
use app\service\risk\AlertAdminService;
use app\validate\Alert as AlertValidate;
use think\exception\ValidateException;
use think\response\File;
use think\response\Json;
use think\response\View;

class Alert extends AdminBase
{
    protected string $menuKey = 'alert';

    protected string $pageTitle = '预警中心';

    public function index(): View
    {
        return $this->renderList('/admin/alert/index', [
            'filter_options' => [
                'risk_level'   => AlertModel::RISK_LEVEL_LABELS,
                'status'       => AlertModel::STATUS_LABELS,
                'measure_code' => AlertModel::MEASURE_LABELS,
            ],
            'handle_actions' => [
                'default' => [
                    AlertModel::ACTION_CLOSE          => AlertModel::HANDLE_ACTION_LABELS[AlertModel::ACTION_CLOSE],
                    AlertModel::ACTION_FALSE_POSITIVE => AlertModel::HANDLE_ACTION_LABELS[AlertModel::ACTION_FALSE_POSITIVE],
                ],
                'inquiry' => [
                    AlertModel::ACTION_SUBMIT_MATERIALS => AlertModel::HANDLE_ACTION_LABELS[AlertModel::ACTION_SUBMIT_MATERIALS],
                    AlertModel::ACTION_COMPLETE         => AlertModel::HANDLE_ACTION_LABELS[AlertModel::ACTION_COMPLETE],
                    AlertModel::ACTION_FALSE_POSITIVE   => AlertModel::HANDLE_ACTION_LABELS[AlertModel::ACTION_FALSE_POSITIVE],
                ],
            ],
            'list_card_title' => '交易预警',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function requestPayload(): array
    {
        $post = $this->request->post();
        if ($post === [] || $this->request->isJson()) {
            $decoded = json_decode($this->request->getContent() ?: '', true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return is_array($post) ? $post : [];
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(AlertValidate::class)
                ->scene('list')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $filters  = AlertValidate::toListFilters($params);
        $page     = (int) ($params['page'] ?? 1);
        $pageSize = AlertValidate::toListPageSize($params);

        try {
            $payload = (new AlertAdminService())->search($filters, $page, $pageSize);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload);
    }

    public function detail(): Json
    {
        $params = $this->request->get();

        try {
            validate(AlertValidate::class)
                ->scene('detail')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id = (int) ($params['id'] ?? 0);

        try {
            $detail = (new AlertAdminService())->detail($id);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        if ($detail === null) {
            return $this->fail('预警不存在', 404, null, 404);
        }

        return $this->success($detail);
    }

    public function handle(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(AlertValidate::class)
                ->scene('handle')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id   = (int) ($post['id'] ?? 0);
        $data = AlertValidate::toHandleData($post);

        try {
            $row = (new AlertAdminService())->handle($id, $data);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('处置失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '处置成功');
    }

    public function upload(): Json
    {
        $post = $this->request->post();

        try {
            validate(AlertValidate::class)
                ->scene('upload')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $file = $this->request->file('file');
        if ($file === null) {
            return $this->fail('请选择要上传的文件', 422, null, 422);
        }

        $id = (int) ($post['id'] ?? 0);

        try {
            $row = (new AlertAdminService())->upload($id, $file);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('上传失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '上传成功');
    }

    public function downloadAttachment(): File|Json
    {
        $params = $this->request->get();

        try {
            validate(AlertValidate::class)
                ->scene('download')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id      = (int) ($params['attachment_id'] ?? 0);
        $service = new AlertAdminService();
        $attach  = $service->findAttachment($id);
        if ($attach === null) {
            return $this->fail('附件不存在', 404, null, 404);
        }

        $path = $service->absoluteStoragePath($attach);
        if (!is_file($path)) {
            return $this->fail('文件不存在或已清理', 404, null, 404);
        }

        return download($path, (string) $attach->file_name);
    }
}
