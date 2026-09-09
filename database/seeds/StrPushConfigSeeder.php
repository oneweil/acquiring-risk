<?php

declare(strict_types=1);

use database\factories\StrPushConfigFactory;
use think\facade\Db;
use think\migration\Seeder;

class StrPushConfigSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('str_push_config')->delete(true);

        $this->table('str_push_config')
            ->insert([(new StrPushConfigFactory())->builtinConfig()])
            ->saveData();
    }
}
