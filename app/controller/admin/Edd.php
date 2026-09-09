<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\EddCase;
use app\service\risk\EddAdminService;
use app\validate\Edd as EddValidate;
use think\exception\ValidateException;
use think\response\File;
use think\response\Json;
use think\response\View;

class Edd extends AdminBase
{
    protected string $menuKey = 'edd';

    protected string $pageTitle = 'EDD 强化尽调';

    public function index(): View
    {
        return $this->renderList('/admin/edd/index', [
            'filter_options' => [
                'trigger' => EddCase::TRIGGER_LABELS,
                'status'  => EddCase::STATUS_LABELS,
            ],
            'checklist_items'  => EddCase::CHECKLIST_ITEMS,
            'list_card_title'  => 'EDD 强化尽职调查',
            'default_deadline' => date('Y-m-d', strtotime('+30 days')),
        ]);
    }

    /**
     * 合并 form / JSON 请求体
     *
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
            $stats = (new EddAdminService())->stats();
        } catch (\Throwable $e) {
            return $this->fail('统计失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($stats);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(EddValidate::class)
                ->scene('list')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $filters  = EddValidate::toListFilters($params);
        $page     = (int) ($params['page'] ?? 1);
        $pageSize = EddValidate::toListPageSize($params);

        try {
            $payload = (new EddAdminService())->search($filters, $page, $pageSize);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload);
    }

    public function detail(): Json
    {
        $params = $this->request->get();

        try {
            validate(EddValidate::class)
                ->scene('detail')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id = (int) ($params['id'] ?? 0);

        try {
            $detail = (new EddAdminService())->detail($id);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        if ($detail === null) {
            return $this->fail('EDD 工单不存在', 404, null, 404);
        }

        return $this->success($detail);
    }

    public function create(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(EddValidate::class)
                ->scene('create')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        try {
            $row = (new EddAdminService())->create(EddValidate::toCreateData($post));
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('创建失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, 'EDD 工单已创建');
    }

    public function collect(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(EddValidate::class)
                ->scene('collect')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id        = (int) ($post['id'] ?? 0);
        $checklist = EddValidate::toChecklist($post);

        try {
            $row = (new EddAdminService())->collect($id, $checklist);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('启动失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '已启动资料收集');
    }

    public function upload(): Json
    {
        $post = $this->request->post();

        try {
            validate(EddValidate::class)
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

        $id  = (int) ($post['id'] ?? 0);
        $key = trim((string) ($post['checklist_key'] ?? ''));

        try {
            $row = (new EddAdminService())->upload($id, $key, $file);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('上传失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '上传成功');
    }

    public function deleteAttachment(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(EddValidate::class)
                ->scene('attachment_delete')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $attachmentId = (int) ($post['attachment_id'] ?? 0);

        try {
            $row = (new EddAdminService())->deleteAttachment($attachmentId);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('删除失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '已删除附件');
    }

    public function submit(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(EddValidate::class)
                ->scene('submit')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id = (int) ($post['id'] ?? 0);

        try {
            $row = (new EddAdminService())->submit($id);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('提交失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($row, '已进入审核');
    }

    public function review(): Json
    {
        $post = $this->requestPayload();

        try {
            validate(EddValidate::class)
                ->scene('review')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id     = (int) ($post['id'] ?? 0);
        $action = trim((string) ($post['action'] ?? ''));
        $remark = trim((string) ($post['review_remark'] ?? ''));

        try {
            $row = (new EddAdminService())->review($id, $action, $remark);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        } catch (\Throwable $e) {
            return $this->fail('审核失败：' . $e->getMessage(), 500, null, 500);
        }

        $msg = $action === 'approve' ? 'EDD 审核通过' : 'EDD 审核未通过';

        return $this->success($row, $msg);
    }

    public function downloadAttachment(): File|Json
    {
        $params = $this->request->get();

        try {
            validate(EddValidate::class)
                ->scene('download')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id     = (int) ($params['id'] ?? 0);
        $service = new EddAdminService();
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
