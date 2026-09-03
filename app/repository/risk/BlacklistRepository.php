<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\Blacklist;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Paginator;

class BlacklistRepository
{
    /**
     * @param array{type?: string, keyword?: string} $filters
     *
     * @throws DbException
     */
    public function search(array $filters, ?int $page = null, ?int $pageSize = null): Paginator
    {
        $query = Blacklist::order('id', 'desc');

        $type = trim((string) ($filters['type'] ?? ''));
        if ($type !== '' && in_array($type, Blacklist::TYPES, true)) {
            $query->where('type', $type);
        }

        $keyword = trim((string) ($filters['keyword'] ?? ''));
        if ($keyword !== '') {
            $like = '%' . addcslashes($keyword, '%_\\') . '%';
            $query->where(function ($q) use ($like): void {
                $q->whereLike('value', $like)->whereLike('reason', $like, 'OR');
            });
        }

        $pageSize = $pageSize ?? (int) config('paginate.list_rows', 10);
        $page     = $page ?? max(1, (int) request()->param((string) config('paginate.var_page', 'page'), 1));

        // 官方分页查询：https://doc.thinkphp.cn/v8_0/pagination_query.html
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
    public function find(int $id): ?Blacklist
    {
        return Blacklist::find($id);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): Blacklist
    {
        $model = Blacklist::create([
            'code'           => $this->nextCode(),
            'type'           => $data['type'],
            'value'          => $data['value'],
            'reason'         => $data['reason'],
            'risk_level'     => $data['risk_level'],
            'effective_date' => $data['effective_date'],
            'expiry_date'    => $data['expiry_date'],
            'status'         => $data['status'] ? 1 : 0,
        ]);

        return $model;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Blacklist
    {
        $model = Blacklist::find($id);
        if ($model === null) {
            throw new ModelNotFoundException('黑名单不存在');
        }

        $model->save([
            'type'        => $data['type'],
            'value'       => $data['value'],
            'reason'      => $data['reason'],
            'risk_level'  => $data['risk_level'],
            'expiry_date' => $data['expiry_date'],
            'status'      => $data['status'] ? 1 : 0,
        ]);

        return $model;
    }

    /**
     * 同 type+value 且生效中是否已存在（排除自身）
     *
     * @throws DbException
     */
    public function existsActiveDuplicate(string $type, string $value, ?int $excludeId = null): bool
    {
        $query = Blacklist::where('type', $type)
            ->where('value', $value)
            ->where('status', 1);

        if ($excludeId !== null) {
            $query->where('id', '<>', $excludeId);
        }

        return $query->count() > 0;
    }

    /**
     * 供后续 evaluate 调用：生效且未过期
     *
     * @throws DbException
     */
    public function isHit(string $type, string $value): bool
    {
        $today = date('Y-m-d');

        return Blacklist::where('type', $type)
            ->where('value', $value)
            ->where('status', 1)
            ->where(function ($q) use ($today): void {
                $q->whereNull('expiry_date')->whereOr('expiry_date', '>=', $today);
            })
            ->count() > 0;
    }

    /**
     * @throws DbException
     */
    public function nextCode(): string
    {
        $last = Blacklist::order('id', 'desc')->value('code');
        $seq  = 1;
        if (is_string($last) && preg_match('/^BL(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return 'BL' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
