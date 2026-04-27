<?php

namespace app\service;

use app\model\SysOperLog;

class SysOperLogService
{
    public function selectOperLogList($params = [])
    {
        $query = SysOperLog::query();

        if (!empty($params['title'])) {
            $query->where('title', 'like', '%' . $params['title'] . '%');
        }
        if (isset($params['business_type']) && $params['business_type'] !== '') {
            $query->where('business_type', $params['business_type']);
        }
        if (!empty($params['oper_name'])) {
            $query->where('oper_name', 'like', '%' . $params['oper_name'] . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (!empty($params['begin_time'])) {
            $query->where('oper_time', '>=', $params['begin_time']);
        }
        if (!empty($params['end_time'])) {
            $query->where('oper_time', '<=', $params['end_time']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->orderBy('oper_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function insertOperLog($data)
    {
        return SysOperLog::create($data);
    }

    public function deleteOperLogByIds($operIds)
    {
        return SysOperLog::whereIn('oper_id', $operIds)->delete();
    }

    public function cleanOperLog()
    {
        return SysOperLog::truncate();
    }
}
