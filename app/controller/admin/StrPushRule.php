<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class StrPushRule extends AdminBase
{
    protected string $menuKey = 'str_push_rule';

    protected string $pageTitle = 'STR 推送规则';

    public function index(): View
    {
        return $this->renderPage();
    }
}
