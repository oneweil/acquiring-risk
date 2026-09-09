<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\StrReport;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Paginator;

class StrReportRepository
{
    /**
     * @param array{
     *   merchant_id?: string,
     *   order_no?: string,
     *   status?: string,
     *   type?: string,
     *   tab?: string
     * } $filters
     *
     * @throws DbException
     */
    public function search(array $filters, ?int $page = null, ?int $pageSize = null): Paginator
    {
        $query = StrReport::order('id', 'desc');

        if (isset($filters['merchant_id'])) {
            $query->whereLike('merchant_id', '%' . addcslashes((string) $filters['merchant_id'], '%_\\') . '%');
        }

        if (isset($filters['order_no'])) {
            $query->whereLike('order_no', '%' . addcslashes((string) $filters['order_no'], '%_\\') . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $tab = (string) ($filters['tab'] ?? 'all');
        if ($tab === 'pending') {
            $query->where('status', StrReport::STATUS_PENDING_CONFIRM);
        } elseif ($tab === 'ltr') {
            $query->where('type', StrReport::TYPE_LTR);
        } elseif ($tab === 'str') {
            $query->where('type', StrReport::TYPE_STR);
        } elseif (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $pageSize = $pageSize ?? (int) config('paginate.list_rows', 10);
        $page     = $page ?? max(1, (int) request()->param((string) config('paginate.var_page', 'page'), 1));

        return $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
            'query'     => array_filter($filters, static fn ($value): bool => $value !== null && $value !== ''),
        ]);
    }

    /**
     * @return array{pending: int, ltr_month: int, str_month: int, submitted_quarter: int}
     *
     * @throws DbException
     */
    public function stats(): array
    {
        $monthStart   = date('Y-m-01 00:00:00');
        $quarterMonth = (int) ceil((int) date('n') / 3);
        $quarterStart = date('Y') . '-' . str_pad((string) (($quarterMonth - 1) * 3 + 1), 2, '0', STR_PAD_LEFT) . '-01 00:00:00';

        $pending = (int) StrReport::where('status', StrReport::STATUS_PENDING_CONFIRM)->count();

        $ltrMonth = (int) StrReport::where('type', StrReport::TYPE_LTR)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $strMonth = (int) StrReport::where('type', StrReport::TYPE_STR)
            ->where('created_at', '>=', $monthStart)
            ->count();

        $submittedQuarter = (int) StrReport::whereIn('status', [
            StrReport::STATUS_SUBMITTED,
            StrReport::STATUS_ARCHIVED,
        ])
            ->where(function ($q) use ($quarterStart): void {
                $q->where('submitted_at', '>=', $quarterStart)
                    ->whereOr(function ($q2) use ($quarterStart): void {
                        $q2->whereNull('submitted_at')->where('created_at', '>=', $quarterStart);
                    });
            })
            ->count();

        return [
            'pending'           => $pending,
            'ltr_month'         => $ltrMonth,
            'str_month'         => $strMonth,
            'submitted_quarter' => $submittedQuarter,
        ];
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?StrReport
    {
        return StrReport::find($id);
    }

    /**
     * @throws DbException
     */
    public function findByReportNo(string $reportNo): ?StrReport
    {
        return StrReport::where('report_no', $reportNo)->find();
    }

    /**
     * 同订单号是否已有未 dismiss 的报送
     *
     * @throws DbException
     */
    public function findActiveByOrderNo(string $orderNo): ?StrReport
    {
        return StrReport::where('order_no', $orderNo)
            ->where('status', '<>', StrReport::STATUS_DISMISSED)
            ->order('id', 'desc')
            ->find();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): StrReport
    {
        return StrReport::create($data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): StrReport
    {
        $model = StrReport::find($id);
        if ($model === null) {
            throw new ModelNotFoundException('STR 报送不存在');
        }

        $model->save($data);

        return $model;
    }

    /**
     * @throws DbException
     */
    public function nextReportNo(): string
    {
        $prefix = 'STR' . date('Ymd');
        $last   = StrReport::whereLike('report_no', $prefix . '%')
            ->order('report_no', 'desc')
            ->value('report_no');

        $seq = 1;
        if (is_string($last) && preg_match('/^STR\d{8}(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @throws DbException
     */
    public function countAttachments(int $reportId): int
    {
        return (int) \app\model\StrAttachment::where('str_report_id', $reportId)->count();
    }
}
