<?php

namespace app\controller\system;

use support\Request;
use app\service\SysDeptService;
use app\common\AjaxResult;

class SysDeptController
{
    public function list(Request $request)
    {
        $service = new SysDeptService();
        $params = convert_to_snake_case($request->all());
        $depts = $service->selectDeptList($params);
        return AjaxResult::success($depts);
    }

    public function excludeChild(Request $request)
    {
        $parts = explode('/', $request->path());
        $deptId = intval(end($parts));
        $service = new SysDeptService();
        return AjaxResult::success($service->excludeChildDeptList($deptId));
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $deptId = intval(end($parts));
        $service = new SysDeptService();
        $dept = $service->selectDeptById($deptId);
        if (!$dept) {
            return AjaxResult::error('部门不存在');
        }
        return AjaxResult::success($dept);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysDeptService();
        $result = $service->insertDept($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysDeptService();
        $result = $service->updateDept($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function updateSort(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        if (!empty($data['depts'])) {
            foreach ($data['depts'] as $dept) {
                $dept = convert_to_snake_case($dept);
                if (isset($dept['dept_id']) && isset($dept['order_num'])) {
                    \app\model\SysDept::where('dept_id', $dept['dept_id'])->update(['order_num' => $dept['order_num']]);
                }
            }
        }
        return AjaxResult::success();
    }

    public function remove(Request $request)
    {
        $parts = explode('/', $request->path());
        $deptId = intval(end($parts));
        $service = new SysDeptService();
        $result = $service->deleteDeptById($deptId);
        if (!$result) {
            return AjaxResult::error('存在下级部门或关联用户，不允许删除');
        }
        return AjaxResult::success();
    }

    public function treeselect(Request $request)
    {
        $service = new SysDeptService();
        $depts = $service->selectDeptList();
        $tree = $service->buildDeptTreeSelect($depts, 0);
        return AjaxResult::success($tree);
    }
}
