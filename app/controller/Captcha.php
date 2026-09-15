<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Session;

/**
 * 验证码
 */
class Captcha extends BaseController
{
    /**
     * 输出验证码图片（math / char）
     */
    public function captchaImage()
    {
        $type = (string) $this->request->get('type', 'math');
        if ($type === 'math') {
            $a = random_int(1, 9);
            $b = random_int(1, 9);
            $ops = ['+', '-', 'x'];
            $op = $ops[array_rand($ops)];
            $answer = match ($op) {
                '+' => $a + $b,
                '-' => $a - $b,
                default => $a * $b,
            };
            $text = "{$a}{$op}{$b}=?";
            Session::set('KAPTCHA_SESSION_KEY', (string) $answer);
        } else {
            $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $text = '';
            for ($i = 0; $i < 4; $i++) {
                $text .= $chars[random_int(0, strlen($chars) - 1)];
            }
            Session::set('KAPTCHA_SESSION_KEY', $text);
        }

        $width = 110;
        $height = 36;
        $im = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($im, 243, 251, 254);
        imagefilledrectangle($im, 0, 0, $width, $height, $bg);

        for ($i = 0; $i < 6; $i++) {
            $line = imagecolorallocate($im, random_int(120, 200), random_int(120, 200), random_int(120, 200));
            imageline($im, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $line);
        }
        for ($i = 0; $i < 60; $i++) {
            $dot = imagecolorallocate($im, random_int(100, 200), random_int(100, 200), random_int(100, 200));
            imagesetpixel($im, random_int(0, $width - 1), random_int(0, $height - 1), $dot);
        }

        $len = strlen($text);
        $step = (int) max(14, ($width - 16) / max(1, $len));
        for ($i = 0; $i < $len; $i++) {
            $color = imagecolorallocate($im, random_int(10, 90), random_int(80, 150), random_int(10, 90));
            $x = 8 + $i * $step + random_int(-1, 1);
            $y = random_int(8, 14);
            imagestring($im, 5, $x, $y, $text[$i], $color);
        }

        ob_start();
        imagepng($im);
        imagedestroy($im);
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma'        => 'no-cache',
        ]);
    }
}
