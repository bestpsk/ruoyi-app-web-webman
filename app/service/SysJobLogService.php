<?php

namespace app\service;

use app\model\SysJobLog;

class SysJobLogService
{
    public function selectJobLogList($params = [])
    {
        $query = SysJobLog::query();

        if (!empty($params['job_name'])) {
            $query->where('job_name', 'like', '%' . $params['job_name'] . '%');
        }
        if (!empty($params['job_group'])) {
            $query->where('job_group', $params['job_group']);
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (!empty($params['invoke_target'])) {
            $query->where('invoke_target', 'like', '%' . $params['invoke_target'] . '%');
        }

        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('job_log_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function deleteJobLogByIds($jobLogIds)
    {
        return SysJobLog::whereIn('job_log_id', $jobLogIds)->delete();
    }

    public function cleanJobLog()
    {
        return SysJobLog::truncate();
    }
}
