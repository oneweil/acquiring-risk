<?php

declare(strict_types=1);

namespace database\factories;

use app\model\Disposition;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * 处置策略模拟数据工厂（Faker）
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

        $row = [
            'code'        => strtoupper($this->faker->unique()->bothify('CUSTOM_???_##')),
            'name'        => mb_substr($this->faker->words(2, true), 0, 64),
            'description' => mb_substr($this->faker->sentence(8), 0, 255),
            'risk_level'  => $this->faker->randomElement(Disposition::RISK_LEVELS),
            'scope'       => $this->faker->randomElement(Disposition::SCOPES),
            'priority'    => $this->faker->numberBetween(1, 100),
            'is_block'    => $this->faker->boolean(30) ? 1 : 0,
            'push_alert'  => $this->faker->boolean(85) ? 1 : 0,
            'status'      => $this->faker->boolean(90) ? 1 : 0,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];

        return array_merge($row, $overrides);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return list<array<string, mixed>>
     */
    public function times(int $count, array $overrides = []): array
    {
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = $this->definition($overrides);
        }

        return $rows;
    }

    /**
     * 原型 9 条内置策略（xsdfk.html DISPOSITION_MEASURES）
     *
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
                'description' => '冻结商户收单权限，禁止新交易接入',
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
                'code'        => 'MANUAL_REVIEW',
                'name'        => '人工审核',
                'description' => '交易挂起，推送风控专员队列，限时内复核决策',
                'risk_level'  => 'high',
                'scope'       => 'transaction',
                'priority'    => 10,
                'is_block'    => 0,
            ],
            [
                'code'        => 'DELAY_SETTLE',
                'name'        => '延迟结算',
                'description' => '授权成功但延长结算周期（T+7 / T+14），观察拒付情况',
                'risk_level'  => 'medium',
                'scope'       => 'transaction',
                'priority'    => 15,
                'is_block'    => 0,
            ],
            [
                'code'        => 'LIMIT_AMOUNT',
                'name'        => '限制单笔额度',
                'description' => '动态降低该商户/卡号单笔授权上限',
                'risk_level'  => 'high',
                'scope'       => 'merchant',
                'priority'    => 20,
                'is_block'    => 0,
            ],
            [
                'code'        => 'WATCHLIST',
                'name'        => '加入观察',
                'description' => '加入观察名单，后续交易加强监控但不立即阻断',
                'risk_level'  => 'medium',
                'scope'       => 'transaction',
                'priority'    => 25,
                'is_block'    => 0,
            ],
            [
                'code'        => 'CHARGEBACK_INQUIRY',
                'name'        => '调单',
                'description' => '发起调单（Retrieval Request），要求商户提供交易凭证',
                'risk_level'  => 'high',
                'scope'       => 'transaction',
                'priority'    => 30,
                'is_block'    => 0,
            ],
            [
                'code'        => 'ALERT_ONLY',
                'name'        => '仅预警',
                'description' => '记录风险事件并推送预警，不阻断当前交易',
                'risk_level'  => 'medium',
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
