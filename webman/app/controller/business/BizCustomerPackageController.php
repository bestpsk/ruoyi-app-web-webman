<?php

namespace app\controller\business;

use support\Request;
use app\service\BizCustomerPackageService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizCustomerPackageController
{
    public function list(Request $request)
    {
        $service = new BizCustomerPackageService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectPackageList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $packageId = intval(end($parts));
        $service = new BizCustomerPackageService();
        $package = $service->selectPackageById($packageId);
        if (!$package) return AjaxResult::error('套餐不存在');
        return AjaxResult::success($package);
    }

    public function getByCustomer(Request $request)
    {
        $customerId = $request->input('customerId');
        $allParams = $request->all();
        $status = (isset($allParams['status']) && $allParams['status'] !== '') ? $allParams['status'] : null;
        $service = new BizCustomerPackageService();
        $result = $service->selectPackagesByCustomer($customerId, $status);
        return AjaxResult::success($result);
    }
}
