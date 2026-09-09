<?php

declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\SysUser;
use app\service\risk\PermissionService;
use think\Response;

/**
 * 后台登录入口（放在 admin 目录外，与受保护区分离；不走 Auth）
 */
class Login extends BaseController
{
    public function index(): Response
    {
        if (admin_is_logged_in()) {
            return redirect('/admin/dashboard');
        }

        return view('login/index', [
            'error' => $this->request->get('error', ''),
        ]);
    }

    public function doLogin(): Response
    {
        $username = trim((string) $this->request->post('username', ''));
        $password = (string) $this->request->post('password', '');

        if ($username === '' || $password === '') {
            return redirect('/login?error=1');
        }

        /** @var SysUser|null $user */
        $user = SysUser::where('account', $username)->find();
        if ($user === null) {
            return redirect('/login?error=1');
        }

        if ((string) $user->status !== SysUser::STATUS_ENABLED) {
            return redirect('/login?error=1');
        }

        // 需读取隐藏字段 password
        $hash = (string) $user->getData('password');
        if ($hash === '' || !password_verify($password, $hash)) {
            return redirect('/login?error=1');
        }

        $permService = new PermissionService();
        session('admin_user', $permService->buildSessionPayload($user));
        $permService->touchLastLogin((int) $user->id);

        return redirect('/admin/dashboard');
    }

    public function logout(): Response
    {
        session('admin_user', null);

        return redirect('/login');
    }
}
