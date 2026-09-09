<?php

declare(strict_types=1);

use database\factories\EddCaseFactory;
use think\facade\Db;
use think\migration\Seeder;

class EddCaseSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('edd_attachment')->delete(true);
        Db::name('edd_case')->delete(true);

        $rows = (new EddCaseFactory())->demoSet();
        $this->table('edd_case')->insert($rows)->saveData();
    }
}
