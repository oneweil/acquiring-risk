<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class User extends AdminBase
{
    protected string $menuKey = 'user';

    protected string $pageTitle = '用户管理';

    public function index(): View
    {
        return $this->renderPage();
    }
}
