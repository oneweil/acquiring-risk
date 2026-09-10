<?php

declare(strict_types=1);

use database\factories\MerchantAssessmentFactory;
use database\factories\MerchantFactory;
use think\facade\Db;
use think\migration\Seeder;

class MerchantSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('merchant_assessment')->delete(true);
        Db::name('merchant')->delete(true);

        $merchantRows = (new MerchantFactory())->demoRows(12);
        $this->table('merchant')->insert($merchantRows)->saveData();

        $merchantIds = array_column($merchantRows, 'merchant_id');
        $assessRows  = (new MerchantAssessmentFactory())->demoRows($merchantIds);
        $this->table('merchant_assessment')->insert($assessRows)->saveData();

        if ($this->output !== null) {
            $this->output->writeln(' == MerchantSeeder: inserted ' . count($merchantRows) . ' merchants + assessments');
        }
    }
}
