<?php

namespace app\controller\monitor;

use support\Request;
use app\service\SysLogininforService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysLogininforController
{
    public function list(Request $request)
    {
        $service = new SysLogininforService();
        $result = $service->selectLogininforList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function remove(Request $request)
    {
        $infoIds = explode(',', $request->input('infoIds', ''));
        $infoIds = array_map('intval', array_filter($infoIds));
        $service = new SysLogininforService();
        return AjaxResult::toAjax($service->deleteLogininforByIds($infoIds) ? 1 : 0);
    }

    public function clean(Request $request)
    {
        $service = new SysLogininforService();
        $service->cleanLogininfor();
        return AjaxResult::success();
    }

    public function unlock(Request $request)
    {
        $parts = explode('/', $request->path());
        $userName = end($parts);
        $service = new SysLogininforService();
        $service->unlock($userName);
        return AjaxResult::success();
    }
}
