<?php

declare(strict_types=1);

namespace app\repository\risk;

use app\model\StrAttachment;
use think\Collection;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

class StrAttachmentRepository
{
    /**
     * @return Collection<int, StrAttachment>
     *
     * @throws DbException
     */
    public function listByReportId(int $reportId): Collection
    {
        return StrAttachment::where('str_report_id', $reportId)
            ->order('id', 'asc')
            ->select();
    }

    /**
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function find(int $id): ?StrAttachment
    {
        return StrAttachment::find($id);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws DbException
     */
    public function create(array $data): StrAttachment
    {
        return StrAttachment::create($data);
    }
}
