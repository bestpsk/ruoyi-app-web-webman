<?php

namespace app\controller\business;

use support\Request;
use app\service\BizAttendanceConfigService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizAttendanceConfigController
{
    protected $configService;

    public function __construct()
    {
        $this->configService = new BizAttendanceConfigService();
    }

    public function list(Request $request)
    {
        $params = convert_to_snake_case($request->get());
        $result = $this->configService->selectConfigList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function info(Request $request, $configId)
    {
        $config = $this->configService->selectConfigById($configId);
        if (!$config) {
            return AjaxResult::error('配置不存在');
        }
        return AjaxResult::success($config);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        
        $result = $this->configService->insertConfig($data);
        if ($result) {
            return AjaxResult::success($result, '新增成功');
        }
        return AjaxResult::error('新增失败');
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        
        $result = $this->configService->updateConfig($data);
        if ($result) {
            return AjaxResult::success(null, '修改成功');
        }
        return AjaxResult::error('修改失败');
    }

    public function remove(Request $request)
    {
        $configIds = $request->post('configIds');
        if (empty($configIds)) {
            return AjaxResult::error('请选择要删除的配置');
        }
        
        $result = $this->configService->deleteConfigByIds($configIds);
        if ($result) {
            return AjaxResult::success(null, '删除成功');
        }
        return AjaxResult::error('删除失败');
    }

    public function getUserRule(Request $request)
    {
        $userId = $request->loginUser->userId;
        $rule = $this->configService->getUserRule($userId);
        return AjaxResult::success($rule);
    }
}
