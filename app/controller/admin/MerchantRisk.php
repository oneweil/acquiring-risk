<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class MerchantRisk extends AdminBase
{
    protected string $menuKey = 'merchant_risk';

    protected string $pageTitle = '商户评估规则';

    public function index(): View
    {
        return $this->renderPage();
    }
}
