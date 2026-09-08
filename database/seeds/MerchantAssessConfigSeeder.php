<?php

declare(strict_types=1);

use database\factories\MerchantAssessDimensionFactory;
use database\factories\MerchantAssessRuleFactory;
use think\facade\Db;
use think\migration\Seeder;

class MerchantAssessConfigSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('merchant_assess_rule')->delete(true);
        Db::name('merchant_assess_dimension')->delete(true);

        $this->table('merchant_assess_dimension')
            ->insert((new MerchantAssessDimensionFactory())->builtinRows())
            ->saveData();

        $this->table('merchant_assess_rule')
            ->insert((new MerchantAssessRuleFactory())->builtinRows())
            ->saveData();
    }
}
