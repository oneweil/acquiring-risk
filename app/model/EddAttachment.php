<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * EDD 尽调附件（物理表 risk_edd_attachment）
 *
 * @property int    $id
 * @property int    $edd_case_id
 * @property string $checklist_key
 * @property string $file_name
 * @property int    $file_size
 * @property string $storage_path
 * @property string $file_type
 * @property string $uploaded_at
 * @property string $created_at
 * @property string $updated_at
 */
class EddAttachment extends Model
{
    protected $name = 'edd_attachment';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'edd_case_id' => 'integer',
        'file_size'   => 'integer',
    ];

    public const TYPE_PDF = 'pdf';
    public const TYPE_IMG = 'img';
    public const TYPE_DOC = 'doc';
    public const TYPE_ZIP = 'zip';

    /** @var list<string> */
    public const FILE_TYPES = [
        self::TYPE_PDF,
        self::TYPE_IMG,
        self::TYPE_DOC,
        self::TYPE_ZIP,
    ];

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'zip', 'xlsx',
    ];

    public static function normalizeFileType(string $ext): string
    {
        $e = strtolower(ltrim($ext, '.'));
        if (in_array($e, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp'], true)) {
            return self::TYPE_IMG;
        }
        if (in_array($e, ['doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'], true)) {
            return self::TYPE_DOC;
        }
        if (in_array($e, ['zip', 'rar', '7z'], true)) {
            return self::TYPE_ZIP;
        }

        return self::TYPE_PDF;
    }
}
