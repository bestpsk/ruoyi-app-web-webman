<?php

namespace app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Request;
use Webman\Http\Response;
use app\service\TokenService;
use app\common\AjaxResult;
use app\common\Constants;

class AuthMiddleware implements MiddlewareInterface
{
    protected $whitelist = [
        '/login',
        '/register',
        '/captchaImage',
        '/logout',
    ];

    public function process(Request $request, callable $handler): Response
    {
        $path = $request->path();

        if (in_array($path, $this->whitelist)) {
            return $handler($request);
        }

        if (str_starts_with($path, '/common/')) {
            return $handler($request);
        }

        $tokenService = new TokenService();
        $loginUser = $tokenService->getLoginUser($request);

        if (!$loginUser) {
            return json(['code' => 401, 'msg' => '未登录或登录已过期']);
        }

        $tokenService->verifyToken($loginUser);

        $request->loginUser = $loginUser;

        return $handler($request);
    }
}
