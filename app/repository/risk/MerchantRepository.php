<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\Merchant;
use think\db\exception\DbException;
use think\Paginator;

class MerchantRepository
{
    /**
     * @param array{
     *   merchant_id?: string,
     *   name?: string,
     *   review_status?: string,
     *   trading_status?: string,
     *   merchant_ids?: list<string>
     * } $filters
     *
     * @throws DbException
     */
    public function search(array $filters, int $page, int $pageSize): Paginator
    {
        $query = Merchant::order('merchant_id', 'desc');

        if (isset($filters['merchant_ids'])) {
            $ids = array_values(array_filter(array_map('strval', $filters['merchant_ids'])));
            if ($ids === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('merchant_id', $ids);
            }
        }

        if (isset($filters['merchant_id']) && $filters['merchant_id'] !== '') {
            $query->whereLike('merchant_id', '%' . $filters['merchant_id'] . '%');
        }

        if (isset($filters['name']) && $filters['name'] !== '') {
            $query->whereLike('name', '%' . $filters['name'] . '%');
        }

        if (isset($filters['review_status']) && $filters['review_status'] !== '') {
            $query->where('review_status', (string) $filters['review_status']);
        }

        if (isset($filters['trading_status']) && $filters['trading_status'] !== '') {
            $query->where('status', (string) $filters['trading_status']);
        }

        return $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);
    }

    /**
     * @throws DbException
     */
    public function findByMerchantId(string $merchantId): ?Merchant
    {
        if ($merchantId === '') {
            return null;
        }

        $model = Merchant::where('merchant_id', $merchantId)->find();

        return $model instanceof Merchant ? $model : null;
    }

    public function countAll(): int
    {
        return (int) Merchant::count();
    }

    /**
     * @return array{normal: int, suspended: int, watch: int, not_opened: int, restricted: int}
     */
    public function countByStatus(): array
    {
        $rows = Merchant::field('status')
            ->fieldRaw('COUNT(*) AS cnt')
            ->group('status')
            ->select()
            ->toArray();

        $out = [
            'normal'     => 0,
            'suspended'  => 0,
            'watch'      => 0,
            'not_opened' => 0,
            'restricted' => 0,
        ];

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            if (isset($out[$status])) {
                $out[$status] = (int) ($row['cnt'] ?? 0);
            }
        }

        return $out;
    }

    /**
     * @param list<string|int> $merchantIds
     * @return array<string, string> merchant_id => name
     */
    public function mapNamesByIds(array $merchantIds): array
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

        $rows = Merchant::whereIn('merchant_id', $ids)
            ->field(['merchant_id', 'name'])
            ->select()
            ->toArray();

        $map = [];
        foreach ($rows as $row) {
            $mid = (string) ($row['merchant_id'] ?? '');
            if ($mid === '') {
                continue;
            }
            $map[$mid] = trim((string) ($row['name'] ?? ''));
        }

        return $map;
    }

    /**
     * 幂等写入：仅当 source_version >= 已有版本时更新。
     *
     * @param array<string, mixed> $data
     * @return array{model: Merchant, skipped: bool, created: bool}
     *
     * @throws DbException
     */
    public function upsert(array $data): array
    {
        $merchantId = (string) $data['merchant_id'];
        $version    = (int) $data['source_version'];
        $now        = date('Y-m-d H:i:s');

        $existing = $this->findByMerchantId($merchantId);
        if ($existing !== null && (int) $existing->source_version > $version) {
            return ['model' => $existing, 'skipped' => true, 'created' => false];
        }

        $payload = [
            'merchant_id'     => $merchantId,
            'name'            => (string) $data['name'],
            'status'          => (string) $data['status'],
            'industry'        => $data['industry'] ?? null,
            'country'         => $data['country'] ?? null,
            'register_at'     => $data['register_at'] ?? null,
            'onboard_at'      => $data['onboard_at'] ?? null,
            'website'         => $data['website'] ?? null,
            'email'           => $data['email'] ?? null,
            'mobile'          => $data['mobile'] ?? null,
            'address'         => $data['address'] ?? null,
            'website_status'  => $data['website_status'] ?? null,
            'compliance_hits' => $data['compliance_hits'] ?? null,
            'review_status'   => $data['review_status'] ?? null,
            'source_version'  => $version,
            'extra'           => $data['extra'] ?? null,
            'synced_at'       => $now,
        ];

        if ($existing === null) {
            $model = Merchant::create($payload);

            return ['model' => $model, 'skipped' => false, 'created' => true];
        }

        $existing->save($payload);

        return ['model' => $existing, 'skipped' => false, 'created' => false];
    }
}
