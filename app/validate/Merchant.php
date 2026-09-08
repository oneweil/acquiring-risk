<?php

declare(strict_types=1);

namespace app\validate;

use app\model\MerchantAssessment;
use think\Validate;

/**
 * 商户列表筛选 / 详情参数校验
 */
class Merchant extends Validate
{
    /** @var list<string> */
    public const TRADING_STATUSES = [
        'normal',
        'watch',
        'restricted',
        'suspended',
        'not_opened',
    ];

    /** @var list<string> */
    public const REVIEW_STATUSES = [
        'approved',
        'rejected',
    ];

    protected $rule = [
        'merchant_id'    => 'max:32',
        'name'           => 'max:200',
        'risk_level'     => 'checkRiskLevel',
        'review_status'  => 'checkReviewStatus',
        'trading_status' => 'checkTradingStatus',
        'page'           => 'integer|gt:0',
        'pageSize'       => 'integer|in:10,20,50',
    ];

    protected $message = [
        'merchant_id.require'   => '请填写商户号',
        'merchant_id.max'       => '商户号最长 32 字符',
        'name.max'              => '商户名称最长 200 字符',
        'page.integer'          => '页码无效',
        'page.gt'               => '页码无效',
        'pageSize.integer'      => '每页条数无效',
        'pageSize.in'           => '每页条数无效',
    ];

    protected $scene = [
        'list'   => ['merchant_id', 'name', 'risk_level', 'review_status', 'trading_status', 'page', 'pageSize'],
        'detail' => ['merchant_id'],
    ];

    public function sceneDetail()
    {
        return $this->only(['merchant_id'])
            ->append('merchant_id', 'require');
    }

    /**
     * @param mixed $value
     */
    protected function checkRiskLevel(mixed $value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!in_array((string) $value, MerchantAssessment::RISK_LEVELS, true)) {
            return '风险等级无效';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkReviewStatus(mixed $value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!in_array((string) $value, self::REVIEW_STATUSES, true)) {
            return '审核状态无效';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkTradingStatus(mixed $value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (!in_array((string) $value, self::TRADING_STATUSES, true)) {
            return '收单状态无效';
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
        foreach (['merchant_id', 'name', 'risk_level', 'review_status', 'trading_status'] as $key) {
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
