<?php

namespace app\controller\business;

use support\Request;
use app\service\BizOperationRecordService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class BizOperationRecordController
{
    public function list(Request $request)
    {
        $service = new BizOperationRecordService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectRecordList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $data['operator_user_id'] = $request->loginUser->user->user_id ?? 0;
        $data['operator_user_name'] = $request->loginUser->user->real_name ?? $request->loginUser->user->user_name ?? '';
        $service = new BizOperationRecordService();
        $result = $service->insertRecord($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $recordIds = $request->input('recordIds');
        if (empty($recordIds)) {
            $parts = explode('/', $request->path());
            $recordIds = end($parts);
        }
        $recordIds = explode(',', $recordIds);
        $recordIds = array_map('intval', array_filter($recordIds));
        $service = new BizOperationRecordService();
        $result = $service->deleteRecordByIds($recordIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function getInfo($id)
    {
        $service = new BizOperationRecordService();
        $result = $service->getRecordDetailById(intval($id));
        if (!$result) {
            return AjaxResult::error('操作记录不存在');
        }
        return AjaxResult::success($result);
    }
}
