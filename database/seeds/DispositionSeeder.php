<?php

declare(strict_types=1);

use database\factories\DispositionFactory;
use think\facade\Db;
use think\migration\Seeder;

class DispositionSeeder extends Seeder
{
    public function run(): void
    {
        // 可重复执行：先清空再写入内置策略
        Db::name('disposition')->delete(true);

        $rows = (new DispositionFactory())->builtinRows();
        $this->table('disposition')->insert($rows)->saveData();
    }
}
