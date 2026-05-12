<?php

namespace app\controller\monitor;

use support\Request;
use app\service\SysOperLogService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysOperlogController
{
    public function list(Request $request)
    {
        $service = new SysOperLogService();
        $result = $service->selectOperLogList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function remove(Request $request)
    {
        $operIds = explode(',', $request->input('operIds', ''));
        $operIds = array_map('intval', array_filter($operIds));
        $service = new SysOperLogService();
        return AjaxResult::toAjax($service->deleteOperLogByIds($operIds) ? 1 : 0);
    }

    public function clean(Request $request)
    {
        $service = new SysOperLogService();
        $service->cleanOperLog();
        return AjaxResult::success();
    }
}
