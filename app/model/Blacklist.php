<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 风控黑名单（物理表 risk_blacklist）
 *
 * @property int         $id
 * @property string      $code
 * @property string      $type
 * @property string      $value
 * @property string      $reason
 * @property string      $risk_level
 * @property string      $effective_date
 * @property string|null $expiry_date
 * @property int|bool    $status
 * @property string      $created_at
 * @property string      $updated_at
 */
class Blacklist extends Model
{
    protected $name = 'blacklist';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var list<string> */
    public const TYPES = ['ip', 'email', 'card', 'country', 'website', 'phone'];

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        'ip'      => 'IP',
        'email'   => '邮箱',
        'card'    => '卡号',
        'country' => '国家',
        'website' => '网站',
        'phone'   => '手机号',
    ];

    /** @var list<string> */
    public const RISK_LEVELS = ['low', 'medium', 'high', 'critical'];

    /** @var array<string, string> */
    public const RISK_LEVEL_LABELS = [
        'low'      => '低风险',
        'medium'   => '中风险',
        'high'     => '高风险',
        'critical' => '极高风险',
    ];
}
