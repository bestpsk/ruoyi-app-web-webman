<?php

namespace app\controller\monitor;

use support\Request;
use app\service\SysUserOnlineService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysUserOnlineController
{
    public function list(Request $request)
    {
        $service = new SysUserOnlineService();
        $list = $service->selectOnlineList($request->all());
        return TableDataInfo::result($list, count($list));
    }

    public function forceLogout(Request $request)
    {
        $parts = explode('/', $request->path());
        $tokenId = end($parts);
        $service = new SysUserOnlineService();
        $result = $service->forceLogout($tokenId);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
