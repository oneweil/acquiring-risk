<?php

declare(strict_types=1);

use database\factories\RuleFactory;
use think\facade\Db;
use think\migration\Seeder;

class RuleSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('rule')->delete(true);

        $rows = (new RuleFactory())->builtinRows();
        $this->table('rule')->insert($rows)->saveData();
    }
}
