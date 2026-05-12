<?php

namespace app\controller\business;

use support\Request;
use app\service\BizRepaymentService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizRepaymentController
{
    public function list(Request $request)
    {
        $service = new BizRepaymentService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectRepaymentList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $repaymentId = intval(end($parts));
        $service = new BizRepaymentService();
        $record = $service->selectRepaymentById($repaymentId);
        if (!$record) {
            return AjaxResult::error('还款记录不存在');
        }
        return AjaxResult::success($record);
    }

    public function owedPackages(Request $request)
    {
        $customerId = $request->get('customerId') ?? $request->get('customer_id');
        if (!$customerId) {
            return AjaxResult::error('客户ID不能为空');
        }
        $service = new BizRepaymentService();
        $list = $service->selectOwedPackageList($customerId);
        return AjaxResult::success($list);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        
        if (empty($data['customer_id'])) {
            return AjaxResult::error('客户ID不能为空');
        }
        if (empty($data['package_id'])) {
            return AjaxResult::error('套餐ID不能为空');
        }
        if (empty($data['repayment_amount']) || $data['repayment_amount'] <= 0) {
            return AjaxResult::error('还款金额必须大于0');
        }
        
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $data['creator_user_id'] = $request->loginUser->user->user_id ?? 0;
        $data['creator_user_name'] = $request->loginUser->user->real_name ?? $request->loginUser->user->user_name ?? '';
        
        $service = new BizRepaymentService();
        
        try {
            $result = $service->insertRepayment($data);
            return AjaxResult::success($result);
        } catch (\Exception $e) {
            return AjaxResult::error('添加还款记录失败：' . $e->getMessage());
        }
    }

    public function audit(Request $request)
    {
        $repaymentId = $request->post('repaymentId') ?? $request->post('repayment_id');
        if (!$repaymentId) {
            return AjaxResult::error('还款ID不能为空');
        }
        
        $auditBy = $request->loginUser->user->real_name ?? $request->loginUser->user->user_name ?? '';
        $service = new BizRepaymentService();
        
        try {
            $result = $service->auditRepayment($repaymentId, $auditBy);
            if ($result) {
                return AjaxResult::success('审核成功');
            } else {
                return AjaxResult::error('审核失败，该记录可能已被审核或不存在');
            }
        } catch (\Exception $e) {
            return AjaxResult::error('审核失败：' . $e->getMessage());
        }
    }

    public function cancel(Request $request)
    {
        $repaymentId = $request->post('repaymentId') ?? $request->post('repayment_id');
        if (!$repaymentId) {
            return AjaxResult::error('还款ID不能为空');
        }
        
        $service = new BizRepaymentService();
        $result = $service->cancelRepayment($repaymentId);
        
        if ($result) {
            return AjaxResult::success('取消成功');
        } else {
            return AjaxResult::error('取消失败，该记录可能已被审核或不存在');
        }
    }
}
