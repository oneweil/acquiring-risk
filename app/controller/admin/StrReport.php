<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class StrReport extends AdminBase
{
    protected string $menuKey = 'str_report';

    protected string $pageTitle = 'STR 报送';

    public function index(): View
    {
        return $this->renderPage();
    }
}
