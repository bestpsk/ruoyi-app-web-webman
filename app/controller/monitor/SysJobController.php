<?php

namespace app\controller\monitor;

use support\Request;
use app\service\SysJobService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysJobController
{
    public function list(Request $request)
    {
        $service = new SysJobService();
        $result = $service->selectJobList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $jobId = intval(end($parts));
        $service = new SysJobService();
        $job = $service->selectJobById($jobId);
        if (!$job) return AjaxResult::error('任务不存在');
        return AjaxResult::success($job);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysJobService();
        return AjaxResult::toAjax($service->insertJob($data) ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysJobService();
        return AjaxResult::toAjax($service->updateJob($data) ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $jobIds = explode(',', $request->input('jobIds', ''));
        $jobIds = array_map('intval', array_filter($jobIds));
        $service = new SysJobService();
        return AjaxResult::toAjax($service->deleteJobByIds($jobIds) ? 1 : 0);
    }

    public function changeStatus(Request $request)
    {
        $jobId = $request->post('jobId');
        $status = $request->post('status');
        $service = new SysJobService();
        return AjaxResult::toAjax($service->changeStatus($jobId, $status) ? 1 : 0);
    }

    public function run(Request $request)
    {
        $jobId = $request->post('jobId');
        $service = new SysJobService();
        $job = $service->selectJobById($jobId);
        if (!$job) return AjaxResult::error('任务不存在');
        $service->run($job);
        return AjaxResult::success();
    }
}
