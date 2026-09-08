<?php

declare(strict_types=1);

namespace app\repository\doopsun;

use think\facade\Db;
use think\Paginator;

/**
 * 收单主站订单只读查询（doopsun_db.doopsun_order）
 */
class DoopsunOrderRepository
{
    private const CONN = 'doopsun_db';

    private const TABLE = 'doopsun_order';

    private const MERCHANT_TABLE = 'doopsun_merchants';

    /** doopsun 成功态（与商户当日聚合约定一致） */
    public const DOOPSUN_STATUS_SUCCESS = 1;

    /** del=3 表示走 3DS（见 docs/06） */
    public const DEL_THREE_DS = 3;

    /** @var array<int, string> */
    public const CARD_TYPE_LABELS = [
        0 => '未知',
        1 => 'Visa',
        2 => 'Mastercard',
    ];

    /** @var list<string> */
    private const FALLBACK_CURRENCIES = ['USD', 'EUR', 'GBP', 'JPY', 'HKD'];

    /**
     * @param array{
     *   merchant_id?: string,
     *   order_no?: string,
     *   currency?: string,
     *   order_nos?: list<string>,
     *   doopsun_status?: int,
     *   doopsun_status_ne?: int,
     *   exclude_order_nos?: list<string>
     * } $filters
     */
    public function search(array $filters, int $page, int $pageSize): Paginator
    {
        $query = $this->db()->table(self::TABLE);

        $this->applyFilters($query, $filters);

        return $query
            ->order('doopsun_orderdate', 'desc')
            ->order('id', 'desc')
            ->paginate([
                'list_rows' => $pageSize,
                'page'      => $page,
            ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByOrderNo(string $orderNo): ?array
    {
        $orderNo = trim($orderNo);
        if ($orderNo === '') {
            return null;
        }

        $row = $this->db()->table(self::TABLE)
            ->where('orderid', $orderNo)
            ->order('doopsun_orderdate', 'desc')
            ->order('id', 'desc')
            ->find();

        return is_array($row) ? $row : null;
    }

    /**
     * 通道订单号唯一，用于详情精确定位
     *
     * @return array<string, mixed>|null
     */
    public function findByChannelNo(string $channelNo): ?array
    {
        $channelNo = trim($channelNo);
        if ($channelNo === '') {
            return null;
        }

        $row = $this->db()->table(self::TABLE)
            ->where('doopsun_orderid', $channelNo)
            ->find();

        return is_array($row) ? $row : null;
    }

    /**
     * @param list<string|int> $merchantIds
     * @return array<string, string> merchantId => name
     */
    public function mapMerchantNames(array $merchantIds): array
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

        $rows = $this->db()->table(self::MERCHANT_TABLE)
            ->whereIn('merchantId', $ids)
            ->field(['merchantId', 'name'])
            ->select()
            ->toArray();

        $map = [];
        foreach ($rows as $row) {
            $mid = (string) ($row['merchantId'] ?? '');
            if ($mid === '') {
                continue;
            }
            $map[$mid] = trim((string) ($row['name'] ?? ''));
        }

        return $map;
    }

    /**
     * @return list<string>
     */
    public function distinctCurrencies(): array
    {
        try {
            $rows = $this->db()->table(self::TABLE)
                ->where('currency', '<>', '')
                ->whereNotNull('currency')
                ->distinct(true)
                ->order('currency', 'asc')
                ->column('currency');
        } catch (\Throwable) {
            return self::FALLBACK_CURRENCIES;
        }

        $out = [];
        foreach ($rows as $cur) {
            $cur = strtoupper(trim((string) $cur));
            if ($cur !== '' && !in_array($cur, $out, true)) {
                $out[] = $cur;
            }
        }

        return $out !== [] ? $out : self::FALLBACK_CURRENCIES;
    }

    /**
     * @param \think\db\BaseQuery $query
     * @param array<string, mixed> $filters
     */
    private function applyFilters(mixed $query, array $filters): void
    {
        if (isset($filters['order_nos'])) {
            $nos = array_values(array_filter(array_map('strval', $filters['order_nos'])));
            if ($nos === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('orderid', $nos);
            }
        }

        if (isset($filters['exclude_order_nos'])) {
            $exclude = array_values(array_filter(array_map('strval', $filters['exclude_order_nos'])));
            if ($exclude !== []) {
                $query->whereNotIn('orderid', $exclude);
            }
        }

        if (isset($filters['merchant_id']) && $filters['merchant_id'] !== '') {
            $query->whereLike('merchantid', '%' . $filters['merchant_id'] . '%');
        }

        if (isset($filters['order_no']) && $filters['order_no'] !== '') {
            $query->whereLike('orderid', '%' . $filters['order_no'] . '%');
        }

        if (isset($filters['currency']) && $filters['currency'] !== '') {
            $query->where('currency', $filters['currency']);
        }

        if (isset($filters['doopsun_status'])) {
            $query->where('status', (int) $filters['doopsun_status']);
        }

        if (isset($filters['doopsun_status_ne'])) {
            $query->where('status', '<>', (int) $filters['doopsun_status_ne']);
        }
    }

    private function db()
    {
        return Db::connect(self::CONN);
    }
}
