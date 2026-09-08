<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\service\risk\OrderAdminService;
use app\validate\Order as OrderValidate;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class Order extends AdminBase
{
    protected string $menuKey = 'order';

    protected string $pageTitle = '订单监控';

    public function index(): View
    {
        $options = (new OrderAdminService())->filterOptions();

        return $this->renderList('/admin/order/index', [
            'filter_options'  => $options,
            'list_card_title' => '订单实时监控',
        ]);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(OrderValidate::class)
                ->scene('list')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $filters  = OrderValidate::toListFilters($params);
        $page     = (int) ($params['page'] ?? 1);
        $pageSize = OrderValidate::toListPageSize($params);

        try {
            $payload = (new OrderAdminService())->search($filters, $page, $pageSize);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload);
    }

    public function detail(): Json
    {
        $params = $this->request->get();
        if (!isset($params['id']) || trim((string) $params['id']) === '') {
            $params['id'] = trim((string) ($params['order_no'] ?? $params['channel_no'] ?? ''));
        }

        try {
            validate(OrderValidate::class)
                ->scene('detail')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $id = trim((string) ($params['id'] ?? ''));

        try {
            $detail = (new OrderAdminService())->detail($id);
        } catch (\Throwable $e) {
            return $this->fail('查询失败：' . $e->getMessage(), 500, null, 500);
        }

        if ($detail === null) {
            return $this->fail('订单不存在', 404, null, 404);
        }

        return $this->success($detail);
    }
}
