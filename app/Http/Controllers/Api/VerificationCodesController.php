<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\Api\VerificationCodeRequest;
use Overtrue\EasySms\EasySms;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $verificationCodeRequest, EasySms $easySms)
    {
        $phone = $verificationCodeRequest->phone;

        //随机生成四位数
        $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);

        //发送验证码
        try {
//          $easySms->send($phone, [
//              'content' => "【Lbbs社区】您的验证码是{$code}。如非本人操作，请忽略本短信"
//          ]);
        } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
            $message = $exception->getException('yunpian')->getMessage();
            return $this->response->errorInternal($message ?: '短信发送失败');
        }

        $key = 'verificationCode_' . str_random(15);
        $expireAt = now()->addMinutes(10);

        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expireAt);

        return $this->response->array([
            'key' => $key,
            'expire_at' => $expireAt->toDateTimeString(),
        ])->setStatusCode(201);
    }
}