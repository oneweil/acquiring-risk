<?php

declare(strict_types=1);

namespace database\factories;

use app\model\Alert;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * 交易预警模拟数据工厂
 */
class AlertFactory
{
    private Generator $faker;

    private int $seq = 1;

    public function __construct(?Generator $faker = null)
    {
        $this->faker = $faker ?? FakerFactory::create('zh_CN');
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public function definition(array $overrides = []): array
    {
        $now     = date('Y-m-d H:i:s');
        $measure = $overrides['measure_code'] ?? $this->faker->randomElement(Alert::MEASURE_CODES);
        $risk    = $overrides['risk_level'] ?? $this->faker->randomElement(Alert::RISK_LEVELS);
        $amount  = $this->faker->randomFloat(2, 50, 8000);
        $currency = $this->faker->randomElement(['USD', 'HKD', 'EUR']);

        $row = [
            'alert_no'       => 'AL' . date('Ymd') . str_pad((string) $this->seq++, 4, '0', STR_PAD_LEFT),
            'scope'          => Alert::SCOPE_ORDER,
            'alerted_at'     => $now,
            'merchant_id'    => 'M10000' . $this->faker->numberBetween(1, 9),
            'order_no'       => 'MO' . date('Ymd') . $this->faker->numerify('#####'),
            'amount_display' => $currency . ' ' . number_format($amount, 2),
            'risk_level'     => $risk,
            'rule_name'      => '模拟命中规则',
            'measure_code'   => $measure,
            'action_name'    => Alert::MEASURE_LABELS[$measure] ?? $measure,
            'hit_details'    => null,
            'status'         => Alert::STATUS_PENDING,
            'handle_remark'  => null,
            'inquiry_desc'   => null,
            'evaluation_id'  => null,
            'str_report_id'  => null,
            'operator_id'    => null,
            'handled_at'     => null,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        $merged = array_merge($row, $overrides);
        if (isset($merged['hit_details']) && is_array($merged['hit_details'])) {
            $merged['hit_details'] = json_encode($merged['hit_details'], JSON_UNESCAPED_UNICODE);
        }
        if (!isset($overrides['action_name']) && isset($merged['measure_code'])) {
            $code = (string) $merged['measure_code'];
            $merged['action_name'] = Alert::MEASURE_LABELS[$code] ?? $code;
        }

        return $merged;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function demoSet(): array
    {
        return [
            $this->definition([
                'alert_no'       => 'AL2026060001',
                'alerted_at'     => '2026-06-22 09:15:00',
                'merchant_id'    => 'M100003',
                'order_no'       => 'MO20260600078',
                'amount_display' => 'USD 2,480.00',
                'risk_level'     => Alert::RISK_LEVEL_HIGH,
                'rule_name'      => '同一卡号10分钟高频',
                'measure_code'   => Alert::MEASURE_ALERT_ONLY,
                'hit_details'    => [
                    ['id' => 'R005', 'name' => '同一卡号10分钟高频', 'risk_level' => 'high', 'measure' => 'ALERT_ONLY'],
                ],
                'status'         => Alert::STATUS_PENDING,
                'created_at'     => '2026-06-22 09:15:00',
                'updated_at'     => '2026-06-22 09:15:00',
            ]),
            $this->definition([
                'alert_no'       => 'AL2026060002',
                'alerted_at'     => '2026-06-22 10:02:00',
                'merchant_id'    => 'M100005',
                'order_no'       => 'MO20260600091',
                'amount_display' => 'USD 899.00',
                'risk_level'     => Alert::RISK_LEVEL_CRITICAL,
                'rule_name'      => '欺诈率超 VFMP',
                'measure_code'   => Alert::MEASURE_CHARGEBACK_INQUIRY,
                'hit_details'    => [
                    ['id' => 'R042', 'name' => '欺诈率超 VFMP', 'risk_level' => 'critical', 'measure' => 'CHARGEBACK_INQUIRY'],
                ],
                'status'         => Alert::STATUS_PENDING,
                'str_report_id'  => 'STR202606002',
                'created_at'     => '2026-06-22 10:02:00',
                'updated_at'     => '2026-06-22 10:02:00',
            ]),
            $this->definition([
                'alert_no'       => 'AL2026060003',
                'alerted_at'     => '2026-06-22 11:20:00',
                'merchant_id'    => 'M100001',
                'order_no'       => 'MO20260600105',
                'amount_display' => 'HKD 15,800.00',
                'risk_level'     => Alert::RISK_LEVEL_HIGH,
                'rule_name'      => '单笔异常大单',
                'measure_code'   => Alert::MEASURE_CHARGEBACK_INQUIRY,
                'hit_details'    => [
                    ['id' => 'R023', 'name' => '单笔异常大单', 'risk_level' => 'high', 'measure' => 'CHARGEBACK_INQUIRY'],
                ],
                'status'         => Alert::STATUS_PROCESSING,
                'inquiry_desc'   => '已向商户索要物流签收证明与交易凭证',
                'handle_remark'  => '等待商户补充材料',
                'created_at'     => '2026-06-22 11:20:00',
                'updated_at'     => '2026-06-22 14:00:00',
            ]),
            $this->definition([
                'alert_no'       => 'AL2026060004',
                'alerted_at'     => '2026-06-21 16:40:00',
                'merchant_id'    => 'M100004',
                'order_no'       => 'MO20260600055',
                'amount_display' => 'USD 320.00',
                'risk_level'     => Alert::RISK_LEVEL_MID,
                'rule_name'      => '发卡国与IP不一致',
                'measure_code'   => Alert::MEASURE_ALERT_ONLY,
                'hit_details'    => [
                    ['id' => 'R016', 'name' => '发卡国与IP不一致', 'risk_level' => 'mid', 'measure' => 'ALERT_ONLY'],
                ],
                'status'         => Alert::STATUS_CLOSED,
                'handle_remark'  => '跨境常见场景，已阅关闭',
                'handled_at'     => '2026-06-21 17:10:00',
                'created_at'     => '2026-06-21 16:40:00',
                'updated_at'     => '2026-06-21 17:10:00',
            ]),
            $this->definition([
                'alert_no'       => 'AL2026060005',
                'alerted_at'     => '2026-06-22 13:08:00',
                'merchant_id'    => 'M100002',
                'order_no'       => 'MO20260600120',
                'amount_display' => 'EUR 1,050.00',
                'risk_level'     => Alert::RISK_LEVEL_CRITICAL,
                'rule_name'      => '黑名单卡 BIN',
                'measure_code'   => Alert::MEASURE_DECLINE,
                'hit_details'    => [
                    ['id' => 'R001', 'name' => '黑名单卡 BIN', 'risk_level' => 'critical', 'measure' => 'DECLINE'],
                ],
                'status'         => Alert::STATUS_PENDING,
                'created_at'     => '2026-06-22 13:08:00',
                'updated_at'     => '2026-06-22 13:08:00',
            ]),
            $this->definition([
                'alert_no'       => 'AL2026060006',
                'alerted_at'     => '2026-06-22 15:30:00',
                'merchant_id'    => 'M100003',
                'order_no'       => 'MO20260600135',
                'amount_display' => 'USD 680.00',
                'risk_level'     => Alert::RISK_LEVEL_HIGH,
                'rule_name'      => '高风险 MCC 大额',
                'measure_code'   => Alert::MEASURE_3DS_CHALLENGE,
                'hit_details'    => [
                    ['id' => 'R030', 'name' => '高风险 MCC 大额', 'risk_level' => 'high', 'measure' => '3DS_CHALLENGE'],
                ],
                'status'         => Alert::STATUS_PENDING,
                'created_at'     => '2026-06-22 15:30:00',
                'updated_at'     => '2026-06-22 15:30:00',
            ]),
            $this->definition([
                'alert_no'       => 'AL2026060007',
                'alerted_at'     => '2026-06-20 09:00:00',
                'merchant_id'    => 'M100001',
                'order_no'       => 'MO20260600012',
                'amount_display' => 'USD 12,500.00',
                'risk_level'     => Alert::RISK_LEVEL_HIGH,
                'rule_name'      => 'Large Ticket',
                'measure_code'   => Alert::MEASURE_WATCHLIST,
                'hit_details'    => [
                    ['id' => 'R013', 'name' => 'Large Ticket', 'risk_level' => 'high', 'measure' => 'WATCHLIST'],
                ],
                'status'         => Alert::STATUS_CLOSED,
                'handle_remark'  => '误报：促销大额订单',
                'str_report_id'  => 'STR202606001',
                'handled_at'     => '2026-06-20 10:00:00',
                'created_at'     => '2026-06-20 09:00:00',
                'updated_at'     => '2026-06-20 10:00:00',
            ]),
            $this->definition([
                'alert_no'       => 'AL2026060008',
                'alerted_at'     => '2026-06-22 16:45:00',
                'merchant_id'    => 'M100005',
                'order_no'       => 'MO20260600148',
                'amount_display' => 'USD 199.00',
                'risk_level'     => Alert::RISK_LEVEL_MID,
                'rule_name'      => '账单收货国不一致',
                'measure_code'   => Alert::MEASURE_DELAY_SETTLE,
                'hit_details'    => [
                    ['id' => 'R021', 'name' => '账单收货国不一致', 'risk_level' => 'mid', 'measure' => 'DELAY_SETTLE'],
                ],
                'status'         => Alert::STATUS_PENDING,
                'created_at'     => '2026-06-22 16:45:00',
                'updated_at'     => '2026-06-22 16:45:00',
            ]),
        ];
    }
}
