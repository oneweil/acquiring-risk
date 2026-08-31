<?php

declare(strict_types=1);

namespace app\service\mock;

/**
 * 订单监控假数据（演示阶段，后续替换为 Model/Repository）
 */
class OrderMockService
{
    private const PAGE_SIZE = 10;

    /** @var array<int, array<string, mixed>>|null */
    private static ?array $orders = null;

    /**
     * @return array<string, list<string>>
     */
    public static function filterOptions(): array
    {
        return [
            'currency'   => ['USD', 'EUR', 'GBP', 'JPY', 'HKD'],
            'risk_level' => ['极高风险', '高风险', '中风险', '低风险'],
            'status'     => ['成功', '拦截', '审核中', '失败'],
        ];
    }

    /**
     * @param array<string, string> $filters
     * @return array{items: list<array<string, mixed>>, total: int}
     */
    public static function search(array $filters, int $page, ?int $pageSize = null): array
    {
        $pageSize = $pageSize ?? (int) config('paginate.list_rows', 10);
        $page     = max(1, $page);
        $filtered = self::filter(self::all(), $filters);
        $total    = count($filtered);
        $lastPage = max(1, (int) ceil($total / $pageSize));
        $page     = min($page, $lastPage);
        $offset   = ($page - 1) * $pageSize;

        return [
            'items' => array_values(array_slice($filtered, $offset, $pageSize)),
            'total' => $total,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(string $orderNo): ?array
    {
        foreach (self::all() as $order) {
            if ($order['order_no'] === $orderNo) {
                return $order;
            }
        }

        return null;
    }

    /**
     * @param list<array<string, mixed>> $orders
     * @param array<string, string>      $filters
     * @return list<array<string, mixed>>
     */
    private static function filter(array $orders, array $filters): array
    {
        return array_values(array_filter($orders, static function (array $order) use ($filters): bool {
            if ($filters['merchant_id'] !== '' && stripos($order['merchant_id'], $filters['merchant_id']) === false) {
                return false;
            }
            if ($filters['order_no'] !== '' && stripos($order['order_no'], $filters['order_no']) === false) {
                return false;
            }
            if ($filters['currency'] !== '' && $order['currency'] !== $filters['currency']) {
                return false;
            }
            if ($filters['risk_level'] !== '' && $order['risk_level'] !== $filters['risk_level']) {
                return false;
            }
            if ($filters['status'] !== '' && $order['status'] !== $filters['status']) {
                return false;
            }

            return true;
        }));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function all(): array
    {
        if (self::$orders !== null) {
            return self::$orders;
        }

        $merchants = [
            ['id' => 'M100001', 'name' => 'GlobalPay Tech'],
            ['id' => 'M100002', 'name' => 'HK Digital Mall'],
            ['id' => 'M100003', 'name' => 'EuroShop Online'],
            ['id' => 'M100004', 'name' => 'GameCard Hub'],
            ['id' => 'M100005', 'name' => 'LuxTravel Booking'],
        ];

        $currencies  = ['USD', 'EUR', 'GBP', 'JPY', 'HKD'];
        $cardTypes   = ['Visa', 'Mastercard', 'Amex', 'JCB'];
        $countries   = ['US', 'HK', 'GB', 'DE', 'JP', 'SG'];
        $riskLevels  = ['极低风险', '低风险', '中风险', '高风险', '极高风险'];
        $statuses    = ['成功', '拦截', '审核中', '失败'];
        $rules       = ['无', '单笔大额交易', 'OFAC制裁名单', '高频交易', 'IP国家不匹配', '黑名单卡BIN'];
        $actions     = ['通过', '拒绝交易', '人工审核', '3DS强制验证', '调单'];
        $threeDS     = ['通过', '未通过', '未参与'];

        self::$orders = [];

        for ($i = 1; $i <= 38; $i++) {
            $seq      = 58000 + $i;
            $merchant = $merchants[$i % count($merchants)];
            $currency = $currencies[$i % count($currencies)];
            $amount   = match ($currency) {
                'JPY'   => (float) (8000 + ($i * 137) % 50000),
                'HKD'   => round(1200 + ($i * 89) % 68000, 2),
                default => round(28.5 + ($i * 47) % 9800, 2),
            };

            $riskLevel = $riskLevels[$i % count($riskLevels)];
            if ($riskLevel === '极低风险') {
                $riskLevel = '低风险';
            }

            $status = $statuses[$i % count($statuses)];
            if ($status === '成功' && in_array($riskLevel, ['高风险', '极高风险'], true)) {
                $status = $i % 2 === 0 ? '拦截' : '审核中';
            }

            $rule   = $rules[$i % count($rules)];
            $action = $status === '成功' ? '通过' : $actions[$i % count($actions)];

            if ($rule === '无' && $status === '成功') {
                $rule = '无';
            } elseif ($rule === '无') {
                $rule = 'IP国家不匹配';
            }

            $day    = sprintf('%02d', 20 + ($i % 5));
            $hour   = sprintf('%02d', 8 + ($i % 12));
            $minute = sprintf('%02d', ($i * 3) % 60);

            self::$orders[] = [
                'order_no'         => 'MO202606' . (string) $seq,
                'channel_no'       => 'CO202606' . str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'merchant_id'      => $merchant['id'],
                'merchant_name'    => $merchant['name'],
                'trade_time'       => "2026-06-{$day} {$hour}:{$minute}:12",
                'currency'         => $currency,
                'amount'           => $amount,
                'card_type'        => $cardTypes[$i % count($cardTypes)],
                'card_country'     => $countries[$i % count($countries)],
                'ip_country'       => $countries[($i + 2) % count($countries)],
                'three_ds'         => $threeDS[$i % count($threeDS)],
                'risk_level'       => $riskLevel,
                'status'           => $status,
                'hit_rule'         => $rule,
                'action'           => $action,
                'website'          => strtolower(str_replace(' ', '', $merchant['name'])) . '.com/checkout',
                'card_no'          => '4111******' . str_pad((string) ($seq % 10000), 4, '0', STR_PAD_LEFT),
                'card_bin'         => '411111',
                'avs_result'       => $i % 4 === 0 ? 'Partial Match' : 'Full Match',
                'cvv_result'       => $i % 5 === 0 ? 'No Match' : 'Match',
                'eci'              => '05',
                'email'            => 'buyer' . $seq . '@mail.com',
                'ip'               => '198.51.100.' . ($i % 200 + 10),
                'billing_country'  => $countries[$i % count($countries)],
                'shipping_country' => $countries[($i + 1) % count($countries)],
                'mcc'              => $i % 2 === 0 ? '5967' : '5411',
                'is_proxy'         => $i % 7 === 0,
            ];
        }

        usort(self::$orders, static fn (array $a, array $b): int => strcmp($b['trade_time'], $a['trade_time']));

        return self::$orders;
    }
}
