<?php
require_once('./back/common.php');
require_once('./back/opeDB.php');
require_once('./back/opeUserTable.php');
require_once('./back/opeLogTable.php');

$arr = json_decode(file_get_contents('php://input'), true);
if ($arr['command'] === 'update_log') {
    
    $idm = $arr['idm'] ?? '0000000000000000';
    $name = $arr['name'] ?? 'guest';
    $studentId = $arr['studentId'] ?? '00X000';
    
    $opeLogTable = new OpeLogTable($studentId);
    $opeLogTable->setIdm($idm);
    $opeLogTable->setName($name);
    
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($opeLogTable->update_log());
}

?>