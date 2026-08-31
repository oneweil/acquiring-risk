<?php

declare(strict_types=1);

namespace app\controller\admin;

use think\response\View;

class MerchantRiskLevel extends AdminBase
{
    protected string $menuKey = 'merchant_risk_level';

    protected string $pageTitle = '商户风险等级规则';

    public function index(): View
    {
        return $this->renderPage();
    }
}
