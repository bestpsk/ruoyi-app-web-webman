<?php

namespace app\controller\wms;

use support\Request;
use app\service\BizStockOutService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizStockOutController
{
    public function list(Request $request)
    {
        $service = new BizStockOutService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectStockOutList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $stockOutId = intval(end($parts));
        $service = new BizStockOutService();
        $stockOut = $service->selectStockOutById($stockOutId);
        if (!$stockOut) return AjaxResult::error('出库单不存在');
        return AjaxResult::success($stockOut);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $realName = trim($request->loginUser->user->real_name ?? '');
        $userName = trim($request->loginUser->user->user_name ?? '');
        $data['create_by'] = $realName ?: $userName;
        if (isset($data['items'])) {
            $data['items'] = convert_to_snake_case($data['items']);
        }
        $service = new BizStockOutService();
        $result = $service->insertStockOut($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        if (isset($data['items'])) {
            $data['items'] = convert_to_snake_case($data['items']);
        }
        $service = new BizStockOutService();
        $result = $service->updateStockOut($data);
        if (!$result) return AjaxResult::error('修改失败，出库单不存在或已确认');
        return AjaxResult::success();
    }

    public function remove(Request $request)
    {
        $stockOutIds = explode(',', $request->input('stockOutIds', ''));
        $stockOutIds = array_map('intval', array_filter($stockOutIds));
        $service = new BizStockOutService();
        $result = $service->deleteStockOutByIds($stockOutIds);
        if (!$result) return AjaxResult::error('删除失败，已确认的出库单不可删除');
        return AjaxResult::success();
    }

    public function confirm(Request $request)
    {
        $parts = explode('/', $request->path());
        $id = intval(end($parts));
        $service = new BizStockOutService();
        $result = $service->confirmStockOut($id);
        if (!$result['success']) return AjaxResult::error($result['msg']);
        return AjaxResult::success($result['msg']);
    }

    public function cancelConfirm(Request $request)
    {
        $parts = explode('/', $request->path());
        $id = intval(end($parts));
        $service = new BizStockOutService();
        $result = $service->cancelConfirmStockOut($id);
        if (!$result['success']) return AjaxResult::error($result['msg']);
        return AjaxResult::success($result['msg']);
    }
}
