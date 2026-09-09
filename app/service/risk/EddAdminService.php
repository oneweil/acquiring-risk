<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\EddAttachment;
use app\model\EddCase;
use app\model\MerchantAssessment;
use app\repository\doopsun\DoopsunMerchantRepository;
use app\repository\risk\EddAttachmentRepository;
use app\repository\risk\EddCaseRepository;
use app\repository\risk\MerchantAssessmentRepository;
use app\resource\EddCaseResource;
use think\exception\ValidateException;
use think\facade\Filesystem;
use think\file\UploadedFile;

/**
 * 后台 EDD 强化尽调：建单、资料收集、审核状态机
 */
class EddAdminService
{
    public function __construct(
        private readonly EddCaseRepository $caseRepo = new EddCaseRepository(),
        private readonly EddAttachmentRepository $attachRepo = new EddAttachmentRepository(),
        private readonly DoopsunMerchantRepository $doopsunRepo = new DoopsunMerchantRepository(),
        private readonly MerchantAssessmentRepository $assessRepo = new MerchantAssessmentRepository(),
    ) {
    }

    /**
     * @return array{active: int, pending: int, passed: int, failed: int}
     */
    public function stats(): array
    {
        return $this->caseRepo->stats();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function search(array $filters, int $page, int $pageSize): array
    {
        $paginator = $this->caseRepo->search($filters, $page, $pageSize);
        $items     = [];

        foreach ($paginator as $model) {
            /** @var EddCase $model */
            $items[] = $this->toResourceArray($model, false);
        }

        $payload         = $paginator->toArray();
        $payload['data'] = $items;

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(int $id): ?array
    {
        $model = $this->caseRepo->find($id);
        if ($model === null) {
            return null;
        }

        return $this->toResourceArray($model, true);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array
    {
        $merchantId = (string) $data['merchant_id'];
        $merchant   = $this->doopsunRepo->findByMerchantId($merchantId);
        if ($merchant === null) {
            throw new ValidateException('商户不存在');
        }

        $merchantName = (string) ($merchant['name'] ?? $merchantId);
        $riskLevel    = MerchantAssessment::RISK_LEVEL_MID;
        $assess       = $this->assessRepo->findByMerchantId($merchantId);
        if ($assess !== null) {
            $riskLevel = (string) $assess->risk_level;
        }

        $model = $this->caseRepo->create([
            'case_no'       => $this->caseRepo->nextCaseNo(),
            'merchant_id'   => $merchantId,
            'merchant_name' => mb_substr($merchantName, 0, 128),
            'trigger'       => (string) $data['trigger'],
            'risk_level'    => $riskLevel,
            'status'        => EddCase::STATUS_PENDING,
            'deadline'      => (string) $data['deadline'],
            'assignee'      => $data['assignee'] ?? null,
            'progress'      => 0,
            'checklist'     => [],
            'notes'         => $data['notes'] ?? null,
            'linked_str_id' => $data['linked_str_id'] ?? null,
        ]);

        return $this->toResourceArray($model, false);
    }

    /**
     * @param array<string, bool> $checklist
     * @return array<string, mixed>
     */
    public function collect(int $id, array $checklist): array
    {
        $model = $this->requireCase($id);
        if ((string) $model->status !== EddCase::STATUS_PENDING) {
            throw new ValidateException('当前状态不可启动资料收集');
        }

        $selected = EddCase::selectedKeys($checklist);
        if ($selected === []) {
            throw new ValidateException('请至少勾选一项所需资料');
        }

        $assignee = $model->assignee;
        if ($assignee === null || $assignee === '' || $assignee === '—') {
            $assignee = session('admin_user.username') ?: '合规专员';
        }

        $this->caseRepo->update($id, [
            'checklist' => $checklist,
            'status'    => EddCase::STATUS_COLLECTING,
            'assignee'  => $assignee,
            'progress'  => 0,
        ]);

        return $this->toResourceArray($this->requireCase($id), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function upload(int $id, string $checklistKey, UploadedFile $file): array
    {
        $model = $this->requireCase($id);
        if ((string) $model->status !== EddCase::STATUS_COLLECTING) {
            throw new ValidateException('当前状态不可上传资料');
        }

        $checklist = is_array($model->checklist) ? $model->checklist : [];
        if (empty($checklist[$checklistKey])) {
            throw new ValidateException('该项未在启动时勾选，无需上传');
        }

        $ext = strtolower((string) $file->extension());
        if ($ext === '' || !in_array($ext, EddAttachment::ALLOWED_EXTENSIONS, true)) {
            throw new ValidateException('不支持的文件类型');
        }

        $caseNo = (string) $model->case_no;
        $subdir = 'edd/' . $caseNo . '/' . $checklistKey;
        $saved  = Filesystem::disk('public')->putFile($subdir, $file);
        if ($saved === false || $saved === '') {
            throw new ValidateException('文件保存失败');
        }

        $now = date('Y-m-d H:i:s');
        $this->attachRepo->create([
            'edd_case_id'   => $id,
            'checklist_key' => $checklistKey,
            'file_name'     => mb_substr((string) $file->getOriginalName(), 0, 255),
            'file_size'     => (int) $file->getSize(),
            'storage_path'  => str_replace('\\', '/', (string) $saved),
            'file_type'     => EddAttachment::normalizeFileType($ext),
            'uploaded_at'   => $now,
        ]);

        $this->refreshProgress($model);

        return $this->toResourceArray($this->requireCase($id), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteAttachment(int $attachmentId): array
    {
        $attach = $this->attachRepo->find($attachmentId);
        if ($attach === null) {
            throw new ValidateException('附件不存在');
        }

        $caseId = (int) $attach->edd_case_id;
        $model  = $this->requireCase($caseId);
        if ((string) $model->status !== EddCase::STATUS_COLLECTING) {
            throw new ValidateException('当前状态不可删除资料');
        }

        $path = (string) $attach->storage_path;
        $this->attachRepo->delete($attachmentId);
        if ($path !== '') {
            try {
                Filesystem::disk('public')->delete($path);
            } catch (\Throwable) {
                // 文件缺失不影响业务
            }
        }

        $this->refreshProgress($model);

        return $this->toResourceArray($this->requireCase($caseId), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function submit(int $id): array
    {
        $model = $this->requireCase($id);
        if ((string) $model->status !== EddCase::STATUS_COLLECTING) {
            throw new ValidateException('当前状态不可提交审核');
        }

        $meta = $this->computeProgressMeta($model);
        if ($meta['selected'] === [] || $meta['missing'] !== []) {
            throw new ValidateException('请先完成勾选材料的上传');
        }

        $this->caseRepo->update($id, [
            'status'   => EddCase::STATUS_REVIEWING,
            'progress' => 100,
        ]);

        return $this->toResourceArray($this->requireCase($id), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function review(int $id, string $action, string $remark): array
    {
        $model = $this->requireCase($id);
        if ((string) $model->status !== EddCase::STATUS_REVIEWING) {
            throw new ValidateException('当前状态不可审核');
        }

        $remark = trim($remark);
        if ($remark === '') {
            throw new ValidateException('请填写审核备注');
        }

        $status = $action === 'approve' ? EddCase::STATUS_PASSED : EddCase::STATUS_REJECTED;

        $this->caseRepo->update($id, [
            'status'        => $status,
            'review_remark' => mb_substr($remark, 0, 1000),
            'reviewed_at'   => date('Y-m-d H:i:s'),
            'progress'      => $status === EddCase::STATUS_PASSED ? 100 : (int) $model->progress,
        ]);

        return $this->toResourceArray($this->requireCase($id), true);
    }

    public function findAttachment(int $id): ?EddAttachment
    {
        return $this->attachRepo->find($id);
    }

    public function absoluteStoragePath(EddAttachment $attach): string
    {
        $relative = str_replace('\\', '/', (string) $attach->storage_path);

        return Filesystem::disk('public')->path($relative);
    }

    private function requireCase(int $id): EddCase
    {
        $model = $this->caseRepo->find($id);
        if ($model === null) {
            throw new ValidateException('EDD 工单不存在');
        }

        return $model;
    }

    /**
     * @return array{selected: list<string>, missing: list<string>, progress: int, counts: array<string, int>}
     */
    private function computeProgressMeta(EddCase $model): array
    {
        $checklist = is_array($model->checklist) ? $model->checklist : [];
        $selected  = EddCase::selectedKeys($checklist);
        $counts    = $this->attachRepo->countsByKey((int) $model->id);
        $missing   = [];

        foreach ($selected as $key) {
            if (($counts[$key] ?? 0) < 1) {
                $missing[] = $key;
            }
        }

        $total    = count($selected);
        $done     = $total > 0 ? $total - count($missing) : 0;
        $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        return [
            'selected' => $selected,
            'missing'  => $missing,
            'progress' => $progress,
            'counts'   => $counts,
        ];
    }

    private function refreshProgress(EddCase $model): void
    {
        $meta = $this->computeProgressMeta($model);
        $this->caseRepo->update((int) $model->id, [
            'progress' => $meta['progress'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResourceArray(EddCase $model, bool $withDetail = false): array
    {
        $meta = $this->computeProgressMeta($model);
        $data = $model->toArray();
        $data['docs_ready']   = $meta['selected'] !== [] && $meta['missing'] === [];
        $data['missing_keys'] = $meta['missing'];

        if ($withDetail) {
            $docs = [];
            foreach (EddCase::CHECKLIST_KEYS as $key) {
                $docs[$key] = [];
            }
            foreach ($this->attachRepo->listByCaseId((int) $model->id) as $attach) {
                $key = (string) $attach->checklist_key;
                if (!isset($docs[$key])) {
                    $docs[$key] = [];
                }
                $docs[$key][] = [
                    'id'           => (int) $attach->id,
                    'name'         => (string) $attach->file_name,
                    'size'         => $this->formatSize((int) $attach->file_size),
                    'file_size'    => (int) $attach->file_size,
                    'file_type'    => (string) $attach->file_type,
                    'upload_time'  => (string) $attach->uploaded_at,
                    'download_url' => '/admin/edd/attachment/download?id=' . (int) $attach->id,
                ];
            }
            $data['docs']            = $docs;
            $data['checklist_items'] = EddCase::CHECKLIST_ITEMS;
        }

        return EddCaseResource::make($data)->toArray();
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024) . ' KB';
        }

        return round($bytes / (1024 * 1024), 1) . ' MB';
    }
}
