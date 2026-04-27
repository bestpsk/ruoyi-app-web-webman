<?php

namespace app\controller\system;

use support\Request;
use app\service\SysMenuService;
use app\common\AjaxResult;

class SysMenuController
{
    public function list(Request $request)
    {
        $service = new SysMenuService();
        $userId = $request->loginUser ? $request->loginUser->userId : null;
        $menus = $service->selectMenuList($request->all(), $userId);
        return AjaxResult::success($menus);
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $menuId = intval(end($parts));
        $service = new SysMenuService();
        $menu = $service->selectMenuById($menuId);
        if (!$menu) {
            return AjaxResult::error('菜单不存在');
        }
        return AjaxResult::success($menu);
    }

    public function treeselect(Request $request)
    {
        $service = new SysMenuService();
        return AjaxResult::success($service->treeselect());
    }

    public function roleMenuTreeselect(Request $request)
    {
        $parts = explode('/', $request->path());
        $roleId = intval(end($parts));
        $service = new SysMenuService();
        return AjaxResult::success($service->roleMenuTreeselect($roleId));
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysMenuService();
        $result = $service->insertMenu($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysMenuService();
        $result = $service->updateMenu($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function updateSort(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $service = new SysMenuService();
        if (!empty($data['menus'])) {
            foreach ($data['menus'] as $menu) {
                $menu = convert_to_snake_case($menu);
                if (isset($menu['menu_id']) && isset($menu['order_num'])) {
                    \app\model\SysMenu::where('menu_id', $menu['menu_id'])->update(['order_num' => $menu['order_num']]);
                }
            }
        }
        return AjaxResult::success();
    }

    public function remove(Request $request)
    {
        $parts = explode('/', $request->path());
        $menuId = intval(end($parts));
        $service = new SysMenuService();
        $result = $service->deleteMenuById($menuId);
        if (!$result) {
            return AjaxResult::error('存在子菜单,不允许删除');
        }
        return AjaxResult::success();
    }
}
