<?php

declare(strict_types=1);

namespace app\controller\api;

use think\Response;

/**
 * 风控评估 API 占位（后续接规则引擎）
 */
class Risk extends ApiBase
{
    /**
     * POST /api/v1/risk/evaluate
     */
    public function evaluate(): Response
    {
        return $this->success([
            'decision'  => 'pass',
            'score'     => 0,
            'hit_rules' => [],
            'message'   => 'API skeleton ready, risk engine not implemented yet',
        ]);
    }
}
