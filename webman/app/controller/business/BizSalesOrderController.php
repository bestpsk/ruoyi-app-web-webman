<?php

namespace app\controller\business;

use support\Request;
use app\service\BizSalesOrderService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizSalesOrderController
{
    public function list(Request $request)
    {
        $service = new BizSalesOrderService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectOrderList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $orderId = intval(end($parts));
        $service = new BizSalesOrderService();
        $order = $service->selectOrderById($orderId);
        if (!$order) return AjaxResult::error('订单不存在');
        return AjaxResult::success($order);
    }

    public function add(Request $request)
    {
        try {
            $data = convert_to_snake_case($request->post());
            $items = $data['items'] ?? [];
            unset($data['items']);
            $data['create_by'] = $request->loginUser->user->user_name ?? '';
            $data['creator_user_id'] = $request->loginUser->user->user_id ?? 0;
            $data['creator_user_name'] = $request->loginUser->user->real_name ?? $request->loginUser->user->user_name ?? '';
            $service = new BizSalesOrderService();
            $result = $service->insertOrder($data, $items);
            return AjaxResult::toAjax($result ? 1 : 0);
        } catch (\Exception $e) {
            \support\Log::error('销售开单失败', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'post_data' => $request->post()
            ]);
            return AjaxResult::error('开单失败: ' . $e->getMessage(), 500);
        }
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $items = $data['items'] ?? [];
        unset($data['items']);
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizSalesOrderService();
        $result = $service->updateOrder($data, $items);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $orderIds = $request->input('orderIds');
        if (empty($orderIds)) {
            $parts = explode('/', $request->path());
            $orderIds = end($parts);
        }
        $orderIds = explode(',', $orderIds);
        $orderIds = array_map('intval', array_filter($orderIds));
        $service = new BizSalesOrderService();
        $result = $service->deleteOrderByIds($orderIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function enterpriseAudit(Request $request)
    {
        $orderId = $request->post('orderId');
        $auditBy = $request->loginUser->user->real_name ?? $request->loginUser->user->user_name ?? '';
        $service = new BizSalesOrderService();
        $result = $service->enterpriseAudit($orderId, $auditBy);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function financeAudit(Request $request)
    {
        $orderId = $request->post('orderId');
        $auditBy = $request->loginUser->user->real_name ?? $request->loginUser->user->user_name ?? '';
        $service = new BizSalesOrderService();
        $result = $service->financeAudit($orderId, $auditBy);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
