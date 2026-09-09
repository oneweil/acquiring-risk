<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\EddAttachment;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class EddAttachmentRepository
{
    /**
     * @return Collection<int, EddAttachment>
     *
     * @throws DbException
     */
    public function listByCaseId(int $caseId): Collection
    {
        return EddAttachment::where('edd_case_id', $caseId)
            ->order('id', 'asc')
            ->select();
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?EddAttachment
    {
        return EddAttachment::find($id);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): EddAttachment
    {
        return EddAttachment::create($data);
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function delete(int $id): ?EddAttachment
    {
        $model = EddAttachment::find($id);
        if ($model === null) {
            return null;
        }

        $model->delete();

        return $model;
    }

    /**
     * 各 checklist_key 是否至少有一个附件
     *
     * @return array<string, int> key => count
     *
     * @throws DbException
     */
    public function countsByKey(int $caseId): array
    {
        $rows = EddAttachment::where('edd_case_id', $caseId)
            ->field('checklist_key, COUNT(*) AS cnt')
            ->group('checklist_key')
            ->select();

        $map = [];
        foreach ($rows as $row) {
            $map[(string) $row->getAttr('checklist_key')] = (int) $row->getAttr('cnt');
        }

        return $map;
    }
}
