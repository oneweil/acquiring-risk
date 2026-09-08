<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\MerchantLevelPolicy;
use app\service\risk\MerchantRiskLevelAdminService;
use app\validate\MerchantLevelConfig as MerchantLevelConfigValidate;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class MerchantRiskLevel extends AdminBase
{
    protected string $menuKey = 'merchant_risk_level';

    protected string $pageTitle = '商户风险等级规则';

    public function index(): View
    {
        $config = (new MerchantRiskLevelAdminService())->getConfig();

        return $this->renderList('/admin/merchant_risk_level/index', [
            'review_cycle_options' => MerchantLevelPolicy::REVIEW_CYCLE_LABELS,
            'config'               => $config,
            'policies'             => $config['policies'] ?? [],
        ]);
    }

    public function save(): Json
    {
        $post = $this->request->post();
        if ($this->request->isJson()) {
            $decoded = json_decode($this->request->getContent() ?: '', true);
            if (is_array($decoded)) {
                $post = $decoded;
            }
        }

        try {
            validate(MerchantLevelConfigValidate::class)
                ->scene('save')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        try {
            $payload = (new MerchantRiskLevelAdminService())->saveConfig(
                MerchantLevelConfigValidate::toSaveData($post)
            );
        } catch (\Throwable $e) {
            return $this->fail('保存失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload, '保存成功');
    }

    public function reassess(): Json
    {
        return $this->fail('评估引擎未就绪', 501, null, 501);
    }
}
