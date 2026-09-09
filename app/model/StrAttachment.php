<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * STR/LTR 报送附件（物理表 risk_str_attachment）
 *
 * @property int    $id
 * @property int    $str_report_id
 * @property string $file_name
 * @property int    $file_size
 * @property string $storage_path
 * @property string $file_type
 * @property int    $is_auto
 * @property string $uploaded_at
 * @property string $created_at
 * @property string $updated_at
 */
class StrAttachment extends Model
{
    protected $name = 'str_attachment';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'str_report_id' => 'integer',
        'file_size'     => 'integer',
        'is_auto'       => 'boolean',
    ];

    public const TYPE_XML  = 'xml';
    public const TYPE_PDF  = 'pdf';
    public const TYPE_ZIP  = 'zip';
    public const TYPE_XLSX = 'xlsx';

    /** @var list<string> */
    public const FILE_TYPES = [
        self::TYPE_XML,
        self::TYPE_PDF,
        self::TYPE_ZIP,
        self::TYPE_XLSX,
    ];

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = [
        'xml', 'pdf', 'zip', 'xlsx',
    ];

    public const MAX_BYTES = 10 * 1024 * 1024;

    public static function normalizeFileType(string $ext): string
    {
        $e = strtolower(ltrim($ext, '.'));
        if (in_array($e, self::FILE_TYPES, true)) {
            return $e;
        }

        return self::TYPE_PDF;
    }
}
