<?php

namespace app\controller\monitor;

use support\Request;
use app\service\SysJobLogService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysJobLogController
{
    public function list(Request $request)
    {
        $service = new SysJobLogService();
        $result = $service->selectJobLogList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function remove(Request $request)
    {
        $jobLogIds = explode(',', $request->input('jobLogIds', ''));
        $jobLogIds = array_map('intval', array_filter($jobLogIds));
        $service = new SysJobLogService();
        return AjaxResult::toAjax($service->deleteJobLogByIds($jobLogIds) ? 1 : 0);
    }

    public function clean(Request $request)
    {
        $service = new SysJobLogService();
        $service->cleanJobLog();
        return AjaxResult::success();
    }
}
