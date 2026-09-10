<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\MerchantAssessment;
use think\db\exception\DbException;

class MerchantAssessmentRepository
{
    /**
     * @param list<string> $merchantIds
     * @return array<string, MerchantAssessment> keyed by merchant_id
     *
     * @throws DbException
     */
    public function mapByMerchantIds(array $merchantIds): array
    {
        $ids = [];
        foreach ($merchantIds as $id) {
            $id = trim((string) $id);
            if ($id !== '') {
                $ids[] = $id;
            }
        }
        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            return [];
        }

        $rows = MerchantAssessment::whereIn('merchant_id', $ids)->select();
        $map  = [];
        foreach ($rows as $row) {
            $map[(string) $row->merchant_id] = $row;
        }

        return $map;
    }

    /**
     * @throws DbException
     */
    public function findByMerchantId(string $merchantId): ?MerchantAssessment
    {
        if ($merchantId === '') {
            return null;
        }

        $model = MerchantAssessment::where('merchant_id', $merchantId)->find();

        return $model instanceof MerchantAssessment ? $model : null;
    }

    /**
     * @return list<string>
     *
     * @throws DbException
     */
    public function merchantIdsByRiskLevel(string $riskLevel): array
    {
        if ($riskLevel === '') {
            return [];
        }

        return MerchantAssessment::where('risk_level', $riskLevel)
            ->column('merchant_id');
    }

    /**
     * @return array{low: int, mid: int, high: int}
     *
     * @throws DbException
     */
    public function countByRiskLevel(): array
    {
        $rows = MerchantAssessment::field('risk_level')
            ->fieldRaw('COUNT(*) AS cnt')
            ->group('risk_level')
            ->select()
            ->toArray();

        $out = ['low' => 0, 'mid' => 0, 'high' => 0];
        foreach ($rows as $row) {
            $level = (string) ($row['risk_level'] ?? '');
            if (isset($out[$level])) {
                $out[$level] = (int) ($row['cnt'] ?? 0);
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function upsertByMerchantId(array $data): MerchantAssessment
    {
        $merchantId = (string) $data['merchant_id'];
        $existing   = $this->findByMerchantId($merchantId);
        if ($existing === null) {
            return MerchantAssessment::create($data);
        }

        $existing->save($data);

        return $existing;
    }
}
