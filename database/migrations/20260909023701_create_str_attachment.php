<?php

declare(strict_types=1);

use think\migration\Migrator;

/**
 * 物理表名：risk_str_attachment（连接前缀 risk_ + str_attachment）
 */
class CreateStrAttachment extends Migrator
{
    public function up(): void
    {
        $table = $this->table('str_attachment', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'collation'   => 'utf8mb4_general_ci',
            'comment'     => 'STR/LTR 报送附件',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'identity' => true,
                'signed'   => false,
                'comment'  => '主键',
            ])
            ->addColumn('str_report_id', 'biginteger', [
                'signed'  => false,
                'null'    => false,
                'comment'  => '关联 str_report.id',
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
                'comment'  => '相对 public 盘路径，如 str/20260909/xxx.xml',
            ])
            ->addColumn('file_type', 'string', [
                'limit'   => 16,
                'null'    => false,
                'default' => 'xml',
                'comment'  => '文件类型：xml / pdf / zip / xlsx',
            ])
            ->addColumn('is_auto', 'boolean', [
                'null'    => false,
                'default' => 0,
                'comment'  => '是否系统自动生成附件：1是 0否',
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
            ->addIndex(['str_report_id'], ['name' => 'idx_str_report_id'])
            ->create();
    }

    public function down(): void
    {
        $this->table('str_attachment')->drop()->save();
    }
}
