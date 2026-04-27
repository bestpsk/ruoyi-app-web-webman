<?php

namespace app\controller\system;

use support\Request;
use app\service\SysDictDataService;
use app\service\SysDictTypeService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysDictDataController
{
    public function list(Request $request)
    {
        $service = new SysDictDataService();
        $result = $service->selectDictDataList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $dictCode = intval(end($parts));
        $service = new SysDictDataService();
        $dict = $service->selectDictDataById($dictCode);
        if (!$dict) return AjaxResult::error('字典数据不存在');
        return AjaxResult::success($dict);
    }

    public function dictType(Request $request)
    {
        $parts = explode('/', $request->path());
        $dictType = end($parts);
        $service = new SysDictTypeService();
        $data = $service->selectDictDataByType($dictType);
        if ($data === null) {
            $data = [];
        }
        return AjaxResult::success($data);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysDictDataService();
        $result = $service->insertDictData($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysDictDataService();
        $result = $service->updateDictData($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $dictCodes = explode(',', $request->input('dictCodes', ''));
        $dictCodes = array_map('intval', array_filter($dictCodes));
        $service = new SysDictDataService();
        $result = $service->deleteDictDataByIds($dictCodes);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
