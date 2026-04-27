<?php

namespace app\common;

class Constants
{
    const SUCCESS = 200;
    const WARN = 601;
    const ERROR = 500;
    const UNAUTHORIZED = 401;

    const JWT_SECRET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    const JWT_ALGO = 'HS512';
    const TOKEN_EXPIRE = 30;
    const TOKEN_PREFIX = 'Bearer ';
    const LOGIN_USER_KEY = 'login_user_key';
    const JWT_USERNAME = 'sub';

    const LOGIN_TOKEN_KEY = 'login_tokens:';
    const CAPTCHA_CODE_KEY = 'captcha_codes:';
    const SYS_CONFIG_KEY = 'sys_config:';
    const SYS_DICT_KEY = 'sys_dict:';
    const PWD_ERR_CNT_KEY = 'pwd_err_cnt:';

    const SUPER_ADMIN_ROLE_ID = 1;
    const SUPER_ADMIN = 'admin';
    const ALL_PERMISSION = '*:*:*';

    const MENU_TYPE_DIR = 'M';
    const MENU_TYPE_MENU = 'C';
    const MENU_TYPE_BUTTON = 'F';

    const NORMAL = '0';
    const DISABLE = '1';
    const DEL_FLAG = '2';

    const LAYOUT = 'Layout';
    const PARENT_VIEW = 'ParentView';
    const INNER_LINK = 'InnerLink';

    const MILLIS_MINUTE = 60 * 1000;
    const MILLIS_MINUTE_TWENTY = 20 * 60 * 1000;

    const CAPTCHA_EXPIRE = 2;
    const PWD_ERR_MAX_COUNT = 5;
    const PWD_ERR_LOCK_TIME = 10;
}
