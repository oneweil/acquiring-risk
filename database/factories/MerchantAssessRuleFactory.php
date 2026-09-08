<?php

declare(strict_types=1);

namespace database\factories;

/**
 * 商户评估规则行默认数据（对齐计划 seed 表；无 name / measure_code）
 */
class MerchantAssessRuleFactory
{
    /**
     * @return list<array<string, mixed>>
     */
    public function builtinRows(): array
    {
        $now  = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($this->definitions() as $def) {
            $rows[] = [
                'rule_id'          => $def['rule_id'],
                'category'         => $def['category'],
                'description'      => $def['description'],
                'content_template' => $def['content_template'],
                'score'            => $def['score'],
                'config'           => json_encode($def['config'], JSON_UNESCAPED_UNICODE),
                'enabled'          => 1,
                'sort'             => $def['sort'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array{
     *   rule_id: string,
     *   category: string,
     *   sort: int,
     *   content_template: string,
     *   description: string,
     *   score: int,
     *   config: array<string, mixed>
     * }>
     */
    public function definitions(): array
    {
        return [
            [
                'rule_id' => 'MA_IND_01', 'category' => 'industry', 'sort' => 10,
                'content_template' => '行业 = {match_key}（MCC 5967/5999 等）',
                'description' => '数字商品 / 礼品卡；拒付难追回，行业基线高风险',
                'score' => 85,
                'config' => ['match_key' => '虚拟商品'],
            ],
            [
                'rule_id' => 'MA_IND_02', 'category' => 'industry', 'sort' => 20,
                'content_template' => '行业 = {match_key}',
                'description' => '跨境物流与三国不一致高发',
                'score' => 55,
                'config' => ['match_key' => '跨境电商'],
            ],
            [
                'rule_id' => 'MA_IND_03', 'category' => 'industry', 'sort' => 30,
                'content_template' => '行业 = {match_key}',
                'description' => '订阅模式，Chargeback 相对较低',
                'score' => 35,
                'config' => ['match_key' => '软件SaaS'],
            ],
            [
                'rule_id' => 'MA_IND_04', 'category' => 'industry', 'sort' => 40,
                'content_template' => '行业 = {match_key}',
                'description' => '季节性波动；部分 MCC 受限',
                'score' => 40,
                'config' => ['match_key' => '旅游'],
            ],
            [
                'rule_id' => 'MA_IND_05', 'category' => 'industry', 'sort' => 50,
                'content_template' => '行业 = {match_key}',
                'description' => '实物交付，可追踪物流',
                'score' => 30,
                'config' => ['match_key' => '实体零售'],
            ],
            [
                'rule_id' => 'MA_CFG_IND', 'category' => 'industry', 'sort' => 60,
                'content_template' => '其他未列明行业（默认）',
                'description' => '取行业映射表未命中时的兜底分值',
                'score' => 50,
                'config' => ['match_key' => 'industry_default'],
            ],
            [
                'rule_id' => 'MA_WEB_OK', 'category' => 'website', 'sort' => 10,
                'content_template' => '网站能访问',
                'description' => 'DNS 解析成功且 HTTP 探测返回成功状态',
                'score' => 15,
                'config' => ['match_key' => 'compliant'],
            ],
            [
                'rule_id' => 'MA_WEB_DOWN', 'category' => 'website', 'sort' => 20,
                'content_template' => '网站不能访问',
                'description' => '系统探测不可达；建议挂起或复评',
                'score' => 80,
                'config' => ['match_key' => 'mismatch'],
            ],
            [
                'rule_id' => 'MA_COMP_0', 'category' => 'compliance', 'sort' => 10,
                'content_template' => '制裁名单 / PEP / 负面舆情筛查命中 0 条',
                'description' => '0 命中为通过',
                'score' => 10,
                'config' => ['match_key' => '0'],
            ],
            [
                'rule_id' => 'MA_COMP_1', 'category' => 'compliance', 'sort' => 20,
                'content_template' => '筛查命中 ≥ 1 条记录',
                'description' => '需人工复核受益人 / UBO',
                'score' => 55,
                'config' => ['match_key' => '1'],
            ],
            [
                'rule_id' => 'MA_COMP_2', 'category' => 'compliance', 'sort' => 30,
                'content_template' => '筛查命中 ≥ 2 条或制裁直接命中',
                'description' => '建议拒绝入驻或暂停收单',
                'score' => 90,
                'config' => ['match_key' => '2'],
            ],
            [
                'rule_id' => 'MA_CB_0', 'category' => 'chargeback', 'sort' => 10,
                'content_template' => '滚动30天拒付率 ≤ {threshold} %',
                'description' => '低拒付；正常经营区间',
                'score' => 20,
                'config' => ['threshold' => 0.5],
            ],
            [
                'rule_id' => 'MA_CB_1', 'category' => 'chargeback', 'sort' => 20,
                'content_template' => '滚动30天拒付率 ≤ {threshold} %（VAMP Early Warning）',
                'description' => '接近预警线',
                'score' => 40,
                'config' => ['threshold' => 0.9],
            ],
            [
                'rule_id' => 'MA_CB_2', 'category' => 'chargeback', 'sort' => 30,
                'content_template' => '滚动30天拒付率 ≤ {threshold} %（VAMP Identification）',
                'description' => '识别线以内；加强观察',
                'score' => 65,
                'config' => ['threshold' => 1.5],
            ],
            [
                'rule_id' => 'MA_CB_3', 'category' => 'chargeback', 'sort' => 40,
                'content_template' => '滚动30天拒付率 > {threshold} %（Excessive）',
                'description' => '超识别线；建议限制结算 / 复评',
                'score' => 90,
                'config' => ['threshold' => 1.5],
            ],
            [
                'rule_id' => 'MA_FR_0', 'category' => 'fraud', 'sort' => 10,
                'content_template' => '滚动30天欺诈率 ≤ {threshold} %',
                'description' => '低欺诈；正常区间',
                'score' => 20,
                'config' => ['threshold' => 0.2],
            ],
            [
                'rule_id' => 'MA_FR_1', 'category' => 'fraud', 'sort' => 20,
                'content_template' => '滚动30天欺诈率 ≤ {threshold} %（VFMP / EFM 监控线）',
                'description' => '接近监控线',
                'score' => 50,
                'config' => ['threshold' => 0.5],
            ],
            [
                'rule_id' => 'MA_FR_2', 'category' => 'fraud', 'sort' => 30,
                'content_template' => '滚动30天欺诈率 > {threshold} %',
                'description' => '超监控线；建议人工审核',
                'score' => 85,
                'config' => ['threshold' => 0.5],
            ],
            [
                'rule_id' => 'MA_RF_0', 'category' => 'refund', 'sort' => 10,
                'content_template' => '滚动30天退款率 ≤ {threshold} %',
                'description' => '正常退款区间',
                'score' => 20,
                'config' => ['threshold' => 5],
            ],
            [
                'rule_id' => 'MA_RF_1', 'category' => 'refund', 'sort' => 20,
                'content_template' => '滚动30天退款率 ≤ {threshold} %',
                'description' => '偏高；加强监控',
                'score' => 50,
                'config' => ['threshold' => 15],
            ],
            [
                'rule_id' => 'MA_RF_2', 'category' => 'refund', 'sort' => 30,
                'content_template' => '滚动30天退款率 > {threshold} % 且交易笔数 ≥ 100',
                'description' => '高退款占比；洗钱或虚假交易信号',
                'score' => 85,
                'config' => ['threshold' => 15],
            ],
            [
                'rule_id' => 'MA_TEN_0', 'category' => 'tenure', 'sort' => 10,
                'content_template' => '注册时长 ≥ {threshold} 天',
                'description' => '按商户注册时间计算；成熟主体，历史可验证',
                'score' => 15,
                'config' => ['threshold' => 365],
            ],
            [
                'rule_id' => 'MA_TEN_1', 'category' => 'tenure', 'sort' => 20,
                'content_template' => '注册时长 ≥ {threshold} 天',
                'description' => '按商户注册时间计算；已过爬坡期',
                'score' => 30,
                'config' => ['threshold' => 180],
            ],
            [
                'rule_id' => 'MA_TEN_2', 'category' => 'tenure', 'sort' => 30,
                'content_template' => '注册时长 ≥ {threshold} 天',
                'description' => '按商户注册时间计算；Ramp-up 阶段',
                'score' => 50,
                'config' => ['threshold' => 90],
            ],
            [
                'rule_id' => 'MA_CFG_TEN', 'category' => 'tenure', 'sort' => 40,
                'content_template' => '注册时长 < {threshold} 天（新注册）',
                'description' => '注册未满最低档；入网评估无注册时间时默认取此档',
                'score' => 75,
                'config' => ['match_key' => 'tenure_new_score', 'threshold' => 90],
            ],
            [
                'rule_id' => 'MA_VOL_0', 'category' => 'volume_anomaly', 'sort' => 10,
                'content_template' => '当日交易量 ≤ 近30日均值 × {low_threshold} %',
                'description' => '正常波动范围',
                'score' => 15,
                'config' => ['low_threshold' => 200],
            ],
            [
                'rule_id' => 'MA_VOL_1', 'category' => 'volume_anomaly', 'sort' => 20,
                'content_template' => '当日交易量 > 近30日均值 × {low_threshold} % 且 ≤ × {threshold} %',
                'description' => '异常放量；加强监控',
                'score' => 50,
                'config' => ['low_threshold' => 200, 'threshold' => 500],
            ],
            [
                'rule_id' => 'MA_VOL_2', 'category' => 'volume_anomaly', 'sort' => 30,
                'content_template' => '当日交易量 > 近30日均值 × {threshold} %',
                'description' => 'Account Takeover / 商户账户被盗（同 R026）',
                'score' => 85,
                'config' => ['threshold' => 500],
            ],
            [
                'rule_id' => 'MA_GEO_L', 'category' => 'geo', 'sort' => 10,
                'content_template' => '注册国家 ∈ {countries}',
                'description' => '低风险地区；监管完善、欺诈率较低（多国同一档）',
                'score' => 25,
                'config' => ['countries' => ['US', 'GB', 'DE', 'SG']],
            ],
            [
                'rule_id' => 'MA_GEO_M', 'category' => 'geo', 'sort' => 20,
                'content_template' => '注册国家 ∈ {countries}',
                'description' => '中等风险；离岸架构常见',
                'score' => 45,
                'config' => ['countries' => ['HK']],
            ],
            [
                'rule_id' => 'MA_GEO_H', 'category' => 'geo', 'sort' => 30,
                'content_template' => '注册国家 ∈ {countries}',
                'description' => '高风险 / 制裁关注；欺诈高发或合规风险',
                'score' => 90,
                'config' => ['countries' => ['NG', 'RU', 'IR']],
            ],
            [
                'rule_id' => 'MA_CFG_GEO', 'category' => 'geo', 'sort' => 40,
                'content_template' => '其他未列明国家（默认）',
                'description' => '未命中任一档时的兜底分值',
                'score' => 40,
                'config' => ['match_key' => 'geo_default'],
            ],
        ];
    }
}
