<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class AuditLog extends AdminBase
{
    protected string $menuKey = 'audit_log';

    protected string $pageTitle = '日志记录';

    public function index(): View
    {
        return $this->renderPage();
    }
}
