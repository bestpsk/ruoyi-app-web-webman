<?php

namespace app\service;

use app\model\SysLogininfor;

class SysLogininforService
{
    public function selectLogininforList($params = [])
    {
        $query = SysLogininfor::query();

        if (!empty($params['ipaddr'])) {
            $query->where('ipaddr', 'like', '%' . $params['ipaddr'] . '%');
        }
        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }
        if (!empty($params['begin_time'])) {
            $query->where('login_time', '>=', $params['begin_time']);
        }
        if (!empty($params['end_time'])) {
            $query->where('login_time', '<=', $params['end_time']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->orderBy('info_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function insertLogininfor($data)
    {
        return SysLogininfor::create($data);
    }

    public function deleteLogininforByIds($infoIds)
    {
        return SysLogininfor::whereIn('info_id', $infoIds)->delete();
    }

    public function cleanLogininfor()
    {
        return SysLogininfor::truncate();
    }

    public function unlock($userName)
    {
        $redis = \support\Redis::connection();
        $redis->del(\app\common\Constants::PWD_ERR_CNT_KEY . $userName);
        return true;
    }
}
