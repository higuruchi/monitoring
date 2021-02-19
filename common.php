<?php
// 送られてきたデータのエスケープ処理を行う
function h($str) {
    $str = htmlspecialchars($str , ENT_QUOTES, 'UTF-8');
    return $str;
}

?>