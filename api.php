<?php

require_once('common.php');
require_once('opeDB.php');
session_start();
session_regenerate_id();

function check_login() {
    if ($_SESSION['login'] === true) {
        return true;
    } else {
        return false;
    }
}

// 送られてくるデータによって処理を分岐する
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_POST['command'] === 'update_user') {
        if ($_SESSION['login']) {
            $userId = $_POST['userId'];
            $newName = $_POST['newName'];

            $opeDB = new OpeDB();
            if ($_SESSION['accessRight'] === true) {
                $opeDB->setUserId($userId);
                echo json_encode($opeDB->update_user($newName));
            } else if ($_SESSION['userId'] === $userId) {
               $opeDB->setUserId($userId);
               echo json_encode($opeDB->update_user($newName));
            } else {
                $retarr = [
                    'result' => 'faile',
                    'detail'=>'you do not have access right'
                ];
                echo json_encode($retarr);
            }
        } else {
            $retarr = [
                'result' => 'faile'
            ];
            echo json_encode($retarr);
        }
    } else if ($_POST['command'] === 'update_log'){
        $arr = json_decode(file_get_contents('php://input'), true);

        $idm = $arr['idm'] ?? '0000000000000000';
        $name = $arr['name'] ?? 'guest';
            
        $opeDB = new OpeDB();
        $opeDB->setIdm($idm);
        $opeDB->setName($name);
        $retarr = $opeDB->update_log();
    
        echo json_encode($retarr);
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    header('Content-Type: application/json; charset=UTF-8');

    if (h($_GET['command']) === 'search') {

        $from = urldecode($_GET['from']);
        $to = urldecode($_GET['to']);
        $idm = $_GET['idm'] ?? '';
        $name = $_GET['name'] ?? '';
        // $opeDB = new OpeDB($_SESSION['name'], $_SESSION['idm']);

        $opeDB = new OpeDB();
        echo json_encode($opeDB->search_log($idm, $name, $from, $to));

    } else if (h($_GET['command']) === 'use_now') {

        if (check_login() === true) {
            $opeDB = new OpeDB();
            echo json_encode($opeDB->use_now());
        }  else {
            $retarr = [
                'result' => 'fail'
            ];
            echo json_encode($retarr);
        }


    } else if (h($_GET['command']) === 'show_user') {
        
        if (check_login() === true) {

            $opeDB = new OpeDB();
            echo json_encode($opeDB->show_user(-1, '', ''));
        
        } else {
            $retarr = [
                'result' => 'fail'
            ];
            echo json_encode($retarr);
        }

    }   
}

?>