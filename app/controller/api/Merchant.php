<?php

declare(strict_types=1);

namespace app\controller\api;

use app\service\risk\MerchantSyncService;
use app\validate\api\MerchantSync as MerchantSyncValidate;
use think\exception\ValidateException;
use think\Response;

/**
 * 商户投影 API（主站推送）
 *
 * upsert：新建自动入网评估；更新可用 assess=true 强制重评
 */
class Merchant extends ApiBase
{
    /**
     * POST /api/v1/merchant/upsert
     */
    public function upsert(): Response
    {
        $params = $this->request->post();
        try {
            validate(MerchantSyncValidate::class)->scene('upsert')->check($params);
        } catch (ValidateException $e) {
            return $this->fail((string) $e->getError(), 422, null, 422);
        }

        $data = MerchantSyncValidate::toUpsertData($params);

        try {
            $result = (new MerchantSyncService())->upsert($data);
        } catch (ValidateException $e) {
            return $this->fail((string) $e->getError(), 422, null, 422);
        }

        return $this->success($result);
    }

    /**
     * POST /api/v1/merchant/upsert_batch
     */
    public function upsertBatch(): Response
    {
        $params = $this->request->post();
        try {
            validate(MerchantSyncValidate::class)->scene('batch')->check($params);
        } catch (ValidateException $e) {
            return $this->fail((string) $e->getError(), 422, null, 422);
        }

        $items = [];
        foreach ((array) ($params['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            try {
                validate(MerchantSyncValidate::class)->scene('upsert')->check($item);
            } catch (ValidateException $e) {
                return $this->fail((string) $e->getError(), 422, null, 422);
            }
            $items[] = MerchantSyncValidate::toUpsertData($item);
        }

        return $this->success((new MerchantSyncService())->upsertBatch($items));
    }

    /**
     * GET /api/v1/merchant/:merchant_id
     */
    public function read(string $merchant_id = ''): Response
    {
        $merchantId = trim($merchant_id !== '' ? $merchant_id : (string) $this->request->param('merchant_id', ''));
        try {
            validate(MerchantSyncValidate::class)->scene('get')->check(['merchant_id' => $merchantId]);
            $result = (new MerchantSyncService())->get($merchantId);
        } catch (ValidateException $e) {
            return $this->fail((string) $e->getError(), 422, null, 422);
        }

        return $this->success($result);
    }
}
