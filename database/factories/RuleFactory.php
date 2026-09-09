<?php

declare(strict_types=1);

namespace database\factories;

/**
 * 风控规则内置数据：与 xsdfk.html「规则条件配置」页 + RULES 数组一致（非随机模拟）
 *
 * - 带 data-rule-id 的行：id/name/measure/enabled 取自 RULES；展示文案与阈值取自页面 HTML
 * - 仅页面有、无 RULES.id 的行：分配 R044–R048，保持与原型默认策略/开关一致
 */
class RuleFactory
{
    /** @var array<string, string> 原型策略名 → disposition.code */
    private const MEASURE_NAME_MAP = [
        '拒绝交易'    => 'DECLINE',
        '暂停收单'    => 'SUSPEND_MERCHANT',
        '3DS强验'     => '3DS_CHALLENGE',
        '人工审核'    => 'MANUAL_REVIEW',
        '延迟结算'    => 'DELAY_SETTLE',
        '限制单笔额度' => 'LIMIT_AMOUNT',
        '加入观察'    => 'WATCHLIST',
        '调单'        => 'CHARGEBACK_INQUIRY',
        '仅预警'      => 'ALERT_ONLY',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function builtinRows(): array
    {
        $now  = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($this->definitions() as $i => $def) {
            $measure = $def['measure'];
            $code    = self::MEASURE_NAME_MAP[$measure] ?? null;
            if ($code === null) {
                throw new \RuntimeException('未知处置策略：' . $measure . '（' . $def['rule_id'] . '）');
            }

            $rows[] = [
                'rule_id'          => $def['rule_id'],
                'category'         => $def['category'],
                'name'             => $def['name'],
                'description'      => $def['description'],
                'content_template' => $def['content_template'],
                'config'           => json_encode($def['config'], JSON_UNESCAPED_UNICODE),
                'measure_code'     => $code,
                'enabled'          => !empty($def['enabled']) ? 1 : 0,
                'push_str'         => StrPushConfigFactory::defaultPushStr($def['category'], $code) ? 1 : 0,
                'sort'             => ($i + 1) * 10,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        return $rows;
    }

    /**
     * 按原型页面卡片顺序逐条固化（与 #rules .rule-item 一致）
     *
     * @return list<array{
     *   rule_id: string,
     *   category: string,
     *   name: string,
     *   description: string,
     *   content_template: string,
     *   config: array<string, scalar>,
     *   measure: string,
     *   enabled: bool
     * }>
     */
    private function definitions(): array
    {
        return [
            // —— 卡号频率 ——
            [
                'rule_id' => 'R005', 'category' => 'card_velocity', 'name' => '同一卡号10分钟高频',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '防连刷盗卡；Visa/Mastercard 建议 10min/3笔',
                'content_template' => '同一卡号 {window_minutes} 分钟内成功交易 ≥ {min_count} 笔',
                'config' => ['window_minutes' => 10, 'min_count' => 3],
            ],
            [
                'rule_id' => 'R044', 'category' => 'card_velocity', 'name' => '同一卡号授权失败',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '识别卡测试（Card Testing）行为；与 BIN 级失败规则互补',
                'content_template' => '同一卡号 {window_minutes} 分钟内授权失败 ≥ {min_count} 次',
                'config' => ['window_minutes' => 30, 'min_count' => 3],
            ],
            [
                'rule_id' => 'R045', 'category' => 'card_velocity', 'name' => '同一卡号跨商户',
                'measure' => '3DS强验', 'enabled' => true,
                'description' => '识别卡号流转 / 共享卡池风险',
                'content_template' => '同一卡号跨 ≥ {min_merchants} 个不同商户 {window_hours} 小时内交易',
                'config' => ['min_merchants' => 3, 'window_hours' => 1],
            ],

            // —— IP / 身份频率 ——
            [
                'rule_id' => 'R006', 'category' => 'ip_velocity', 'name' => '同一IP多卡号卡测试',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '典型卡测试特征：单IP多卡号轮询',
                'content_template' => '同一 IP {window_minutes} 分钟内不同卡号尝试 ≥ {min_cards} 张',
                'config' => ['window_minutes' => 15, 'min_cards' => 5],
            ],
            [
                'rule_id' => 'R046', 'category' => 'ip_velocity', 'name' => '同一IP授权失败',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '撞库 / 暴力试卡拦截',
                'content_template' => '同一 IP {window_minutes} 分钟内授权失败 ≥ {min_count} 次',
                'config' => ['window_minutes' => 60, 'min_count' => 5],
            ],
            [
                'rule_id' => 'R022', 'category' => 'ip_velocity', 'name' => '同一邮箱多卡号',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '识别批量注册 / 多卡绑定',
                'content_template' => '同一邮箱 {window_hours} 小时内关联 ≥ {min_cards} 张不同卡号',
                'config' => ['window_hours' => 24, 'min_cards' => 3],
            ],

            // —— 交易时间点 ——
            [
                'rule_id' => 'R033', 'category' => 'card_velocity', 'name' => '极短窗口密集下单',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '极短窗口密集下单；机械连刷 / 脚本批量支付',
                'content_template' => '同一卡号 {window_minutes} 分钟内成功交易 ≥ {min_count} 笔',
                'config' => ['window_minutes' => 5, 'min_count' => 4],
            ],
            [
                'rule_id' => 'R034', 'category' => 'time_pattern', 'name' => '非营业时段高频',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '非营业/凌晨时段高频下单；偏离正常消费时间规律',
                'content_template' => '交易时间在 {hour_start} - {hour_end} 且同卡号 {window_hours} 小时内 ≥ {min_count} 笔',
                'config' => ['hour_start' => '00:00', 'hour_end' => '06:00', 'window_hours' => 1, 'min_count' => 3],
            ],
            [
                'rule_id' => 'R035', 'category' => 'ip_velocity', 'name' => '商户单小时爆发',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '商户单小时交易爆发；账户被盗或异常放量',
                'content_template' => '同一商户 {window_hours} 小时内成功交易 ≥ {min_count} 笔',
                'config' => ['window_hours' => 1, 'min_count' => 15],
            ],
            [
                'rule_id' => 'R036', 'category' => 'ip_velocity', 'name' => '单IP时段集中下单',
                'measure' => '3DS强验', 'enabled' => true,
                'description' => '单 IP 时段内集中下单；代理池或自动化工具特征',
                'content_template' => '同一 IP {window_minutes} 分钟内成功交易 ≥ {min_count} 笔',
                'config' => ['window_minutes' => 30, 'min_count' => 8],
            ],

            // —— AVS / CVV / 3DS ——
            [
                'rule_id' => 'R003', 'category' => 'avs_cvv_3ds', 'name' => 'CVV验证失败',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '无有效 CVV 不应放行 CNP 交易',
                'content_template' => 'CVV 验证失败或未提供（CVC Check: Fail / Not Provided）',
                'config' => [],
            ],
            [
                'rule_id' => 'R012', 'category' => 'avs_cvv_3ds', 'name' => 'AVS完全不匹配',
                'measure' => '3DS强验', 'enabled' => true,
                'description' => 'Address Verification System 全不匹配',
                'content_template' => 'AVS 完全不匹配（邮编 + 地址均 No Match）且金额 > {amount_usd} USD',
                'config' => ['amount_usd' => 100],
            ],
            [
                'rule_id' => 'R017', 'category' => 'avs_cvv_3ds', 'name' => 'AVS部分不匹配',
                'measure' => '3DS强验', 'enabled' => true,
                'description' => 'Partial AVS Match 需加强验证',
                'content_template' => 'AVS 部分不匹配（邮编或地址单项 No Match）且金额 > {amount_usd} USD',
                'config' => ['amount_usd' => 500],
            ],
            [
                'rule_id' => 'R011', 'category' => 'avs_cvv_3ds', 'name' => 'PSD2 SCA不合规',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => 'PSD2 强客户认证（SCA）要求；低额豁免除外',
                'content_template' => 'EEA/英国交易未通过 3DS 2.x 强验证（SCA 不合规）且金额 ≥ {amount_eur} EUR',
                'config' => ['amount_eur' => 30],
            ],
            [
                'rule_id' => 'R010', 'category' => 'avs_cvv_3ds', 'name' => '3DS验证失败(ECI未认证)',
                'measure' => '3DS强验', 'enabled' => true,
                'description' => '无 Liability Shift 保护的高额 CNP 交易；大额强制 Step-Up 验证',
                'content_template' => '3DS 验证失败、ECI=07 或未通过 Challenge 且金额 > {amount_usd} USD',
                'config' => ['amount_usd' => 200],
            ],

            // —— 交易金额（含原型页面中的 R014）——
            [
                'rule_id' => 'R013', 'category' => 'amount', 'name' => '单笔大额交易',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '平台统一大额交易阈值（Large Ticket）；按交易币种分别判断',
                'content_template' => '单笔金额 ≥ {amount_usd} USD 或 ≥ {amount_hkd} HKD',
                'config' => ['amount_usd' => 10000, 'amount_hkd' => 50000],
            ],
            [
                'rule_id' => 'R028', 'category' => 'amount', 'name' => '日累计交易金额',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '日累计笔数/金额双维度；识别短时大额资金聚集',
                'content_template' => '同卡号/同商户 {window_hours} 小时内累计 ≥ {min_count} 笔 或 金额 ≥ {amount_hkd} HKD',
                'config' => ['window_hours' => 24, 'min_count' => 5, 'amount_hkd' => 100000],
            ],
            [
                'rule_id' => 'R029', 'category' => 'amount', 'name' => '月累计交易金额',
                'measure' => '加入观察', 'enabled' => true,
                'description' => '月累计金额超平台阈值；识别异常交易规模',
                'content_template' => '同商户 {window_days} 天内累计交易额 ≥ {amount_usd} USD 或 ≥ {amount_hkd} HKD',
                'config' => ['window_days' => 30, 'amount_usd' => 500000, 'amount_hkd' => 2000000],
            ],
            [
                'rule_id' => 'R014', 'category' => 'amount', 'name' => '新商户爬坡期监控',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '新商户爬坡期平台默认阈值（Ramp-up），与入网时长联动',
                'content_template' => '新商户（入网 < {onboard_days} 天）单日累计 > {daily_usd} USD 或 单笔 > {amount_usd} USD',
                'config' => ['onboard_days' => 90, 'daily_usd' => 50000, 'amount_usd' => 2000],
            ],
            [
                'rule_id' => 'R023', 'category' => 'amount', 'name' => '单笔异常大单',
                'measure' => '仅预警', 'enabled' => true,
                'description' => '单笔瞬时值偏离商户历史均额（异常大单）',
                'content_template' => '单笔金额 > 商户近 {window_days} 天均额 × {percent} %',
                'config' => ['window_days' => 30, 'percent' => 300],
            ],
            [
                'rule_id' => 'R032', 'category' => 'amount', 'name' => '订单金额超近期均值',
                'measure' => '仅预警', 'enabled' => true,
                'description' => '本笔金额远超近期常态（如近30天均额500，本笔3,000即达600%）',
                'content_template' => '当前订单金额 > 近 {window_days} 天平均金额的 {percent} %',
                'config' => ['window_days' => 30, 'percent' => 600],
            ],

            // —— 地理与制裁 ——
            [
                'rule_id' => 'R001', 'category' => 'geo_sanctions', 'name' => 'OFAC制裁国家命中',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '制裁合规；直接拒绝并记录审计日志',
                'content_template' => 'OFAC / 制裁名单国家（CU/IR/KP/SY/RU 等）发卡或 IP 来源',
                'config' => [],
            ],
            [
                'rule_id' => 'R015', 'category' => 'geo_sanctions', 'name' => '发卡国IP账单三国不一致',
                'measure' => '3DS强验', 'enabled' => true,
                'description' => '跨境欺诈高发特征',
                'content_template' => '发卡国 ≠ IP 国家 ≠ 账单国（三国不一致）',
                'config' => [],
            ],
            [
                'rule_id' => 'R016', 'category' => 'geo_sanctions', 'name' => '发卡国与IP不一致',
                'measure' => '仅预警', 'enabled' => true,
                'description' => '常见跨境购物；轻度预警。若发卡国、IP、账单国均不一致见 R015',
                'content_template' => '发卡国与 IP 国家不一致',
                'config' => [],
            ],
            [
                'rule_id' => 'R020', 'category' => 'geo_sanctions', 'name' => '高风险收货国家',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '可配置国家风险等级清单',
                'content_template' => '收货国家属于高风险名单（NG/UA/PK 等欺诈高发地区）',
                'config' => [],
            ],
            [
                'rule_id' => 'R021', 'category' => 'geo_sanctions', 'name' => '账单收货国不一致',
                'measure' => '仅预警', 'enabled' => true,
                'description' => 'Billing/Shipping Mismatch；礼品卡/数字商品高发',
                'content_template' => '账单地址与收货地址国家不一致 且 金额 > {amount_usd} USD',
                'config' => ['amount_usd' => 200],
            ],

            // —— 欺诈行为 ——
            [
                'rule_id' => 'R004', 'category' => 'fraud', 'name' => 'VPN/Proxy/Tor节点',
                'measure' => '3DS强验', 'enabled' => true,
                'description' => '匿名网络来源；卡测试与欺诈常用',
                'content_template' => '检测到 VPN / Proxy / Tor 节点 IP',
                'config' => [],
            ],
            [
                'rule_id' => 'R019', 'category' => 'fraud', 'name' => '一次性邮箱',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '如 tempmail、guerrillamail 等',
                'content_template' => '一次性 / 临时邮箱域名（Disposable Email）',
                'config' => [],
            ],
            [
                'rule_id' => 'R008', 'category' => 'fraud', 'name' => 'BIN级卡测试攻击',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => 'BIN 级卡测试攻击',
                'content_template' => '同一 BIN（前6位）在 {window_minutes} 分钟内 ≥ {min_count} 笔失败',
                'config' => ['window_minutes' => 30, 'min_count' => 10],
            ],
            [
                'rule_id' => 'R007', 'category' => 'fraud', 'name' => '微额卡测试交易',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '微额测试交易（Micro-charge）；与下方金额特征规则互补',
                'content_template' => '单笔金额 ≤ {amount_usd} USD 且同一 IP {window_minutes} 分钟内 ≥ {min_count} 笔',
                'config' => ['amount_usd' => 1, 'window_minutes' => 10, 'min_count' => 5],
            ],
            [
                'rule_id' => 'R030', 'category' => 'fraud', 'name' => '金额尾数特征',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '异常尾数模式（固定小数/整数），暗示卡测试或自动化攻击',
                'content_template' => '连续 {streak} 笔交易金额均以 {tail} 结尾',
                'config' => ['streak' => 5, 'tail' => '0.99'],
            ],
            [
                'rule_id' => 'R031', 'category' => 'fraud', 'name' => '金额分布离散度异常',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '金额高度集中、离散度极低；典型卡测试金额分布特征',
                'content_template' => '近 {sample_size} 笔交易金额均在 HKD {amount_min} - {amount_max} 区间内',
                'config' => ['sample_size' => 20, 'amount_min' => '9.90', 'amount_max' => '10.10'],
            ],
            [
                'rule_id' => 'R037', 'category' => 'card_velocity', 'name' => '连续相同金额下单',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '连续固定金额重复下单；脚本跑批或卡测试常见特征',
                'content_template' => '同一卡号/同商户 连续 {streak} 笔交易金额完全相同',
                'config' => ['streak' => 5],
            ],
            [
                'rule_id' => 'R009', 'category' => 'fraud', 'name' => 'Test-then-Buy模式',
                'measure' => '人工审核', 'enabled' => true,
                'description' => 'Test-then-Buy 欺诈模式',
                'content_template' => '先微额（≤ {micro_usd} USD）后大额（≥ {large_usd} USD）同一卡号 {window_minutes} 分钟内',
                'config' => ['micro_usd' => 5, 'large_usd' => 500, 'window_minutes' => 60],
            ],
            [
                'rule_id' => 'R024', 'category' => 'fraud', 'name' => '同一地址多卡号',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '地址关联多卡；重货/代下单风险',
                'content_template' => '同一收货地址 {window_hours} 小时内 ≥ {min_cards} 张不同卡号',
                'config' => ['window_hours' => 24, 'min_cards' => 5],
            ],

            // —— 商户监控 ——
            [
                'rule_id' => 'R039', 'category' => 'merchant', 'name' => '合规筛查强制命中',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => 'AML / 制裁名单 / PEP 命中；自动拒绝或挂起审核',
                'content_template' => '合规筛查命中 ≥ 2 条或制裁直接命中',
                'config' => [],
            ],
            [
                'rule_id' => 'R040', 'category' => 'merchant', 'name' => '拒付率超VAMP识别线',
                'measure' => '限制单笔额度', 'enabled' => true,
                'description' => '仅已入网商户复评；Mastercard ECP Excessive；触发观察 / 限制结算',
                'content_template' => '拒付率 > {threshold_pct} %（Visa VAMP 识别线）',
                'config' => ['threshold_pct' => 1.5],
            ],
            [
                'rule_id' => 'R041', 'category' => 'merchant', 'name' => '拒付率超VAMP预警线',
                'measure' => '加入观察', 'enabled' => true,
                'description' => '未达识别线时强制提升至中风险；加入观察名单',
                'content_template' => '拒付率 > {threshold_pct} %（Visa VAMP 预警线）',
                'config' => ['threshold_pct' => 0.9],
            ],
            [
                'rule_id' => 'R042', 'category' => 'merchant', 'name' => '欺诈率超VFMP监控线',
                'measure' => '调单', 'enabled' => true,
                'description' => '仅已入网商户复评；人工审核 / 调单',
                'content_template' => '欺诈率 > {threshold_pct} %（VFMP / EFM）',
                'config' => ['threshold_pct' => 0.5],
            ],
            [
                'rule_id' => 'R026', 'category' => 'merchant', 'name' => '商户异常放量',
                'measure' => '暂停收单', 'enabled' => true,
                'description' => 'Account Takeover / 商户账户被盗',
                'content_template' => '商户当日交易量 ≥ 近30日均值 × {percent} %（异常放量）',
                'config' => ['percent' => 500],
            ],
            [
                'rule_id' => 'R025', 'category' => 'merchant', 'name' => '网站不在白名单',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '防钓鱼站 / 未授权接入（白名单校验，非黑名单类型）',
                'content_template' => '交易网站 URL 不在商户报备域名白名单内',
                'config' => [],
            ],
            [
                'rule_id' => 'R047', 'category' => 'merchant', 'name' => '异常退款率',
                'measure' => '仅预警', 'enabled' => false,
                'description' => '异常退款；洗钱或虚假交易信号',
                'content_template' => '退款率 > {refund_pct} % 且 交易笔数 ≥ {min_count}（滚动30天）',
                'config' => ['refund_pct' => 20, 'min_count' => 100],
            ],
            [
                'rule_id' => 'R048', 'category' => 'merchant', 'name' => '高风险MCC限额',
                'measure' => '人工审核', 'enabled' => true,
                'description' => '按商户类别码（MCC）差异化限额',
                'content_template' => '高风险 MCC（5967/5999/7273 等）单笔 > {amount_usd} USD',
                'config' => ['amount_usd' => 1000],
            ],

            // —— 黑名单 ——
            [
                'rule_id' => 'R027', 'category' => 'blacklist', 'name' => '黑名单IP命中',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '类型「IP」；交易来源 IP 命中「黑名单管理」生效条目',
                'content_template' => 'IP 命中黑名单',
                'config' => [],
            ],
            [
                'rule_id' => 'R038', 'category' => 'blacklist', 'name' => '黑名单邮箱命中',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '类型「邮箱」；下单邮箱命中黑名单生效条目',
                'content_template' => '邮箱命中黑名单',
                'config' => [],
            ],
            [
                'rule_id' => 'R002', 'category' => 'blacklist', 'name' => '黑名单卡号命中',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '类型「卡号」；卡号或 BIN 命中内部黑名单或卡组 negative file',
                'content_template' => '卡号命中黑名单 / negative file',
                'config' => [],
            ],
            [
                'rule_id' => 'R018', 'category' => 'blacklist', 'name' => '黑名单国家命中',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '类型「国家」；发卡国 / IP国家 / 账单国 / 收货国命中黑名单国家',
                'content_template' => '国家命中黑名单',
                'config' => [],
            ],
            [
                'rule_id' => 'R043', 'category' => 'blacklist', 'name' => '黑名单网站命中',
                'measure' => '拒绝交易', 'enabled' => true,
                'description' => '类型「网站」；交易网站域名命中黑名单生效条目',
                'content_template' => '网站命中黑名单',
                'config' => [],
            ],
        ];
    }
}
