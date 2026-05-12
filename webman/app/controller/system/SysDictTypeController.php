<?php

namespace app\controller\system;

use support\Request;
use app\service\SysDictTypeService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysDictTypeController
{
    public function list(Request $request)
    {
        $service = new SysDictTypeService();
        $params = convert_to_snake_case($request->all());
        $result = $service->selectDictTypeList($params);
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $dictId = intval(end($parts));
        $service = new SysDictTypeService();
        $dict = $service->selectDictTypeById($dictId);
        if (!$dict) return AjaxResult::error('字典类型不存在');
        return AjaxResult::success($dict);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysDictTypeService();
        $result = $service->insertDictType($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysDictTypeService();
        $result = $service->updateDictType($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $dictIds = explode(',', $request->input('dictIds', ''));
        $dictIds = array_map('intval', array_filter($dictIds));
        $service = new SysDictTypeService();
        $result = $service->deleteDictTypeByIds($dictIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function refreshCache(Request $request)
    {
        $service = new SysDictTypeService();
        $service->resetDictCache();
        return AjaxResult::success();
    }

    public function optionselect(Request $request)
    {
        $service = new SysDictTypeService();
        return AjaxResult::success($service->optionselect());
    }
}
