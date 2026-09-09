<?php

declare(strict_types=1);

use database\factories\StrReportFactory;
use think\facade\Db;
use think\migration\Seeder;

class StrReportSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('str_attachment')->delete(true);
        Db::name('str_report')->delete(true);

        $rows = (new StrReportFactory())->demoSet();
        $this->table('str_report')->insert($rows)->saveData();
    }
}
