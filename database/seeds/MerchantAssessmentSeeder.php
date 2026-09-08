<?php

declare(strict_types=1);

use database\factories\MerchantAssessmentFactory;
use think\facade\Db;
use think\migration\Seeder;

class MerchantAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('merchant_assessment')->delete(true);

        $merchantIds = [];
        try {
            $merchantIds = Db::connect('doopsun_db')
                ->table('doopsun_merchants')
                ->order('merchantId', 'desc')
                ->limit(30)
                ->column('merchantId');
        } catch (\Throwable $e) {
            $this->output->writeln(' == MerchantAssessmentSeeder: skip (doopsun_db unavailable): ' . $e->getMessage());

            return;
        }

        if ($merchantIds === []) {
            $this->output->writeln(' == MerchantAssessmentSeeder: no merchants in doopsun_merchants, skipped');

            return;
        }

        $rows = (new MerchantAssessmentFactory())->demoRows($merchantIds);
        $this->table('merchant_assessment')->insert($rows)->saveData();
    }
}
