<?php
    session_start();
    session_regenerate_id();
    
    if (!$_SESSION['login']) {
        header('Location: ./index.html');
    }
?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>入退室管理システム</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.js"></script>
    <script type="text/javascript" src="home.js?3"></script>
    <link rel="stylesheet" type="text/css" href="home.css?1">
    <link href="https://use.fontawesome.com/releases/v5.6.1/css/all.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Kosugi&display=swap" rel="stylesheet">

</head>
<body>
    <header>
        <h1>入退室管理システム</h1>
        <div name="login"><i class="fas fa-sign-out-alt fa-2x" name="login"></i><span>ログアウト</span></div>
    </header>
    
    <div class="middle">
        <aside>
            <nav>
                <ul>
                    <li name="home">ホーム</li>
                    <li name="search_log">ログ検索</li>
                    <li name="manage_user">ユーザ管理</li>
                    <li name="statistics">統計情報</li>
                    <li name="now_use">現在入室している人</li>
                    <li name="minutes">議事録</li>
                </ul>
            </nav>
        </aside>
        <div class="main">
        </div>
    </div>
</body>
</html>