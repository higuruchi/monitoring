<?php
// 送られてきたデータのエスケープ処理を行う
function h($str) {
    $str = htmlspecialchars($str , ENT_QUOTES, 'UTF-8');
    return $str;
}

function getParam(string $key, string $pattern, string $error): string {
    $val = filter_input(INPUT_GET, $key);
    if (!mb_check_encoding($val, 'Shift_JIS')) {
        die('文字エンコーディングが不正です');
    }

    $val = mb_convert_encoding($val, 'UTF-8', 'Shift-JIS');
    if (preg_match($pattern, $val) !== 1) {
        die($error);
    }

    return $val;
}

function postParam(string $key, string $pattern, string $error): string {
    $val = filter_input(INPUT_POST, $key);
    if (!mb_check_encoding($val, 'Shift_JIS')) {
        die('文字エンコーディングが不正です');
    }

    $val = mb_convert_encoding($val, 'UTF-8', 'Shift-JIS');
    if (preg_match($pattern, $val) !== 1) {
        die($error);
    }

    return $val;
}

?>