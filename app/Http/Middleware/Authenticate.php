<?php

namespace App\Http\Middleware;
use Dingo\Api\Routing\Helpers;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    use Helpers;
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
//        if (! $request->expectsJson()) {
//            return route('login');
//        }

        return $this->response->errorUnauthorized('用户凭证错误');
    }
}
