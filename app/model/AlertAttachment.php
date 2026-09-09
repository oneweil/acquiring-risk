<?php

declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 交易预警调单附件（物理表 risk_alert_attachment）
 *
 * @property int    $id
 * @property int    $alert_id
 * @property string $file_name
 * @property int    $file_size
 * @property string $storage_path
 * @property string $file_type
 * @property string $uploaded_at
 * @property string $created_at
 * @property string $updated_at
 */
class AlertAttachment extends Model
{
    protected $name = 'alert_attachment';

    protected $autoWriteTimestamp = 'datetime';

    protected $createTime = 'created_at';

    protected $updateTime = 'updated_at';

    /** @var array<string, string> */
    protected $type = [
        'alert_id'  => 'integer',
        'file_size' => 'integer',
    ];

    public const TYPE_PDF  = 'pdf';
    public const TYPE_JPG  = 'jpg';
    public const TYPE_JPEG = 'jpeg';
    public const TYPE_PNG  = 'png';
    public const TYPE_DOC  = 'doc';
    public const TYPE_DOCX = 'docx';
    public const TYPE_ZIP  = 'zip';

    /** @var list<string> */
    public const FILE_TYPES = [
        self::TYPE_PDF,
        self::TYPE_JPG,
        self::TYPE_JPEG,
        self::TYPE_PNG,
        self::TYPE_DOC,
        self::TYPE_DOCX,
        self::TYPE_ZIP,
    ];

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'zip',
    ];

    public const MAX_BYTES = 10 * 1024 * 1024;

    public static function normalizeFileType(string $ext): string
    {
        $e = strtolower(ltrim($ext, '.'));
        if ($e === 'jpeg') {
            return self::TYPE_JPG;
        }
        if (in_array($e, self::FILE_TYPES, true)) {
            return $e;
        }

        return self::TYPE_PDF;
    }
}
