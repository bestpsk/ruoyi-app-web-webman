<?php

namespace app\service;

use app\model\SysUserDetail;

class SysUserDetailService
{
    public function selectDetailByUserId($userId)
    {
        return SysUserDetail::where('user_id', $userId)->first();
    }

    public function insertDetail($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        return SysUserDetail::create($data);
    }

    public function updateDetail($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        if (isset($data['detail_id'])) {
            return SysUserDetail::where('detail_id', $data['detail_id'])->update($data);
        }
        if (isset($data['user_id'])) {
            $detail = SysUserDetail::where('user_id', $data['user_id'])->first();
            if ($detail) {
                return SysUserDetail::where('user_id', $data['user_id'])->update($data);
            }
            return $this->insertDetail($data);
        }
        return false;
    }

    public function deleteDetailByUserId($userId)
    {
        return SysUserDetail::where('user_id', $userId)->delete();
    }
}
