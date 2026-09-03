<?php

declare(strict_types=1);

namespace app\validate;

use app\model\Disposition as DispositionModel;
use think\Validate;

/**
 * 处置策略参数校验（官方验证器 + 场景）
 */
class Disposition extends Validate
{
    protected $rule = [
        'code'        => 'require|max:64|checkCode',
        'name'        => 'require|max:64',
        'description' => 'require|max:255',
        'risk_level'  => 'require|checkRiskLevel',
        'scope'       => 'require|checkScope',
        'priority'    => 'require|integer|egt:0',
        'is_block'    => 'require|checkBool',
        'push_alert'  => 'require|checkBool',
        'status'      => 'require|checkBool',
        'page'        => 'integer|gt:0',
    ];

    protected $message = [
        'code.require'        => '请填写策略编码',
        'code.max'            => '策略编码最长 64 字符',
        'name.require'        => '请填写策略名称',
        'name.max'            => '策略名称最长 64 字符',
        'description.require' => '请填写说明',
        'description.max'     => '说明最长 255 字符',
        'risk_level.require'  => '风险等级无效',
        'scope.require'       => '作用域无效',
        'priority.require'    => '请填写优先级',
        'priority.integer'    => '优先级无效',
        'priority.egt'        => '优先级无效',
        'is_block.require'    => '是否阻断无效',
        'push_alert.require'  => '推送预警无效',
        'status.require'      => '状态无效',
        'page.integer'        => '页码无效',
        'page.gt'             => '页码无效',
    ];

    protected $scene = [
        'list' => ['risk_level', 'scope', 'status', 'page'],
        'save' => ['code', 'name', 'description', 'risk_level', 'scope', 'priority', 'is_block', 'push_alert', 'status'],
    ];

    public function sceneList()
    {
        return $this->only(['risk_level', 'scope', 'status', 'page'])
            ->remove('risk_level', 'require')
            ->remove('scope', 'require')
            ->remove('status', 'require');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function toSaveData(array $data): array
    {
        return [
            'code'        => strtoupper(trim((string) ($data['code'] ?? ''))),
            'name'        => trim((string) ($data['name'] ?? '')),
            'description' => trim((string) ($data['description'] ?? '')),
            'risk_level'  => trim((string) ($data['risk_level'] ?? '')),
            'scope'       => trim((string) ($data['scope'] ?? '')),
            'priority'    => (int) ($data['priority'] ?? 100),
            'is_block'    => self::toBool($data['is_block'] ?? false),
            'push_alert'  => self::toBool($data['push_alert'] ?? true),
            'status'      => self::toBool($data['status'] ?? true),
        ];
    }

    /**
     * 列表筛选：仅保留已通过校验且非空的条件，供 Repository isset 后 where
     *
     * @param array<string, mixed> $data
     * @return array{risk_level?: string, scope?: string, status?: bool}
     */
    public static function toListFilters(array $data): array
    {
        $filters = [];

        $riskLevel = trim((string) ($data['risk_level'] ?? ''));
        if ($riskLevel !== '') {
            $filters['risk_level'] = $riskLevel;
        }

        $scope = trim((string) ($data['scope'] ?? ''));
        if ($scope !== '') {
            $filters['scope'] = $scope;
        }

        $statusRaw = $data['status'] ?? '';
        if ($statusRaw !== '' && $statusRaw !== null) {
            $filters['status'] = self::toBool($statusRaw);
        }

        return $filters;
    }

    protected function checkCode(mixed $value): bool|string
    {
        $code = trim((string) $value);
        if ($code === '') {
            return '请填写策略编码';
        }
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,63}$/', $code)) {
            return '策略编码须以字母开头，仅含字母数字下划线';
        }

        return true;
    }

    protected function checkRiskLevel(mixed $value): bool|string
    {
        $level = trim((string) $value);
        if ($level === '') {
            return true;
        }

        return in_array($level, DispositionModel::RISK_LEVELS, true) ? true : '风险等级无效';
    }

    protected function checkScope(mixed $value): bool|string
    {
        $scope = trim((string) $value);
        if ($scope === '') {
            return true;
        }

        return in_array($scope, DispositionModel::SCOPES, true) ? true : '作用域无效';
    }

    protected function checkBool(mixed $value): bool|string
    {
        if ($value === '' || $value === null) {
            return true;
        }

        if (is_bool($value)) {
            return true;
        }

        return in_array($value, [0, 1, '0', '1', true, false, 'true', 'false'], true)
            ? true
            : '参数无效';
    }

    private static function toBool(mixed $value): bool
    {
        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parsed !== null) {
            return $parsed;
        }

        return in_array($value, [1, '1', true], true);
    }
}
