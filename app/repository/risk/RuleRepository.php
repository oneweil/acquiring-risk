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

                $save = [
                    'category'         => $row['category'],
                    'name'             => $row['name'],
                    'description'      => $row['description'],
                    'content_template' => $row['content_template'],
                    'config'           => $config,
                    'measure_code'     => $row['measure_code'],
                    'enabled'          => !empty($row['enabled']) ? 1 : 0,
                    'sort'             => (int) ($row['sort'] ?? 0),
                ];
                if (array_key_exists('push_str', $row)) {
                    $save['push_str'] = !empty($row['push_str']) ? 1 : 0;
                }

                $model->save($save);
            }
        });
    }

    /**
     * STR 推送规则列表（分页）
     *
     * @param array{category?: string, push_str?: int|bool, keyword?: string} $filters
     *
     * @throws DbException
     */
    public function searchForStrPush(array $filters, int $page, int $pageSize): \think\Paginator
    {
        $query = Rule::order('sort', 'asc')->order('id', 'asc');

        if (isset($filters['category']) && $filters['category'] !== '') {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['push_str'])) {
            $query->where('push_str', (int) ((bool) $filters['push_str']));
        }
        if (isset($filters['keyword']) && $filters['keyword'] !== '') {
            $like = '%' . $filters['keyword'] . '%';
            $query->where(function ($q) use ($like): void {
                $q->whereLike('name', $like)->whereLike('rule_id', $like, 'OR');
            });
        }

        return $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);
    }

    public function countPushStrEnabled(): int
    {
        return (int) Rule::where('push_str', 1)->count();
    }

    public function countAll(): int
    {
        return (int) Rule::count();
    }

    /**
     * @param list<array{rule_id: string, push_str: bool}> $items
     *
     * @throws DbException
     */
    public function batchUpdatePushStr(array $items): void
    {
        foreach ($items as $item) {
            $model = Rule::where('rule_id', $item['rule_id'])->find();
            if ($model === null) {
                throw new \RuntimeException('规则不存在：' . $item['rule_id']);
            }
            $model->save([
                'push_str' => !empty($item['push_str']) ? 1 : 0,
            ]);
        }
    }

    /**
     * @param list<string> $ruleIds
     * @return array<string, bool> rule_id => push_str
     *
     * @throws DbException
     */
    public function mapPushStrByRuleIds(array $ruleIds): array
    {
        if ($ruleIds === []) {
            return [];
        }

        $rows = Rule::whereIn('rule_id', $ruleIds)->field(['rule_id', 'push_str'])->select();
        $map  = [];
        foreach ($rows as $row) {
            $map[(string) $row->rule_id] = (bool) $row->push_str;
        }

        return $map;
    }
}
