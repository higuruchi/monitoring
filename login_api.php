<?php
session_start();

require_once('opeDB.php');
require_once('common.php');

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (h($_POST['command']) === 'login') {
        $name = $_POST['name'] ?? '';
        $userId = $_POST['userId'] ?? '';
        $retarr = [
            'name'=>$name,
            'userId'=>$userId
        ];
        echo json_encode($retarr);
        exit();

        $opeDB = new OpeDB();
        if ($name !== '') {
            $opeDB->setName($name);
        }
        if ($userId !== '') {
            $opeDB->setUserId($userId);
        }
        $ret = $opeDB->check_user();

        if ($ret !== false) {
            $_SESSION['login'] = true;
            $_SESSION['name'] = $ret['name'];
            $_SESSION['idm'] = $ret['idm'];
            $retarr = [
                'result' => 'success',
            ];
        } else {
            $retarr = [
                'result' => 'fail'
            ];
        }
        echo json_encode($retarr);
    }
}

?>