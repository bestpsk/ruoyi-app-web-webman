<?php

namespace app\service;

use app\model\SysMenu;
use app\common\Constants;

class SysMenuService
{
    public function selectMenuList($params = [], $userId = null)
    {
        $query = SysMenu::where('status', '0');

        if (!empty($params['menu_name'])) {
            $query->where('menu_name', 'like', '%' . $params['menu_name'] . '%');
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $query->orderBy('order_num', 'asc');

        if ($userId && $userId !== 1) {
            $menuIds = SysMenu::join('sys_role_menu', 'sys_menu.menu_id', '=', 'sys_role_menu.menu_id')
                ->join('sys_user_role', 'sys_role_menu.role_id', '=', 'sys_user_role.role_id')
                ->where('sys_user_role.user_id', $userId)
                ->where('sys_menu.status', '0')
                ->pluck('sys_menu.menu_id')
                ->toArray();
            $query->whereIn('menu_id', $menuIds);
        }

        return $query->get();
    }

    public function selectMenuTreeByUserId($userId)
    {
        if ($userId === 1) {
            $menus = SysMenu::where('menu_type', '!=', 'F')
                ->where('status', '0')
                ->orderBy('order_num', 'asc')
                ->get();
        } else {
            $menus = SysMenu::join('sys_role_menu', 'sys_menu.menu_id', '=', 'sys_role_menu.menu_id')
                ->join('sys_user_role', 'sys_role_menu.role_id', '=', 'sys_user_role.role_id')
                ->join('sys_role', 'sys_user_role.role_id', '=', 'sys_role.role_id')
                ->where('sys_user_role.user_id', $userId)
                ->where('sys_menu.status', '0')
                ->where('sys_role.status', '0')
                ->where('sys_menu.menu_type', '!=', 'F')
                ->select('sys_menu.*')
                ->orderBy('sys_menu.order_num', 'asc')
                ->get()
                ->unique('menu_id');
        }

        return $this->getChildPerms($menus, 0);
    }

    public function selectMenuById($menuId)
    {
        return SysMenu::find($menuId);
    }

    public function insertMenu($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return SysMenu::create($data);
    }

    public function updateMenu($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return SysMenu::where('menu_id', $data['menu_id'])->update($data);
    }

    public function deleteMenuById($menuId)
    {
        $hasChildren = SysMenu::where('parent_id', $menuId)->exists();
        if ($hasChildren) {
            return false;
        }
        return SysMenu::where('menu_id', $menuId)->delete();
    }

    public function treeselect()
    {
        $menus = SysMenu::where('status', '0')->orderBy('order_num', 'asc')->get();
        return $this->buildMenuTree($menus, 0);
    }

    public function roleMenuTreeselect($roleId)
    {
        $menus = SysMenu::where('status', '0')->orderBy('order_num', 'asc')->get();
        $checkedKeys = \app\model\SysRoleMenu::where('role_id', $roleId)->pluck('menu_id')->toArray();

        return [
            'menus' => $this->buildMenuTree($menus, 0),
            'checkedKeys' => $checkedKeys,
        ];
    }

    public function buildMenus($menus)
    {
        $routers = [];
        foreach ($menus as $menu) {
            $router = [
                'hidden' => $menu['visible'] === '1',
                'name' => $this->getRouteName($menu),
                'path' => $this->getRouterPath($menu),
                'component' => $this->getComponent($menu),
                'query' => $menu['query'] ?? '',
                'meta' => [
                    'title' => $menu['menu_name'],
                    'icon' => $menu['icon'] ?: '#',
                    'noCache' => ($menu['is_cache'] ?? '0') === '1',
                    'link' => ($menu['is_frame'] ?? 1) === 0 ? $menu['path'] : null,
                ],
            ];

            $children = $menu['children'] ?? [];

            if (!empty($children) && $menu['menu_type'] === Constants::MENU_TYPE_DIR) {
                $router['alwaysShow'] = true;
                $router['redirect'] = 'noRedirect';
                $router['children'] = $this->buildMenus($children);
            } elseif ($this->isMenuFrame($menu)) {
                $router['meta'] = null;
                $childRouter = [
                    'path' => $menu['path'],
                    'component' => $menu['component'],
                    'name' => $this->getRouteName($menu),
                    'query' => $menu['query'] ?? '',
                    'meta' => [
                        'title' => $menu['menu_name'],
                        'icon' => $menu['icon'] ?: '#',
                        'noCache' => ($menu['is_cache'] ?? '0') === '1',
                        'link' => null,
                    ],
                ];
                $router['children'] = [$childRouter];
            } elseif ($menu['parent_id'] == 0 && $this->isInnerLink($menu)) {
                $router['meta'] = [
                    'title' => $menu['menu_name'],
                    'icon' => $menu['icon'] ?: '#',
                ];
                $router['path'] = '/';
                $routerPath = $this->innerLinkReplaceEach($menu['path']);
                $childRouter = [
                    'path' => $routerPath,
                    'component' => Constants::INNER_LINK,
                    'name' => ucfirst($routerPath),
                    'meta' => [
                        'title' => $menu['menu_name'],
                        'icon' => $menu['icon'] ?: '#',
                        'link' => $menu['path'],
                    ],
                ];
                $router['children'] = [$childRouter];
            }

            $routers[] = $router;
        }
        return $routers;
    }

    private function getChildPerms($menus, $parentId)
    {
        $result = [];
        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $menuArr = $menu->toArray();
                $menuArr['children'] = $this->getChildPerms($menus, $menu['menu_id']);
                $result[] = $menuArr;
            }
        }
        return $result;
    }

    private function buildMenuTree($menus, $parentId)
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $node = [
                    'id' => $menu['menu_id'],
                    'label' => $menu['menu_name'],
                    'children' => $this->buildMenuTree($menus, $menu['menu_id']),
                ];
                if (empty($node['children'])) {
                    unset($node['children']);
                }
                $tree[] = $node;
            }
        }
        return $tree;
    }

    private function getRouteName($menu)
    {
        $routeName = $menu['route_name'] ?? '';
        if (empty($routeName)) {
            $path = $menu['path'] ?? '';
            if ($this->isExternalLink($path)) {
                $routeName = 'Menu' . $menu['menu_id'];
            } else {
                $routeName = ucfirst($path);
            }
        }
        return $routeName;
    }

    private function isExternalLink($path)
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    private function getRouterPath($menu)
    {
        $routerPath = $menu['path'] ?? '';
        if ($menu['parent_id'] != 0 && $this->isInnerLink($menu)) {
            $routerPath = $this->innerLinkReplaceEach($routerPath);
        }
        if ($menu['parent_id'] == 0 && $menu['menu_type'] === Constants::MENU_TYPE_DIR && $menu['is_frame'] == 1) {
            $routerPath = '/' . $menu['path'];
        } elseif ($this->isMenuFrame($menu)) {
            $routerPath = '/';
        }
        return $routerPath;
    }

    private function getComponent($menu)
    {
        $component = Constants::LAYOUT;
        if (!empty($menu['component']) && !$this->isMenuFrame($menu)) {
            $component = $menu['component'];
        } elseif (empty($menu['component']) && $menu['parent_id'] != 0 && $this->isInnerLink($menu)) {
            $component = Constants::INNER_LINK;
        } elseif (empty($menu['component']) && $menu['parent_id'] != 0 && $menu['menu_type'] === Constants::MENU_TYPE_DIR) {
            $component = Constants::PARENT_VIEW;
        }
        return $component;
    }

    private function isMenuFrame($menu)
    {
        return $menu['parent_id'] == 0 && $menu['menu_type'] === Constants::MENU_TYPE_MENU && $menu['is_frame'] == 1;
    }

    private function isInnerLink($menu)
    {
        $path = $menu['path'] ?? '';
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    private function innerLinkReplaceEach($path)
    {
        return str_replace(
            ['http://', 'https://', 'www.', '.', ':'],
            ['', '', '', '/', '/'],
            $path
        );
    }
}
