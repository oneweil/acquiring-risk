<?php

declare(strict_types=1);

use database\factories\AlertFactory;
use think\facade\Db;
use think\migration\Seeder;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('alert_attachment')->delete(true);
        Db::name('alert')->delete(true);

        $rows = (new AlertFactory())->demoSet();
        $this->table('alert')->insert($rows)->saveData();
    }
}
