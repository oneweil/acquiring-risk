<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class Rule extends AdminBase
{
    protected string $menuKey = 'rule';

    protected string $pageTitle = '风控规则';

    public function index(): View
    {
        return $this->renderPage();
    }
}
