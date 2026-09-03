<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\Rule;
use think\db\exception\DbException;
use think\facade\Db;

class RuleRepository
{
    /**
     * 全量规则，按分类常量顺序 + 组内 sort
     *
     * @return list<Rule>
     *
     * @throws DbException
     */
    public function allOrdered(): array
    {
        $categoryOrder = array_flip(Rule::CATEGORIES);

        $rows = Rule::order('sort', 'asc')->order('id', 'asc')->select()->all();

        usort($rows, static function (Rule $a, Rule $b) use ($categoryOrder): int {
            $oa = $categoryOrder[$a->category] ?? 999;
            $ob = $categoryOrder[$b->category] ?? 999;
            if ($oa !== $ob) {
                return $oa <=> $ob;
            }
            if ((int) $a->sort !== (int) $b->sort) {
                return (int) $a->sort <=> (int) $b->sort;
            }

            return (int) $a->id <=> (int) $b->id;
        });

        return $rows;
    }

    /**
     * @return array<string, list<Rule>>
     *
     * @throws DbException
     */
    public function allGrouped(): array
    {
        $grouped = [];
        foreach (Rule::CATEGORIES as $category) {
            $grouped[$category] = [];
        }

        foreach ($this->allOrdered() as $rule) {
            $cat = (string) $rule->category;
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $rule;
        }

        return $grouped;
    }

    /**
     * @param list<array{rule_id: string, config: array<string, mixed>, measure_code: string, enabled: bool}> $items
     *
     * @throws DbException
     */
    public function batchUpdate(array $items): void
    {
        Db::transaction(function () use ($items): void {
            foreach ($items as $item) {
                $model = Rule::where('rule_id', $item['rule_id'])->find();
                if ($model === null) {
                    throw new \RuntimeException('规则不存在：' . $item['rule_id']);
                }

                $model->save([
                    'config'       => $item['config'],
                    'measure_code' => $item['measure_code'],
                    'enabled'      => $item['enabled'] ? 1 : 0,
                ]);
            }
        });
    }

    /**
     * 用内置默认行覆盖可编辑字段（保留主键与 rule_id）
     *
     * @param list<array<string, mixed>> $rows Factory builtinRows()
     *
     * @throws DbException
     */
    public function resetToDefaults(array $rows): void
    {
        Db::transaction(function () use ($rows): void {
            foreach ($rows as $row) {
                $ruleId = (string) ($row['rule_id'] ?? '');
                if ($ruleId === '') {
                    continue;
                }

                $model = Rule::where('rule_id', $ruleId)->find();
                if ($model === null) {
                    continue;
                }

                $config = $row['config'] ?? [];
                if (is_string($config)) {
                    $decoded = json_decode($config, true);
                    $config  = is_array($decoded) ? $decoded : [];
                }

                $model->save([
                    'category'         => $row['category'],
                    'name'             => $row['name'],
                    'description'      => $row['description'],
                    'content_template' => $row['content_template'],
                    'config'           => $config,
                    'measure_code'     => $row['measure_code'],
                    'enabled'          => !empty($row['enabled']) ? 1 : 0,
                    'sort'             => (int) ($row['sort'] ?? 0),
                ]);
            }
        });
    }
}
