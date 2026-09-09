<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\service\risk\StrPushRuleAdminService;
use app\validate\StrPushRule as StrPushRuleValidate;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class StrPushRule extends AdminBase
{
    protected string $menuKey = 'str_push_rule';

    protected string $pageTitle = 'STR 推送规则';

    public function index(): View
    {
        $service = new StrPushRuleAdminService();
        $config  = $service->getConfig();

        return $this->renderList('/admin/str_push_rule/index', [
            'config'              => $config,
            'config_json'         => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS),
            'category_options'    => $service->categoryOptions(),
            'risk_level_options'  => $service->riskLevelOptions(),
        ]);
    }

    public function list(): Json
    {
        $params = $this->request->get();

        try {
            validate(StrPushRuleValidate::class)
                ->scene('list')
                ->failException(true)
                ->check($params);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        $filters  = StrPushRuleValidate::toListFilters($params);
        $page     = (int) ($params['page'] ?? 1);
        $pageSize = StrPushRuleValidate::toListPageSize($params);

        return $this->success(
            (new StrPushRuleAdminService())->listRules($filters, $page, $pageSize)
        );
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
            validate(StrPushRuleValidate::class)
                ->scene('save')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        try {
            $payload = (new StrPushRuleAdminService())->save(
                StrPushRuleValidate::toSaveData($post)
            );
        } catch (\Throwable $e) {
            return $this->fail('保存失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload, '保存成功');
    }

    public function reset(): Json
    {
        try {
            $payload = (new StrPushRuleAdminService())->resetToDefaults();
        } catch (\Throwable $e) {
            return $this->fail('重置失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload, '已恢复默认 STR 推送规则');
    }
}
