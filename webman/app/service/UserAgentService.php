<?php

namespace app\service;

class UserAgentService
{
    public static function getBrowser($ua)
    {
        if (empty($ua)) return 'Unknown';
        if (strpos($ua, 'MSIE') !== false || strpos($ua, 'Trident') !== false) return 'IE';
        if (strpos($ua, 'Edg') !== false) return 'Edge';
        if (strpos($ua, 'Firefox') !== false) return 'Firefox';
        if (strpos($ua, 'OPR') !== false || strpos($ua, 'Opera') !== false) return 'Opera';
        if (strpos($ua, 'Chrome') !== false) return 'Chrome';
        if (strpos($ua, 'Safari') !== false) return 'Safari';
        return 'Unknown';
    }

    public static function getOS($ua)
    {
        if (empty($ua)) return 'Unknown';
        if (strpos($ua, 'Windows') !== false) {
            if (strpos($ua, 'Windows NT 10.0') !== false) return 'Windows 10';
            if (strpos($ua, 'Windows NT 6.3') !== false) return 'Windows 8.1';
            if (strpos($ua, 'Windows NT 6.2') !== false) return 'Windows 8';
            if (strpos($ua, 'Windows NT 6.1') !== false) return 'Windows 7';
            return 'Windows';
        }
        if (strpos($ua, 'Mac OS X') !== false) return 'Mac OS X';
        if (strpos($ua, 'Linux') !== false) {
            if (strpos($ua, 'Android') !== false) return 'Android';
            return 'Linux';
        }
        if (strpos($ua, 'iPhone') !== false || strpos($ua, 'iPad') !== false) return 'iOS';
        return 'Unknown';
    }
}
