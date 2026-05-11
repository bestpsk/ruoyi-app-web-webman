<?php

namespace app\controller\business;

use support\Request;
use app\service\BizShipmentService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizShipmentController
{
    protected $shipmentService;

    public function __construct()
    {
        $this->shipmentService = new BizShipmentService();
    }

    public function list(Request $request)
    {
        $params = convert_to_snake_case($request->all());
        $result = $this->shipmentService->selectShipmentList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $shipmentId = intval(end($parts));
        $shipment = $this->shipmentService->selectShipmentById($shipmentId);
        if (!$shipment) return AjaxResult::error('出货单不存在');
        return AjaxResult::success($shipment);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $result = $this->shipmentService->insertShipment($data);
        if (isset($result['error'])) {
            return AjaxResult::error($result['error']);
        }
        return AjaxResult::success($result, '新增成功');
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $result = $this->shipmentService->updateShipment($data);
        if (!$result) {
            return AjaxResult::error('修改失败');
        }
        return AjaxResult::success(null, '修改成功');
    }

    public function remove(Request $request)
    {
        $parts = explode('/', $request->path());
        $shipmentIds = explode(',', end($parts));
        $shipmentIds = array_map('intval', array_filter($shipmentIds));
        $result = $this->shipmentService->deleteShipmentByIds($shipmentIds);
        if (!$result) {
            return AjaxResult::error('删除失败，已审核的出货单不可删除');
        }
        return AjaxResult::success(null, '删除成功');
    }

    public function audit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['audit_by'] = $request->loginUser->user->user_name ?? '';
        $result = $this->shipmentService->audit($data);
        if (!$result) {
            return AjaxResult::error('审核失败');
        }
        return AjaxResult::success(null, '审核成功');
    }

    public function ship(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $result = $this->shipmentService->ship($data);
        if (!$result) {
            return AjaxResult::error('发货失败');
        }
        return AjaxResult::success(null, '发货成功');
    }

    public function confirmReceipt(Request $request)
    {
        $parts = explode('/', $request->path());
        $shipmentId = intval(end($parts));
        $result = $this->shipmentService->confirmReceipt($shipmentId);
        if (!$result) {
            return AjaxResult::error('确认收货失败');
        }
        return AjaxResult::success(null, '确认收货成功');
    }
}
