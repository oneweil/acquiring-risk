<?php

declare(strict_types=1);

namespace app\controller\admin;

use app\service\risk\RuleAdminService;
use app\validate\Rule as RuleValidate;
use think\exception\ValidateException;
use think\response\Json;
use think\response\View;

class Rule extends AdminBase
{
    protected string $menuKey = 'rule';

    protected string $pageTitle = '风控规则';

    public function index(): View
    {
        return $this->renderList('/admin/rule/index', [
            'list_card_title' => '规则条件配置',
        ]);
    }

    public function list(): Json
    {
        return $this->success((new RuleAdminService())->listPayload());
    }

    public function save(): Json
    {
        $post = $this->request->post();
        if (!isset($post['items']) && $this->request->isJson()) {
            $decoded = json_decode($this->request->getContent() ?: '', true);
            if (is_array($decoded)) {
                $post = $decoded;
            }
        }

        try {
            validate(RuleValidate::class)
                ->scene('save')
                ->failException(true)
                ->check($post);
        } catch (ValidateException $e) {
            return $this->fail($this->validateErrorMessage($e), 422, null, 422);
        }

        try {
            $payload = (new RuleAdminService())->batchSave(RuleValidate::toSaveItems($post));
        } catch (\Throwable $e) {
            return $this->fail('保存失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload, '保存成功');
    }

    public function reset(): Json
    {
        try {
            $payload = (new RuleAdminService())->resetToDefaults();
        } catch (\Throwable $e) {
            return $this->fail('重置失败：' . $e->getMessage(), 500, null, 500);
        }

        return $this->success($payload, '已恢复默认规则');
    }
}
