<?php

declare(strict_types=1);

namespace database\factories;

use app\model\EddCase;
use app\model\MerchantAssessment;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * EDD 工单模拟数据工厂
 */
class EddCaseFactory
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
        $status   = $overrides['status'] ?? EddCase::STATUS_PENDING;
        $trigger  = $overrides['trigger'] ?? $this->faker->randomElement(EddCase::TRIGGERS);
        $checklist = $overrides['checklist'] ?? (
            $status === EddCase::STATUS_PENDING
                ? []
                : EddCase::defaultChecklist()
        );

        $row = [
            'case_no'       => 'EDD' . date('Ymd') . str_pad((string) $this->seq++, 3, '0', STR_PAD_LEFT),
            'merchant_id'   => 'M10000' . $this->faker->numberBetween(1, 9),
            'merchant_name' => $this->faker->company(),
            'trigger'       => $trigger,
            'risk_level'    => $this->faker->randomElement(MerchantAssessment::RISK_LEVELS),
            'status'        => $status,
            'deadline'      => $this->faker->dateTimeBetween('+7 days', '+45 days')->format('Y-m-d'),
            'assignee'      => $status === EddCase::STATUS_PENDING ? null : '合规专员',
            'progress'      => (int) ($overrides['progress'] ?? 0),
            'checklist'     => is_string($checklist) ? $checklist : json_encode($checklist, JSON_UNESCAPED_UNICODE),
            'notes'         => mb_substr($this->faker->sentence(8), 0, 200),
            'linked_str_id' => null,
            'review_remark' => null,
            'reviewed_at'   => null,
            'created_at'    => $now,
            'updated_at'    => $now,
        ];

        unset($overrides['checklist']);

        return array_merge($row, $overrides);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function demoSet(): array
    {
        $baseDate = '2026-06-15 10:00:00';

        return [
            $this->definition([
                'case_no'       => 'EDD202606001',
                'merchant_id'   => 'M100003',
                'merchant_name' => 'GameCard Pro',
                'trigger'       => EddCase::TRIGGER_HIGH_RISK_INDUSTRY,
                'risk_level'    => MerchantAssessment::RISK_LEVEL_MID,
                'status'        => EddCase::STATUS_COLLECTING,
                'deadline'      => '2026-07-15',
                'assignee'      => '合规-王',
                'progress'      => 67,
                'checklist'     => json_encode([
                    'ubo' => true, 'source' => true, 'business' => true,
                    'site' => true, 'bank' => true, 'pep' => true, 'visit' => false,
                ], JSON_UNESCAPED_UNICODE),
                'notes'         => '虚拟商品行业，需加强 UBO 核实',
                'created_at'    => $baseDate,
                'updated_at'    => $baseDate,
            ]),
            $this->definition([
                'case_no'       => 'EDD202606002',
                'merchant_id'   => 'M100005',
                'merchant_name' => 'QuickBuy Store',
                'trigger'       => EddCase::TRIGGER_SUSPICIOUS_TXN,
                'risk_level'    => MerchantAssessment::RISK_LEVEL_HIGH,
                'status'        => EddCase::STATUS_REVIEWING,
                'deadline'      => '2026-07-18',
                'assignee'      => '合规-张',
                'progress'      => 100,
                'checklist'     => json_encode([
                    'ubo' => true, 'source' => true, 'business' => true,
                    'site' => true, 'bank' => true, 'pep' => true, 'visit' => false,
                ], JSON_UNESCAPED_UNICODE),
                'notes'         => '关联 STR202606002 可疑交易报送',
                'linked_str_id' => 'STR202606002',
                'created_at'    => '2026-06-18 14:30:00',
                'updated_at'    => '2026-06-18 14:30:00',
            ]),
            $this->definition([
                'case_no'       => 'EDD202606003',
                'merchant_id'   => 'M100003',
                'merchant_name' => 'GameCard Pro',
                'trigger'       => EddCase::TRIGGER_STR_LINK,
                'risk_level'    => MerchantAssessment::RISK_LEVEL_MID,
                'status'        => EddCase::STATUS_PENDING,
                'deadline'      => '2026-07-22',
                'assignee'      => null,
                'progress'      => 0,
                'checklist'     => json_encode([], JSON_UNESCAPED_UNICODE),
                'notes'         => 'STR202606003 触发自动创建',
                'linked_str_id' => 'STR202606003',
                'created_at'    => '2026-06-22 09:20:00',
                'updated_at'    => '2026-06-22 09:20:00',
            ]),
            $this->definition([
                'case_no'       => 'EDD202606004',
                'merchant_id'   => 'M100001',
                'merchant_name' => 'GlobalShop Inc.',
                'trigger'       => EddCase::TRIGGER_VOLUME_ANOMALY,
                'risk_level'    => MerchantAssessment::RISK_LEVEL_LOW,
                'status'        => EddCase::STATUS_PASSED,
                'deadline'      => '2026-06-10',
                'assignee'      => '合规-李',
                'progress'      => 100,
                'checklist'     => json_encode([
                    'ubo' => true, 'source' => true, 'business' => true,
                    'site' => true, 'bank' => true, 'pep' => true, 'visit' => true,
                ], JSON_UNESCAPED_UNICODE),
                'notes'         => '月累计交易额突增，经核实为促销季正常放量',
                'review_remark' => '材料齐全且可核验，交易放量为促销季正常波动，同意通过 EDD',
                'reviewed_at'   => '2026-06-08 16:00:00',
                'created_at'    => '2026-05-10 09:00:00',
                'updated_at'    => '2026-06-08 16:00:00',
            ]),
            $this->definition([
                'case_no'       => 'EDD202606005',
                'merchant_id'   => 'M100005',
                'merchant_name' => 'QuickBuy Store',
                'trigger'       => EddCase::TRIGGER_PEP_SANCTION,
                'risk_level'    => MerchantAssessment::RISK_LEVEL_HIGH,
                'status'        => EddCase::STATUS_REJECTED,
                'deadline'      => '2026-05-01',
                'assignee'      => '合规-张',
                'progress'      => 50,
                'checklist'     => json_encode([
                    'ubo' => true, 'source' => true, 'business' => true,
                    'site' => true, 'bank' => true, 'pep' => true, 'visit' => false,
                ], JSON_UNESCAPED_UNICODE),
                'notes'         => 'UBO 筛查命中 PEP 关联，资金来源无法核实',
                'review_remark' => 'UBO 命中 PEP 关联，资金来源说明缺失且无法补充核实',
                'reviewed_at'   => '2026-04-28 10:00:00',
                'created_at'    => '2026-04-01 11:00:00',
                'updated_at'    => '2026-04-28 10:00:00',
            ]),
        ];
    }
}
