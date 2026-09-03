<?php

declare(strict_types=1);

namespace app\validate;

use app\model\Blacklist as BlacklistModel;
use think\Validate;

/**
 * 黑名单参数校验（官方验证器 + 场景）
 *
 * @see docs/thinkphp/v8/Verification.md
 */
class Blacklist extends Validate
{
    protected $rule = [
        'type'        => 'require|checkType',
        'value'       => 'require|max:128',
        'reason'      => 'require|max:255',
        'risk_level'  => 'require|checkRiskLevel',
        'expiry_mode' => 'require|in:long,custom',
        'expiry_date' => 'requireIf:expiry_mode,custom|checkExpiryDate',
        'status'      => 'require|checkStatus',
        'keyword'     => 'max:128',
        'page'        => 'integer|gt:0',
    ];

    protected $message = [
        'type.require'          => '类型无效',
        'value.require'         => '请填写黑名单值',
        'value.max'             => '黑名单值最长 128 字符',
        'reason.require'        => '请填写原因说明',
        'reason.max'            => '原因说明最长 255 字符',
        'risk_level.require'    => '风险等级无效',
        'expiry_mode.require'   => '请选择到期方式',
        'expiry_mode.in'        => '到期方式无效',
        'expiry_date.requireIf' => '请填写有效的到期日期',
        'status.require'        => '状态无效',
        'keyword.max'           => '关键词最长 128 字符',
        'page.integer'          => '页码无效',
        'page.gt'               => '页码无效',
    ];

    protected $scene = [
        'list' => ['type', 'keyword', 'page'],
        'save' => ['type', 'value', 'reason', 'risk_level', 'expiry_mode', 'expiry_date', 'status'],
    ];

    /**
     * 列表筛选：type 可为空
     */
    public function sceneList()
    {
        return $this->only(['type', 'keyword', 'page'])
            ->remove('type', 'require');
    }

    /**
     * 验证通过后整理入库数据（替代控制器私有方法）
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function toSaveData(array $data): array
    {
        $statusRaw = $data['status'] ?? true;
        $status    = filter_var($statusRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($status === null) {
            $status = in_array($statusRaw, [1, '1', true], true);
        }

        $expiryMode = trim((string) ($data['expiry_mode'] ?? 'long'));
        $expiryDate = null;
        if ($expiryMode === 'custom') {
            $expiryDate = trim((string) ($data['expiry_date'] ?? ''));
            if ($expiryDate === '') {
                $expiryDate = null;
            }
        }

        return [
            'type'           => trim((string) ($data['type'] ?? '')),
            'value'          => trim((string) ($data['value'] ?? '')),
            'reason'         => trim((string) ($data['reason'] ?? '')),
            'risk_level'     => trim((string) ($data['risk_level'] ?? '')),
            'effective_date' => date('Y-m-d'),
            'expiry_date'    => $expiryDate,
            'status'         => (bool) $status,
        ];
    }

    /**
     * 列表筛选参数整理
     *
     * @param array<string, mixed> $data
     * @return array{type: string, keyword: string}
     */
    public static function toListFilters(array $data): array
    {
        return [
            'type'    => trim((string) ($data['type'] ?? '')),
            'keyword' => trim((string) ($data['keyword'] ?? '')),
        ];
    }

    protected function checkType(mixed $value): bool|string
    {
        $type = trim((string) $value);
        if ($type === '') {
            return true;
        }

        return in_array($type, BlacklistModel::TYPES, true) ? true : '类型无效';
    }

    protected function checkRiskLevel(mixed $value): bool|string
    {
        $level = trim((string) $value);

        return in_array($level, BlacklistModel::RISK_LEVELS, true) ? true : '风险等级无效';
    }

    protected function checkExpiryDate(mixed $value, mixed $rule, array $data = []): bool|string
    {
        $mode = (string) ($data['expiry_mode'] ?? 'long');
        if ($mode !== 'custom') {
            return true;
        }

        $date = trim((string) $value);
        if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '请填写有效的到期日期';
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $date);

        return ($dt && $dt->format('Y-m-d') === $date) ? true : '请填写有效的到期日期';
    }

    protected function checkStatus(mixed $value): bool|string
    {
        if (is_bool($value)) {
            return true;
        }

        return in_array($value, [0, 1, '0', '1', true, false, 'true', 'false'], true)
            ? true
            : '状态无效';
    }
}
