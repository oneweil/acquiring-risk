<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class Role extends AdminBase
{
    protected string $menuKey = 'role';

    protected string $pageTitle = '角色权限';

    public function index(): View
    {
        return $this->renderPage();
    }
}
