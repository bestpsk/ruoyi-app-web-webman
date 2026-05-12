<?php

namespace app\common;

use app\model\SysUser;

class LoginUser
{
    public $userId = 0;
    public $deptId = 0;
    public $token = '';
    public $loginTime = 0;
    public $expireTime = 0;
    public $ipaddr = '';
    public $loginLocation = '';
    public $browser = '';
    public $os = '';
    public $permissions = [];
    public $user = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function isAdmin()
    {
        return $this->userId === 1 || ($this->user && method_exists($this->user, 'isAdmin') && $this->user->isAdmin());
    }

    public function toArray()
    {
        return [
            'userId' => $this->userId,
            'deptId' => $this->deptId,
            'token' => $this->token,
            'loginTime' => $this->loginTime,
            'expireTime' => $this->expireTime,
            'ipaddr' => $this->ipaddr,
            'loginLocation' => $this->loginLocation,
            'browser' => $this->browser,
            'os' => $this->os,
            'permissions' => is_array($this->permissions) ? array_values($this->permissions) : [],
            'user' => $this->user ? $this->user->toArray() : null,
        ];
    }

    public static function fromArray(array $data)
    {
        $loginUser = new self();
        foreach ($data as $key => $value) {
            if (property_exists($loginUser, $key)) {
                $loginUser->$key = $value;
            }
        }

        if (is_array($loginUser->user) && !empty($loginUser->user)) {
            $userModel = new SysUser();
            $userModel->setRawAttributes($loginUser->user, true);
            $userModel->exists = !empty($loginUser->user['user_id']);
            $loginUser->user = $userModel;
        }

        return $loginUser;
    }
}
