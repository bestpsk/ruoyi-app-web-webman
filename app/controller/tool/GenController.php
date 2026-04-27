<?php

namespace app\controller\tool;

use support\Request;
use app\service\GenTableService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class GenController
{
    public function list(Request $request)
    {
        $service = new GenTableService();
        $result = $service->selectGenTableList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $tableId = intval(end($parts));
        $service = new GenTableService();
        $table = $service->selectGenTableById($tableId);
        if (!$table) return AjaxResult::error('表不存在');
        return AjaxResult::success($table);
    }

    public function dbList(Request $request)
    {
        $service = new GenTableService();
        $result = $service->selectDbTableList($request->all());
        return TableDataInfo::result($result['rows'], $result['total']);
    }

    public function importTable(Request $request)
    {
        $tableNames = $request->post('tables', '');
        if (is_string($tableNames)) {
            $tableNames = explode(',', $tableNames);
        }
        $service = new GenTableService();
        $service->importGenTable($tableNames);
        return AjaxResult::success();
    }

    public function edit(Request $request)
    {
        $data = $request->post();
        $service = new GenTableService();
        $service->updateGenTable($data);
        return AjaxResult::success();
    }

    public function remove(Request $request)
    {
        $tableIds = explode(',', $request->input('tableIds', ''));
        $tableIds = array_map('intval', array_filter($tableIds));
        $service = new GenTableService();
        return AjaxResult::toAjax($service->deleteGenTableByIds($tableIds) ? 1 : 0);
    }

    public function preview(Request $request)
    {
        $parts = explode('/', $request->path());
        $tableId = intval(end($parts));
        $service = new GenTableService();
        $data = $service->previewCode($tableId);
        return AjaxResult::success($data);
    }

    public function synchDb(Request $request)
    {
        $parts = explode('/', $request->path());
        $tableName = end($parts);
        $service = new GenTableService();
        $service->synchDb($tableName);
        return AjaxResult::success();
    }

    public function download(Request $request)
    {
        $parts = explode('/', $request->path());
        $tableName = end($parts);
        $service = new GenTableService();
        $tempFile = $service->downloadCode([$tableName]);
        return response()->download($tempFile, $tableName . '.zip')->deleteFileAfterSend(true);
    }

    public function batchGenCode(Request $request)
    {
        $tableNames = $request->input('tables', '');
        if (is_string($tableNames)) {
            $tableNames = explode(',', $tableNames);
        }
        $tableNames = array_filter($tableNames);
        if (empty($tableNames)) {
            return AjaxResult::error('请选择要生成的表');
        }
        $service = new GenTableService();
        $tempFile = $service->downloadCode($tableNames);
        return response()->download($tempFile, 'ruoyi.zip')->deleteFileAfterSend(true);
    }
}
