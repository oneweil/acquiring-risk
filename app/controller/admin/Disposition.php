<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class Disposition extends AdminBase
{
    protected string $menuKey = 'disposition';

    protected string $pageTitle = '处置策略';

    public function index(): View
    {
        return $this->renderPage();
    }
}
