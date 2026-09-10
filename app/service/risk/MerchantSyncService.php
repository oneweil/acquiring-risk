<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\Merchant;
use app\model\MerchantAssessment;
use app\repository\risk\MerchantAssessmentRepository;
use app\repository\risk\MerchantRepository;
use think\exception\ValidateException;

/**
 * 主站商户投影写入与入网评估
 *
 * - 新建投影：自动跑入网型评估
 * - 更新投影：默认不重评；assess=true 可强制重评
 */
class MerchantSyncService
{
    public function __construct(
        private readonly MerchantRepository $merchantRepo = new MerchantRepository(),
        private readonly MerchantAssessmentRepository $assessRepo = new MerchantAssessmentRepository(),
        private readonly MerchantAssessService $assessService = new MerchantAssessService(),
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function upsert(array $data): array
    {
        $forceAssess = array_key_exists('assess', $data)
            ? (bool) $data['assess']
            : false;
        unset($data['assess']);

        $result  = $this->merchantRepo->upsert($data);
        $model   = $result['model'];
        $skipped = $result['skipped'];
        $created = $result['created'];

        $assessment = null;
        if ($skipped) {
            $assessment = $this->assessRepo->findByMerchantId((string) $model->merchant_id);
        } elseif ($created || $forceAssess) {
            $assessment = $this->assessService->assessOnboarding($model);
        } else {
            $assessment = $this->assessRepo->findByMerchantId((string) $model->merchant_id);
        }

        return $this->toResponse($model, $assessment, $skipped, $created);
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    public function upsertBatch(array $items): array
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $out[] = $this->upsert($item);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $merchantId): array
    {
        $model = $this->merchantRepo->findByMerchantId($merchantId);
        if ($model === null) {
            throw new ValidateException('商户投影不存在');
        }
        $assessment = $this->assessRepo->findByMerchantId($merchantId);

        return $this->toResponse($model, $assessment, false, false);
    }

    /**
     * @return array<string, mixed>
     */
    private function toResponse(
        Merchant $model,
        ?MerchantAssessment $assessment,
        bool $skipped,
        bool $created,
    ): array {
        $data = [
            'merchant_id'     => (string) $model->merchant_id,
            'name'            => (string) $model->name,
            'status'          => (string) $model->status,
            'industry'        => $model->industry,
            'country'         => $model->country,
            'register_at'     => $model->register_at,
            'onboard_at'      => $model->onboard_at,
            'website_status'  => $model->website_status,
            'compliance_hits' => $model->compliance_hits !== null ? (int) $model->compliance_hits : null,
            'review_status'   => $model->review_status,
            'source_version'  => (int) $model->source_version,
            'synced_at'       => (string) $model->synced_at,
            'skipped'         => $skipped,
            'created'         => $created,
            'risk_score'      => null,
            'risk_level'      => null,
            'assess_type'     => null,
            'assessed_at'     => null,
        ];

        if ($assessment !== null) {
            $data['risk_score']  = (int) $assessment->risk_score;
            $data['risk_level']  = (string) $assessment->risk_level;
            $data['assess_type'] = (string) $assessment->assess_type;
            $data['assessed_at'] = (string) $assessment->assessed_at;
        }

        return $data;
    }
}
