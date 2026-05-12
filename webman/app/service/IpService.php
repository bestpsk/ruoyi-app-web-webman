<?php

namespace app\service;

class IpService
{
    public static function getLocation($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '0.0.0.0' || $ip === '::1') {
            return '内网IP';
        }
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 2]);
            $response = $client->get("http://ip-api.com/json/{$ip}?lang=zh-CN&fields=status,country,regionName,city");
            $data = json_decode($response->getBody()->getContents(), true);
            if (isset($data['status']) && $data['status'] === 'success') {
                return ($data['country'] ?? '') . ' ' . ($data['regionName'] ?? '') . ' ' . ($data['city'] ?? '');
            }
        } catch (\Exception $e) {
        }
        return '未知';
    }
}
