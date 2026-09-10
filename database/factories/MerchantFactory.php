<?php

declare(strict_types=1);

namespace database\factories;

/**
 * 商户投影演示数据
 */
class MerchantFactory
{
    /**
     * @return list<array<string, mixed>>
     */
    public function demoRows(int $count = 12): array
    {
        $now  = date('Y-m-d H:i:s');
        $rows = [];
        $templates = $this->templates();

        for ($i = 0; $i < $count; $i++) {
            $tpl = $templates[$i % count($templates)];
            $mid = sprintf('M%06d', 100001 + $i);
            $rows[] = [
                'merchant_id'     => $mid,
                'name'            => $tpl['name'],
                'status'          => $tpl['status'],
                'industry'        => $tpl['industry'],
                'country'         => $tpl['country'],
                'register_at'     => $tpl['register_at'],
                'onboard_at'      => $tpl['onboard_at'],
                'website'         => $tpl['website'],
                'email'           => $tpl['email'],
                'mobile'          => null,
                'address'         => null,
                'website_status'  => $tpl['website_status'],
                'compliance_hits' => $tpl['compliance_hits'],
                'review_status'   => $tpl['review_status'],
                'source_version'  => time() - $i,
                'extra'           => null,
                'synced_at'       => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
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
                'name' => 'GlobalShop Inc.', 'status' => 'normal', 'industry' => '跨境电商', 'country' => 'US',
                'register_at' => '2020-06-12', 'onboard_at' => '2024-03-15',
                'website' => 'https://globalshop.example', 'email' => 'ops@globalshop.example',
                'website_status' => 'compliant', 'compliance_hits' => 0, 'review_status' => 'approved',
            ],
            [
                'name' => 'SoftCloud SaaS', 'status' => 'normal', 'industry' => '软件SaaS', 'country' => 'SG',
                'register_at' => '2019-01-08', 'onboard_at' => '2023-08-01',
                'website' => 'https://softcloud.example', 'email' => 'risk@softcloud.example',
                'website_status' => 'compliant', 'compliance_hits' => 0, 'review_status' => 'approved',
            ],
            [
                'name' => 'GameCard Pro', 'status' => 'watch', 'industry' => '虚拟商品', 'country' => 'HK',
                'register_at' => '2025-08-10', 'onboard_at' => '2025-11-20',
                'website' => 'https://gamecard.example', 'email' => 'ops@gamecard.example',
                'website_status' => 'compliant', 'compliance_hits' => 0, 'review_status' => 'approved',
            ],
            [
                'name' => 'EuroFashion Shop', 'status' => 'normal', 'industry' => '服饰零售', 'country' => 'DE',
                'register_at' => '2018-03-22', 'onboard_at' => '2022-05-10',
                'website' => 'https://eurofashion.example', 'email' => 'contact@eurofashion.example',
                'website_status' => 'compliant', 'compliance_hits' => 0, 'review_status' => 'approved',
            ],
            [
                'name' => 'QuickBuy Store', 'status' => 'restricted', 'industry' => '实体零售', 'country' => 'DE',
                'register_at' => '2025-11-20', 'onboard_at' => '2026-02-14',
                'website' => 'https://quickbuy.example', 'email' => 'support@quickbuy.example',
                'website_status' => 'mismatch', 'compliance_hits' => 1, 'review_status' => 'approved',
            ],
            [
                'name' => 'BlockedMart', 'status' => 'suspended', 'industry' => '跨境电商', 'country' => 'NG',
                'register_at' => '2026-01-05', 'onboard_at' => '2026-03-01',
                'website' => 'https://blockedmart.example', 'email' => 'a@blockedmart.example',
                'website_status' => 'mismatch', 'compliance_hits' => 2, 'review_status' => 'approved',
            ],
            [
                'name' => 'RejectCo Ltd', 'status' => 'not_opened', 'industry' => '博彩', 'country' => 'CY',
                'register_at' => '2026-04-01', 'onboard_at' => null,
                'website' => 'https://rejectco.example', 'email' => 'info@rejectco.example',
                'website_status' => 'unverified', 'compliance_hits' => 3, 'review_status' => 'rejected',
            ],
            [
                'name' => 'PendingOpen LLC', 'status' => 'not_opened', 'industry' => '跨境电商', 'country' => 'US',
                'register_at' => '2026-05-12', 'onboard_at' => null,
                'website' => 'https://pendingopen.example', 'email' => 'hello@pendingopen.example',
                'website_status' => 'compliant', 'compliance_hits' => 0, 'review_status' => 'approved',
            ],
            [
                'name' => 'TravelPay HK', 'status' => 'normal', 'industry' => '旅游出行', 'country' => 'HK',
                'register_at' => '2021-09-15', 'onboard_at' => '2024-01-20',
                'website' => 'https://travelpay.example', 'email' => 'ops@travelpay.example',
                'website_status' => 'compliant', 'compliance_hits' => 0, 'review_status' => 'approved',
            ],
            [
                'name' => 'DigitalGoods CN', 'status' => 'watch', 'industry' => '虚拟商品', 'country' => 'CN',
                'register_at' => '2024-12-01', 'onboard_at' => '2025-06-18',
                'website' => 'https://digitalgoods.example', 'email' => 'risk@digitalgoods.example',
                'website_status' => 'compliant', 'compliance_hits' => 0, 'review_status' => 'approved',
            ],
            [
                'name' => 'AdultStream', 'status' => 'restricted', 'industry' => '成人', 'country' => 'NL',
                'register_at' => '2023-02-11', 'onboard_at' => '2025-09-01',
                'website' => 'https://adultstream.example', 'email' => 'c@adultstream.example',
                'website_status' => 'mismatch', 'compliance_hits' => 1, 'review_status' => 'approved',
            ],
            [
                'name' => 'CryptoGate', 'status' => 'suspended', 'industry' => '加密货币', 'country' => 'EE',
                'register_at' => '2025-07-07', 'onboard_at' => '2025-12-12',
                'website' => 'https://cryptogate.example', 'email' => 'ops@cryptogate.example',
                'website_status' => 'unverified', 'compliance_hits' => 2, 'review_status' => 'approved',
            ],
        ];
    }
}
