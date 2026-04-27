<?php

namespace app\controller\system;

use support\Request;
use app\service\SysNoticeService;
use app\common\AjaxResult;
use app\common\TableDataInfo;

class SysNoticeController
{
    public function list(Request $request)
    {
        $service = new SysNoticeService();
        $result = $service->selectNoticeList($request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }

    public function getInfo(Request $request)
    {
        $parts = explode('/', $request->path());
        $noticeId = intval(end($parts));
        $service = new SysNoticeService();
        $notice = $service->selectNoticeById($noticeId);
        if (!$notice) return AjaxResult::error('公告不存在');
        return AjaxResult::success($notice);
    }

    public function add(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['create_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysNoticeService();
        return AjaxResult::toAjax($service->insertNotice($data) ? 1 : 0);
    }

    public function edit(Request $request)
    {
        $data = convert_to_snake_case($request->post());
        $data['update_by'] = $request->loginUser->user->user_name ?? '';
        $service = new SysNoticeService();
        return AjaxResult::toAjax($service->updateNotice($data) ? 1 : 0);
    }

    public function remove(Request $request)
    {
        $noticeIds = explode(',', $request->input('noticeIds', ''));
        $noticeIds = array_map('intval', array_filter($noticeIds));
        $service = new SysNoticeService();
        return AjaxResult::toAjax($service->deleteNoticeByIds($noticeIds) ? 1 : 0);
    }

    public function listTop(Request $request)
    {
        $service = new SysNoticeService();
        return AjaxResult::success($service->listTop($request->loginUser->userId));
    }

    public function markRead(Request $request)
    {
        $noticeId = $request->post('noticeId');
        $service = new SysNoticeService();
        return AjaxResult::toAjax($service->markRead($request->loginUser->userId, $noticeId) ? 1 : 0);
    }

    public function markReadAll(Request $request)
    {
        $service = new SysNoticeService();
        return AjaxResult::toAjax($service->markReadAll($request->loginUser->userId) ? 1 : 0);
    }

    public function readUsersList(Request $request)
    {
        $parts = explode('/', $request->path());
        $noticeId = 0;
        foreach ($parts as $i => $p) {
            if ($p === 'readUsers' && isset($parts[$i + 1]) && $parts[$i + 1] === 'list') {
                $noticeId = intval($parts[$i - 1] ?? 0);
                break;
            }
        }
        $service = new SysNoticeService();
        $result = $service->readUsersList($noticeId, $request->all());
        return TableDataInfo::result($result->items(), $result->total());
    }
}
