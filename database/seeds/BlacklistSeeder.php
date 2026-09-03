<?php

declare(strict_types=1);

use database\factories\BlacklistFactory;
use think\facade\Db;
use think\migration\Seeder;

class BlacklistSeeder extends Seeder
{
    public function run(): void
    {
        // 可重复执行：先清空再写入演示数据
        Db::name('blacklist')->delete(true);

        $rows = (new BlacklistFactory())->times(100);
        $this->table('blacklist')->insert($rows)->saveData();
    }
}
