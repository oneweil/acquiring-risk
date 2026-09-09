<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\EddCase;
use app\model\MerchantAssessment;
use app\model\StrAttachment;
use app\model\StrReport;
use app\repository\doopsun\DoopsunMerchantRepository;
use app\repository\doopsun\DoopsunOrderRepository;
use app\repository\risk\EddCaseRepository;
use app\repository\risk\MerchantAssessmentRepository;
use app\repository\risk\StrAttachmentRepository;
use app\repository\risk\StrReportRepository;
use app\resource\StrReportResource;
use think\exception\ValidateException;
use think\facade\Filesystem;
use think\file\UploadedFile;

/**
 * 后台 STR/LTR 报送：建单、人工确认、附件、提交监管、EDD 联动
 */
class StrReportAdminService
{
    public function __construct(
        private readonly StrReportRepository $reportRepo = new StrReportRepository(),
        private readonly StrAttachmentRepository $attachRepo = new StrAttachmentRepository(),
        private readonly EddCaseRepository $eddCaseRepo = new EddCaseRepository(),
        private readonly DoopsunMerchantRepository $doopsunRepo = new DoopsunMerchantRepository(),
        private readonly DoopsunOrderRepository $orderRepo = new DoopsunOrderRepository(),
        private readonly MerchantAssessmentRepository $assessRepo = new MerchantAssessmentRepository(),
    ) {
    }

    /**
     * @return array{pending: int, ltr_month: int, str_month: int, submitted_quarter: int}
     */
    public function stats(): array
    {
        return $this->reportRepo->stats();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function search(array $filters, int $page, int $pageSize): array
    {
        $paginator = $this->reportRepo->search($filters, $page, $pageSize);
        $items     = [];

        foreach ($paginator as $model) {
            /** @var StrReport $model */
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
        $model = $this->reportRepo->find($id);
        if ($model === null) {
            return null;
        }

        return $this->toResourceArray($model, true);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data, ?UploadedFile $file = null): array
    {
        $type    = (string) $data['type'];
        $orderNo = (string) $data['order_no'];

        if ($type === StrReport::TYPE_STR) {
            $desc = trim((string) ($data['suspicious_desc'] ?? ''));
            if ($desc === '') {
                throw new ValidateException('可疑交易 STR 需填写可疑描述');
            }
        }

        $order = $this->orderRepo->findByOrderNo($orderNo);
        if ($order === null) {
            throw new ValidateException('订单不存在：' . $orderNo);
        }

        $merchantId = trim((string) ($order['merchantid'] ?? ''));
        if ($merchantId === '') {
            throw new ValidateException('订单缺少商户号，无法创建报送');
        }

        $existing = $this->reportRepo->findActiveByOrderNo($orderNo);
        if ($existing !== null) {
            throw new ValidateException('该订单已存在报送记录：' . $existing->report_no);
        }

        $merchantName = $merchantId;
        $merchant     = $this->doopsunRepo->findByMerchantId($merchantId);
        if ($merchant !== null) {
            $merchantName = (string) ($merchant['name'] ?? $merchantId);
        }

        $currency = strtoupper((string) $data['currency']);
        $amount   = (float) $data['amount_val'];
        $reason   = trim((string) ($data['trigger_reason'] ?? ''));
        if ($reason === '') {
            $reason = $type === StrReport::TYPE_LTR ? '人工创建大额报送' : '人工创建可疑报送';
        }

        $status = StrReport::STATUS_GENERATED;
        if ($file !== null) {
            $this->assertUploadFile($file);
            $status = StrReport::STATUS_UPLOADED;
        }

        $model = $this->reportRepo->create([
            'report_no'       => $this->reportRepo->nextReportNo(),
            'type'            => $type,
            'trigger_mode'    => StrReport::TRIGGER_MANUAL,
            'merchant_id'     => $merchantId,
            'merchant_name'   => mb_substr($merchantName, 0, 128),
            'order_no'        => $orderNo,
            'currency'        => $currency,
            'amount_val'      => $amount,
            'amount_display'  => StrReport::formatAmountDisplay($currency, $amount),
            'trigger_reason'  => mb_substr($reason, 0, 512),
            'suspicious_desc' => $type === StrReport::TYPE_STR
                ? (string) $data['suspicious_desc']
                : null,
            'status'          => $status,
            'submitter'       => session('admin_user.username') ?: '—',
            'hit_rule_ids'    => null,
        ]);

        if ($file !== null) {
            $this->storeUploadedFile((int) $model->id, $file, false);
        }

        if ($type === StrReport::TYPE_STR) {
            $this->ensureEddFromStr($model);
        }

        return $this->toResourceArray($this->requireReport((int) $model->id), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function confirm(int $id, string $reviewRemark): array
    {
        $model = $this->requireReport($id);
        if ((string) $model->status !== StrReport::STATUS_PENDING_CONFIRM) {
            throw new ValidateException('当前状态无法确认上报');
        }

        $remark = trim($reviewRemark);
        if ($remark === '') {
            throw new ValidateException('请填写审核说明');
        }

        $reviewer = session('admin_user.username') ?: '合规专员';
        $now      = date('Y-m-d H:i:s');

        $this->reportRepo->update($id, [
            'status'        => StrReport::STATUS_UPLOADED,
            'reviewer'      => $reviewer,
            'submitter'     => $reviewer,
            'review_remark' => mb_substr($remark, 0, 1000),
            'reviewed_at'   => $now,
        ]);

        $model = $this->requireReport($id);
        $this->generateAutoAttachment($model);

        if ((string) $model->getAttr('type') === StrReport::TYPE_STR) {
            $this->ensureEddFromStr($model);
        }

        return $this->toResourceArray($this->requireReport($id), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function dismiss(int $id, string $dismissReason): array
    {
        $model = $this->requireReport($id);
        if ((string) $model->status !== StrReport::STATUS_PENDING_CONFIRM) {
            throw new ValidateException('当前状态无法操作');
        }

        $reason = trim($dismissReason);
        if ($reason === '') {
            throw new ValidateException('请填写排除理由');
        }

        $reviewer = session('admin_user.username') ?: '合规专员';

        $this->reportRepo->update($id, [
            'status'         => StrReport::STATUS_DISMISSED,
            'reviewer'       => $reviewer,
            'dismiss_reason' => mb_substr($reason, 0, 1000),
            'reviewed_at'    => date('Y-m-d H:i:s'),
        ]);

        return $this->toResourceArray($this->requireReport($id), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function upload(int $id, UploadedFile $file): array
    {
        $model  = $this->requireReport($id);
        $status = (string) $model->status;
        if (!in_array($status, [StrReport::STATUS_GENERATED, StrReport::STATUS_REJECTED], true)) {
            throw new ValidateException('当前状态不可上传附件');
        }

        $this->assertUploadFile($file);
        $this->storeUploadedFile($id, $file, false);

        $this->reportRepo->update($id, [
            'status' => StrReport::STATUS_UPLOADED,
        ]);

        return $this->toResourceArray($this->requireReport($id), true);
    }

    /**
     * @return array<string, mixed>
     */
    public function submit(int $id): array
    {
        $model = $this->requireReport($id);
        if ((string) $model->status !== StrReport::STATUS_UPLOADED) {
            throw new ValidateException('当前状态不可提交监管');
        }

        $count = $this->reportRepo->countAttachments($id);
        if ($count < 1) {
            throw new ValidateException('请先上传报送附件');
        }

        $submitter = session('admin_user.username') ?: '合规专员';

        $this->reportRepo->update($id, [
            'status'       => StrReport::STATUS_SUBMITTED,
            'submitter'    => $submitter,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->toResourceArray($this->requireReport($id), true);
    }

    public function findAttachment(int $id): ?StrAttachment
    {
        return $this->attachRepo->find($id);
    }

    public function absoluteStoragePath(StrAttachment $attach): string
    {
        $relative = str_replace('\\', '/', (string) $attach->storage_path);

        return Filesystem::disk('public')->path($relative);
    }

    private function requireReport(int $id): StrReport
    {
        $model = $this->reportRepo->find($id);
        if ($model === null) {
            throw new ValidateException('STR 报送不存在');
        }

        return $model;
    }

    /**
     * 确认/新建 STR 后幂等创建 EDD
     */
    private function ensureEddFromStr(StrReport $report): void
    {
        if ((string) $report->getAttr('type') !== StrReport::TYPE_STR) {
            return;
        }

        $linked = (string) $report->report_no;
        if ($this->eddCaseRepo->findByLinkedStrId($linked) !== null) {
            return;
        }

        $merchantId   = (string) $report->merchant_id;
        $merchantName = (string) $report->merchant_name;
        $riskLevel    = MerchantAssessment::RISK_LEVEL_MID;
        $assess       = $this->assessRepo->findByMerchantId($merchantId);
        if ($assess !== null) {
            $riskLevel = (string) $assess->risk_level;
        }

        $this->eddCaseRepo->create([
            'case_no'       => $this->eddCaseRepo->nextCaseNo(),
            'merchant_id'   => $merchantId,
            'merchant_name' => mb_substr($merchantName !== '' ? $merchantName : $merchantId, 0, 128),
            'trigger'       => EddCase::TRIGGER_STR_LINK,
            'risk_level'    => $riskLevel,
            'status'        => EddCase::STATUS_PENDING,
            'deadline'      => date('Y-m-d', strtotime('+30 days')),
            'assignee'      => null,
            'progress'      => 0,
            'checklist'     => [],
            'notes'         => '由 ' . $linked . ' 自动触发创建',
            'linked_str_id' => $linked,
        ]);
    }

    private function assertUploadFile(UploadedFile $file): void
    {
        $ext = strtolower((string) $file->extension());
        if ($ext === '' || !in_array($ext, StrAttachment::ALLOWED_EXTENSIONS, true)) {
            throw new ValidateException('不支持的文件类型，仅支持 XML / PDF / ZIP / XLSX');
        }

        $size = (int) $file->getSize();
        if ($size <= 0 || $size > StrAttachment::MAX_BYTES) {
            throw new ValidateException('单文件大小须在 10MB 以内');
        }
    }

    private function storeUploadedFile(int $reportId, UploadedFile $file, bool $isAuto): StrAttachment
    {
        $ext    = strtolower((string) $file->extension());
        $subdir = 'str/' . date('Ymd');
        $saved  = Filesystem::disk('public')->putFile($subdir, $file);
        if ($saved === false || $saved === '') {
            throw new ValidateException('文件保存失败');
        }

        $now = date('Y-m-d H:i:s');

        return $this->attachRepo->create([
            'str_report_id' => $reportId,
            'file_name'     => mb_substr((string) $file->getOriginalName(), 0, 255),
            'file_size'     => (int) $file->getSize(),
            'storage_path'  => str_replace('\\', '/', (string) $saved),
            'file_type'     => StrAttachment::normalizeFileType($ext),
            'is_auto'       => $isAuto,
            'uploaded_at'   => $now,
        ]);
    }

    private function generateAutoAttachment(StrReport $report): void
    {
        $prefix = strtoupper((string) $report->getAttr('type'));
        $name   = $prefix . '_' . $report->report_no . '_auto.xml';
        $xml    = $this->buildPlaceholderXml($report);

        $subdir = 'str/' . date('Ymd');
        $disk   = Filesystem::disk('public');
        $absDir = $disk->path($subdir);
        if (!is_dir($absDir) && !mkdir($absDir, 0755, true) && !is_dir($absDir)) {
            throw new ValidateException('自动附件目录创建失败');
        }
        $rel = $subdir . '/' . $name;
        $abs = $disk->path($rel);
        if (file_put_contents($abs, $xml) === false) {
            throw new ValidateException('自动附件生成失败');
        }

        $bytes = strlen($xml);
        $now   = date('Y-m-d H:i:s');

        $this->attachRepo->create([
            'str_report_id' => (int) $report->id,
            'file_name'     => $name,
            'file_size'     => $bytes,
            'storage_path'  => str_replace('\\', '/', $rel),
            'file_type'     => StrAttachment::TYPE_XML,
            'is_auto'       => true,
            'uploaded_at'   => $now,
        ]);
    }

    private function buildPlaceholderXml(StrReport $report): string
    {
        $type = strtoupper((string) $report->getAttr('type'));

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<Report type="' . htmlspecialchars($type, ENT_XML1) . '">' . "\n"
            . '  <ReportNo>' . htmlspecialchars((string) $report->report_no, ENT_XML1) . '</ReportNo>' . "\n"
            . '  <MerchantId>' . htmlspecialchars((string) $report->merchant_id, ENT_XML1) . '</MerchantId>' . "\n"
            . '  <OrderNo>' . htmlspecialchars((string) $report->order_no, ENT_XML1) . '</OrderNo>' . "\n"
            . '  <Amount currency="' . htmlspecialchars((string) $report->currency, ENT_XML1) . '">'
            . htmlspecialchars((string) $report->amount_val, ENT_XML1) . '</Amount>' . "\n"
            . '  <TriggerReason>' . htmlspecialchars((string) $report->trigger_reason, ENT_XML1) . '</TriggerReason>' . "\n"
            . '  <GeneratedAt>' . htmlspecialchars(date('c'), ENT_XML1) . '</GeneratedAt>' . "\n"
            . '</Report>' . "\n";
    }

    /**
     * @return array<string, mixed>
     */
    private function toResourceArray(StrReport $model, bool $withDetail = false): array
    {
        $attachments = $this->attachRepo->listByReportId((int) $model->id);
        $attachList  = [];
        foreach ($attachments as $attach) {
            $attachList[] = [
                'id'           => (int) $attach->id,
                'name'         => (string) $attach->file_name,
                'size'         => $this->formatSize((int) $attach->file_size),
                'file_size'    => (int) $attach->file_size,
                'file_type'    => (string) $attach->file_type,
                'is_auto'      => (bool) $attach->is_auto,
                'upload_time'  => (string) $attach->uploaded_at,
                'download_url' => '/admin/str_report/attachment/download?id=' . (int) $attach->id,
            ];
        }

        $data                     = $model->toArray();
        $data['attachment_count'] = count($attachList);

        if ($withDetail) {
            $data['attachments'] = $attachList;
            $linked              = $this->eddCaseRepo->findByLinkedStrId((string) $model->report_no);
            $data['linked_edd']  = $linked === null ? null : [
                'id'           => (int) $linked->id,
                'case_no'      => (string) $linked->case_no,
                'status'       => (string) $linked->status,
                'status_label' => EddCase::STATUS_LABELS[(string) $linked->status] ?? (string) $linked->status,
            ];
        }

        return StrReportResource::make($data)->toArray();
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
