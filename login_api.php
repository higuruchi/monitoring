<?php

session_start();

require_once('opeDB.php');
require_once('common.php');
require_once('signInSignOut.php');

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['command'] === 'login') {
        $studentId = $_POST['studentId'] ?? '';
        $password = $_POST['password'] ?? '';

        $signInSignOut = new signInSignOut($studentId);
        if ($studentId !== '' && $password !== '') {
            $password = md5($password);
            $signInSignOut->setPassword($password);
            $ret = $signInSignOut->login();
            if ($ret !== false) {
                $_SESSION['login'] = true;
                $_SESSION['name'] = $ret['name'];
                $_SESSION['idm'] = $ret['idm'];
                $_SESSION['userId'] = $ret['id'];
                $_SESSION['accessRight'] = $ret['access_right'];
                $_SERVER['studentId'] = $ret['student_id'];
                $retarr = [
                    'result' => 'success',
                    'name' => $_SESSION['name']
                ];
            }
        } else {
            $retarr = [
                'result' => 'fail'
            ];
        }
        echo json_encode($retarr);
    }
}

?>