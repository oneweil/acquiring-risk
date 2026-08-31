<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class Dashboard extends AdminBase
{
    protected string $menuKey = 'dashboard';

    protected string $pageTitle = '风控总览';

    public function index(): View
    {
        return view('/admin/dashboard/index', $this->layoutData());
    }
}
