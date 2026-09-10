<?php

declare(strict_types=1);

namespace app\validate\api;

use app\model\Merchant;
use app\model\MerchantAssessment;
use think\Validate;

/**
 * 商户投影 upsert / assess 校验
 */
class MerchantSync extends Validate
{
    protected $rule = [
        'merchant_id'     => 'require|max:32',
        'name'            => 'require|max:200',
        'status'          => 'require|checkStatus',
        'source_version'  => 'require|integer|egt:0',
        'industry'        => 'max:64',
        'country'         => 'max:8',
        'register_at'     => 'dateFormat:Y-m-d',
        'onboard_at'      => 'dateFormat:Y-m-d',
        'website'         => 'max:255',
        'email'           => 'max:128',
        'mobile'          => 'max:32',
        'address'         => 'max:255',
        'website_status'  => 'checkWebsiteStatus',
        'compliance_hits' => 'integer|egt:0',
        'review_status'   => 'checkReviewStatus',
        'assess'          => 'boolean',
        'items'           => 'require|array|checkBatchItems',
    ];

    protected $message = [
        'merchant_id.require'    => 'merchant_id 必填',
        'name.require'           => 'name 必填',
        'status.require'         => 'status 必填',
        'source_version.require' => 'source_version 必填',
        'source_version.integer' => 'source_version 须为整数',
        'items.require'          => 'items 必填',
        'items.array'            => 'items 须为数组',
    ];

    protected $scene = [
        'upsert' => [
            'merchant_id', 'name', 'status', 'source_version',
            'industry', 'country', 'register_at', 'onboard_at',
            'website', 'email', 'mobile', 'address',
            'website_status', 'compliance_hits', 'review_status', 'assess',
        ],
        'batch'  => ['items'],
        'get'    => ['merchant_id'],
    ];

    /**
     * @param mixed $value
     */
    protected function checkStatus(mixed $value): bool|string
    {
        if (!in_array((string) $value, Merchant::STATUSES, true)) {
            return 'status 无效';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkWebsiteStatus(mixed $value): bool|string
    {
        if ($value === null || $value === '') {
            return true;
        }
        $allowed = array_keys(MerchantAssessment::WEBSITE_STATUS_LABELS);
        if (!in_array((string) $value, $allowed, true)) {
            return 'website_status 无效';
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
        if (!in_array((string) $value, Merchant::REVIEW_STATUSES, true)) {
            return 'review_status 无效';
        }

        return true;
    }

    /**
     * @param mixed $value
     */
    protected function checkBatchItems(mixed $value): bool|string
    {
        if (!is_array($value) || $value === []) {
            return 'items 不能为空';
        }
        if (count($value) > 200) {
            return '单次最多 200 条';
        }

        return true;
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public static function toUpsertData(array $params): array
    {
        $data = [
            'merchant_id'    => trim((string) ($params['merchant_id'] ?? '')),
            'name'           => trim((string) ($params['name'] ?? '')),
            'status'         => trim((string) ($params['status'] ?? '')),
            'source_version' => (int) ($params['source_version'] ?? 0),
        ];

        foreach (['industry', 'country', 'register_at', 'onboard_at', 'website', 'email', 'mobile', 'address', 'website_status', 'review_status'] as $key) {
            if (!array_key_exists($key, $params) || $params[$key] === null || $params[$key] === '') {
                continue;
            }
            $data[$key] = is_string($params[$key]) ? trim($params[$key]) : $params[$key];
        }

        if (array_key_exists('compliance_hits', $params) && $params['compliance_hits'] !== null && $params['compliance_hits'] !== '') {
            $data['compliance_hits'] = (int) $params['compliance_hits'];
        }

        if (array_key_exists('extra', $params) && is_array($params['extra'])) {
            $data['extra'] = $params['extra'];
        }

        if (array_key_exists('assess', $params)) {
            $data['assess'] = filter_var($params['assess'], FILTER_VALIDATE_BOOLEAN);
        }

        return $data;
    }
}
