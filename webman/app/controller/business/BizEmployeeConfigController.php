<?php

namespace app\controller\business;

use support\Request;
use app\service\BizEmployeeConfigService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizEmployeeConfigController
{
    public function list(Request $request)
    {
        $service = new BizEmployeeConfigService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectConfigList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $configId = intval(end($parts));
        $service = new BizEmployeeConfigService();
        $config = $service->selectConfigById($configId);
        if (!$config) return AjaxResult::error('配置不存在');
        return AjaxResult::success($config);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizEmployeeConfigService();
        $result = $service->insertConfig($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizEmployeeConfigService();
        $result = $service->updateConfig($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function updateSchedulable(Request $request)
    {
        $userId = $request->input('userId');
        $isSchedulable = $request->input('isSchedulable', '1');
        $service = new BizEmployeeConfigService();
        $result = $service->updateSchedulable($userId, $isSchedulable);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function saveRestDates(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $userId = $data['user_id'];
        $restDates = $data['rest_dates'] ?? [];
        $service = new BizEmployeeConfigService();
        $result = $service->updateRestDates($userId, $restDates);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function getRestDates(Request $request)
    {
        $userId = $request->input('userId');
        $service = new BizEmployeeConfigService();
        $restDates = $service->getRestDatesByUserId($userId);
        return AjaxResult::success($restDates);
    }

    public function remove(Request $request)
    {
        $configIds = explode(',', $request->input('configIds', ''));
        $configIds = array_map('intval', array_filter($configIds));
        $service = new BizEmployeeConfigService();
        $result = $service->deleteConfigByIds($configIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $service = new BizEmployeeConfigService();
        $list = $service->searchEmployee($keyword);
        return AjaxResult::success($list);
    }
}
