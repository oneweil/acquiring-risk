<?php

declare(strict_types=1);

namespace app\validate;

use app\model\Alert as AlertModel;
use think\Validate;

/**
 * 交易预警参数校验
 */
class Alert extends Validate
{
    protected $rule = [
        'id'            => 'require|integer|gt:0',
        'attachment_id' => 'require|integer|gt:0',
        'risk_level'    => 'checkRiskLevel',
        'status'        => 'checkStatus',
        'measure_code'  => 'checkMeasureCode',
        'action'        => 'require|checkHandleAction',
        'remark'        => 'max:1000',
        'inquiry_desc'  => 'max:2000',
        'page'          => 'integer|gt:0',
        'pageSize'      => 'integer|in:10,20,50',
    ];

    protected $message = [
        'id.require'            => '缺少预警 ID',
        'id.integer'            => '预警 ID 无效',
        'id.gt'                 => '预警 ID 无效',
        'attachment_id.require' => '缺少附件 ID',
        'attachment_id.integer' => '附件 ID 无效',
        'attachment_id.gt'      => '附件 ID 无效',
        'action.require'        => '请选择处置结论',
        'remark.max'            => '处理备注最长 1000 字符',
        'inquiry_desc.max'      => '调单说明最长 2000 字符',
        'page.integer'          => '页码无效',
        'page.gt'               => '页码无效',
        'pageSize.integer'      => '每页条数无效',
        'pageSize.in'           => '每页条数无效',
    ];

    protected $scene = [
        'list'     => ['risk_level', 'status', 'measure_code', 'page', 'pageSize'],
        'detail'   => ['id'],
        'handle'   => ['id', 'action', 'remark', 'inquiry_desc'],
        'upload'   => ['id'],
        'download' => ['attachment_id'],
    ];

    /**
     * @param array<string, mixed> $data
     * @return array{risk_level?: string, status?: string, measure_code?: string, exclude_closed?: bool}
     */
    public static function toListFilters(array $data): array
    {
        $filters = [];

        $risk = trim((string) ($data['risk_level'] ?? ''));
        if ($risk !== '') {
            $filters['risk_level'] = $risk;
        }

        $status = trim((string) ($data['status'] ?? ''));
        if ($status !== '') {
            $filters['status'] = $status;
        } else {
            $filters['exclude_closed'] = true;
        }

        $measure = trim((string) ($data['measure_code'] ?? ''));
        if ($measure !== '') {
            $filters['measure_code'] = $measure;
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
     * @return array{action: string, remark: ?string, inquiry_desc: ?string}
     */
    public static function toHandleData(array $data): array
    {
        $remark  = trim((string) ($data['remark'] ?? ''));
        $inquiry = trim((string) ($data['inquiry_desc'] ?? ''));

        return [
            'action'       => trim((string) ($data['action'] ?? '')),
            'remark'       => $remark !== '' ? $remark : null,
            'inquiry_desc' => $inquiry !== '' ? $inquiry : null,
        ];
    }

    protected function checkRiskLevel(mixed $value): bool|string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return true;
        }

        return in_array($v, AlertModel::RISK_LEVELS, true) ? true : '风险等级无效';
    }

    protected function checkStatus(mixed $value): bool|string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return true;
        }

        return in_array($v, AlertModel::STATUSES, true) ? true : '状态无效';
    }

    protected function checkMeasureCode(mixed $value): bool|string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return true;
        }

        return in_array($v, AlertModel::MEASURE_CODES, true) ? true : '处置策略无效';
    }

    protected function checkHandleAction(mixed $value): bool|string
    {
        $v = trim((string) $value);

        return in_array($v, AlertModel::HANDLE_ACTIONS, true) ? true : '处置结论无效';
    }
}
