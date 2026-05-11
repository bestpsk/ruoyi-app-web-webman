<?php

namespace app\service;

use app\model\SysJob;
use app\model\SysJobLog;

class SysJobService
{
    public function selectJobList($params = [])
    {
        $query = SysJob::query();

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
        return $query->orderBy('job_id', 'asc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectJobById($jobId)
    {
        return SysJob::find($jobId);
    }

    public function insertJob($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return SysJob::create($data);
    }

    public function updateJob($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return SysJob::where('job_id', $data['job_id'])->update($data);
    }

    public function deleteJobByIds($jobIds)
    {
        return SysJob::whereIn('job_id', $jobIds)->delete();
    }

    public function changeStatus($jobId, $status)
    {
        return SysJob::where('job_id', $jobId)->update([
            'status' => $status,
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    public function run($job)
    {
        $jobLog = new SysJobLog();
        $jobLog->job_name = $job->job_name;
        $jobLog->job_group = $job->job_group;
        $jobLog->invoke_target = $job->invoke_target;
        $jobLog->job_message = $job->job_name . ' 总共耗时：0毫秒';
        $jobLog->status = '0';
        $jobLog->start_time = date('Y-m-d H:i:s');
        $jobLog->end_time = date('Y-m-d H:i:s');
        $jobLog->create_time = date('Y-m-d H:i:s');
        $jobLog->save();
        return true;
    }
}
