<?php

namespace app\controller\business;

use support\Request;
use app\service\BizScheduleService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizScheduleController
{
    public function list(Request $request)
    {
        $service = new BizScheduleService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectScheduleList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $scheduleId = intval(end($parts));
        $service = new BizScheduleService();
        $schedule = $service->selectScheduleById($scheduleId);
        if (!$schedule) return AjaxResult::error('行程不存在');
        return AjaxResult::success($schedule);
    }

    public function calendar(Request $request)
    {
        $service = new BizScheduleService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectScheduleByDateRange($params);
        return AjaxResult::success($result);
    }

    public function dates(Request $request)
    {
        $service = new BizScheduleService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectScheduleDates($params);
        return AjaxResult::success($result);
    }

    public function employeeSchedule(Request $request)
    {
        $service = new BizScheduleService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectEmployeeSchedule($params);
        return AjaxResult::success($result);
    }

    public function enterpriseSchedule(Request $request)
    {
        $service = new BizScheduleService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectEnterpriseSchedule($params);
        return AjaxResult::success($result);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizScheduleService();
        $result = $service->insertSchedule($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function addBatch(Request $request)
    {
        $dataList = $request->post();
        $service = new BizScheduleService();
        
        $insertData = [];
        $createBy = $request->loginUser->user->user_name ?? '';
        
        foreach ($dataList as $item) {
            $item = convert_to_snake_case($item);
            $item['create_by'] = $createBy;
            $insertData[] = $item;
        }
        
        $result = $service->insertScheduleBatch($insertData);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizScheduleService();
        $result = $service->updateSchedule($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $parts = explode('/', $request->path());
        $idsStr = end($parts);
        $scheduleIds = explode(',', $idsStr);
        $scheduleIds = array_map('intval', array_filter($scheduleIds));
        
        if (empty($scheduleIds)) {
            return AjaxResult::error('请选择要删除的行程');
        }
        
        $service = new BizScheduleService();
        $result = $service->deleteScheduleByIds($scheduleIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
