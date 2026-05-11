<?php

namespace app\controller\system;

use support\Request;
use app\service\SysConfigService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysConfigController
{
    public function list(Request $request)
    {
        $service = new SysConfigService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectConfigList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $configId = intval(end($parts));
        $service = new SysConfigService();
        $config = $service->selectConfigById($configId);
        if (!$config) return AjaxResult::error('参数不存在');
        return AjaxResult::success($config);
    }

    public function getConfigKey(Request $request)
    {
        $parts = explode('/', $request->path());
        $configKey = end($parts);
        $service = new SysConfigService();
        return AjaxResult::success('', $service->selectConfigByKey($configKey));
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysConfigService();
        return AjaxResult::toAjax($service->insertConfig($data) ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysConfigService();
        return AjaxResult::toAjax($service->updateConfig($data) ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $configIds = explode(',', $request->input('configIds', ''));
        $configIds = array_map('intval', array_filter($configIds));
        $service = new SysConfigService();
        return AjaxResult::toAjax($service->deleteConfigByIds($configIds) ? 1 : 0);
    }

    public function refreshCache(Request $request)
    {
        $service = new SysConfigService();
        $service->resetConfigCache();
        return AjaxResult::success();
    }
}
