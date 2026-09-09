<?php

declare(strict_types=1);

namespace database\factories;

use app\model\StrReport;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * STR/LTR 报送模拟数据工厂
 */
class StrReportFactory
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
        $now      = date('Y-m-d H:i:s');
        $type     = $overrides['type'] ?? $this->faker->randomElement(StrReport::TYPES);
        $currency = $overrides['currency'] ?? $this->faker->randomElement(StrReport::CURRENCIES);
        $amount   = $overrides['amount_val'] ?? $this->faker->randomFloat(2, 1000, 25000);

        $row = [
            'report_no'       => 'STR' . date('Ymd') . str_pad((string) $this->seq++, 3, '0', STR_PAD_LEFT),
            'type'            => $type,
            'trigger_mode'    => StrReport::TRIGGER_MANUAL,
            'merchant_id'     => 'M10000' . $this->faker->numberBetween(1, 9),
            'merchant_name'   => $this->faker->company(),
            'order_no'        => 'MO' . date('Ymd') . $this->faker->numerify('#####'),
            'currency'        => $currency,
            'amount_val'      => $amount,
            'amount_display'  => StrReport::formatAmountDisplay((string) $currency, (float) $amount),
            'trigger_reason'  => $type === StrReport::TYPE_LTR
                ? '单笔金额达到大额阈值'
                : '命中可疑交易规则',
            'suspicious_desc' => $type === StrReport::TYPE_STR ? mb_substr($this->faker->sentence(12), 0, 200) : null,
            'status'          => StrReport::STATUS_GENERATED,
            'submitter'       => null,
            'reviewer'        => null,
            'review_remark'   => null,
            'dismiss_reason'  => null,
            'reject_reason'   => null,
            'linked_alert_id' => null,
            'hit_rule_ids'    => null,
            'reviewed_at'     => null,
            'submitted_at'    => null,
            'created_at'      => $now,
            'updated_at'      => $now,
        ];

        $merged = array_merge($row, $overrides);
        if (!isset($overrides['amount_display']) && isset($overrides['amount_val'])) {
            $merged['amount_display'] = StrReport::formatAmountDisplay(
                (string) $merged['currency'],
                (float) $merged['amount_val']
            );
        }
        if (isset($merged['hit_rule_ids']) && is_array($merged['hit_rule_ids'])) {
            $merged['hit_rule_ids'] = json_encode($merged['hit_rule_ids'], JSON_UNESCAPED_UNICODE);
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
                'report_no'      => 'STR202606001',
                'type'           => StrReport::TYPE_LTR,
                'trigger_mode'   => StrReport::TRIGGER_MANUAL,
                'merchant_id'    => 'M100003',
                'merchant_name'  => 'GameCard Pro',
                'order_no'       => 'MO20260658001',
                'currency'       => 'USD',
                'amount_val'     => 12500.00,
                'trigger_reason' => '单笔金额 ≥ 10,000 USD（Large Ticket 规则 R013）',
                'status'         => StrReport::STATUS_SUBMITTED,
                'submitter'      => '合规专员-张',
                'submitted_at'   => '2026-06-21 09:00:00',
                'created_at'     => '2026-06-20 14:22:00',
                'updated_at'     => '2026-06-21 09:00:00',
            ]),
            $this->definition([
                'report_no'       => 'STR202606002',
                'type'            => StrReport::TYPE_STR,
                'trigger_mode'    => StrReport::TRIGGER_MANUAL,
                'merchant_id'     => 'M100005',
                'merchant_name'   => 'QuickBuy Store',
                'order_no'        => 'MO20260658002',
                'currency'        => 'USD',
                'amount_val'      => 3200.00,
                'trigger_reason'  => '同一IP多卡号卡测试 + 商户异常放量',
                'suspicious_desc' => '同一 IP 15 分钟内关联 6 张不同卡号，且商户当日交易量突增 620%',
                'status'          => StrReport::STATUS_UPLOADED,
                'submitter'       => '风控专员-李',
                'linked_alert_id' => 'AL1001',
                'created_at'      => '2026-06-21 11:05:00',
                'updated_at'      => '2026-06-21 15:30:00',
            ]),
            $this->definition([
                'report_no'       => 'STR202606003',
                'type'            => StrReport::TYPE_STR,
                'trigger_mode'    => StrReport::TRIGGER_AUTO,
                'merchant_id'     => 'M100003',
                'merchant_name'   => 'GameCard Pro',
                'order_no'        => 'MO20260658003',
                'currency'        => 'HKD',
                'amount_val'      => 88000.00,
                'trigger_reason'  => '命中规则：Test-then-Buy模式 · 高风险',
                'suspicious_desc' => '先微额 0.99 USD 后大额 880 HKD，同一卡号 45 分钟内',
                'status'          => StrReport::STATUS_PENDING_CONFIRM,
                'submitter'       => '系统自动',
                'created_at'      => '2026-06-22 09:18:00',
                'updated_at'      => '2026-06-22 09:18:00',
            ]),
            $this->definition([
                'report_no'      => 'STR202606004',
                'type'           => StrReport::TYPE_LTR,
                'trigger_mode'   => StrReport::TRIGGER_MANUAL,
                'merchant_id'    => 'M100001',
                'merchant_name'  => 'GlobalShop Inc.',
                'order_no'       => 'MO20260658004',
                'currency'       => 'USD',
                'amount_val'     => 15800.00,
                'trigger_reason' => '单笔金额 ≥ 10,000 USD',
                'status'         => StrReport::STATUS_GENERATED,
                'submitter'      => null,
                'created_at'     => '2026-06-22 16:40:00',
                'updated_at'     => '2026-06-22 16:40:00',
            ]),
            $this->definition([
                'report_no'       => 'STR202606005',
                'type'            => StrReport::TYPE_STR,
                'trigger_mode'    => StrReport::TRIGGER_MANUAL,
                'merchant_id'     => 'M100005',
                'merchant_name'   => 'QuickBuy Store',
                'order_no'        => 'MO20260658005',
                'currency'        => 'EUR',
                'amount_val'      => 2100.00,
                'trigger_reason'  => 'OFAC 制裁国家 IP 关联 + 拒付率超 VAMP 识别线',
                'suspicious_desc' => '交易 IP 来源于制裁关联地区，商户拒付率 1.2% 超识别线',
                'status'          => StrReport::STATUS_REJECTED,
                'submitter'       => '合规专员-张',
                'reject_reason'   => '报送字段不完整：缺少受益所有人信息',
                'created_at'      => '2026-06-23 08:55:00',
                'updated_at'      => '2026-06-23 10:00:00',
            ]),
            $this->definition([
                'report_no'      => 'STR202606006',
                'type'           => StrReport::TYPE_LTR,
                'trigger_mode'   => StrReport::TRIGGER_AUTO,
                'merchant_id'    => 'M100004',
                'merchant_name'  => 'TravelEase',
                'order_no'       => 'MO20260658006',
                'currency'       => 'USD',
                'amount_val'     => 11200.00,
                'trigger_reason' => '单笔金额 ≥ 10,000 USD',
                'status'         => StrReport::STATUS_UPLOADED,
                'submitter'      => '系统自动',
                'hit_rule_ids'   => ['R013'],
                'created_at'     => '2026-06-23 11:20:00',
                'updated_at'     => '2026-06-23 11:20:00',
            ]),
            $this->definition([
                'report_no'      => 'STR202606008',
                'type'           => StrReport::TYPE_LTR,
                'trigger_mode'   => StrReport::TRIGGER_AUTO,
                'merchant_id'    => 'M100001',
                'merchant_name'  => 'GlobalShop Inc.',
                'order_no'       => 'MO20260658008',
                'currency'       => 'USD',
                'amount_val'     => 22300.00,
                'trigger_reason' => '单笔金额 ≥ 10,000 USD（Large Ticket 规则 R013）',
                'status'         => StrReport::STATUS_ARCHIVED,
                'submitter'      => '合规专员-张',
                'submitted_at'   => '2026-06-24 14:00:00',
                'hit_rule_ids'   => ['R013'],
                'created_at'     => '2026-06-24 08:30:00',
                'updated_at'     => '2026-06-25 10:00:00',
            ]),
        ];
    }
}
