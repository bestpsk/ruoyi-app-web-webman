<?php

namespace app\controller\system;

use support\Request;
use app\service\HrUserSalaryService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class HrUserSalaryController
{
    public function typeList(Request $request)
    {
        $service = new HrUserSalaryService();
        $params = convert_to_snake_case($request->get());
        $list = $service->selectSalaryTypeList($params);
        return AjaxResult::success($list);
    }

    public function listByUser(Request $request)
    {
        $parts = explode('/', $request->path());
        $userId = intval(end($parts));
        $service = new HrUserSalaryService();
        $list = $service->selectUserSalaryList($userId);
        return AjaxResult::success($list);
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $salaryId = intval(end($parts));
        $service = new HrUserSalaryService();
        $salary = $service->selectUserSalaryById($salaryId);
        return AjaxResult::success($salary);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new HrUserSalaryService();
        $result = $service->insertUserSalary($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new HrUserSalaryService();
        $result = $service->updateUserSalary($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $parts = explode('/', $request->path());
        $salaryIds = explode(',', end($parts));
        $salaryIds = array_map('intval', array_filter($salaryIds));
        $service = new HrUserSalaryService();
        $result = $service->deleteUserSalaryByIds($salaryIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
