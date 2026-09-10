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
    Route::get('alert/list', 'admin.Alert/list');
    Route::get('alert/detail', 'admin.Alert/detail');
    Route::post('alert/handle', 'admin.Alert/handle');
    Route::post('alert/upload', 'admin.Alert/upload');
    Route::get('alert/attachment/download', 'admin.Alert/downloadAttachment');
    Route::get('alert', 'admin.Alert/index');
    Route::get('merchant/stats', 'admin.Merchant/stats');
    Route::get('merchant/list', 'admin.Merchant/list');
    Route::get('merchant/detail', 'admin.Merchant/detail');
    Route::post('merchant/reassess', 'admin.Merchant/reassess');
    Route::get('merchant', 'admin.Merchant/index');
    Route::post('merchant_risk_level/save', 'admin.MerchantRiskLevel/save');
    Route::post('merchant_risk_level/reassess', 'admin.MerchantRiskLevel/reassess');
    Route::get('merchant_risk_level', 'admin.MerchantRiskLevel/index');
    Route::get('merchant_risk/config', 'admin.MerchantRisk/config');
    Route::post('merchant_risk/save', 'admin.MerchantRisk/save');
    Route::post('merchant_risk/reassess', 'admin.MerchantRisk/reassess');
    Route::get('merchant_risk', 'admin.MerchantRisk/index');
    Route::get('str_report/stats', 'admin.StrReport/stats');
    Route::get('str_report/list', 'admin.StrReport/list');
    Route::get('str_report/detail', 'admin.StrReport/detail');
    Route::post('str_report/create', 'admin.StrReport/create');
    Route::post('str_report/confirm', 'admin.StrReport/confirm');
    Route::post('str_report/dismiss', 'admin.StrReport/dismiss');
    Route::post('str_report/upload', 'admin.StrReport/upload');
    Route::get('str_report/attachment/download', 'admin.StrReport/downloadAttachment');
    Route::post('str_report/submit', 'admin.StrReport/submit');
    Route::get('str_report', 'admin.StrReport/index');
    Route::get('str_push_rule/list', 'admin.StrPushRule/list');
    Route::post('str_push_rule/save', 'admin.StrPushRule/save');
    Route::post('str_push_rule/reset', 'admin.StrPushRule/reset');
    Route::get('str_push_rule', 'admin.StrPushRule/index');
    Route::get('edd/stats', 'admin.Edd/stats');
    Route::get('edd/list', 'admin.Edd/list');
    Route::get('edd/detail', 'admin.Edd/detail');
    Route::post('edd/create', 'admin.Edd/create');
    Route::post('edd/collect', 'admin.Edd/collect');
    Route::post('edd/upload', 'admin.Edd/upload');
    Route::post('edd/attachment/delete', 'admin.Edd/deleteAttachment');
    Route::get('edd/attachment/download', 'admin.Edd/downloadAttachment');
    Route::post('edd/submit', 'admin.Edd/submit');
    Route::post('edd/review', 'admin.Edd/review');
    Route::get('edd', 'admin.Edd/index');
    Route::get('rule/list', 'admin.Rule/list');
    Route::post('rule/save', 'admin.Rule/save');
    Route::post('rule/reset', 'admin.Rule/reset');
    Route::get('rule', 'admin.Rule/index');
    Route::get('disposition/list', 'admin.Disposition/list');
    Route::post('disposition/save', 'admin.Disposition/save');
    Route::get('disposition', 'admin.Disposition/index');
    Route::get('blacklist/list', 'admin.Blacklist/list');
    Route::post('blacklist/save', 'admin.Blacklist/save');
    Route::get('blacklist', 'admin.Blacklist/index');

    Route::get('user/list', 'admin.User/list')->middleware('permission', 'users:view');
    Route::get('user/role_options', 'admin.User/roleOptions')->middleware('permission', 'users:view');
    Route::post('user/save', 'admin.User/save')->middleware('permission', 'users:edit');
    Route::post('user/reset_password', 'admin.User/resetPassword')->middleware('permission', 'users:edit');
    Route::post('user/toggle_status', 'admin.User/toggleStatus')->middleware('permission', 'users:edit');
    Route::post('user/roles', 'admin.User/roles')->middleware('permission', 'users:edit');
    Route::get('user', 'admin.User/index')->middleware('permission', 'users:view');

    Route::get('role/list', 'admin.Role/list')->middleware('permission', 'roles:view');
    Route::get('role/permission_catalog', 'admin.Role/permissionCatalog')->middleware('permission', 'roles:view');
    Route::get('role/users_assign', 'admin.Role/usersAssign')->middleware('permission', 'roles:view');
    Route::post('role/save', 'admin.Role/save')->middleware('permission', 'roles:edit');
    Route::post('role/permissions', 'admin.Role/permissions')->middleware('permission', 'roles:edit');
    Route::post('role/users', 'admin.Role/users')->middleware('permission', 'roles:edit');
    Route::post('role/delete', 'admin.Role/delete')->middleware('permission', 'roles:edit');
    Route::get('role', 'admin.Role/index')->middleware('permission', 'roles:view');

    Route::get('audit_log', 'admin.AuditLog/index');
})->middleware(['session', 'auth']);

// ---------- 开放/内部 API（API Key，不走 Session） ----------
Route::group('api', function () {
    Route::group('v1', function () {
        Route::post('risk/evaluate', 'api.Risk/evaluate');
        Route::post('merchant/upsert', 'api.Merchant/upsert');
        Route::post('merchant/upsert_batch', 'api.Merchant/upsertBatch');
        Route::get('merchant/:merchant_id', 'api.Merchant/read');
    });
})->middleware(['api_auth']);
