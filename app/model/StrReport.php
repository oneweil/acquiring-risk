<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * STR/LTR 合规报送（物理表 risk_str_report）
 *
 * @property int               $id
 * @property string            $report_no
 * @property string            $type
 * @property string            $trigger_mode
 * @property string            $merchant_id
 * @property string            $merchant_name
 * @property string            $order_no
 * @property string            $currency
 * @property string|float      $amount_val
 * @property string            $amount_display
 * @property string            $trigger_reason
 * @property string|null       $suspicious_desc
 * @property string            $status
 * @property string|null       $submitter
 * @property string|null       $reviewer
 * @property string|null       $review_remark
 * @property string|null       $dismiss_reason
 * @property string|null       $reject_reason
 * @property string|null       $linked_alert_id
 * @property array|string|null $hit_rule_ids
 * @property string|null       $reviewed_at
 * @property string|null       $submitted_at
 * @property string            $created_at
 * @property string            $updated_at
 */
class StrReport extends Model
{
    protected $name = 'str_report';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    // 注意：表字段名为 type，勿再声明 protected $type（会与字段冲突）
    // hit_rule_ids 以 JSON 字符串存库，由 Service / Resource 编解码

    public const TYPE_LTR = 'ltr';
    public const TYPE_STR = 'str';

    /** @var list<string> */
    public const TYPES = [
        self::TYPE_LTR,
        self::TYPE_STR,
    ];

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        self::TYPE_LTR => '大额交易 (LTR)',
        self::TYPE_STR => '可疑交易 (STR)',
    ];

    public const TRIGGER_AUTO   = 'auto';
    public const TRIGGER_MANUAL = 'manual';

    /** @var list<string> */
    public const TRIGGER_MODES = [
        self::TRIGGER_AUTO,
        self::TRIGGER_MANUAL,
    ];

    /** @var array<string, string> */
    public const TRIGGER_MODE_LABELS = [
        self::TRIGGER_AUTO   => '自动推送',
        self::TRIGGER_MANUAL => '人工创建',
    ];

    public const STATUS_PENDING_CONFIRM = 'pending_confirm';
    public const STATUS_GENERATED       = 'generated';
    public const STATUS_UPLOADED        = 'uploaded';
    public const STATUS_SUBMITTED       = 'submitted';
    public const STATUS_DISMISSED       = 'dismissed';
    public const STATUS_ARCHIVED        = 'archived';
    public const STATUS_REJECTED        = 'rejected';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING_CONFIRM,
        self::STATUS_GENERATED,
        self::STATUS_UPLOADED,
        self::STATUS_SUBMITTED,
        self::STATUS_DISMISSED,
        self::STATUS_ARCHIVED,
        self::STATUS_REJECTED,
    ];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING_CONFIRM => '待确认',
        self::STATUS_GENERATED       => '已生成',
        self::STATUS_UPLOADED        => '已上传',
        self::STATUS_SUBMITTED       => '已提交监管',
        self::STATUS_DISMISSED       => '无需上报',
        self::STATUS_ARCHIVED        => '已归档',
        self::STATUS_REJECTED        => '已退回',
    ];

    /** @var list<string> */
    public const CURRENCIES = ['USD', 'EUR', 'GBP', 'HKD'];

    /**
     * 格式化金额展示串
     */
    public static function formatAmountDisplay(string $currency, float|string $amountVal): string
    {
        $num = number_format((float) $amountVal, 2, '.', ',');

        return strtoupper($currency) . ' ' . $num;
    }
}
