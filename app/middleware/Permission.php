<?php

declare(strict_types=1);

namespace app\middleware;

use app\service\risk\PermissionService;
use Closure;
use think\Request;
use think\Response;

/**
 * 按权限码拦截（路由参数传入权限码；SYS_ADMIN 全放行）
 */
class Permission
{
    public function handle(Request $request, Closure $next, string $permCode = ''): Response
    {
        $permCode = trim($permCode);
        if ($permCode === '') {
            return $next($request);
        }

        $user = session('admin_user');
        $user = is_array($user) ? $user : null;

        if (!(new PermissionService())->can($user, $permCode)) {
            if ($request->isAjax() || str_contains((string) $request->header('accept'), 'application/json')) {
                return json([
                    'code' => 403,
                    'msg'  => '无权限执行此操作',
                    'data' => null,
                ], 403);
            }

            return response('无权限访问', 403);
        }

        return $next($request);
    }
}
