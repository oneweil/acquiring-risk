<?php

declare(strict_types=1);

use think\facade\Route;

/*
|--------------------------------------------------------------------------
| 路由与中间件约定（单应用）
|--------------------------------------------------------------------------
| 全局中间件：空（鉴权不放全局）
| 登录相关：session
| 后台受保护区：session + auth
| API：api_auth（无 Session）
*/

Route::get('/', function () {
    return redirect('/login');
});

// ---------- 后台登录（需要 Session，不要 Auth） ----------
Route::group(function () {
    Route::get('login', 'Login/index');
    Route::post('login', 'Login/doLogin');
    Route::get('logout', 'Login/logout');
})->middleware(['session']);

// ---------- 后台业务（Session + 登录校验） ----------
Route::group('admin', function () {
    Route::get('dashboard', 'admin.Dashboard/index');
    // 更长路径须写在短路径之前，避免被短路径抢走
    Route::get('order/detail', 'admin.Order/detail');
    Route::get('order/list', 'admin.Order/list');
    Route::get('order', 'admin.Order/index');
    Route::get('alert', 'admin.Alert/index');
    Route::get('onboarding', 'admin.Onboarding/index');
    Route::get('merchant', 'admin.Merchant/index');
    Route::get('merchant_risk_level', 'admin.MerchantRiskLevel/index');
    Route::get('merchant_risk', 'admin.MerchantRisk/index');
    Route::get('str_report', 'admin.StrReport/index');
    Route::get('str_push_rule', 'admin.StrPushRule/index');
    Route::get('edd', 'admin.Edd/index');
    Route::get('rule', 'admin.Rule/index');
    Route::get('disposition/list', 'admin.Disposition/list');
    Route::post('disposition/save', 'admin.Disposition/save');
    Route::get('disposition', 'admin.Disposition/index');
    Route::get('blacklist/list', 'admin.Blacklist/list');
    Route::post('blacklist/save', 'admin.Blacklist/save');
    Route::get('blacklist', 'admin.Blacklist/index');
    Route::get('user', 'admin.User/index');
    Route::get('role', 'admin.Role/index');
    Route::get('audit_log', 'admin.AuditLog/index');
})->middleware(['session', 'auth']);

// ---------- 开放/内部 API（API Key，不走 Session） ----------
Route::group('api', function () {
    Route::group('v1', function () {
        Route::post('risk/evaluate', 'api.Risk/evaluate');
    });
})->middleware(['api_auth']);
