<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class Onboarding extends AdminBase
{
    protected string $menuKey = 'onboarding';

    protected string $pageTitle = '商户入网';

    public function index(): View
    {
        return $this->renderPage();
    }
}
