<?php

namespace app\controller\wms;

use support\Request;
use app\service\BizInventoryService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizInventoryController
{
    public function list(Request $request)
    {
        $service = new BizInventoryService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectInventoryList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function warn(Request $request)
    {
        $service = new BizInventoryService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectWarnList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $productId = intval(end($parts));
        $service = new BizInventoryService();
        $inventory = $service->selectInventoryByProductId($productId);
        if (!$inventory) return AjaxResult::error('库存记录不存在');
        return AjaxResult::success($inventory);
    }
}
