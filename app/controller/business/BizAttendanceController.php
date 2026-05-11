<?php

namespace app\controller\business;

use support\Request;
use app\service\BizAttendanceRuleService;
use app\service\BizAttendanceRecordService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizAttendanceController
{
    public function todayRecord(Request $request)
    {
        $userId = $request->loginUser->user->user_id;
        $service = new BizAttendanceRecordService();
        $record = $service->getTodayRecord($userId);
        return AjaxResult::success($record);
    }

    public function clock(Request $request)
    {
        $user = $request->loginUser->user;
        $data = convert_to_snake_case($request->post());
        $data['user_id'] = $user->user_id;
        $data['user_name'] = $user->real_name ?? $user->user_name;

        $service = new BizAttendanceRecordService();
        $result = $service->clock($data);

        if (isset($result['error'])) {
            return AjaxResult::error($result['error']);
        }

        return AjaxResult::success('打卡成功', $result);
    }

    public function todayClockList(Request $request)
    {
        $userId = $request->loginUser->user->user_id;
        $service = new BizAttendanceRecordService();
        $list = $service->getTodayClockList($userId);
        return AjaxResult::success($list);
    }

    public function clockList(Request $request)
    {
        $recordId = $request->get('record_id');
        if (!$recordId) {
            return AjaxResult::error('缺少参数：record_id');
        }
        
        $clocks = \app\model\BizAttendanceClock::where('record_id', $recordId)
            ->orderBy('clock_time', 'asc')
            ->get();
            
        return AjaxResult::success($clocks);
    }

    public function clockIn(Request $request)
    {
        $user = $request->loginUser->user;
        $data = convert_to_snake_case($request->post());
        $data['user_id'] = $user->user_id;
        $data['user_name'] = $user->real_name ?? $user->user_name;

        $service = new BizAttendanceRecordService();
        $result = $service->clockIn($data);

        if (isset($result['error'])) {
            return AjaxResult::error($result['error']);
        }

        return AjaxResult::success('打卡成功', $result);
    }

    public function clockOut(Request $request)
    {
        $user = $request->loginUser->user;
        $data = convert_to_snake_case($request->post());
        $data['user_id'] = $user->user_id;
        $data['user_name'] = $user->real_name ?? $user->user_name;

        $service = new BizAttendanceRecordService();
        $result = $service->clockOut($data);

        if (isset($result['error'])) {
            return AjaxResult::error($result['error']);
        }

        return AjaxResult::success('打卡成功', $result);
    }

    public function monthStats(Request $request)
    {
        $userId = $request->input('user_id', $request->loginUser->user->user_id);
        $month = $request->input('month', date('Y-m'));

        $service = new BizAttendanceRecordService();
        $stats = $service->getMonthStats($userId, $month);
        return AjaxResult::success($stats);
    }

    public function recordList(Request $request)
    {
        $service = new BizAttendanceRecordService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectRecordList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function recordInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $recordId = intval(end($parts));
        $service = new BizAttendanceRecordService();
        $record = $service->selectRecordById($recordId);
        if (!$record) {
            return AjaxResult::error('记录不存在');
        }
        return AjaxResult::success($record);
    }

    public function ruleList(Request $request)
    {
        $service = new BizAttendanceRuleService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectRuleList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function ruleInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $ruleId = intval(end($parts));
        $service = new BizAttendanceRuleService();
        $rule = $service->selectRuleById($ruleId);
        if (!$rule) {
            return AjaxResult::error('规则不存在');
        }
        return AjaxResult::success($rule);
    }

    public function addRule(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizAttendanceRuleService();
        $result = $service->insertRule($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function editRule(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizAttendanceRuleService();
        $result = $service->updateRule($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function removeRule(Request $request)
    {
        $parts = explode('/', $request->path());
        $ruleIds = explode(',', end($parts));
        $ruleIds = array_map('intval', array_filter($ruleIds));
        $service = new BizAttendanceRuleService();
        $result = $service->deleteRuleByIds($ruleIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
