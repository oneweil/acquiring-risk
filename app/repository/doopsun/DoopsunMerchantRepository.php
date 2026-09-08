<?php

declare(strict_types=1);

namespace app\repository\doopsun;

use think\facade\Db;
use think\Paginator;

/**
 * 收单主站商户只读查询（doopsun_db.doopsun_merchants）
 */
class DoopsunMerchantRepository
{
    private const CONN = 'doopsun_db';

    private const TABLE = 'doopsun_merchants';

    private const ORDER_TABLE = 'doopsun_order';

    /**
     * @param array{
     *   merchant_id?: string,
     *   name?: string,
     *   review_status?: string,
     *   trading_status?: string,
     *   merchant_ids?: list<string>
     * } $filters
     */
    public function search(array $filters, int $page, int $pageSize): Paginator
    {
        $query = $this->db()->table(self::TABLE);

        if (isset($filters['merchant_ids'])) {
            $ids = array_values(array_filter(array_map('strval', $filters['merchant_ids'])));
            if ($ids === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('merchantId', $ids);
            }
        }

        if (isset($filters['merchant_id']) && $filters['merchant_id'] !== '') {
            $query->whereLike('merchantId', '%' . $filters['merchant_id'] . '%');
        }

        if (isset($filters['name']) && $filters['name'] !== '') {
            $query->whereLike('name', '%' . $filters['name'] . '%');
        }

        if (isset($filters['review_status'])) {
            if ($filters['review_status'] === 'approved') {
                $query->where('status', 0);
            } elseif ($filters['review_status'] === 'rejected') {
                $query->where('status', 2);
            }
        }

        if (isset($filters['trading_status'])) {
            $this->applyTradingStatusFilter($query, (string) $filters['trading_status']);
        }

        return $query
            ->order('merchantId', 'desc')
            ->paginate([
                'list_rows' => $pageSize,
                'page'      => $page,
            ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByMerchantId(string $merchantId): ?array
    {
        if ($merchantId === '') {
            return null;
        }

        $row = $this->db()->table(self::TABLE)
            ->where('merchantId', $merchantId)
            ->find();

        return is_array($row) ? $row : null;
    }

    public function countAll(): int
    {
        return (int) $this->db()->table(self::TABLE)->count();
    }

    /**
     * @return array{normal: int, suspended: int, watch: int, not_opened: int, restricted: int}
     */
    public function countByTradingBucket(): array
    {
        $rows = $this->db()->table(self::TABLE)
            ->fieldRaw($this->tradingStatusCaseSql() . ' AS bucket')
            ->fieldRaw('COUNT(*) AS cnt')
            ->group('bucket')
            ->select()
            ->toArray();

        $out = [
            'normal'      => 0,
            'suspended'   => 0,
            'watch'       => 0,
            'not_opened'  => 0,
            'restricted'  => 0,
        ];

        foreach ($rows as $row) {
            $bucket = (string) ($row['bucket'] ?? '');
            $cnt    = (int) ($row['cnt'] ?? 0);
            if (isset($out[$bucket])) {
                $out[$bucket] = $cnt;
            }
        }

        return $out;
    }

    /**
     * 当日成功订单笔数/金额（按商户聚合）
     *
     * @param list<string|int> $merchantIds
     * @return array<string, array{today_count: int, today_amount: float}>
     */
    public function todayOrderAggByMerchantIds(array $merchantIds): array
    {
        $ids = [];
        foreach ($merchantIds as $id) {
            $id = trim((string) $id);
            if ($id !== '') {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            return [];
        }

        $dayStart = strtotime(date('Y-m-d 00:00:00'));
        $dayEnd   = $dayStart + 86400;

        $rows = $this->db()->table(self::ORDER_TABLE)
            ->whereIn('merchantid', $ids)
            ->where('status', 1)
            ->where('doopsun_orderdate', '>=', $dayStart)
            ->where('doopsun_orderdate', '<', $dayEnd)
            ->field('merchantid')
            ->fieldRaw('COUNT(*) AS today_count')
            ->fieldRaw('COALESCE(SUM(orderamount),0) AS today_amount')
            ->group('merchantid')
            ->select()
            ->toArray();

        $map = [];
        foreach ($rows as $row) {
            $mid = (string) ($row['merchantid'] ?? '');
            if ($mid === '') {
                continue;
            }
            $map[$mid] = [
                'today_count'  => (int) ($row['today_count'] ?? 0),
                'today_amount' => (float) ($row['today_amount'] ?? 0),
            ];
        }

        return $map;
    }

    /**
     * @param \think\db\BaseQuery $query
     */
    private function applyTradingStatusFilter(mixed $query, string $status): void
    {
        switch ($status) {
            case 'suspended':
                $query->where(function ($q): void {
                    $q->where('is_abate', 0)->whereOr('status', 2);
                });
                break;
            case 'not_opened':
                $query->where('status', 1)->where('is_abate', '<>', 0);
                break;
            case 'watch':
                $query->where('status', 0)->where('is_warning', 1)->where('is_abate', '<>', 0);
                break;
            case 'normal':
                $query->where('status', 0)
                    ->where(function ($q): void {
                        $q->whereNull('is_warning')->whereOr('is_warning', 0);
                    })
                    ->where('is_abate', '<>', 0);
                break;
            case 'restricted':
                $query->whereRaw('1 = 0');
                break;
            default:
                break;
        }
    }

    private function tradingStatusCaseSql(): string
    {
        return "CASE
            WHEN is_abate = 0 OR status = 2 THEN 'suspended'
            WHEN status = 1 THEN 'not_opened'
            WHEN status = 0 AND IFNULL(is_warning, 0) = 1 THEN 'watch'
            WHEN status = 0 THEN 'normal'
            ELSE 'not_opened'
        END";
    }

    private function db()
    {
        return Db::connect(self::CONN);
    }
}
