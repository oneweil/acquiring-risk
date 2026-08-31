<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class Merchant extends AdminBase
{
    protected string $menuKey = 'merchant';

    protected string $pageTitle = '商户列表';

    public function index(): View
    {
        return $this->renderPage();
    }
}
