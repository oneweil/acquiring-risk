<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\Alert;
use app\model\AlertAttachment;
use app\model\StrReport;
use app\repository\risk\AlertAttachmentRepository;
use app\repository\risk\AlertRepository;
use app\repository\risk\StrReportRepository;
use app\resource\AlertResource;
use think\exception\ValidateException;
use think\facade\Filesystem;
use think\file\UploadedFile;

/**
 * 后台交易预警：列表、详情、调单上传、处置（不改订单授权终态）
 */
class AlertAdminService
{
    public function __construct(
        private readonly AlertRepository $alertRepo = new AlertRepository(),
        private readonly AlertAttachmentRepository $attachRepo = new AlertAttachmentRepository(),
        private readonly StrReportRepository $strRepo = new StrReportRepository(),
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function search(array $filters, int $page, int $pageSize): array
    {
        $paginator = $this->alertRepo->search($filters, $page, $pageSize);
        $items     = [];
        $ids       = [];

        foreach ($paginator as $model) {
            /** @var Alert $model */
            $ids[] = (int) $model->id;
        }

        $attachCounts = $this->attachRepo->countsByAlertIds($ids);
        $strMap       = $this->loadStrSummaries($paginator);

        foreach ($paginator as $model) {
            /** @var Alert $model */
            $id = (int) $model->id;
            $items[] = $this->toResourceArray(
                $model,
                false,
                $attachCounts[$id] ?? 0,
                $strMap[$id] ?? null
            );
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
        $model = $this->alertRepo->find($id);
        if ($model === null) {
            return null;
        }

        return $this->toResourceArray(
            $model,
            true,
            $this->attachRepo->countByAlertId($id),
            $this->resolveStrSummary($model)
        );
    }

    /**
     * @param array{action: string, remark: ?string, inquiry_desc: ?string} $data
     * @return array<string, mixed>
     */
    public function handle(int $id, array $data): array
    {
        $model  = $this->requireAlert($id);
        $action = (string) $data['action'];

        if ($model->isClosed()) {
            throw new ValidateException('预警已关闭，不可再处置');
        }

        $isInquiry = $model->isInquiry();

        if (!$isInquiry && in_array($action, [Alert::ACTION_SUBMIT_MATERIALS, Alert::ACTION_COMPLETE], true)) {
            throw new ValidateException('当前处置策略不支持该调单操作');
        }

        if ($isInquiry && $action === Alert::ACTION_CLOSE) {
            throw new ValidateException('调单预警请使用「调单完成」或「误报关闭」');
        }

        if (!$isInquiry && !in_array($action, [Alert::ACTION_CLOSE, Alert::ACTION_FALSE_POSITIVE], true)) {
            throw new ValidateException('处置结论无效');
        }

        $needsAttachment = $isInquiry
            && in_array($action, [Alert::ACTION_SUBMIT_MATERIALS, Alert::ACTION_COMPLETE], true);
        if ($needsAttachment && $this->attachRepo->countByAlertId($id) < 1) {
            throw new ValidateException('请上传调单相关材料后再提交');
        }

        $now    = date('Y-m-d H:i:s');
        $update = [
            'handle_remark' => $data['remark'],
        ];

        if ($isInquiry && array_key_exists('inquiry_desc', $data)) {
            $update['inquiry_desc'] = $data['inquiry_desc'];
        }

        if ($action === Alert::ACTION_SUBMIT_MATERIALS) {
            $update['status'] = Alert::STATUS_PROCESSING;
        } else {
            $update['status']      = Alert::STATUS_CLOSED;
            $update['handled_at']  = $now;
            $operatorId            = session('admin_user.id');
            $update['operator_id'] = is_numeric($operatorId) ? (int) $operatorId : null;
        }

        $this->alertRepo->update($id, $update);

        return $this->toResourceArray(
            $this->requireAlert($id),
            true,
            $this->attachRepo->countByAlertId($id),
            $this->resolveStrSummary($this->requireAlert($id))
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function upload(int $id, UploadedFile $file): array
    {
        $model = $this->requireAlert($id);
        if ($model->isClosed()) {
            throw new ValidateException('预警已关闭，不可上传附件');
        }
        if (!$model->isInquiry()) {
            throw new ValidateException('仅调单预警可上传材料');
        }

        $ext = strtolower((string) $file->extension());
        if ($ext === '' || !in_array($ext, AlertAttachment::ALLOWED_EXTENSIONS, true)) {
            throw new ValidateException('不支持的文件类型');
        }

        if ((int) $file->getSize() > AlertAttachment::MAX_BYTES) {
            throw new ValidateException('文件过大，最大 10MB');
        }

        $subdir = 'alert/' . date('Ymd');
        $saved  = Filesystem::disk('public')->putFile($subdir, $file);
        if ($saved === false || $saved === '') {
            throw new ValidateException('文件保存失败');
        }

        $now = date('Y-m-d H:i:s');
        $this->attachRepo->create([
            'alert_id'     => $id,
            'file_name'    => mb_substr((string) $file->getOriginalName(), 0, 255),
            'file_size'    => (int) $file->getSize(),
            'storage_path' => str_replace('\\', '/', (string) $saved),
            'file_type'    => AlertAttachment::normalizeFileType($ext),
            'uploaded_at'  => $now,
        ]);

        if ((string) $model->status === Alert::STATUS_PENDING) {
            $this->alertRepo->update($id, ['status' => Alert::STATUS_PROCESSING]);
        }

        return $this->toResourceArray(
            $this->requireAlert($id),
            true,
            $this->attachRepo->countByAlertId($id),
            $this->resolveStrSummary($this->requireAlert($id))
        );
    }

    public function findAttachment(int $id): ?AlertAttachment
    {
        return $this->attachRepo->find($id);
    }

    public function absoluteStoragePath(AlertAttachment $attach): string
    {
        $relative = str_replace('\\', '/', (string) $attach->storage_path);

        return Filesystem::disk('public')->path($relative);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResourceArray(
        Alert $model,
        bool $withAttachments,
        int $attachmentCount,
        ?array $strSummary
    ): array {
        $payload = $model->toArray();
        $payload['attachment_count'] = $attachmentCount;
        $payload['str_summary']      = $strSummary;

        if ($withAttachments) {
            $payload['attachments'] = $this->mapAttachments($this->attachRepo->listByAlertId((int) $model->id));
        }

        return AlertResource::make($payload)->toArray();
    }

    /**
     * @param iterable<\app\model\AlertAttachment> $rows
     * @return list<array<string, mixed>>
     */
    private function mapAttachments(iterable $rows): array
    {
        $list = [];
        foreach ($rows as $row) {
            $list[] = [
                'id'           => (int) $row->id,
                'file_name'    => (string) $row->file_name,
                'file_size'    => (int) $row->file_size,
                'file_type'    => (string) $row->file_type,
                'storage_path' => (string) $row->storage_path,
                'uploaded_at'  => (string) $row->uploaded_at,
                'download_url' => '/admin/alert/attachment/download?attachment_id=' . (int) $row->id,
            ];
        }

        return $list;
    }

    /**
     * @param iterable<Alert> $models
     * @return array<int, array<string, mixed>>
     */
    private function loadStrSummaries(iterable $models): array
    {
        $map = [];
        foreach ($models as $model) {
            $summary = $this->resolveStrSummary($model);
            if ($summary !== null) {
                $map[(int) $model->id] = $summary;
            }
        }

        return $map;
    }

    /**
     * @return array{report_no: string, status: ?string, status_label: ?string}|null
     */
    private function resolveStrSummary(Alert $model): ?array
    {
        $reportNo = trim((string) ($model->str_report_id ?? ''));
        if ($reportNo !== '') {
            $report = $this->strRepo->findByReportNo($reportNo);
            if ($report !== null) {
                return $this->formatStrSummary($report);
            }

            return [
                'report_no'    => $reportNo,
                'status'       => null,
                'status_label' => null,
            ];
        }

        $orderNo = trim((string) ($model->order_no ?? ''));
        if ($orderNo === '') {
            return null;
        }

        $report = $this->strRepo->findActiveByOrderNo($orderNo);
        if ($report === null) {
            return null;
        }

        return $this->formatStrSummary($report);
    }

    /**
     * @return array{report_no: string, status: string, status_label: string}
     */
    private function formatStrSummary(StrReport $report): array
    {
        $status = (string) $report->status;

        return [
            'report_no'    => (string) $report->report_no,
            'status'       => $status,
            'status_label' => StrReport::STATUS_LABELS[$status] ?? $status,
        ];
    }

    private function requireAlert(int $id): Alert
    {
        $model = $this->alertRepo->find($id);
        if ($model === null) {
            throw new ValidateException('预警不存在');
        }

        return $model;
    }
}
