<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['namespace' => 'App\Http\Controllers\Api'], function ($api) {

    $api->group([
        'middleware' => 'api.throttle',
        'limit'   => config('api.rate_limits.sign.limit'),
        'expires' => config('api.rate_limits.sign.expires'),
    ], function ($api) {
        // 短信验证码
        $api->post('verificationCodes', 'VerificationCodesController@store')->name('api.verificationCodes.store');
        // 用户注册
        $api->post('users', 'UsersController@store')->name('api.users.store');
        // 图片验证码
        $api->post('captchas', 'CaptchasController@store')->name('api.captchas.store');
        $api->get('get-captcha', 'CaptchasController@getCaptchasImage')->name('api.get-captcha.getCaptchasImage');

        // 第三方登录
        $api->post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
            ->name('api.socials.authorizations.store');
    });
});

//071LJZQz1vPKkc0jJcNz1nC9Rz1LJZQc

//https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx733895b6cd4a1273&secret=2937df6ce5a310da45814d5f83b6ca01&code=071LJZQz1vPKkc0jJcNz1nC9Rz1LJZQc&grant_type=authorization_code
//https://api.weixin.qq.com/sns/userinfo?access_token=22_f4eWQvPlVCv_aGXPiQYq_RliJfaUiOrRceofiKiHKJw41KJgGb-0qjTFYzZJa37ZVB6X-cqpNywzYUnBFGpdaQ&openid=o5Sm91WO1zTnvHTdNFzHgsw4J9uU&lang=zh_CN

//22_f4eWQvPlVCv_aGXPiQYq_RliJfaUiOrRceofiKiHKJw41KJgGb-0qjTFYzZJa37ZVB6X-cqpNywzYUnBFGpdaQ
//open_id = o5Sm91WO1zTnvHTdNFzHgsw4J9uU