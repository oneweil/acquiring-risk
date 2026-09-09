<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\AlertAttachment;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class AlertAttachmentRepository
{
    /**
     * @return Collection<int, AlertAttachment>
     *
     * @throws DbException
     */
    public function listByAlertId(int $alertId): Collection
    {
        return AlertAttachment::where('alert_id', $alertId)
            ->order('id', 'asc')
            ->select();
    }

    /**
     * @param list<int> $alertIds
     * @return array<int, int> alert_id => count
     *
     * @throws DbException
     */
    public function countsByAlertIds(array $alertIds): array
    {
        if ($alertIds === []) {
            return [];
        }

        $rows = AlertAttachment::whereIn('alert_id', $alertIds)
            ->field('alert_id, COUNT(*) AS cnt')
            ->group('alert_id')
            ->select();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->getAttr('alert_id')] = (int) $row->getAttr('cnt');
        }

        return $map;
    }

    /**
     * @throws DbException
     */
    public function countByAlertId(int $alertId): int
    {
        return (int) AlertAttachment::where('alert_id', $alertId)->count();
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?AlertAttachment
    {
        return AlertAttachment::find($id);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): AlertAttachment
    {
        return AlertAttachment::create($data);
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function delete(int $id): ?AlertAttachment
    {
        $model = AlertAttachment::find($id);
        if ($model === null) {
            return null;
        }

        $model->delete();

        return $model;
    }
}
