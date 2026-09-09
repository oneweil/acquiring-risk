<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_alert_attachment（连接前缀 risk_ + alert_attachment）
 */
class CreateAlertAttachment extends Migrator
{
    public function up(): void
    {
        $table = $this->table('alert_attachment', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => '交易预警调单附件',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('alert_id', 'biginteger', [
                'signed'  => false,
                'null'    => false,
                'comment'  => '关联 alert.id',
            ])
            ->addColumn('file_name', 'string', [
                'limit'   => 255,
                'null'    => false,
                'comment'  => '原始文件名',
            ])
            ->addColumn('file_size', 'integer', [
                'signed'  => false,
                'null'    => false,
                'default' => 0,
                'comment'  => '文件大小（字节）',
            ])
            ->addColumn('storage_path', 'string', [
                'limit'   => 512,
                'null'    => false,
                'comment'  => '相对 public 盘路径，如 alert/20260909/xxx.pdf',
            ])
            ->addColumn('file_type', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'pdf',
                'comment'  => '文件类型：pdf / jpg / png / doc / docx / zip',
            ])
            ->addColumn('uploaded_at', 'datetime', [
                'null'    => false,
                'comment'  => '上传时间',
            ])
            ->addColumn('created_at', 'datetime', [
                'null'    => false,
                'comment'  => '创建时间',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null'    => false,
                'comment'  => '最后更新时间',
            ])
            ->addIndex(['alert_id'], ['name' => 'idx_alert_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('alert_attachment')->drop()->save();
    }
}
