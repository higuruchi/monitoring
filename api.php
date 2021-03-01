<?php

require_once('./back/common.php');
require_once('./back/opeDB.php');
require_once('./back/opeUserTable.php');
require_once('./back/opeLogTable.php');

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
            $studentId = $_SESSION['studentId'];
            $newName = $_POST['newName'];
            $password = $_POST['password'];

            // echo json_encode(['stuid'=>$studentId, 'newname'=>$newName, 'pass'=>$password]);
            // exit();

            $opeUserTable = new OpeUserTable($studentId);
            $opeUserTable->setPassword($password);
            echo json_encode($opeUserTable->update_user($newName));
            
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
        $studentId = $arr['studentId'] ?? '00X000';
            
        $opeLogTable = new OpeLogTable($studentId);
        $opeLogTable->setIdm($idm);
        $opeLogTable->setName($name);
        $retarr = $opeLogTable->update_log();
    
        echo json_encode($retarr);
    } else if ($_POST['command'] === 'update_password') {
        if ($_SESSION['login']) {
            $password = $_POST['password'];
            $newPassword = $_POST['newPassword'];

            if (!empty($password) && !empty($newPassword)) {
                // echo json_encode(['stuid'=>$_SESSION['studentId']]);
                $opeUserTable = new OpeUserTable($_SESSION['studentId']);
                $opeUserTable->setPassword($password);
                echo json_encode($opeUserTable->update_password($newPassword));
            }

        } else {
            $retarr = [
                'result'=>'fail',
                'detail'=>'you_are_not_logged_in'
            ];
            echo json_encode($retarr);
        }
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    header('Content-Type: application/json; charset=UTF-8');

    if ($_GET['command'] === 'search') {

        $from = urldecode($_GET['from']);
        $to = urldecode($_GET['to']);
        $idm = $_GET['idm'] ?? '';
        $name = $_GET['name'] ?? '';

        $opeLogTable = new OpeLogTable('0X000');
        echo json_encode($opeLogTable->search_log($idm, $name, $from, $to));

    } else if ($_GET['command'] === 'use_now') {

        if (check_login() === true) {
            $opeLogTable = new OpeLogTable('00X000');
            echo json_encode($opeLogTable->use_now());
        }  else {
            $retarr = [
                'result' => 'fail'
            ];
            echo json_encode($retarr);
        }


    } else if ($_GET['command'] === 'show_user') {
        
        // if (check_login() === true) {

        //     $opeDB = new OpeDB();
        //     echo json_encode($opeDB->show_user(-1, '', ''));
        
        // } else {
        //     $retarr = [
        //         'result' => 'fail'
        //     ];
        //     echo json_encode($retarr);
        // }

    }   
}

?>