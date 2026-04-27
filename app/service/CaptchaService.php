<?php

namespace app\service;

use support\Redis;
use app\common\Constants;

class CaptchaService
{
    public static function getCaptcha()
    {
        $captchaEnabled = SysConfigService::selectCaptchaEnabled();
        if (!$captchaEnabled) {
            return [
                'captchaEnabled' => false,
                'img' => '',
                'uuid' => '',
            ];
        }

        $uuid = self::fastUUID();
        $result = self::generateMathCaptcha();
        $answer = $result['answer'];
        $image = $result['image'];

        $redis = Redis::connection();
        $verifyKey = Constants::CAPTCHA_CODE_KEY . $uuid;
        $redis->setex($verifyKey, Constants::CAPTCHA_EXPIRE * 60, $answer);

        return [
            'captchaEnabled' => true,
            'img' => $image,
            'uuid' => $uuid,
        ];
    }

    public static function validateCaptcha($code, $uuid)
    {
        $verifyKey = Constants::CAPTCHA_CODE_KEY . ($uuid ?? '');
        $redis = Redis::connection();
        $captcha = $redis->get($verifyKey);
        if ($captcha === null) {
            return false;
        }
        $redis->del($verifyKey);
        return strcasecmp($code, $captcha) === 0;
    }

    private static function generateMathCaptcha()
    {
        $operators = ['+', '-', '*'];
        $op = $operators[array_rand($operators)];
        $a = mt_rand(1, 50);
        $b = mt_rand(1, 20);

        switch ($op) {
            case '+': $answer = $a + $b; break;
            case '-': $answer = $a - $b; break;
            case '*': $a = mt_rand(1, 9); $b = mt_rand(1, 9); $answer = $a * $b; break;
        }

        $text = "{$a} {$op} {$b} = ?";

        $width = 160;
        $height = 50;
        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, mt_rand(230, 255), mt_rand(230, 255), mt_rand(230, 255));
        imagefill($image, 0, 0, $bgColor);

        for ($i = 0; $i < 80; $i++) {
            $lineColor = imagecolorallocate($image, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
            imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $lineColor);
        }

        for ($i = 0; $i < 200; $i++) {
            $pixelColor = imagecolorallocate($image, mt_rand(150, 220), mt_rand(150, 220), mt_rand(150, 220));
            imagesetpixel($image, mt_rand(0, $width), mt_rand(0, $height), $pixelColor);
        }

        $textColor = imagecolorallocate($image, mt_rand(30, 120), mt_rand(30, 120), mt_rand(30, 120));
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = intval(($width - $textWidth) / 2);
        $y = intval(($height - $textHeight) / 2);
        imagestring($image, $fontSize, $x, $y, $text, $textColor);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        $base64 = base64_encode($imageData);

        return ['answer' => (string)$answer, 'image' => $base64];
    }

    private static function fastUUID()
    {
        return sprintf('%04x%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
}
