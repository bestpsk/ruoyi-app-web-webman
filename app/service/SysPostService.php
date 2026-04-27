<?php

namespace app\service;

use app\model\SysPost;

class SysPostService
{
    public function selectPostList($params = [])
    {
        $query = SysPost::query();

        if (!empty($params['post_code'])) {
            $query->where('post_code', 'like', '%' . $params['post_code'] . '%');
        }
        if (!empty($params['post_name'])) {
            $query->where('post_name', 'like', '%' . $params['post_name'] . '%');
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->orderBy('post_sort', 'asc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectPostById($postId)
    {
        return SysPost::find($postId);
    }

    public function selectPostAll()
    {
        return SysPost::where('status', '0')->orderBy('post_sort', 'asc')->get();
    }

    public function insertPost($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return SysPost::create($data);
    }

    public function updatePost($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return SysPost::where('post_id', $data['post_id'])->update($data);
    }

    public function deletePostByIds($postIds)
    {
        return SysPost::whereIn('post_id', $postIds)->delete();
    }
}
