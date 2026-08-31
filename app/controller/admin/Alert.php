<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class Alert extends AdminBase
{
    protected string $menuKey = 'alert';

    protected string $pageTitle = '预警中心';

    public function index(): View
    {
        return $this->renderPage();
    }
}
