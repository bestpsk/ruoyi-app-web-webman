<?php

namespace app\controller\business;

use support\Request;
use app\service\BizEnterpriseService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizEnterpriseController
{
    public function list(Request $request)
    {
        $service = new BizEnterpriseService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectEnterpriseList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $enterpriseId = intval(end($parts));
        $service = new BizEnterpriseService();
        $enterprise = $service->selectEnterpriseById($enterpriseId);
        if (!$enterprise) return AjaxResult::error('企业不存在');
        return AjaxResult::success($enterprise);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $service = new BizEnterpriseService();
        $result = $service->selectEnterpriseForSearch($keyword);
        return AjaxResult::success($result);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizEnterpriseService();
        $result = $service->insertEnterprise($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizEnterpriseService();
        $result = $service->updateEnterprise($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $enterpriseIds = explode(',', $request->input('enterpriseIds', ''));
        $enterpriseIds = array_map('intval', array_filter($enterpriseIds));
        $service = new BizEnterpriseService();
        $result = $service->deleteEnterpriseByIds($enterpriseIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function changeStatus(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $enterpriseId = intval($data['enterprise_id'] ?? 0);
        $status = $data['status'] ?? '';
        $service = new BizEnterpriseService();
        $result = $service->updateEnterpriseStatus($enterpriseId, $status);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
