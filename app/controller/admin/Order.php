<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\service\mock\OrderMockService;
use think\response\Json;
use think\response\View;

class Order extends AdminBase
{
    protected string $menuKey = 'order';

    protected string $pageTitle = '订单监控';

    /** @var list<string> */
    private const FILTER_KEYS = ['merchant_id', 'order_no', 'currency', 'risk_level', 'status'];

    private const LIST_PATH = '/admin/order';

    /**
     * 页面壳（筛选选项由服务端输出，列表数据走 AJAX）
     */
    public function index(): View
    {
        return $this->renderList('/admin/order/index', [
            'filter_options'  => OrderMockService::filterOptions(),
            'list_path'       => self::LIST_PATH,
            'list_card_title' => '订单实时监控',
        ]);
    }

    /**
     * 订单列表 JSON
     */
    public function list(): Json
    {
        $filters  = $this->listFilters(self::FILTER_KEYS);
        $page     = $this->listPage();
        $pageSize = (int) config('paginate.list_rows', 10);
        $result   = OrderMockService::search($filters, $page, $pageSize);
        $total    = $result['total'];
        $lastPage = max(1, (int) ceil($total / max(1, $pageSize)));
        $page     = min($page, $lastPage);

        return json([
            'code' => 0,
            'msg'  => 'ok',
            'data' => [
                'items'     => $result['items'],
                'total'     => $total,
                'page'      => $page,
                'page_size' => $pageSize,
                'last_page' => $lastPage,
            ],
        ]);
    }

    public function detail(): Json
    {
        $orderNo = trim((string) $this->request->get('id', ''));
        if ($orderNo === '') {
            return json(['code' => 400, 'msg' => '缺少订单号', 'data' => null], 400);
        }

        $order = OrderMockService::find($orderNo);
        if ($order === null) {
            return json(['code' => 404, 'msg' => '订单不存在', 'data' => null], 404);
        }

        return json(['code' => 0, 'msg' => 'ok', 'data' => $order]);
    }
}
