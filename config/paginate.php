<?php

declare(strict_types=1);

// 分页配置（与 Db/Model::paginate 一致）
return [
    // 分页驱动：Bootstrap 5（AdminLTE 4）
    'type'      => \app\paginator\Bootstrap5::class,
    // 分页变量名
    'var_page'  => 'page',
    // 默认每页数量
    'list_rows' => 10,
];
