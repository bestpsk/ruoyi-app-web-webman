<?php

namespace app\controller\wms;

use support\Request;
use app\service\BizStockInService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizStockInController
{
    public function list(Request $request)
    {
        $service = new BizStockInService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectStockInList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $stockInId = intval(end($parts));
        $service = new BizStockInService();
        $stockIn = $service->selectStockInById($stockInId);
        if (!$stockIn) return AjaxResult::error('入库单不存在');
        return AjaxResult::success($stockIn);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $loginUser = $request->loginUser->user;
        $realName = trim($loginUser->real_name ?? '');
        $userName = trim($loginUser->user_name ?? '');
        $data['create_by'] = $realName ?: $userName;
        $data['operator_id'] = $request->loginUser->userId ?? 0;
        $data['operator_name'] = $realName ?: $userName;
        if (isset($data['items'])) {
            $data['items'] = convert_to_snake_case($data['items']);
        }
        $service = new BizStockInService();
        $result = $service->insertStockIn($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        if (isset($data['items'])) {
            $data['items'] = convert_to_snake_case($data['items']);
        }
        $service = new BizStockInService();
        $result = $service->updateStockIn($data);
        if (!$result) return AjaxResult::error('修改失败，入库单不存在或已确认');
        return AjaxResult::success();
    }

    public function remove(Request $request)
    {
        $stockInIds = explode(',', $request->input('stockInIds', ''));
        $stockInIds = array_map('intval', array_filter($stockInIds));
        $service = new BizStockInService();
        $result = $service->deleteStockInByIds($stockInIds);
        if (!$result) return AjaxResult::error('删除失败，已确认的入库单不可删除');
        return AjaxResult::success();
    }

    public function confirm(Request $request)
    {
        $parts = explode('/', $request->path());
        $id = intval(end($parts));
        $service = new BizStockInService();
        $result = $service->confirmStockIn($id);
        if (!$result['success']) return AjaxResult::error($result['msg']);
        return AjaxResult::success($result['msg']);
    }

    public function cancelConfirm(Request $request)
    {
        $parts = explode('/', $request->path());
        $id = intval(end($parts));
        $service = new BizStockInService();
        $result = $service->cancelConfirmStockIn($id);
        if (!$result['success']) return AjaxResult::error($result['msg']);
        return AjaxResult::success($result['msg']);
    }
}
