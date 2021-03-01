<?php

session_start();

require_once('opeDB.php');
require_once('common.php');
require_once('opeUserTable.php');

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['command'] === 'login') {
        $studentId = $_POST['studentId'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($studentId !== '' && $password !== '') {
            // $signInSignOut = new signInSignOut($studentId);
            // $signInSignOut->setPassword($password);
            // $ret = $signInSignOut->login();

            $opeUserTable = new OpeUserTable($studentId);
            $opeUserTable->setPassword($password);
            $ret = $opeUserTable->check_user();
            if ($ret !== false) {
                $_SESSION['login'] = true;
                $_SESSION['idm'] = $ret['idm'];
                $_SESSION['userId'] = $ret['id'];
                $_SESSION['accessRight'] = $ret['access_right'];
                $_SESSION['studentId'] = $ret['student_id'];
                $retarr = [
                    'result' => 'success',
                    'name' => $ret[0]['name']
                ];
            } else {
                $retarr = [
                    'result' => 'fail'
                ];
            }
        } else {
            $retarr = [
                'result' => 'fail'
            ];
        }
        echo json_encode($retarr);
    } else if ($_POST['command'] === 'logout') {
        $_SESSION = array();
        if (isset($_COOKIE["PHPSESSID"])) {
            setcookie("PHPSESSID", '', time() - 1800, '/');
        }
        session_destroy();
        echo json_encode(['result' => 'success']);
    }
}

?>