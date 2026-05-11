<?php

namespace app\controller\system;

use support\Request;
use app\service\SysUserDetailService;
use app\common\AjaxResult;

class SysUserDetailController
{
    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $userId = intval(end($parts));
        $service = new SysUserDetailService();
        $detail = $service->selectDetailByUserId($userId);
        return AjaxResult::success($detail);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysUserDetailService();
        $result = $service->insertDetail($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysUserDetailService();
        $result = $service->updateDetail($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
