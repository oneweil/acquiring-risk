<?php

declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * API 密钥鉴权（仅挂在 /api，勿挂全局）
 *
 * 调用方在 Header 携带：X-Api-Key
 * 演示阶段密钥写死，后续改为配置/数据库
 */
class ApiAuth
{
    private const DEMO_API_KEY = 'risk-demo-api-key';

    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = (string) $request->header('X-Api-Key', '');

        if ($apiKey === '' || !hash_equals(self::DEMO_API_KEY, $apiKey)) {
            return json([
                'code' => 401,
                'msg'  => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        return $next($request);
    }
}
