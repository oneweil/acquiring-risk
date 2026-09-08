<?php

declare(strict_types=1);

namespace database\factories;

/**
 * 商户评估演示数据（对真实 doopsun merchantId 生成；无商户则空）
 */
class MerchantAssessmentFactory
{
    /**
     * @param list<string|int> $merchantIds
     * @return list<array<string, mixed>>
     */
    public function demoRows(array $merchantIds): array
    {
        $now  = date('Y-m-d H:i:s');
        $rows = [];
        $templates = $this->templates();

        foreach (array_values($merchantIds) as $i => $merchantId) {
            $tpl = $templates[$i % count($templates)];
            $rows[] = [
                'merchant_id'      => (string) $merchantId,
                'risk_score'       => $tpl['risk_score'],
                'risk_level'       => $tpl['risk_level'],
                'assess_type'      => $tpl['assess_type'],
                'website_status'   => $tpl['website_status'],
                'compliance_hits'  => $tpl['compliance_hits'],
                'assess_details'   => json_encode($tpl['assess_details'], JSON_UNESCAPED_UNICODE),
                'assessed_at'      => $tpl['assessed_at'],
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function templates(): array
    {
        return [
            [
                'risk_score' => 28, 'risk_level' => 'low', 'assess_type' => 'periodic',
                'website_status' => 'compliant', 'compliance_hits' => 0,
                'assessed_at' => '2026-06-22 08:00:00',
                'assess_details' => $this->details([
                    ['行业风险', 17, 55, '行业 = 跨境电商'],
                    ['拒付率', 19, 20, '滚动30天拒付率 ≤ 0.5 %'],
                    ['欺诈率', 17, 20, '滚动30天欺诈率 ≤ 0.2 %'],
                    ['退款率', 10, 20, '滚动30天退款率 ≤ 5 %'],
                    ['网站合规', 10, 15, '网站能访问'],
                    ['合规筛查', 7, 10, '筛查命中 0 条'],
                    ['注册时长', 8, 15, '注册时长 ≥ 365 天'],
                    ['注册地风险', 8, 25, '注册国家 ∈ US,GB,DE,SG'],
                    ['交易放量异常', 4, 15, '当日交易量 ≤ 近30日均值 × 200 %'],
                ]),
            ],
            [
                'risk_score' => 35, 'risk_level' => 'low', 'assess_type' => 'periodic',
                'website_status' => 'compliant', 'compliance_hits' => 0,
                'assessed_at' => '2026-06-22 08:00:00',
                'assess_details' => $this->details([
                    ['行业风险', 17, 35, '行业 = 软件SaaS'],
                    ['拒付率', 19, 20, '滚动30天拒付率 ≤ 0.5 %'],
                    ['欺诈率', 17, 20, '滚动30天欺诈率 ≤ 0.2 %'],
                    ['退款率', 10, 20, '滚动30天退款率 ≤ 5 %'],
                    ['网站合规', 10, 15, '网站能访问'],
                    ['合规筛查', 7, 10, '筛查命中 0 条'],
                    ['注册时长', 8, 30, '注册时长 ≥ 180 天'],
                    ['注册地风险', 8, 25, '注册国家 ∈ US,GB,DE,SG'],
                    ['交易放量异常', 4, 15, '当日交易量 ≤ 近30日均值 × 200 %'],
                ]),
            ],
            [
                'risk_score' => 62, 'risk_level' => 'mid', 'assess_type' => 'periodic',
                'website_status' => 'compliant', 'compliance_hits' => 0,
                'assessed_at' => '2026-06-22 08:00:00',
                'assess_details' => $this->details([
                    ['行业风险', 17, 85, '行业 = 虚拟商品'],
                    ['拒付率', 19, 40, '滚动30天拒付率 ≤ 0.9 %'],
                    ['欺诈率', 17, 50, '滚动30天欺诈率 ≤ 0.5 %'],
                    ['退款率', 10, 50, '滚动30天退款率 ≤ 15 %'],
                    ['网站合规', 10, 15, '网站能访问'],
                    ['合规筛查', 7, 10, '筛查命中 0 条'],
                    ['注册时长', 8, 50, '注册时长 ≥ 90 天'],
                    ['注册地风险', 8, 45, '注册国家 ∈ HK'],
                    ['交易放量异常', 4, 50, '当日交易量 > 近30日均值 × 200 % 且 ≤ × 500 %'],
                ]),
            ],
            [
                'risk_score' => 78, 'risk_level' => 'high', 'assess_type' => 'periodic',
                'website_status' => 'mismatch', 'compliance_hits' => 1,
                'assessed_at' => '2026-06-22 08:00:00',
                'assess_details' => $this->details([
                    ['行业风险', 17, 30, '行业 = 实体零售'],
                    ['拒付率', 19, 65, '滚动30天拒付率 ≤ 1.5 %'],
                    ['欺诈率', 17, 85, '滚动30天欺诈率 > 0.5 %'],
                    ['退款率', 10, 85, '滚动30天退款率 > 15 %'],
                    ['网站合规', 10, 80, '网站不能访问'],
                    ['合规筛查', 7, 55, '筛查命中 ≥ 1 条'],
                    ['注册时长', 8, 50, '注册时长 ≥ 90 天'],
                    ['注册地风险', 8, 25, '注册国家 ∈ US,GB,DE,SG'],
                    ['交易放量异常', 4, 85, '当日交易量 > 近30日均值 × 500 %'],
                ]),
            ],
            [
                'risk_score' => 81, 'risk_level' => 'high', 'assess_type' => 'onboarding',
                'website_status' => 'mismatch', 'compliance_hits' => 2,
                'assessed_at' => '2026-06-18 16:18:00',
                'assess_details' => $this->details([
                    ['行业风险', 34, 85, '行业 = 虚拟商品'],
                    ['网站合规', 20, 80, '网站不能访问'],
                    ['合规筛查', 14, 90, '筛查命中 ≥ 2 条或制裁直接命中'],
                    ['注册时长', 16, 75, '注册时长 < 90 天（新注册）'],
                    ['注册地风险', 16, 45, '注册国家 ∈ HK'],
                ]),
            ],
        ];
    }

    /**
     * @param list<array{0:string,1:int,2:int,3:string}> $rows
     * @return list<array{name:string,weight:int,raw:int,weighted:int,rule_content:string}>
     */
    private function details(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            [$name, $weight, $raw, $rule] = $row;
            $out[] = [
                'name'         => $name,
                'weight'       => $weight,
                'raw'          => $raw,
                'weighted'     => (int) round($raw * $weight / 100),
                'rule_content' => $rule,
            ];
        }

        return $out;
    }
}
