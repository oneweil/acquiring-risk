<?php

declare(strict_types=1);

use database\factories\MerchantLevelConfigFactory;
use think\facade\Db;
use think\migration\Seeder;

class MerchantLevelConfigSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('merchant_level_policy')->delete(true);
        Db::name('merchant_level_config')->delete(true);

        $factory = new MerchantLevelConfigFactory();

        $this->table('merchant_level_config')->insert([$factory->builtinConfig()])->saveData();
        $this->table('merchant_level_policy')->insert($factory->builtinPolicies())->saveData();
    }
}
