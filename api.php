<?php

// 送られてきたデータのエスケープ処理を行う
function h($str) {

    $str = htmlspecialchars($str , ENT_QUOTES, 'UTF-8');
    return $str;
}

// データベースへ接続を行う
function connectDB(string $dsn, string $user, string $password) {

    $dbh = new PDO($dsn,$user,$password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

// 現在利用している利用者のデータを取ってくる
function use_now() {

    try {
        $dbh = connectDB('mysql:dbname=monitoring;host=localhost;charset=utf8', 'root', 'Fumiya_0324');
        $sql = 'SELECT user.name, log.enter_time '.
                'FROM log '.
                'INNER JOIN user ON log.user_id=user.id '.
                'WHERE log.exit_time IS NULL '.
                'AND log.enter_time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY) '.
                'ORDER BY log.enter_time DESC';
        $stmt = $dbh->query($sql);
        $ret = $stmt->fetchAll();
        
        return $ret;
    }catch(Exception $e) {
        return $e;
    }
}

// 任意のidmのユーザがuserテーブルにいるかどうか確認する
function check_user($dbh, string $idm) {
   
    $sql = 'SELECT * FROM user WHERE idm=?';
    $stmt = $dbh->prepare($sql);
    $data[] = $idm;
    $stmt->execute($data);

    $ret = $stmt->fetch();
    if ($ret) {
        return $ret['id'];
    } else {
        return false;
    }
}

// userテーブルにユーザを登録する
function add_user($dbh, string $idm, string $name) {

    $sql = 'INSERT INTO user (idm, name) VALUES (?, ?)';
    $stmt = $dbh ->prepare($sql);
    $data[] = $idm;
    $data[] = $name;
    $stmt->execute($data);
    
    return true;
    
}

// logテーブルのログを更新する
function update_log($dbh, string $idm, string $name) {

    if (check_user($dbh, $idm) === false) {
        add_user($dbh, $idm, $name);
    }

    $sql = 'SELECT * '.
            'FROM log INNER JOIN user '.
            'ON log.user_id=user.id '.
            'WHERE exit_time IS NULL '.
            'AND enter_time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY) '.
            'AND user.idm=? '.
            'ORDER BY log.enter_time DESC LIMIT 1';

    $stmt = $dbh->prepare($sql);
    $data[] = $idm;
    $stmt->execute($data);
    unset($data);
    $ret = $stmt->fetch();

    if ($ret) {
        // 退出する

        $sql = 'UPDATE log '.
                'SET exit_time=CURRENT_TIMESTAMP '.
                'WHERE exit_time IS NULL '.
                'AND user_id=? '.
                'ORDER BY enter_time DESC LIMIT 1';
        $stmt = $dbh->prepare($sql);
        $data[] = $ret['user_id'];
        $stmt->execute($data);
        unset($data);

        $retarr = [
            'result' => 'out'
        ];
        return $retarr;
    } else {
        // 新しく入室

        $user_id = check_user($dbh, $idm);

        $sql = 'INSERT INTO log (user_id) VALUES (?)';
        $stmt = $dbh->prepare($sql);
        $data[] = $user_id;
        $stmt->execute($data);

        $retarr = [
            'result' => 'in'
        ];
        return $retarr;
    }
}

// 任意の検索条件に当てはまるログデータをとってくる
function search_log(string $idm, string $name, string $from, string $to) {
    $sql = 'SELECT * FROM log INNER JOIN user ON log.user_id=user.id WHERE ';

    if ($idm !== '') {
        $sql = $sql.'idm=? ';
        $data[] = $idm;
    } else if ($name !== '') {
        $sql = $sql.'name=? ';
        $data[] = $name;
    }

    if (($idm !== '' || $name !== '') && ($from !== '' && $to !== '')) {
        $sql = $sql.'AND ';
    }
    if ($from !== '' && $to !== '') {
        $sql = $sql.'(enter_time BETWEEN ? AND ? OR exit_time BETWEEN ? AND ?)';
        $data[] = $from;
        $data[] = $to;
        $data[] = $from;
        $data[] = $to;
    }

    try {
        $dbh = connectDB('mysql:dbname=monitoring;host=localhost;charset=utf8', 'root', 'Fumiya_0324');
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);

        $retarr = [
            'result' => 'success',
            'log' => $stmt->fetchAll(),
            'from' => $from
        ];
        return $retarr;
    } catch (Exception $e) {
        $retarr = [
            'error' => $e
        ];
        return $retarr;
    }
}

// 送られてくるデータによって処理を分岐する
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arr = json_decode(file_get_contents('php://input'), true);

    $idm = $arr['idm'];
    $name = $arr['name'];

    if (is_null($name)) {
        $name = 'guest';
    }
    
    try {
        $dbh = connectDB('mysql:dbname=monitoring;host=localhost;charset=utf8', 'root', 'Fumiya_0324');
        $dbh->beginTransaction();
        
        $retarr = update_log($dbh, $idm, $name);
        
        $dbh->commit();
    } catch (Exception $e) {
        $dbh->rollBack();
    } 
    $dbh = null;

    echo json_encode($retarr);

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=UTF-8');

    if ($_GET['command'] === 'search') {

        $from = h($_GET['from']);
        $to = h($_GET['to']);
        $idm = h($_GET['idm']);
        $name = h($_GET['name']);

        echo json_encode(search_log($idm, $name, $from, $to));

    } else if ($_GET['command'] === 'use_now') {
        // $now = new DateTime();
        // $to = $now->format('Y-m-d H:i:s');
        // $now->sub(new DateInterval('P1D'));
        // $from = $now->format('Y-m-d H:i:s');

        // echo json_encode(search_log('', '', $from, $to));
        
        echo json_encode(use_now());
    }
}

?>