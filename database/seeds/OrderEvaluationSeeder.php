<?php

declare(strict_types=1);

use database\factories\OrderEvaluationFactory;
use think\facade\Db;
use think\migration\Seeder;

/**
 * 订单评估/命中演示；跑完后按 order_no 回填 alert.evaluation_id
 */
class OrderEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        Db::name('order_hit')->delete(true);
        Db::name('order_evaluation')->delete(true);

        $bundles = (new OrderEvaluationFactory())->demoBundles();
        foreach ($bundles as $bundle) {
            $evalId = (int) Db::name('order_evaluation')->insertGetId($bundle['evaluation']);
            $hitRows = [];
            foreach ($bundle['hits'] as $hit) {
                $hitRows[] = array_merge($hit, ['evaluation_id' => $evalId]);
            }
            if ($hitRows !== []) {
                Db::name('order_hit')->insertAll($hitRows);
            }

            $orderNo = (string) $bundle['evaluation']['order_no'];
            Db::name('alert')->where('order_no', $orderNo)->update(['evaluation_id' => $evalId]);
        }
    }
}
