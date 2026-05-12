<?php

namespace app\controller;

use support\Request;
use app\service\CaptchaService;
use app\common\AjaxResult;

class CaptchaController
{
    public function captchaImage(Request $request)
    {
        $result = CaptchaService::getCaptcha();
        return AjaxResult::success('', $result);
    }
}
