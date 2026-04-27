<?php

namespace app\service;

use app\model\SysDept;

class SysDeptService
{
    public function selectDeptList($params = [])
    {
        $query = SysDept::where('del_flag', '0');

        if (!empty($params['dept_name'])) {
            $query->where('dept_name', 'like', '%' . $params['dept_name'] . '%');
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $query->orderBy('order_num', 'asc');
        return $query->get();
    }

    public function selectDeptById($deptId)
    {
        return SysDept::where('del_flag', '0')->find($deptId);
    }

    public function insertDept($data)
    {
        $parentDept = SysDept::find($data['parent_id'] ?? 0);
        $ancestors = '0';
        if ($parentDept) {
            $ancestors = $parentDept->ancestors . ',' . $parentDept->dept_id;
        }
        $data['ancestors'] = $ancestors;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['del_flag'] = '0';
        return SysDept::create($data);
    }

    public function updateDept($data)
    {
        $deptId = $data['dept_id'];
        $parentDept = SysDept::find($data['parent_id'] ?? 0);
        if ($parentDept) {
            $data['ancestors'] = $parentDept->ancestors . ',' . $parentDept->dept_id;
        }
        $data['update_time'] = date('Y-m-d H:i:s');
        return SysDept::where('dept_id', $deptId)->update($data);
    }

    public function deleteDeptById($deptId)
    {
        $hasChildren = SysDept::where('parent_id', $deptId)->where('del_flag', '0')->exists();
        if ($hasChildren) {
            return false;
        }
        $hasUsers = \app\model\SysUser::where('dept_id', $deptId)->where('del_flag', '0')->exists();
        if ($hasUsers) {
            return false;
        }
        return SysDept::where('dept_id', $deptId)->update(['del_flag' => '2']);
    }

    public function deptTreeSelect()
    {
        $depts = SysDept::where('del_flag', '0')->orderBy('order_num', 'asc')->get();
        return $this->buildDeptTree($depts, 0);
    }

    public function excludeChildDeptList($deptId)
    {
        $depts = SysDept::where('del_flag', '0')->orderBy('order_num', 'asc')->get();
        $result = [];
        foreach ($depts as $dept) {
            if ($dept->dept_id == $deptId) continue;
            if (strpos($dept->ancestors, (string)$deptId) === false) {
                $result[] = $dept;
            }
        }
        return $result;
    }

    public function buildDeptTree($depts, $parentId)
    {
        $tree = [];
        foreach ($depts as $dept) {
            if ($dept['parent_id'] == $parentId) {
                $node = $dept->toArray();
                $node['children'] = $this->buildDeptTree($depts, $dept['dept_id']);
                $tree[] = $node;
            }
        }
        return $tree;
    }

    public function buildDeptTreeSelect($depts, $parentId)
    {
        $tree = [];
        foreach ($depts as $dept) {
            if ($dept['parent_id'] == $parentId) {
                $node = [
                    'id' => $dept['dept_id'],
                    'label' => $dept['dept_name'],
                    'children' => $this->buildDeptTreeSelect($depts, $dept['dept_id']),
                ];
                if (empty($node['children'])) {
                    unset($node['children']);
                }
                $tree[] = $node;
            }
        }
        return $tree;
    }
}
