<?php

declare(strict_types=1);

namespace database\factories;

use app\model\SysRole;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * 系统角色模拟数据工厂
 */
class SysRoleFactory
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
        $now = date('Y-m-d H:i:s');
        $n   = $this->seq++;

        $row = [
            'code'        => 'ROLE_' . $n,
            'name'        => '自定义角色' . $n,
            'type'        => SysRole::TYPE_CUSTOM,
            'status'      => SysRole::STATUS_ENABLED,
            'sort'        => 100 + $n,
            'description' => $this->faker->sentence(6),
            'created_at'  => $now,
            'updated_at'  => $now,
        ];

        return array_merge($row, $overrides);
    }
}
