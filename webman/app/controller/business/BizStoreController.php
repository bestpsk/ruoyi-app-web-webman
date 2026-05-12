<?php

namespace app\controller\business;

use support\Request;
use app\service\BizStoreService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizStoreController
{
    public function list(Request $request)
    {
        $service = new BizStoreService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectStoreList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $storeId = intval(end($parts));
        $service = new BizStoreService();
        $store = $service->selectStoreById($storeId);
        if (!$store) return AjaxResult::error('门店不存在');
        return AjaxResult::success($store);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $enterpriseId = $request->input('enterpriseId', null);
        $service = new BizStoreService();
        $result = $service->selectStoreForSearch($keyword, $enterpriseId);
        return AjaxResult::success($result);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizStoreService();
        $result = $service->insertStore($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizStoreService();
        $result = $service->updateStore($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $storeIds = explode(',', $request->input('storeIds', ''));
        $storeIds = array_map('intval', array_filter($storeIds));
        $service = new BizStoreService();
        $result = $service->deleteStoreByIds($storeIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
