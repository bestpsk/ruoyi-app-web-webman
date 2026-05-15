<?php

namespace app\service;

use app\model\BizEmployeeConfig;
use support\Db;

class BizEmployeeConfigService
{
    public function selectConfigList($params = [])
    {
        $query = BizEmployeeConfig::query()
            ->leftJoin('sys_user_post as up', 'biz_employee_config.user_id', '=', 'up.user_id')
            ->leftJoin('sys_post as p', 'up.post_id', '=', 'p.post_id')
            ->leftJoin('sys_user as su', 'biz_employee_config.user_id', '=', 'su.user_id')
            ->select('biz_employee_config.*', 'p.post_id', 'p.post_name', 'su.real_name', 'su.phonenumber');

        if (!empty($params['user_name'])) {
            $query->where('biz_employee_config.user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['dept_name'])) {
            $query->where('biz_employee_config.dept_name', 'like', '%' . $params['dept_name'] . '%');
        }
        if (isset($params['is_schedulable'])) {
            $query->where('biz_employee_config.is_schedulable', $params['is_schedulable']);
        }
        if (!empty($params['status'])) {
            $query->where('biz_employee_config.status', $params['status']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        $result = $query->orderBy('biz_employee_config.config_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);

        foreach ($result->items() as $item) {
            if (empty($item->post_name)) {
                $postInfo = Db::table('sys_user_post as up')
                    ->join('sys_post as p', 'up.post_id', '=', 'p.post_id')
                    ->where('up.user_id', $item->user_id)
                    ->first();
                if ($postInfo) {
                    $item->post_id = $postInfo->post_id;
                    $item->post_name = $postInfo->post_name;
                }
            }
            if ($item->rest_dates) {
                $item->rest_dates = json_decode($item->rest_dates, true) ?: [];
            } else {
                $item->rest_dates = [];
            }
        }

        return $result;
    }

    public function selectConfigById($configId)
    {
        return BizEmployeeConfig::find($configId);
    }

    public function selectConfigByUserId($userId)
    {
        return BizEmployeeConfig::where('user_id', $userId)->first();
    }

    public function insertConfig($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return BizEmployeeConfig::create($data);
    }

    public function updateConfig($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return BizEnterprise::where('config_id', $data['config_id'])->update($data);
    }

    public function updateSchedulable($userId, $isSchedulable)
    {
        return BizEmployeeConfig::where('user_id', $userId)->update([
            'is_schedulable' => $isSchedulable,
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateRestDates($userId, $restDates)
    {
        return BizEmployeeConfig::where('user_id', $userId)->update([
            'rest_dates' => json_encode($restDates, JSON_UNESCAPED_UNICODE),
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function deleteConfigByIds($configIds)
    {
        return BizEmployeeConfig::whereIn('config_id', $configIds)->delete();
    }

    public function getRestDatesByUserId($userId)
    {
        $config = $this->selectConfigByUserId($userId);
        if ($config && $config->rest_dates) {
            return json_decode($config->rest_dates, true) ?: [];
        }
        return [];
    }

    public function isRestDate($userId, $date)
    {
        $restDates = $this->getRestDatesByUserId($userId);
        return in_array($date, $restDates);
    }

    public function searchEmployee($keyword = '')
    {
        $query = BizEmployeeConfig::query()
            ->leftJoin('sys_user_post as up', 'biz_employee_config.user_id', '=', 'up.user_id')
            ->leftJoin('sys_post as p', 'up.post_id', '=', 'p.post_id')
            ->select('biz_employee_config.user_id', 'biz_employee_config.user_name', 'biz_employee_config.dept_name', 'p.post_name')
            ->where('biz_employee_config.status', '0');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('biz_employee_config.user_name', 'like', '%' . $keyword . '%')
                  ->orWhere('biz_employee_config.dept_name', 'like', '%' . $keyword . '%');
            });
        }

        return $query->orderBy('biz_employee_config.user_id', 'desc')->limit(50)->get();
    }
}
