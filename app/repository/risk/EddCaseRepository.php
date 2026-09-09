<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\EddCase;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Paginator;

class EddCaseRepository
{
    /**
     * @param array{keyword?: string, trigger?: string, status?: string} $filters
     *
     * @throws DbException
     */
    public function search(array $filters, ?int $page = null, ?int $pageSize = null): Paginator
    {
        $query = EddCase::order('id', 'desc');

        if (isset($filters['trigger'])) {
            $query->where('trigger', $filters['trigger']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['keyword'])) {
            $like = '%' . addcslashes((string) $filters['keyword'], '%_\\') . '%';
            $query->where(function ($q) use ($like): void {
                $q->whereLike('merchant_id', $like)->whereLike('merchant_name', $like, 'OR')
                    ->whereLike('case_no', $like, 'OR');
            });
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
     * @return array{active: int, pending: int, passed: int, failed: int}
     *
     * @throws DbException
     */
    public function stats(): array
    {
        $rows = EddCase::field('status, COUNT(*) AS cnt')
            ->group('status')
            ->select();

        $counts = [
            EddCase::STATUS_PENDING    => 0,
            EddCase::STATUS_COLLECTING => 0,
            EddCase::STATUS_REVIEWING  => 0,
            EddCase::STATUS_PASSED     => 0,
            EddCase::STATUS_REJECTED   => 0,
            EddCase::STATUS_EXPIRED    => 0,
        ];

        foreach ($rows as $row) {
            $status = (string) $row->getAttr('status');
            $counts[$status] = (int) $row->getAttr('cnt');
        }

        return [
            'active'  => $counts[EddCase::STATUS_COLLECTING] + $counts[EddCase::STATUS_REVIEWING],
            'pending' => $counts[EddCase::STATUS_PENDING],
            'passed'  => $counts[EddCase::STATUS_PASSED],
            'failed'  => $counts[EddCase::STATUS_REJECTED] + $counts[EddCase::STATUS_EXPIRED],
        ];
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?EddCase
    {
        return EddCase::find($id);
    }

    /**
     * @throws DbException
     */
    public function findByCaseNo(string $caseNo): ?EddCase
    {
        return EddCase::where('case_no', $caseNo)->find();
    }

    /**
     * @throws DbException
     */
    public function findByLinkedStrId(string $linkedStrId): ?EddCase
    {
        if ($linkedStrId === '') {
            return null;
        }

        return EddCase::where('linked_str_id', $linkedStrId)->find();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): EddCase
    {
        return EddCase::create($data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): EddCase
    {
        $model = EddCase::find($id);
        if ($model === null) {
            throw new ModelNotFoundException('EDD 工单不存在');
        }

        $model->save($data);

        return $model;
    }

    /**
     * @throws DbException
     */
    public function nextCaseNo(): string
    {
        $prefix = 'EDD' . date('Ymd');
        $last   = EddCase::whereLike('case_no', $prefix . '%')
            ->order('case_no', 'desc')
            ->value('case_no');

        $seq = 1;
        if (is_string($last) && preg_match('/^EDD\d{8}(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
