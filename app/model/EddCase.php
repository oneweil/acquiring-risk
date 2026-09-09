<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * EDD 强化尽调工单（物理表 risk_edd_case）
 *
 * @property int               $id
 * @property string            $case_no
 * @property string            $merchant_id
 * @property string            $merchant_name
 * @property string            $trigger
 * @property string            $risk_level
 * @property string            $status
 * @property string            $deadline
 * @property string|null       $assignee
 * @property int               $progress
 * @property array|string|null $checklist
 * @property string|null       $notes
 * @property string|null       $linked_str_id
 * @property string|null       $review_remark
 * @property string|null       $reviewed_at
 * @property string            $created_at
 * @property string            $updated_at
 */
class EddCase extends Model
{
    protected $name = 'edd_case';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'progress'  => 'integer',
        'checklist' => 'json',
    ];

    public const STATUS_PENDING    = 'pending';
    public const STATUS_COLLECTING = 'collecting';
    public const STATUS_REVIEWING  = 'reviewing';
    public const STATUS_PASSED     = 'passed';
    public const STATUS_REJECTED   = 'rejected';
    public const STATUS_EXPIRED    = 'expired';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_COLLECTING,
        self::STATUS_REVIEWING,
        self::STATUS_PASSED,
        self::STATUS_REJECTED,
        self::STATUS_EXPIRED,
    ];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        self::STATUS_PENDING    => '待启动',
        self::STATUS_COLLECTING => '资料收集中',
        self::STATUS_REVIEWING  => '审核中',
        self::STATUS_PASSED     => '已通过',
        self::STATUS_REJECTED   => '未通过',
        self::STATUS_EXPIRED    => '已过期',
    ];

    public const TRIGGER_HIGH_RISK_MERCHANT = 'high_risk_merchant';
    public const TRIGGER_SUSPICIOUS_TXN     = 'suspicious_txn';
    public const TRIGGER_PEP_SANCTION       = 'pep_sanction';
    public const TRIGGER_HIGH_RISK_INDUSTRY = 'high_risk_industry';
    public const TRIGGER_VOLUME_ANOMALY     = 'volume_anomaly';
    public const TRIGGER_STR_LINK           = 'str_link';

    /** @var list<string> */
    public const TRIGGERS = [
        self::TRIGGER_HIGH_RISK_MERCHANT,
        self::TRIGGER_SUSPICIOUS_TXN,
        self::TRIGGER_PEP_SANCTION,
        self::TRIGGER_HIGH_RISK_INDUSTRY,
        self::TRIGGER_VOLUME_ANOMALY,
        self::TRIGGER_STR_LINK,
    ];

    /** @var array<string, string> */
    public const TRIGGER_LABELS = [
        self::TRIGGER_HIGH_RISK_MERCHANT => '高风险商户',
        self::TRIGGER_SUSPICIOUS_TXN     => '可疑交易关联',
        self::TRIGGER_PEP_SANCTION       => 'PEP/制裁筛查',
        self::TRIGGER_HIGH_RISK_INDUSTRY => '行业高风险',
        self::TRIGGER_VOLUME_ANOMALY     => '交易量异常',
        self::TRIGGER_STR_LINK           => 'STR关联',
    ];

    /**
     * 固定 7 项尽调清单元数据
     *
     * @var list<array{key: string, label: string, desc: string, required: bool}>
     */
    public const CHECKLIST_ITEMS = [
        [
            'key'      => 'ubo',
            'label'    => '受益所有人 (UBO) 身份核实',
            'desc'     => '提供持股 ≥25% 自然人身份证件及股权结构图',
            'required' => true,
        ],
        [
            'key'      => 'source',
            'label'    => '资金来源说明',
            'desc'     => '说明主要收入来源及预计交易规模',
            'required' => true,
        ],
        [
            'key'      => 'business',
            'label'    => '业务模式说明',
            'desc'     => '详细描述商品/服务、目标客户、定价模式',
            'required' => true,
        ],
        [
            'key'      => 'site',
            'label'    => '网站/APP 实地核验',
            'desc'     => '截图、Whois、第三方流量数据等',
            'required' => true,
        ],
        [
            'key'      => 'bank',
            'label'    => '银行账户验证',
            'desc'     => '近 3 个月银行对账单或开户证明',
            'required' => true,
        ],
        [
            'key'      => 'pep',
            'label'    => 'PEP/制裁筛查报告',
            'desc'     => '董事及 UBO 无 PEP/制裁命中证明',
            'required' => true,
        ],
        [
            'key'      => 'visit',
            'label'    => '现场尽调报告（如适用）',
            'desc'     => '高风险行业或大额商户需实地或视频尽调',
            'required' => false,
        ],
    ];

    /** @var list<string> */
    public const CHECKLIST_KEYS = ['ubo', 'source', 'business', 'site', 'bank', 'pep', 'visit'];

    /**
     * 默认勾选：required=true 的项
     *
     * @return array<string, bool>
     */
    public static function defaultChecklist(): array
    {
        $map = [];
        foreach (self::CHECKLIST_ITEMS as $item) {
            $map[$item['key']] = $item['required'] !== false;
        }

        return $map;
    }

    /**
     * @param array<string, mixed>|null $checklist
     * @return list<string>
     */
    public static function selectedKeys(?array $checklist): array
    {
        if ($checklist === null || $checklist === []) {
            return [];
        }

        $keys = [];
        foreach (self::CHECKLIST_KEYS as $key) {
            if (!empty($checklist[$key])) {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}
