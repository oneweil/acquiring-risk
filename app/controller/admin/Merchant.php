<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\model\MerchantAssessment;
use app\resource\MerchantResource;
use app\service\risk\MerchantAdminService;
use app\validate\Merchant as MerchantValidate;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class Merchant extends AdminBase
{
    protected string $menuKey = 'merchant';

    protected string $pageTitle = '商户列表';

    public function index(): View
    {
        return $this->renderList('/admin/merchant/index', [
            'filter_options' => [
                'risk_level'     => MerchantAssessment::RISK_LEVEL_LABELS,
                'review_status'  => MerchantResource::REVIEW_STATUS_LABELS,
                'trading_status' => MerchantResource::TRADING_STATUS_LABELS,
            ],
            'list_card_title' => '商户列表',
        ]);
    }

    public function stats(): Json
    {
        try {
            $stats = (new MerchantAdminService())->stats();
        } catch (\Throwable $e) {
            return $this->fail('统计失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($stats);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(MerchantValidate::class)
                ->scene('list')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $filters  = MerchantValidate::toListFilters($params);
        $page     = (int) ($params['page'] ?? 1);
        $pageSize = MerchantValidate::toListPageSize($params);

        try {
            $payload = (new MerchantAdminService())->search($filters, $page, $pageSize);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload);
    }

    public function detail(): Json
    {
        $params = $this->request->get();

        try {
            validate(MerchantValidate::class)
                ->scene('detail')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $merchantId = trim((string) ($params['merchant_id'] ?? ''));

        try {
            $detail = (new MerchantAdminService())->detail($merchantId);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        if ($detail === null) {
            return $this->fail('商户不存在', 404, null, 404);
        }

        return $this->success($detail);
    }

    public function reassess(): Json
    {
        return $this->fail('评估引擎未就绪', 501, null, 501);
    }
}
