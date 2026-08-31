<?php

declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use think\Response;

/**
 * 后台登录入口（放在 admin 目录外，与受保护区分离；不走 Auth）
 */
class Login extends BaseController
{
    /**
     * 演示账号（无业务逻辑，仅用于开发阶段登录）
     */
    private const DEMO_USER = 'admin';

    private const DEMO_PASS = 'admin123';

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

        if ($username === self::DEMO_USER && $password === self::DEMO_PASS) {
            session('admin_user', [
                'username' => $username,
                'login_at' => date('Y-m-d H:i:s'),
            ]);

            return redirect('/admin/dashboard');
        }

        return redirect('/login?error=1');
    }

    public function logout(): Response
    {
        session('admin_user', null);

        return redirect('/login');
    }
}
