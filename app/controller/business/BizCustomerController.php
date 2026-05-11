<?php

namespace app\controller\business;

use support\Request;
use app\service\BizCustomerService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizCustomerController
{
    public function list(Request $request)
    {
        $service = new BizCustomerService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectCustomerList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $customerId = intval(end($parts));
        $service = new BizCustomerService();
        $customer = $service->selectCustomerById($customerId);
        if (!$customer) return AjaxResult::error('客户不存在');
        return AjaxResult::success($customer);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $enterpriseId = $request->input('enterpriseId');
        $storeId = $request->input('storeId');
        $hasDeal = $request->input('hasDeal');
        $satisfaction = $request->input('satisfaction');
        $service = new BizCustomerService();
        $result = $service->searchCustomer($keyword, $enterpriseId, $storeId, $hasDeal, $satisfaction);
        return AjaxResult::success($result);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizCustomerService();
        $result = $service->insertCustomer($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new BizCustomerService();
        $result = $service->updateCustomer($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $customerIds = $request->input('customerIds');
        if (empty($customerIds)) {
            $parts = explode('/', $request->path());
            $customerIds = end($parts);
        }
        $customerIds = explode(',', $customerIds);
        $customerIds = array_map('intval', array_filter($customerIds));
        $service = new BizCustomerService();
        $result = $service->deleteCustomerByIds($customerIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
