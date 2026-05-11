<?php

namespace app\controller\business;

use support\Request;
use app\service\BizCustomerArchiveService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizCustomerArchiveController
{
    public function list(Request $request)
    {
        $service = new BizCustomerArchiveService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectArchiveList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());

        if (empty($data['customer_id'])) {
            return AjaxResult::error('客户ID不能为空');
        }

        $data['source_type'] = '3';
        $data['source_id'] = null;
        $data['create_by'] = $request->loginUser->user->user_name ?? '';

        $service = new BizCustomerArchiveService();
        try {
            $result = $service->insertArchive($data);
            return AjaxResult::success($result);
        } catch (\Exception $e) {
            return AjaxResult::error('新增档案失败：' . $e->getMessage());
        }
    }

    public function remove(Request $request, $archiveIds)
    {
        $service = new BizCustomerArchiveService();
        $ids = explode(',', $archiveIds);
        $result = $service->deleteArchiveByIds($ids);
        if ($result) {
            return AjaxResult::success('删除成功');
        }
        return AjaxResult::error('删除失败');
    }
}
