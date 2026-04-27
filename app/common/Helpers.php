<?php

namespace app\common;

class Helpers
{
    public static function toCamelCase($key)
    {
        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }

    public static function arrayKeysToCamelCase($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = self::toCamelCase($key);
            if (is_array($value)) {
                $result[$newKey] = self::arrayKeysToCamelCase($value);
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    public static function userToCamelCase($userData)
    {
        $user = self::arrayKeysToCamelCase($userData);
        
        if (isset($user['dept']) && is_array($user['dept'])) {
            $user['dept'] = self::arrayKeysToCamelCase($user['dept']);
        }
        
        if (isset($user['roles']) && is_array($user['roles'])) {
            $user['roles'] = array_map(function ($role) {
                $r = self::arrayKeysToCamelCase($role);
                if (isset($r['pivot'])) {
                    $r['pivot'] = self::arrayKeysToCamelCase($r['pivot']);
                }
                return $r;
            }, $user['roles']);
        }
        
        if (isset($user['posts']) && is_array($user['posts'])) {
            $user['posts'] = array_map(function ($post) {
                $p = self::arrayKeysToCamelCase($post);
                if (isset($p['pivot'])) {
                    $p['pivot'] = self::arrayKeysToCamelCase($p['pivot']);
                }
                return $p;
            }, $user['posts']);
        }
        
        return $user;
    }
}
