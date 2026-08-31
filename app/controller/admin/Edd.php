<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class Edd extends AdminBase
{
    protected string $menuKey = 'edd';

    protected string $pageTitle = 'EDD 强化尽调';

    public function index(): View
    {
        return $this->renderPage();
    }
}
