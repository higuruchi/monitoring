<?php

session_start();

require_once('opeDB.php');
require_once('common.php');

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['command'] === 'login') {
        $userId = $_POST['userId'] ?? '';
        $password = $_POST['password'] ?? '';

        $opeDB = new OpeDB();
        if ($userId !== '' && $password !== '') {
            $opeDB->setUserId($userId);
            $opeDB->setPassword($password);
            $ret = $opeDB->login();
            if ($ret !== false) {
                $_SESSION['login'] = true;
                $_SESSION['name'] = $ret['name'];
                $_SESSION['idm'] = $ret['idm'];
                $_SESSION['userId'] = $ret['id'];
                $_SESSION['accessRight'] = $ret['access_right'];
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