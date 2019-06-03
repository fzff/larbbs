<?php

namespace App\Providers;

use Dingo\Api\Routing\Route;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class PassportDingoProvider extends ServiceProvider
{
    protected $auth;

    protected $guard = 'api';

    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth->guard($this->guard);
    }

    public function authenticate(Request $request, Route $route)
    {
        if (!$user = $this->auth->user()) {
            throw new UnauthorizedHttpException(
                get_class($this),
                'Unable to authenticate with invalid API key and token.'
            );
        }

        return $user;
    }

    public function getAuthorizationMethod()
    {
        return 'Bearer';
    }
}
