<?php

declare(strict_types=1);

namespace database\factories;

use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * 处置策略：内置 8 条（无订单挂起人审；原型 MANUAL_REVIEW 已废弃）
 */
class DispositionFactory
{
    private Generator $faker;

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
        $now = date('Y-m-d H:i:s');

        return array_merge([
            'code'        => strtoupper($this->faker->unique()->lexify('??????')),
            'name'        => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'risk_level'  => $this->faker->randomElement(['low', 'mid', 'high', 'critical']),
            'scope'       => $this->faker->randomElement(['transaction', 'merchant']),
            'priority'    => $this->faker->numberBetween(1, 100),
            'is_block'    => $this->faker->boolean(30) ? 1 : 0,
            'push_alert'  => $this->faker->boolean(85) ? 1 : 0,
            'status'      => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ], $overrides);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function builtinRows(): array
    {
        $now = date('Y-m-d H:i:s');

        $items = [
            [
                'code'        => 'DECLINE',
                'name'        => '拒绝交易',
                'description' => '立即拒绝授权请求，返回失败响应，记录拒因码',
                'risk_level'  => 'critical',
                'scope'       => 'transaction',
                'priority'    => 1,
                'is_block'    => 1,
            ],
            [
                'code'        => 'SUSPEND_MERCHANT',
                'name'        => '暂停收单',
                'description' => '冻结商户收单权限，禁止新交易接入（商户状态动作，非订单挂起）',
                'risk_level'  => 'critical',
                'scope'       => 'merchant',
                'priority'    => 2,
                'is_block'    => 1,
            ],
            [
                'code'        => '3DS_CHALLENGE',
                'name'        => '3DS强验',
                'description' => '发起 3DS 2.x Challenge 验证，未通过则拒绝；通过后 Liability Shift',
                'risk_level'  => 'high',
                'scope'       => 'transaction',
                'priority'    => 5,
                'is_block'    => 0,
            ],
            [
                'code'        => 'DELAY_SETTLE',
                'name'        => '延迟结算',
                'description' => '授权成功但延长结算周期（T+7 / T+14），观察拒付情况',
                'risk_level'  => 'mid',
                'scope'       => 'transaction',
                'priority'    => 15,
                'is_block'    => 0,
            ],
            [
                'code'        => 'LIMIT_AMOUNT',
                'name'        => '限制单笔额度',
                'description' => '动态降低该商户/卡号单笔授权上限（商户侧限制，非人审队列）',
                'risk_level'  => 'high',
                'scope'       => 'merchant',
                'priority'    => 20,
                'is_block'    => 0,
            ],
            [
                'code'        => 'WATCHLIST',
                'name'        => '加入观察',
                'description' => '加入观察名单，后续交易加强监控但不立即阻断',
                'risk_level'  => 'mid',
                'scope'       => 'transaction',
                'priority'    => 25,
                'is_block'    => 0,
            ],
            [
                'code'        => 'CHARGEBACK_INQUIRY',
                'name'        => '调单',
                'description' => '发起调单（Retrieval Request），要求商户提供交易凭证；订单已终态',
                'risk_level'  => 'high',
                'scope'       => 'transaction',
                'priority'    => 30,
                'is_block'    => 0,
            ],
            [
                'code'        => 'ALERT_ONLY',
                'name'        => '仅预警',
                'description' => '记录风险事件并推送订单预警，不阻断当前交易',
                'risk_level'  => 'mid',
                'scope'       => 'transaction',
                'priority'    => 50,
                'is_block'    => 0,
            ],
        ];

        $rows = [];
        foreach ($items as $item) {
            $rows[] = array_merge($item, [
                'push_alert' => 1,
                'status'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $rows;
    }
}
