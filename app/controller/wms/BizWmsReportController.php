<?php

namespace app\controller\wms;

use support\Request;
use app\service\BizWmsReportService;
use app\common\AjaxResult;

class BizWmsReportController
{
    public function stockInSummary(Request $request)
    {
        $service = new BizWmsReportService();
        $params = convert_to_snake_case($request->all());
        $result = $service->stockInSummary($params);
        return AjaxResult::success($result);
    }

    public function stockOutSummary(Request $request)
    {
        $service = new BizWmsReportService();
        $params = convert_to_snake_case($request->all());
        $result = $service->stockOutSummary($params);
        return AjaxResult::success($result);
    }

    public function inventoryTurnover(Request $request)
    {
        $service = new BizWmsReportService();
        $params = convert_to_snake_case($request->all());
        $result = $service->inventoryTurnover($params);
        return AjaxResult::success($result);
    }

    public function productFlow(Request $request)
    {
        $service = new BizWmsReportService();
        $params = convert_to_snake_case($request->all());
        $result = $service->productFlow($params);
        return AjaxResult::success($result);
    }
}
