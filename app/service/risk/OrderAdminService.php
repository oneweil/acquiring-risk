<?php

declare(strict_types=1);

namespace app\service\risk;

use app\model\OrderEvaluation;
use app\repository\doopsun\DoopsunOrderRepository;
use app\repository\risk\MerchantRepository;
use app\repository\risk\OrderEvaluationRepository;
use app\resource\OrderResource;

/**
 * 后台订单监控：doopsun 订单主数据 + 风控评估应用层组装
 */
class OrderAdminService
{
    public function __construct(
        private readonly DoopsunOrderRepository $orderRepo = new DoopsunOrderRepository(),
        private readonly OrderEvaluationRepository $evalRepo = new OrderEvaluationRepository(),
        private readonly MerchantRepository $merchantRepo = new MerchantRepository(),
    ) {
    }

    /**
     * @return array{currency: list<string>, risk_level: array<string, string>, status: array<string, string>}
     */
    public function filterOptions(): array
    {
        return [
            'currency'   => $this->orderRepo->distinctCurrencies(),
            'risk_level' => OrderEvaluation::RISK_LEVEL_LABELS,
            'status'     => OrderEvaluation::STATUS_LABELS,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed> ThinkPHP 分页形状 + Resource data
     */
    public function search(array $filters, int $page, int $pageSize): array
    {
        $doopsunFilters = $this->buildDoopsunFilters($filters);
        $paginator      = $this->orderRepo->search($doopsunFilters, $page, $pageSize);

        $rawRows = [];
        foreach ($paginator as $item) {
            $rawRows[] = is_array($item) ? $item : (array) $item;
        }

        $assembled = $this->assembleListRows($rawRows, false);
        $payload   = $paginator->toArray();
        $payload['data'] = OrderResource::collection($assembled);

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detail(string $id): ?array
    {
        $id = trim($id);
        if ($id === '') {
            return null;
        }

        $raw = $this->orderRepo->findByChannelNo($id);
        if ($raw === null) {
            $raw = $this->orderRepo->findByOrderNo($id);
        }
        if ($raw === null) {
            return null;
        }

        $rows = $this->assembleListRows([$raw], true);
        $row  = $rows[0] ?? null;
        if ($row === null) {
            return null;
        }

        return OrderResource::make($row)->toArray();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function buildDoopsunFilters(array $filters): array
    {
        $out = [];
        foreach (['merchant_id', 'order_no', 'currency'] as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $out[$key] = $filters[$key];
            }
        }

        $orderNos = null;

        if (isset($filters['risk_level'])) {
            $orderNos = $this->evalRepo->orderNosByRiskLevel((string) $filters['risk_level']);
        }

        $status = isset($filters['status']) ? (string) $filters['status'] : '';
        if ($status === OrderEvaluation::STATUS_BLOCKED) {
            $blocked = $this->evalRepo->orderNosByDecision(OrderEvaluation::DECISION_DECLINE);
            $orderNos = $orderNos === null ? $blocked : array_values(array_intersect($orderNos, $blocked));
        } elseif ($status === OrderEvaluation::STATUS_SUCCESS) {
            $out['doopsun_status'] = DoopsunOrderRepository::DOOPSUN_STATUS_SUCCESS;
            $blocked = $this->evalRepo->orderNosByDecision(OrderEvaluation::DECISION_DECLINE);
            if ($blocked !== []) {
                $out['exclude_order_nos'] = $blocked;
            }
        } elseif ($status === OrderEvaluation::STATUS_FAILED) {
            $out['doopsun_status_ne'] = DoopsunOrderRepository::DOOPSUN_STATUS_SUCCESS;
            $blocked = $this->evalRepo->orderNosByDecision(OrderEvaluation::DECISION_DECLINE);
            if ($blocked !== []) {
                $out['exclude_order_nos'] = $blocked;
            }
        }

        if ($orderNos !== null) {
            $out['order_nos'] = $orderNos;
        }

        return $out;
    }

    /**
     * @param list<array<string, mixed>> $rawRows
     * @return list<array<string, mixed>>
     */
    private function assembleListRows(array $rawRows, bool $withDetails): array
    {
        $orderNos    = [];
        $merchantIds = [];
        foreach ($rawRows as $raw) {
            $ono = (string) ($raw['orderid'] ?? '');
            if ($ono !== '') {
                $orderNos[] = $ono;
            }
            $mid = (string) ($raw['merchantid'] ?? '');
            if ($mid !== '') {
                $merchantIds[] = $mid;
            }
        }

        $nameMap = $this->merchantRepo->mapNamesByIds($merchantIds);
        $evalMap = $this->evalRepo->mapByOrderNos($orderNos, $withDetails);

        $out = [];
        foreach ($rawRows as $raw) {
            $orderNo    = (string) ($raw['orderid'] ?? '');
            $merchantId = (string) ($raw['merchantid'] ?? '');
            $evalBundle = $evalMap[$orderNo] ?? null;
            /** @var OrderEvaluation|null $eval */
            $eval = $evalBundle['evaluation'] ?? null;

            $doopsunStatus = (int) ($raw['status'] ?? 0);
            $decision      = $eval !== null ? (string) $eval->decision : null;
            $displayStatus = $this->resolveDisplayStatus($doopsunStatus, $decision);

            $cardTypeInt = (int) ($raw['cardtype'] ?? 0);
            $cardNo      = $this->nullIfEmpty($raw['cardnum'] ?? null);
            $cardBin     = null;
            if ($cardNo !== null && strlen(preg_replace('/\D/', '', $cardNo) ?? '') >= 6) {
                $digits  = preg_replace('/\D/', '', $cardNo) ?? '';
                $cardBin = substr($digits, 0, 6);
            }

            $row = [
                'order_no'          => $orderNo !== '' ? $orderNo : null,
                'channel_no'        => $this->nullIfEmpty($raw['doopsun_orderid'] ?? null),
                'merchant_id'       => $merchantId !== '' ? $merchantId : null,
                'merchant_name'     => $nameMap[$merchantId] ?? null,
                'trade_time'        => $this->formatTradeTime($raw),
                'currency'          => $this->nullIfEmpty($raw['currency'] ?? null),
                'amount'            => isset($raw['orderamount']) ? (float) $raw['orderamount'] : null,
                'card_type'         => DoopsunOrderRepository::CARD_TYPE_LABELS[$cardTypeInt]
                    ?? (string) $cardTypeInt,
                'card_country'      => null,
                'ip_country'        => null,
                'three_ds'          => $this->mapThreeDs((int) ($raw['del'] ?? 0)),
                'status'            => $displayStatus,
                'risk_level'        => $eval !== null ? (string) $eval->risk_level : null,
                'hit_rule'          => $evalBundle['hit_rule'] ?? '',
                'action'            => $eval !== null ? $this->nullIfEmpty($eval->action) : null,
                'decision'          => $decision,
                'measure_code'      => $eval !== null ? $this->nullIfEmpty($eval->measure_code) : null,
                'evaluated_at'      => $eval !== null ? (string) $eval->evaluated_at : null,
                'website'           => $this->nullIfEmpty($raw['accessurl'] ?? null),
                'card_no'           => $cardNo,
                'card_bin'          => $cardBin,
                'avs_result'        => null,
                'cvv_result'        => null,
                'eci'               => null,
                'email'             => $this->nullIfEmpty($raw['email'] ?? null),
                'ip'                => $this->nullIfEmpty($raw['ip'] ?? null),
                'billing_country'   => null,
                'shipping_country'  => null,
                'mcc'               => null,
                'is_proxy'          => false,
                'hit_details'       => $withDetails ? ($evalBundle['hits'] ?? []) : [],
            ];

            $out[] = $row;
        }

        return $out;
    }

    private function resolveDisplayStatus(int $doopsunStatus, ?string $decision): string
    {
        if ($decision === OrderEvaluation::DECISION_DECLINE) {
            return OrderEvaluation::STATUS_BLOCKED;
        }
        if ($doopsunStatus === DoopsunOrderRepository::DOOPSUN_STATUS_SUCCESS) {
            return OrderEvaluation::STATUS_SUCCESS;
        }

        return OrderEvaluation::STATUS_FAILED;
    }

    private function mapThreeDs(int $del): string
    {
        return $del === DoopsunOrderRepository::DEL_THREE_DS ? '已参与' : '未参与';
    }

    /**
     * @param array<string, mixed> $raw
     */
    private function formatTradeTime(array $raw): ?string
    {
        $ts = (int) ($raw['doopsun_orderdate'] ?? 0);
        if ($ts > 0) {
            return date('Y-m-d H:i:s', $ts);
        }

        $od = trim((string) ($raw['orderdate'] ?? ''));
        if ($od !== '' && $od !== '0000-00-00 00:00:00') {
            return $od;
        }

        return null;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $str = trim((string) $value);

        return $str === '' ? null : $str;
    }
}
