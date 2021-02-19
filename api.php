<?php

require_once('common.php');
require_once('opeDB.php');
session_start();
session_regenerate_id();

if ($_SESSION['login'] == false) {
    $retarr = [
        'result' => 'fail'
    ];
    echo json_encode($retarr);
    exit();
}


// 送られてくるデータによって処理を分岐する
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=UTF-8');
    $arr = json_decode(file_get_contents('php://input'), true);

    $idm = $arr['idm'];
    $name = $arr['name'];

    if (is_null($name)) {
        $name = 'guest';
    }
        
    $opeDB = new OpeDB($name, $idm);
    $retarr = $opeDB->update_log();

    echo json_encode($retarr);

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=UTF-8');

    if (h($_GET['command']) === 'search') {

        $from = h($_GET['from']);
        $to = h($_GET['to']);
        $idm = h($_GET['idm']);
        $name = h($_GET['name']);
        $opeDB = new OpeDB($_SESSION['name'], $_SESSION['idm']);

        echo json_encode($opeDB->search_log($idm, $name, $from, $to));

    } else if (h($_GET['command']) === 'use_now') {

        $opeDB = new OpeDB($_SESSION['name'], $_SESSION['idm']);
        echo json_encode($opeDB->use_now());
    } else if (h($_GET['command']) === 'show_user') {
        $opeDB = new OpeDB($_SESSION['name'], $_SESSION['idm']);
        
        echo json_encode($opeDB->show_user(-1, '', ''));
    }
}

?>