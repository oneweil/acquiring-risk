<?php

declare(strict_types=1);

namespace app\validate;

use app\model\EddCase;
use think\Validate;

/**
 * EDD 强化尽调参数校验
 */
class Edd extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'attachment_id' => 'require|integer|gt:0',
        'keyword'       => 'max:128',
        'trigger'       => 'checkTrigger',
        'status'        => 'checkStatus',
        'merchant_id'   => 'require|max:32',
        'deadline'      => 'require|dateFormat:Y-m-d',
        'assignee'      => 'max:64',
        'notes'         => 'max:500',
        'linked_str_id' => 'max:64',
        'checklist'     => 'require|array|checkChecklist',
        'checklist_key' => 'require|checkChecklistKey',
        'action'        => 'require|in:approve,reject',
        'review_remark' => 'require|max:1000',
        'page'          => 'integer|gt:0',
        'pageSize'      => 'integer|in:10,20,50',
    ];

    protected $message = [
        'id.require'              => '缺少工单 ID',
        'id.integer'              => '工单 ID 无效',
        'id.gt'                   => '工单 ID 无效',
        'attachment_id.require'   => '缺少附件 ID',
        'attachment_id.integer'   => '附件 ID 无效',
        'attachment_id.gt'        => '附件 ID 无效',
        'keyword.max'             => '关键词最长 128 字符',
        'merchant_id.require'     => '请选择关联商户',
        'merchant_id.max'         => '商户号无效',
        'deadline.require'        => '请选择截止日期',
        'deadline.dateFormat'     => '截止日期格式无效',
        'assignee.max'            => '负责人最长 64 字符',
        'notes.max'               => '备注最长 500 字符',
        'linked_str_id.max'       => '关联 STR 编号过长',
        'checklist.require'       => '请勾选所需资料',
        'checklist.array'         => '清单格式无效',
        'checklist_key.require'   => '缺少材料项',
        'action.require'          => '请选择审核动作',
        'action.in'               => '审核动作无效',
        'review_remark.require'   => '请填写审核备注',
        'review_remark.max'       => '审核备注最长 1000 字符',
        'page.integer'            => '页码无效',
        'page.gt'                 => '页码无效',
        'pageSize.integer'        => '每页条数无效',
        'pageSize.in'             => '每页条数无效',
    ];

    protected $scene = [
        'list'              => ['keyword', 'trigger', 'status', 'page', 'pageSize'],
        'detail'            => ['id'],
        'create'            => ['merchant_id', 'trigger', 'deadline', 'assignee', 'notes', 'linked_str_id'],
        'collect'           => ['id', 'checklist'],
        'upload'            => ['id', 'checklist_key'],
        'attachment_delete' => ['attachment_id'],
        'submit'            => ['id'],
        'review'            => ['id', 'action', 'review_remark'],
        'download'          => ['id'],
    ];

    public function sceneList()
    {
        return $this->only(['keyword', 'trigger', 'status', 'page', 'pageSize']);
    }

    public function sceneCreate()
    {
        return $this->only(['merchant_id', 'trigger', 'deadline', 'assignee', 'notes', 'linked_str_id'])
            ->append('trigger', 'require|checkTrigger');
    }

    /**
     * @param array<string, mixed> $data
     * @return array{keyword?: string, trigger?: string, status?: string}
     */
    public static function toListFilters(array $data): array
    {
        $filters = [];

        $keyword = trim((string) ($data['keyword'] ?? ''));
        if ($keyword !== '') {
            $filters['keyword'] = $keyword;
        }

        $trigger = trim((string) ($data['trigger'] ?? ''));
        if ($trigger !== '') {
            $filters['trigger'] = $trigger;
        }

        $status = trim((string) ($data['status'] ?? ''));
        if ($status !== '') {
            $filters['status'] = $status;
        }

        return $filters;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function toListPageSize(array $data): int
    {
        $size = (int) ($data['pageSize'] ?? 0);

        return in_array($size, [10, 20, 50], true)
            ? $size
            : (int) config('paginate.list_rows', 10);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function toCreateData(array $data): array
    {
        $assignee = trim((string) ($data['assignee'] ?? ''));
        $notes    = trim((string) ($data['notes'] ?? ''));
        $linked   = trim((string) ($data['linked_str_id'] ?? ''));

        return [
            'merchant_id'   => trim((string) ($data['merchant_id'] ?? '')),
            'trigger'       => trim((string) ($data['trigger'] ?? '')),
            'deadline'      => trim((string) ($data['deadline'] ?? '')),
            'assignee'      => $assignee !== '' ? $assignee : null,
            'notes'         => $notes !== '' ? $notes : null,
            'linked_str_id' => $linked !== '' ? $linked : null,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, bool>
     */
    public static function toChecklist(array $data): array
    {
        $raw = $data['checklist'] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $map = [];
        foreach (EddCase::CHECKLIST_KEYS as $key) {
            $val = $raw[$key] ?? false;
            if (is_string($val)) {
                $val = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? in_array($val, ['1', 'true', 'on'], true);
            }
            $map[$key] = (bool) $val;
        }

        return $map;
    }

    protected function checkTrigger(mixed $value): bool|string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return true;
        }

        return in_array($v, EddCase::TRIGGERS, true) ? true : '触发原因无效';
    }

    protected function checkStatus(mixed $value): bool|string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return true;
        }

        return in_array($v, EddCase::STATUSES, true) ? true : '状态无效';
    }

    protected function checkChecklistKey(mixed $value): bool|string
    {
        $v = trim((string) $value);

        return in_array($v, EddCase::CHECKLIST_KEYS, true) ? true : '材料项无效';
    }

    /**
     * @param mixed                $value
     * @param mixed                $rule
     * @param array<string, mixed> $data
     */
    protected function checkChecklist(mixed $value, mixed $rule, array $data = []): bool|string
    {
        if (!is_array($value)) {
            return '清单格式无效';
        }

        $map   = self::toChecklist(['checklist' => $value]);
        $count = count(array_filter($map));

        return $count > 0 ? true : '请至少勾选一项所需资料';
    }
}
