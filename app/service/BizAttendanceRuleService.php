<?php

namespace app\service;

use app\model\BizAttendanceRule;

class BizAttendanceRuleService
{
    public function selectRuleList($params = [])
    {
        $query = BizAttendanceRule::query();

        if (!empty($params['rule_name'])) {
            $query->where('rule_name', 'like', '%' . $params['rule_name'] . '%');
        }
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->orderBy('rule_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectRuleById($ruleId)
    {
        return BizAttendanceRule::find($ruleId);
    }

    public function insertRule($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return BizAttendanceRule::create($data);
    }

    public function updateRule($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return BizAttendanceRule::where('rule_id', $data['rule_id'])->update($data);
    }

    public function deleteRuleByIds($ruleIds)
    {
        return BizAttendanceRule::whereIn('rule_id', $ruleIds)->delete();
    }

    public function getActiveRule()
    {
        return BizAttendanceRule::where('status', '0')->first();
    }
}
