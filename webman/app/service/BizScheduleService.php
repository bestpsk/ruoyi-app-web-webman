<?php

namespace app\service;

use app\model\BizSchedule;
use app\model\SysUser;
use app\model\BizEnterprise;
use support\Db;

class BizScheduleService
{
    public function selectScheduleList($params = [])
    {
        $query = BizSchedule::query();

        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['enterprise_id'])) {
            $query->where('enterprise_id', $params['enterprise_id']);
        }
        if (!empty($params['enterprise_name'])) {
            $query->where('enterprise_name', 'like', '%' . $params['enterprise_name'] . '%');
        }
        if (!empty($params['schedule_date'])) {
            $query->where('schedule_date', $params['schedule_date']);
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $query->whereBetween('schedule_date', [$params['start_date'], $params['end_date']]);
        }
        if (!empty($params['purpose'])) {
            $query->where('purpose', $params['purpose']);
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        $result = $query->orderBy('schedule_date', 'desc')->orderBy('schedule_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);

        foreach ($result->items() as $item) {
            $user = SysUser::find($item->user_id);
            if ($user) {
                $item->user_name = $user->real_name ?? $user->user_name;
                $postInfo = Db::table('sys_user_post as up')
                    ->join('sys_post as p', 'up.post_id', '=', 'p.post_id')
                    ->where('up.user_id', $user->user_id)
                    ->first();
                $item->post_name = $postInfo->post_name ?? '';
            }
        }

        return $result;
    }

    public function selectScheduleById($scheduleId)
    {
        return BizSchedule::find($scheduleId);
    }

    public function selectScheduleByDateRange($params)
    {
        $query = BizSchedule::query();

        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $query->whereBetween('schedule_date', [$params['start_date'], $params['end_date']]);
        }
        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        if (!empty($params['enterprise_id'])) {
            $query->where('enterprise_id', $params['enterprise_id']);
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        } else {
            $query->where('status', '0');
        }

        return $query->get();
    }

    public function selectScheduleDates($params)
    {
        $query = BizSchedule::query();

        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        if (!empty($params['year_month'])) {
            $query->where('schedule_date', 'like', $params['year_month'] . '%');
        }

        return $query->pluck('schedule_date')->toArray();
    }

    public function insertSchedule($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return BizSchedule::create($data);
    }

    public function insertScheduleBatch($dataList)
    {
        $insertData = [];
        $createTime = date('Y-m-d H:i:s');
        
        foreach ($dataList as $item) {
            $item['create_time'] = $createTime;
            $insertData[] = $item;
        }

        return BizSchedule::insert($insertData);
    }

    public function updateSchedule($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return BizSchedule::where('schedule_id', $data['schedule_id'])->update($data);
    }

    public function deleteScheduleByIds($scheduleIds)
    {
        return BizSchedule::whereIn('schedule_id', $scheduleIds)->delete();
    }

    public function selectEmployeeSchedule($params)
    {
        $startDate = $params['start_date'] ?? date('Y-m-01');
        $endDate = $params['end_date'] ?? date('Y-m-t');
        
        $userQuery = SysUser::query();
        if (!empty($params['user_name'])) {
            $userQuery->where('user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['status'])) {
            $userQuery->where('status', $params['status']);
        } else {
            $userQuery->where('status', '0');
        }
        
        $users = $userQuery->get();
        
        $scheduleQuery = BizSchedule::query();
        $scheduleQuery->whereBetween('schedule_date', [$startDate, $endDate]);
        if (!empty($params['enterprise_name'])) {
            $scheduleQuery->where('enterprise_name', 'like', '%' . $params['enterprise_name'] . '%');
        }
        if (!empty($params['purpose'])) {
            $scheduleQuery->where('purpose', $params['purpose']);
        }
        
        $schedules = $scheduleQuery->get();
        
        $result = [];
        foreach ($users as $user) {
            $userSchedules = $schedules->where('user_id', $user->user_id);
            $scheduleMap = [];
            
            foreach ($userSchedules as $schedule) {
                $day = date('j', strtotime($schedule->schedule_date));
                $scheduleMap[$day] = $schedule;
            }
            
            $postInfo = Db::table('sys_user_post as up')
                ->join('sys_post as p', 'up.post_id', '=', 'p.post_id')
                ->where('up.user_id', $user->user_id)
                ->first();
            
            $result[] = [
                'user_id' => $user->user_id,
                'user_name' => $user->real_name ?? $user->user_name,
                'post_name' => $postInfo->post_name ?? '',
                'schedules' => $scheduleMap
            ];
        }
        
        return $result;
    }

    public function selectEnterpriseSchedule($params)
    {
        $startDate = $params['start_date'] ?? date('Y-m-01');
        $endDate = $params['end_date'] ?? date('Y-m-t');
        
        $enterpriseQuery = BizEnterprise::query();
        if (!empty($params['enterprise_name'])) {
            $enterpriseQuery->where('enterprise_name', 'like', '%' . $params['enterprise_name'] . '%');
        }
        if (!empty($params['status'])) {
            $enterpriseQuery->where('status', $params['status']);
        } else {
            $enterpriseQuery->where('status', '0');
        }
        
        $enterprises = $enterpriseQuery->get();
        
        $scheduleQuery = BizSchedule::query();
        $scheduleQuery->whereBetween('schedule_date', [$startDate, $endDate]);
        if (!empty($params['user_name'])) {
            $scheduleQuery->where('user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['purpose'])) {
            $scheduleQuery->where('purpose', $params['purpose']);
        }
        
        $schedules = $scheduleQuery->get();

        foreach ($schedules as $schedule) {
            $user = SysUser::find($schedule->user_id);
            if ($user) {
                $schedule->user_name = $user->real_name ?? $user->user_name;
            }
        }
        
        $result = [];
        foreach ($enterprises as $enterprise) {
            $enterpriseSchedules = $schedules->where('enterprise_id', $enterprise->enterprise_id);
            $scheduleMap = [];
            
            foreach ($enterpriseSchedules as $schedule) {
                $day = date('j', strtotime($schedule->schedule_date));
                $scheduleMap[$day] = $schedule;
            }
            
            $result[] = [
                'enterprise_id' => $enterprise->enterprise_id,
                'enterprise_name' => $enterprise->enterprise_name,
                'schedules' => $scheduleMap
            ];
        }
        
        return $result;
    }
}
