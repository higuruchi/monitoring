<?php

require_once('opeDB.php');
require_once('common.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (h($_POST['command']) === 'login') {
        $name = h($_POST['name']);
        $idm = h($_POST['idm']);

        $opeDB = new OpeDB($name, $idm);
        $ret = $opeDB->check_user();

        if ($ret !== false) {
            session_start();
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