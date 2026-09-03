<?php

declare(strict_types=1);

namespace database\factories;

use app\model\Blacklist;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * 黑名单模拟数据工厂（Faker）
 */
class BlacklistFactory
{
    private Generator $faker;

    private int $seq = 1;

    public function __construct(?Generator $faker = null)
    {
        $this->faker = $faker ?? FakerFactory::create('en_US');
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public function definition(array $overrides = []): array
    {
        $type = $overrides['type'] ?? $this->faker->randomElement(Blacklist::TYPES);
        $now  = date('Y-m-d H:i:s');

        $longTerm = $this->faker->boolean(60);
        $effective = $this->faker->dateTimeBetween('-90 days', 'now')->format('Y-m-d');

        $row = [
            'code'           => 'BL' . str_pad((string) $this->seq++, 6, '0', STR_PAD_LEFT),
            'type'           => $type,
            'value'          => $this->valueForType($type),
            'reason'         => mb_substr($this->faker->sentence(6), 0, 255),
            'effective_date' => $effective,
            'expiry_date'    => $longTerm ? null : $this->faker->dateTimeBetween('+7 days', '+180 days')->format('Y-m-d'),
            'status'         => $this->faker->boolean(85) ? 1 : 0,
            'created_at'     => $now,
            'updated_at'     => $now,
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

    private function valueForType(string $type): string
    {
        return match ($type) {
            'ip'      => $this->faker->ipv4(),
            'email'   => $this->faker->safeEmail(),
            'card'    => $this->faker->creditCardNumber(),
            'country' => $this->faker->countryCode(),
            'website' => $this->faker->lexify('????-????.example.com'),
            'phone'   => $this->faker->numerify('1##########'),
            default   => $this->faker->bothify('val-########'),
        };
    }
}
