<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_edd_attachment（连接前缀 risk_ + edd_attachment）
 */
class CreateEddAttachment extends Migrator
{
    public function up(): void
    {
        $table = $this->table('edd_attachment', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => 'EDD 尽调附件',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('edd_case_id', 'biginteger', [
                'signed'  => false,
                'null'    => false,
                'comment'  => '关联 edd_case.id',
            ])
            ->addColumn('checklist_key', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment'  => '清单项 key：ubo/source/…',
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
                'comment'  => '相对 public 盘路径',
            ])
            ->addColumn('file_type', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'pdf',
                'comment'  => 'pdf/img/doc/zip',
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
            ->addIndex(['edd_case_id', 'checklist_key'], ['name' => 'idx_case_key'])
            ->addForeignKey('edd_case_id', 'edd_case', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('edd_attachment')->drop()->save();
    }
}
