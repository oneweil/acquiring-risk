<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\Alert;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Paginator;

class AlertRepository
{
    /**
     * @param array{risk_level?: string, status?: string, measure_code?: string, exclude_closed?: bool} $filters
     *
     * @throws DbException
     */
    public function search(array $filters, ?int $page = null, ?int $pageSize = null): Paginator
    {
        $query = Alert::order('alerted_at', 'desc')->order('id', 'desc');

        if (isset($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } elseif (!empty($filters['exclude_closed'])) {
            $query->where('status', '<>', Alert::STATUS_CLOSED);
        }

        if (isset($filters['measure_code'])) {
            $query->where('measure_code', $filters['measure_code']);
        }

        $pageSize = $pageSize ?? (int) config('paginate.list_rows', 10);
        $page     = $page ?? max(1, (int) request()->param((string) config('paginate.var_page', 'page'), 1));

        $queryParams = array_filter(
            $filters,
            static fn ($value, $key): bool => $key !== 'exclude_closed' && $value !== null && $value !== '',
            ARRAY_FILTER_USE_BOTH
        );

        return $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
            'query'     => $queryParams,
        ]);
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?Alert
    {
        return Alert::find($id);
    }

    /**
     * @throws DbException
     */
    public function findByAlertNo(string $alertNo): ?Alert
    {
        if ($alertNo === '') {
            return null;
        }

        return Alert::where('alert_no', $alertNo)->find();
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): Alert
    {
        return Alert::create($data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Alert
    {
        $model = Alert::find($id);
        if ($model === null) {
            throw new ModelNotFoundException('预警不存在');
        }

        $model->save($data);

        return $model;
    }

    /**
     * @throws DbException
     */
    public function nextAlertNo(): string
    {
        $prefix = 'AL' . date('Ymd');
        $last   = Alert::whereLike('alert_no', $prefix . '%')
            ->order('alert_no', 'desc')
            ->value('alert_no');

        $seq = 1;
        if (is_string($last) && preg_match('/^AL\d{8}(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
