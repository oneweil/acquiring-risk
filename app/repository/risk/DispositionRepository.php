<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\Disposition;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Paginator;

class DispositionRepository
{
    /**
     * @param array{risk_level?: string, scope?: string, status?: bool} $filters 已由 Validate::toListFilters 整理
     *
     * @throws DbException
     */
    public function search(array $filters, ?int $page = null, ?int $pageSize = null): Paginator
    {
        $query = Disposition::order('priority', 'asc')->order('id', 'asc');

        if (isset($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }
        if (isset($filters['scope'])) {
            $query->where('scope', $filters['scope']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status'] ? 1 : 0);
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
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?Disposition
    {
        return Disposition::find($id);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): Disposition
    {
        return Disposition::create([
            'code'        => $data['code'],
            'name'        => $data['name'],
            'description' => $data['description'],
            'risk_level'  => $data['risk_level'],
            'scope'       => $data['scope'],
            'priority'    => $data['priority'],
            'is_block'    => $data['is_block'] ? 1 : 0,
            'push_alert'  => $data['push_alert'] ? 1 : 0,
            'status'      => $data['status'] ? 1 : 0,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Disposition
    {
        $model = Disposition::find($id);
        if ($model === null) {
            throw new ModelNotFoundException('处置策略不存在');
        }

        $model->save([
            'code'        => $data['code'],
            'name'        => $data['name'],
            'description' => $data['description'],
            'risk_level'  => $data['risk_level'],
            'scope'       => $data['scope'],
            'priority'    => $data['priority'],
            'is_block'    => $data['is_block'] ? 1 : 0,
            'push_alert'  => $data['push_alert'] ? 1 : 0,
            'status'      => $data['status'] ? 1 : 0,
        ]);

        return $model;
    }

    /**
     * code 是否已存在（排除自身）
     *
     * @throws DbException
     */
    public function existsCode(string $code, ?int $excludeId = null): bool
    {
        $query = Disposition::where('code', $code);
        if ($excludeId !== null) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->count() > 0;
    }

    /**
     * 启用中的策略（规则页下拉），按 priority 升序
     *
     * @return list<Disposition>
     *
     * @throws DbException
     */
    public function listEnabledOrdered(): array
    {
        return Disposition::where('status', 1)
            ->order('priority', 'asc')
            ->order('id', 'asc')
            ->select()
            ->all();
    }

    /**
     * @return array<string, Disposition> code => model
     *
     * @throws DbException
     */
    public function mapByCode(): array
    {
        $map = [];
        foreach (Disposition::select() as $row) {
            $map[(string) $row->code] = $row;
        }

        return $map;
    }
}
