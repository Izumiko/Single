<?php

declare(strict_types=1);

use Typecho\Widget;

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Single
{
    public static string $name = "Single";
    public static string $version = "2.1";
    public static array $authorCache = [];

    /**
     * 更新检测 (现代化 cURL 或改用 file_get_contents)
     * 注意：现代 PHP 倾向于显式错误处理
     */
    public static function update(): void
    {
        $host = filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_SANITIZE_URL) ?? 'unknown';
        // 构建查询参数
        $query = http_build_query([
                'name'    => self::$name,
                'current' => self::$version,
                'site'    => $host,
        ]);

        $url = "https://api.paugram.com/update/?" . $query;

        // 使用 silenced context 或者 try-catch 处理网络请求
        $ch = curl_init();
        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_USERAGENT      => 'Dreamer-Paul-Updater/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return;
        }

        try {
            $update = json_decode((string)$response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return;
        }

        // 如果解析失败或没有名字，直接返回
        if (!is_array($update) || !isset($update["name"])) {
            return;
        }

        self::$name = $update["name"];

        $pluginName    = self::$name;
        $pluginVersion = self::$version;

        $docsBtn = '';
        if (isset($update['docs'])) {
            $url = htmlspecialchars($update['docs']);
            $docsBtn = "<a href=\"$url\">项目介绍</a>";
        }
        $logBtn = '';
        if (isset($update['link'])) {
            $url = htmlspecialchars($update['link']);
            $logBtn = "<a href=\"$url\">更新日志</a>";
        }
        $textPara = '';
        if (isset($update['text'])) {
            $text = htmlspecialchars($update['text']);
            $textPara = "<p>$text</p>";
        }
        $msgPara = '';
        if (isset($update['message'])) {
            $msg = htmlspecialchars($update['message']);
            $msgPara = "<p>$msg</p>";
        }

        // 使用 Heredoc 语法输出 HTML，保持代码整洁
        echo <<<HTML
        <style>
            .dreamer-paul { text-align:center; margin:1em 0; } 
            .dreamer-paul > * { margin:0 0 1rem } 
            .buttons a { background:#467b96; color:#fff; border-radius:4px; padding:.5em .75em; display:inline-block; margin: 0 0.25em; }
        </style>
        <div class="dreamer-paul">
            <h2>$pluginName ($pluginVersion)</h2>
            <p>By: <a href='https://github.com/Dreamer-Paul'>Dreamer-Paul</a></p>
            <p class="buttons">
                $docsBtn {$logBtn}
            </p>
            {$textPara}
            {$msgPara}
        </div>
HTML;
    }

    /**
     * 夜间模式输出
     */
    public static function is_night(): void
    {
        $cookieNight = $_COOKIE["night"] ?? 'false';

        // 获取 Typecho 设置，使用 Nullsafe 运算符 (?->) 实际上 Typecho 的 widget 可能会返回 mixed，保险起见用常规方式
        $options = Widget::widget('Widget_Options');

        if ($cookieNight === "true" || ($options->night_mode ?? '0') === '2') {
            echo ' class="dark-theme"';
        }
    }

    /**
     * 时间转换
     */
    public static function tran_time(int $ts): string
    {
        $dur = time() - $ts;

        return match (true) {
            $dur < 0       => (string)$ts, // 逻辑上不应发生，但保持兼容
            $dur < 60      => $dur . " 秒前",
            $dur < 3600    => floor($dur / 60) . " 分钟前",
            $dur < 86400   => floor($dur / 3600) . " 小时前",
            $dur < 604800  => floor($dur / 86400) . " 天前",
            $dur < 2592000 => floor($dur / 604800) . " 周前",
            $dur < 31557600 => floor($dur / 2592000) . " 个月前",
            default        => date("Y.m.d", $ts),
        };
    }

    /**
     * 文章配图
     */
    public static function post_image(): ?string
    {
        // 获取文章内容
        $content = Widget::widget('Widget_Archive')->text ?? '';

        // 寻找文章里面的图片
        if (preg_match("/(http|https)(\S)+(jpg|jpeg|png|webp|avif)/", $content, $matches)) {
            return $matches[0];
        }

        return null;
    }
}