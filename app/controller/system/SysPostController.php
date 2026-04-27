<?php

namespace app\controller\system;

use support\Request;
use app\service\SysPostService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysPostController
{
    public function list(Request $request)
    {
        $service = new SysPostService();
        $result = $service->selectPostList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $postId = intval(end($parts));
        $service = new SysPostService();
        $post = $service->selectPostById($postId);
        if (!$post) return AjaxResult::error('岗位不存在');
        return AjaxResult::success($post);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysPostService();
        $result = $service->insertPost($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysPostService();
        $result = $service->updatePost($data);
        return AjaxResult::toAjax($result ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $postIds = explode(',', $request->input('postIds', ''));
        $postIds = array_map('intval', array_filter($postIds));
        $service = new SysPostService();
        $result = $service->deletePostByIds($postIds);
        return AjaxResult::toAjax($result ? 1 : 0);
    }
}
