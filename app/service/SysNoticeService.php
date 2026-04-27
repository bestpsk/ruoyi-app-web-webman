<?php

namespace app\service;

use app\model\SysNotice;
use app\model\SysNoticeRead;

class SysNoticeService
{
    public function selectNoticeList($params = [])
    {
        $query = SysNotice::query();

        if (!empty($params['notice_title'])) {
            $query->where('notice_title', 'like', '%' . $params['notice_title'] . '%');
        }
        if (!empty($params['notice_type'])) {
            $query->where('notice_type', $params['notice_type']);
        }
        if (!empty($params['create_by'])) {
            $query->where('create_by', 'like', '%' . $params['create_by'] . '%');
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->orderBy('notice_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectNoticeById($noticeId)
    {
        return SysNotice::find($noticeId);
    }

    public function insertNotice($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return SysNotice::create($data);
    }

    public function updateNotice($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        return SysNotice::where('notice_id', $data['notice_id'])->update($data);
    }

    public function deleteNoticeByIds($noticeIds)
    {
        SysNoticeRead::whereIn('notice_id', $noticeIds)->delete();
        return SysNotice::whereIn('notice_id', $noticeIds)->delete();
    }

    public function listTop($userId)
    {
        $notices = SysNotice::where('status', '0')
            ->orderBy('create_time', 'desc')
            ->limit(10)
            ->get();

        $readIds = SysNoticeRead::where('user_id', $userId)->pluck('notice_id')->toArray();

        foreach ($notices as $notice) {
            $notice->read = in_array($notice->notice_id, $readIds);
        }

        return $notices;
    }

    public function markRead($userId, $noticeId)
    {
        $exists = SysNoticeRead::where('user_id', $userId)->where('notice_id', $noticeId)->exists();
        if (!$exists) {
            SysNoticeRead::create([
                'user_id' => $userId,
                'notice_id' => $noticeId,
                'read_time' => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    }

    public function markReadAll($userId)
    {
        $notices = SysNotice::where('status', '0')->pluck('notice_id')->toArray();
        $readIds = SysNoticeRead::where('user_id', $userId)->pluck('notice_id')->toArray();
        $unreadIds = array_diff($notices, $readIds);

        $data = [];
        foreach ($unreadIds as $noticeId) {
            $data[] = [
                'user_id' => $userId,
                'notice_id' => $noticeId,
                'read_time' => date('Y-m-d H:i:s'),
            ];
        }
        if (!empty($data)) {
            SysNoticeRead::insert($data);
        }
        return true;
    }

    public function readUsersList($noticeId, $params = [])
    {
        $query = SysNoticeRead::join('sys_user', 'sys_notice_read.user_id', '=', 'sys_user.user_id')
            ->where('sys_notice_read.notice_id', $noticeId)
            ->where('sys_user.del_flag', '0')
            ->select('sys_user.user_id', 'sys_user.user_name', 'sys_user.nick_name', 'sys_notice_read.read_time');

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->paginate($pageSize, ['*'], 'page', $pageNum);
    }
}
