<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\StrReport as StrReportModel;
use app\service\risk\StrReportAdminService;
use app\validate\StrReport as StrReportValidate;
use think\exception\ValidateException;
use think\response\File;
use think\response\Json;
use think\response\View;

class StrReport extends AdminBase
{
    protected string $menuKey = 'str_report';

    protected string $pageTitle = 'STR 报送';

    public function index(): View
    {
        return $this->renderList('/admin/str_report/index', [
            'filter_options' => [
                'status' => StrReportModel::STATUS_LABELS,
                'type'   => StrReportModel::TYPE_LABELS,
            ],
            'list_card_title' => '大额 / 可疑交易 STR 报送',
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

    public function stats(): Json
    {
        try {
            $stats = (new StrReportAdminService())->stats();
        } catch (\Throwable $e) {
            return $this->fail('统计失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($stats);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(StrReportValidate::class)
                ->scene('list')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $filters  = StrReportValidate::toListFilters($params);
        $page     = (int) ($params['page'] ?? 1);
        $pageSize = StrReportValidate::toListPageSize($params);

        try {
            $payload = (new StrReportAdminService())->search($filters, $page, $pageSize);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload);
    }

    public function detail(): Json
    {
        $params = $this->request->get();

        try {
            validate(StrReportValidate::class)
                ->scene('detail')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id = (int) ($params['id'] ?? 0);

        try {
            $detail = (new StrReportAdminService())->detail($id);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        if ($detail === null) {
            return $this->fail('STR 报送不存在', 404, null, 404);
        }

        return $this->success($detail);
    }

    public function create(): Json
    {
        $post = $this->request->post();
        if ($post === []) {
            $post = $this->requestPayload();
        }

        try {
            validate(StrReportValidate::class)
                ->scene('create')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $file = $this->request->file('file');

        try {
            $row = (new StrReportAdminService())->create(
                StrReportValidate::toCreateData($post),
                $file
            );
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('创建失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, 'STR 报送已创建');
    }

    public function confirm(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(StrReportValidate::class)
                ->scene('confirm')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id     = (int) ($post['id'] ?? 0);
        $remark = trim((string) ($post['review_remark'] ?? ''));

        try {
            $row = (new StrReportAdminService())->confirm($id, $remark);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('确认失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '已确认需上报');
    }

    public function dismiss(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(StrReportValidate::class)
                ->scene('dismiss')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id     = (int) ($post['id'] ?? 0);
        $reason = trim((string) ($post['dismiss_reason'] ?? ''));

        try {
            $row = (new StrReportAdminService())->dismiss($id, $reason);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('操作失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '已标记为无需上报');
    }

    public function upload(): Json
    {
        $post = $this->request->post();

        try {
            validate(StrReportValidate::class)
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
            $row = (new StrReportAdminService())->upload($id, $file);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('上传失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '上传成功');
    }

    public function submit(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(StrReportValidate::class)
                ->scene('submit')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id = (int) ($post['id'] ?? 0);

        try {
            $row = (new StrReportAdminService())->submit($id);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('提交失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '已提交监管');
    }

    public function downloadAttachment(): File|Json
    {
        $params = $this->request->get();

        try {
            validate(StrReportValidate::class)
                ->scene('download')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id      = (int) ($params['id'] ?? 0);
        $service = new StrReportAdminService();
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
