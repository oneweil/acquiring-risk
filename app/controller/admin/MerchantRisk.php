<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\service\risk\MerchantRiskAdminService;
use app\validate\MerchantAssessConfig as MerchantAssessConfigValidate;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class MerchantRisk extends AdminBase
{
    protected string $menuKey = 'merchant_risk';

    protected string $pageTitle = '商户评估规则';

    public function index(): View
    {
        $config = (new MerchantRiskAdminService())->getConfig();

        return $this->renderList('/admin/merchant_risk/index', [
            'config_json' => json_encode(
                $config,
                JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            ),
        ]);
    }

    public function config(): Json
    {
        return $this->success((new MerchantRiskAdminService())->getConfig());
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
            validate(MerchantAssessConfigValidate::class)
                ->scene('save')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        try {
            $payload = (new MerchantRiskAdminService())->saveConfig(
                MerchantAssessConfigValidate::toSaveData($post)
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
