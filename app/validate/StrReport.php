<?php

declare(strict_types=1);

namespace app\validate;

use app\model\StrReport as StrReportModel;
use think\Validate;

/**
 * STR/LTR 报送参数校验
 */
class StrReport extends Validate
{
    protected $rule = [
        'id'             => 'require|integer|gt:0',
        'merchant_id'    => 'require|max:32',
        'order_no'       => 'require|max:64',
        'type'           => 'require|checkType',
        'currency'       => 'require|checkCurrency',
        'amount_val'     => 'require|float|gt:0',
        'trigger_reason' => 'max:512',
        'suspicious_desc'=> 'max:5000',
        'status'         => 'checkStatus',
        'tab'            => 'checkTab',
        'review_remark'  => 'require|max:1000',
        'dismiss_reason' => 'require|max:1000',
        'page'           => 'integer|gt:0',
        'pageSize'       => 'integer|in:10,20,50',
    ];

    protected $message = [
        'id.require'              => '缺少报送 ID',
        'id.integer'              => '报送 ID 无效',
        'id.gt'                   => '报送 ID 无效',
        'merchant_id.require'     => '请填写商户号',
        'merchant_id.max'         => '商户号无效',
        'order_no.require'        => '请填写订单号',
        'order_no.max'            => '订单号过长',
        'type.require'            => '请选择报送类型',
        'currency.require'        => '请选择币种',
        'amount_val.require'      => '请填写金额',
        'amount_val.float'        => '金额格式无效',
        'amount_val.gt'           => '金额须大于 0',
        'trigger_reason.max'      => '触发原因过长',
        'suspicious_desc.max'     => '可疑描述过长',
        'review_remark.require'   => '请填写审核说明',
        'review_remark.max'       => '审核说明最长 1000 字符',
        'dismiss_reason.require'  => '请填写排除理由',
        'dismiss_reason.max'      => '排除理由最长 1000 字符',
        'page.integer'            => '页码无效',
        'page.gt'                 => '页码无效',
        'pageSize.integer'        => '每页条数无效',
        'pageSize.in'             => '每页条数无效',
    ];

    protected $scene = [
        'list'     => ['merchant_id', 'order_no', 'status', 'tab', 'type', 'page', 'pageSize'],
        'detail'   => ['id'],
        'create'   => ['order_no', 'type', 'currency', 'amount_val', 'trigger_reason', 'suspicious_desc'],
        'confirm'  => ['id', 'review_remark'],
        'dismiss'  => ['id', 'dismiss_reason'],
        'upload'   => ['id'],
        'submit'   => ['id'],
        'download' => ['id'],
    ];

    public function sceneList()
    {
        return $this->only(['merchant_id', 'order_no', 'status', 'tab', 'type', 'page', 'pageSize'])
            ->remove('merchant_id', 'require')
            ->remove('order_no', 'require')
            ->remove('type', 'require');
    }

    public function sceneCreate()
    {
        return $this->only([
            'order_no', 'type', 'currency', 'amount_val', 'trigger_reason', 'suspicious_desc',
        ])->append('type', 'require|checkType');
    }

    /**
     * @param array<string, mixed> $data
     * @return array{
     *   merchant_id?: string,
     *   order_no?: string,
     *   status?: string,
     *   type?: string,
     *   tab?: string
     * }
     */
    public static function toListFilters(array $data): array
    {
        $filters = [];

        $merchantId = trim((string) ($data['merchant_id'] ?? ''));
        if ($merchantId !== '') {
            $filters['merchant_id'] = $merchantId;
        }

        $orderNo = trim((string) ($data['order_no'] ?? ''));
        if ($orderNo !== '') {
            $filters['order_no'] = $orderNo;
        }

        $status = trim((string) ($data['status'] ?? ''));
        if ($status !== '') {
            $filters['status'] = $status;
        }

        $type = trim((string) ($data['type'] ?? ''));
        if ($type !== '') {
            $filters['type'] = $type;
        }

        $tab = trim((string) ($data['tab'] ?? 'all'));
        if ($tab !== '' && $tab !== 'all') {
            $filters['tab'] = $tab;
        } else {
            $filters['tab'] = 'all';
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
        $type    = trim((string) ($data['type'] ?? ''));
        $reason  = trim((string) ($data['trigger_reason'] ?? ''));
        $desc    = trim((string) ($data['suspicious_desc'] ?? ''));
        $currency = strtoupper(trim((string) ($data['currency'] ?? 'USD')));

        return [
            'order_no'        => trim((string) ($data['order_no'] ?? '')),
            'type'            => $type,
            'currency'        => $currency,
            'amount_val'      => (float) ($data['amount_val'] ?? 0),
            'trigger_reason'  => $reason,
            'suspicious_desc' => $desc !== '' ? $desc : null,
        ];
    }

    protected function checkType(mixed $value): bool|string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return true;
        }

        return in_array($v, StrReportModel::TYPES, true) ? true : '报送类型无效';
    }

    protected function checkCurrency(mixed $value): bool|string
    {
        $v = strtoupper(trim((string) $value));

        return in_array($v, StrReportModel::CURRENCIES, true) ? true : '币种无效';
    }

    protected function checkStatus(mixed $value): bool|string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return true;
        }

        return in_array($v, StrReportModel::STATUSES, true) ? true : '状态无效';
    }

    protected function checkTab(mixed $value): bool|string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return true;
        }

        return in_array($v, ['all', 'pending', 'ltr', 'str'], true) ? true : 'Tab 无效';
    }
}
