<?php

namespace app\service;

use app\model\SysDept;

class DataScopeService
{
    public static function applyDataScope($query, $loginUser, $deptAlias = 'd', $userAlias = 'u')
    {
        if ($loginUser->isAdmin()) {
            return $query;
        }

        $roles = $loginUser->user ? $loginUser->user->roles : [];
        if (empty($roles)) {
            return $query->whereRaw('1 = 0');
        }

        $conditions = [];
        foreach ($roles as $role) {
            $dataScope = $role['data_scope'] ?? '1';
            $roleId = $role['role_id'] ?? 0;

            switch ($dataScope) {
                case '1':
                    return $query;
                case '2':
                    $deptIds = SysDept::join('sys_role_dept', 'sys_dept.dept_id', '=', 'sys_role_dept.dept_id')
                        ->where('sys_role_dept.role_id', $roleId)
                        ->pluck('sys_dept.dept_id')
                        ->toArray();
                    $conditions[] = [$deptAlias . '.dept_id', 'in', $deptIds];
                    break;
                case '3':
                    $conditions[] = [$deptAlias . '.dept_id', '=', $loginUser->deptId];
                    break;
                case '4':
                    $dept = SysDept::find($loginUser->deptId);
                    if ($dept) {
                        $conditions[] = function ($q) use ($deptAlias, $dept, $loginUser) {
                            $q->where($deptAlias . '.dept_id', $loginUser->deptId)
                              ->orWhere($deptAlias . '.dept_id', 'in', function ($subQ) use ($dept) {
                                  $subQ->select('dept_id')->from('sys_dept')
                                       ->whereRaw("find_in_set(?, ancestors)", [$dept->dept_id]);
                              });
                        };
                    }
                    break;
                case '5':
                    $conditions[] = [$userAlias . '.user_id', '=', $loginUser->userId];
                    break;
            }
        }

        if (!empty($conditions)) {
            $query->where(function ($q) use ($conditions) {
                foreach ($conditions as $condition) {
                    if (is_callable($condition)) {
                        $condition($q);
                    } elseif ($condition[1] === 'in') {
                        $q->orWhereIn($condition[0], $condition[2]);
                    } else {
                        $q->orWhere($condition[0], $condition[1], $condition[2]);
                    }
                }
            });
        }

        return $query;
    }
}
