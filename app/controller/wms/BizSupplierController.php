<?php

namespace app\controller\wms;

use support\Request;
use app\service\BizSupplierService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizSupplierController
{
    public function list(Request $request)
    {
        $service = new BizSupplierService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectSupplierList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $supplierId = intval(end($parts));
        $service = new BizSupplierService();
        $supplier = $service->selectSupplierById($supplierId);
        if (!$supplier) return AjaxResult::error('供货商不存在');
        return AjaxResult::success($supplier);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $service = new BizSupplierService();
        $list = $service->searchSupplier($keyword);
        return AjaxResult::success($list);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizSupplierService();
        $result = $service->insertSupplier($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizSupplierService();
        $result = $service->updateSupplier($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $supplierIds = explode(',', $request->input('supplierIds', ''));
        $supplierIds = array_map('intval', array_filter($supplierIds));
        $service = new BizSupplierService();
        $result = $service->deleteSupplierByIds($supplierIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
