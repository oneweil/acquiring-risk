<?php

declare(strict_types=1);

namespace app\controller\api;

use app\model\Merchant;
use app\repository\risk\MerchantAssessmentRepository;
use app\repository\risk\MerchantRepository;
use think\Response;

/**
 * 风控评估 API（商户门禁已接投影；规则引擎后续接入）
 */
class Risk extends ApiBase
{
    /**
     * POST /api/v1/risk/evaluate
     */
    public function evaluate(): Response
    {
        $params     = $this->request->post();
        $merchantId = trim((string) ($params['merchant_id'] ?? ''));

        if ($merchantId === '') {
            return $this->fail('merchant_id 必填', 422, null, 422);
        }

        $merchant = (new MerchantRepository())->findByMerchantId($merchantId);
        if ($merchant === null) {
            return $this->success([
                'decision'  => 'decline',
                'score'     => 0,
                'hit_rules' => [
                    [
                        'rule_id' => 'MERCHANT_UNKNOWN',
                        'name'    => '未知商户',
                        'measure' => '拒绝交易',
                    ],
                ],
                'message'   => '商户投影不存在，请先 upsert',
            ]);
        }

        if ((string) $merchant->status === Merchant::STATUS_SUSPENDED) {
            return $this->success([
                'decision'  => 'decline',
                'score'     => 0,
                'hit_rules' => [
                    [
                        'rule_id' => 'MERCHANT_SUSPENDED',
                        'name'    => '商户已暂停收单',
                        'measure' => '拒绝交易',
                    ],
                ],
                'message'   => '商户收单状态为 suspended',
            ]);
        }

        $assessment = (new MerchantAssessmentRepository())->findByMerchantId($merchantId);
        $score      = $assessment !== null ? (int) $assessment->risk_score : 0;

        return $this->success([
            'decision'  => 'pass',
            'score'     => $score,
            'hit_rules' => [],
            'message'   => 'API skeleton ready, risk engine not implemented yet',
        ]);
    }
}
