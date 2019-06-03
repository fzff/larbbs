<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Traits\PassportToken;
use Illuminate\Http\Request;
use App\Http\Requests\Api\AuthorizationRequest;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class AuthorizationsController extends Controller
{
    use PassportToken;

    public function socialStore($type, SocialAuthorizationRequest $request)
    {
        if (!in_array($type, ['weixin'])) {
            return $this->response->errorBadRequest();
        }

        $driver = \Socialite::driver($type);

        try {
            if ($code = $request->code) {
                $response = $driver->getAccessTokenResponse($code);
                $token = array_get($response, 'access_token');
            } else {
                $token = $request->access_token;

                if ($type == 'weixin') {
                    $driver->setOpenId($request->openid);
                }
            }

            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception $exception) {
            //return $this->response->errorUnauthorized('参数错误，未获取用户信息');
            return $this->response->errorUnauthorized($exception->getMessage());
        }

        switch ($type) {
            case 'weixin':
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
                if ($unionid) {
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                // 没有用户，默认创建一个用户
                if (!$user) {
                    $user = User::create([
                        'name' => $oauthUser->getNickname(),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }

                break;
        }

        $result = $this->getBearerTokenByUser($user, 1, false);

        return $this->response->array($result)->setStatusCode(201);
    }

    public function store(AuthorizationRequest $request, AuthorizationServer $authorizationServer,
                          ServerRequestInterface $serverRequest)
    {
        try {
            return $authorizationServer->respondToAccessTokenRequest($serverRequest, new Response)
                ->withStatus(201);
        } catch (OAuthServerException $exception) {
            return $this->response->errorUnauthorized($exception->getMessage());
        }
    }

    public function update(AuthorizationServer $authorizationServer, ServerRequestInterface $serverRequest)
    {
        try {
            return $authorizationServer->respondToAccessTokenRequest($serverRequest, new Response);
        } catch (OAuthServerException $exception) {
            return $this->response->errorUnauthorized($exception->getMessage());
        }
    }

    public function destroy()
    {
        \Auth::guard('api')->logout();
        return $this->response->noContent();
    }

    protected function respondWithToken($token)
    {
        return $this->response->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
