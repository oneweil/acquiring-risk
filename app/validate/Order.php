<?php

declare(strict_types=1);

namespace app\validate;

use app\model\OrderEvaluation;
use think\Validate;

/**
 * 订单监控筛选 / 详情参数校验
 */
class Order extends Validate
{
    protected $rule = [
        'merchant_id' => 'max:64',
        'order_no'    => 'max:128',
        'currency'    => 'max:16',
        'risk_level'  => 'checkRiskLevel',
        'status'      => 'checkStatus',
        'page'        => 'integer|gt:0',
        'pageSize'    => 'integer|in:10,20,50',
        'id'          => 'max:128',
    ];

    protected $message = [
        'merchant_id.max'  => '商户号过长',
        'order_no.max'     => '订单号过长',
        'id.require'       => '缺少订单标识',
        'id.max'           => '订单标识过长',
        'currency.max'     => '币种无效',
        'page.integer'     => '页码无效',
        'page.gt'          => '页码无效',
        'pageSize.integer' => '每页条数无效',
        'pageSize.in'      => '每页条数无效',
    ];

    protected $scene = [
        'list'   => ['merchant_id', 'order_no', 'currency', 'risk_level', 'status', 'page', 'pageSize'],
        'detail' => ['id'],
    ];

    public function sceneDetail()
    {
        return $this->only(['id'])
            ->append('id', 'require');
    }

    /**
     * @param mixed $value
     */
    protected function checkRiskLevel(mixed $value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!in_array((string) $value, OrderEvaluation::RISK_LEVELS, true)) {
            return '风险等级无效';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkStatus(mixed $value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!in_array((string) $value, OrderEvaluation::STATUSES, true)) {
            return '订单状态无效';
        }

        return true;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public static function toListFilters(array $params): array
    {
        $filters = [];
        foreach (['merchant_id', 'order_no', 'currency', 'risk_level', 'status'] as $key) {
            if (!array_key_exists($key, $params)) {
                continue;
            }
            $val = is_string($params[$key]) ? trim($params[$key]) : $params[$key];
            if ($val === null || $val === '') {
                continue;
            }
            $filters[$key] = is_string($val) ? $val : (string) $val;
        }

        return $filters;
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function toListPageSize(array $params): int
    {
        $size = isset($params['pageSize']) ? (int) $params['pageSize'] : 0;
        if (in_array($size, [10, 20, 50], true)) {
            return $size;
        }

        return (int) config('paginate.list_rows', 10);
    }
}
