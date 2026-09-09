<?php

declare(strict_types=1);

namespace database\factories;

use app\model\SysUser;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * 系统用户模拟数据工厂
 */
class SysUserFactory
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
            'account'       => 'user' . $n,
            'name'          => $this->faker->name(),
            'password'      => password_hash('admin123', PASSWORD_DEFAULT),
            'title'         => $this->faker->randomElement(['风控专员', '合规专员', '风控管理员', '内审专员']),
            'phone'         => '138' . str_pad((string) $n, 8, '0', STR_PAD_LEFT),
            'email'         => 'user' . $n . '@risk.local',
            'status'        => SysUser::STATUS_ENABLED,
            'last_login_at' => null,
            'remark'        => '',
            'created_at'    => $now,
            'updated_at'    => $now,
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
}
