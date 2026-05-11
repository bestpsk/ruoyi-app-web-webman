<?php

namespace app\controller\wms;

use support\Request;
use app\service\BizProductService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizProductController
{
    public function list(Request $request)
    {
        $service = new BizProductService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectProductList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $productId = intval(end($parts));
        $service = new BizProductService();
        $product = $service->selectProductById($productId);
        if (!$product) return AjaxResult::error('货品不存在');
        return AjaxResult::success($product);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $service = new BizProductService();
        $list = $service->searchProduct($keyword);
        return AjaxResult::success($list);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizProductService();
        $result = $service->insertProduct($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizProductService();
        $result = $service->updateProduct($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $productIds = explode(',', $request->input('productIds', ''));
        $productIds = array_map('intval', array_filter($productIds));
        $service = new BizProductService();
        $result = $service->deleteProductByIds($productIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
