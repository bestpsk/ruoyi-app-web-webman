<?php

namespace app\controller\wms;

use support\Request;
use app\service\BizStockCheckService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizStockCheckController
{
    public function list(Request $request)
    {
        $service = new BizStockCheckService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectStockCheckList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $stockCheckId = intval(end($parts));
        $service = new BizStockCheckService();
        $stockCheck = $service->selectStockCheckById($stockCheckId);
        if (!$stockCheck) return AjaxResult::error('盘点单不存在');
        return AjaxResult::success($stockCheck);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $data['operator_id'] = $request->loginUser->userId ?? 0;
        $data['operator_name'] = $request->loginUser->user->real_name ?? '';
        if (isset($data['items'])) {
            $data['items'] = convert_to_snake_case($data['items']);
        }
        $service = new BizStockCheckService();
        $result = $service->insertStockCheck($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        if (isset($data['items'])) {
            $data['items'] = convert_to_snake_case($data['items']);
        }
        $service = new BizStockCheckService();
        $result = $service->updateStockCheck($data);
        if (!$result) return AjaxResult::error('修改失败，盘点单不存在或已确认');
        return AjaxResult::success();
    }

    public function remove(Request $request)
    {
        $stockCheckIds = explode(',', $request->input('stockCheckIds', ''));
        $stockCheckIds = array_map('intval', array_filter($stockCheckIds));
        $service = new BizStockCheckService();
        $result = $service->deleteStockCheckByIds($stockCheckIds);
        if (!$result) return AjaxResult::error('删除失败，已确认的盘点单不可删除');
        return AjaxResult::success();
    }

    public function confirm(Request $request)
    {
        $parts = explode('/', $request->path());
        $id = intval(end($parts));
        $service = new BizStockCheckService();
        $result = $service->confirmStockCheck($id);
        if (!$result['success']) return AjaxResult::error($result['msg']);
        return AjaxResult::success($result['msg']);
    }

    public function loadInventory(Request $request)
    {
        $service = new BizStockCheckService();
        $items = $service->loadInventoryData();
        return AjaxResult::success($items);
    }
}
