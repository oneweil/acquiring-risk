<?php

declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * 后台登录校验（仅挂在 /admin 受保护区，勿挂全局、勿挂登录页）
 */
class Auth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!admin_is_logged_in()) {
            return redirect('/login');
        }

        return $next($request);
    }
}
