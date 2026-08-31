<?php

declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use think\Response;

/**
 * API 基类（鉴权由路由组 middleware=api_auth 挂载）
 */
abstract class ApiBase extends BaseController
{
    protected function success(mixed $data = null, string $msg = 'ok'): Response
    {
        return json([
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    protected function fail(string $msg = 'error', int $code = 1, mixed $data = null, int $httpCode = 200): Response
    {
        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $httpCode);
    }
}
