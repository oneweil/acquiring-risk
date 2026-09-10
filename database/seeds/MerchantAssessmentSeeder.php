<?php

declare(strict_types=1);

use think\migration\Seeder;

/**
 * @deprecated 请使用 MerchantSeeder（本地投影 + 评估）
 */
class MerchantAssessmentSeeder extends Seeder
{
    public function run(): void
    {
        if ($this->output !== null) {
            $this->output->writeln(' == MerchantAssessmentSeeder: deprecated — use MerchantSeeder');
        }
        require_once __DIR__ . '/MerchantSeeder.php';
        $seeder = new MerchantSeeder();
        if (method_exists($seeder, 'setAdapter') && method_exists($this, 'getAdapter')) {
            $seeder->setAdapter($this->getAdapter());
        }
        if ($this->output !== null && method_exists($seeder, 'setOutput')) {
            $seeder->setOutput($this->output);
        }
        $seeder->run();
    }
}
