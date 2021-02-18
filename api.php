<?php


class OpeDB {

    private $dbh;
    private $name;
    private $idm;

    public function __construct($name, $idm) {
        $this->setDbh();
        $this->setName($name);
        $this->setIdm($idm);
    }

    private function connectDB() {
        $dbh = new PDO('mysql:dbname=monitoring;host=localhost;charset=utf8', 'root', 'Fumiya_0324');
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }

    // dbh変数セッター----------------
    private function setDbh() {
        $this->dbh = $this->connectDB();
    }

    private function getDbh() {
        return $this->dbh;
    }
    // -------------------------------

    // name変数ゲッター、セッター---------------
    private function setName(string $name) {
        $this->name = $name;
    }

    private function getName() {
        return $this->name;
    }
    //-------------------------------------------- 

    // idm変数セッター、ゲッター--------------------
    private function setIdm(string $idm) {
        $this->idm = $idm;
    }

    private function getIdm() {
        return $this->idm;
    }
    // --------------------------------------------

    // 任意のidmのユーザがuserテーブルにいるかどうか確認する------
    private function check_user() {
    
        $sql = 'SELECT * FROM user WHERE idm=:idm';
        $stmt = $this->GetDbh->prepare($sql);
        $stmt->bindValue(':idm', $this->getIdm());
        $stmt->execute();

        $ret = $stmt->fetch();
        if ($ret) {
            return $ret['id'];
        } else {
            return false;
        }
    }
    // ----------------------------------------------------------

    // userテーブルにユーザを追加する----------------------------
    private function add_user() {

        $sql = 'INSERT INTO user (idm, name) VALUES (:idm, :name)';
        $stmt = $this->getDbh->prepare($sql);
        $stmt->bindValue(':idm', $this->getIdm());
        $stmt->bindValue(':name', $this->getName());
        $stmt->execute();
        return true;
    }
    // ---------------------------------------------------------

    // logテーブルのログを更新する------------------
    public function update_log() {

        try {
            $this->getDbh()->beginTransaction();

            if ($this->check_user() === false) {
                $this->add_user();
            }

            $sql = 'SELECT * '.
                    'FROM log INNER JOIN user '.
                    'ON log.user_id=user.id '.
                    'WHERE exit_time IS NULL '.
                    'AND enter_time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY) '.
                    'AND user.idm=:idm '.
                    'ORDER BY log.enter_time DESC LIMIT 1';

            $stmt = $this->getDbh->prepare($sql);
            $stmt->bindValue(':idm', $this->getIdm);
            $stmt->execute();
            $ret = $stmt->fetch();

            if ($ret) {
                // 退出する

                $sql = 'UPDATE log '.
                        'SET exit_time=CURRENT_TIMESTAMP '.
                        'WHERE exit_time IS NULL '.
                        'AND user_id=:idm '.
                        'ORDER BY enter_time DESC LIMIT 1';
                $stmt = $this->getDbh->prepare($sql);
                $stmt->bindValue(':idm', $this->getIdm());
                $stmt->execute();

                $retarr = [
                    'result' => 'out'
                ];
            } else {
                // 新しく入室

                $user_id = $this->check_user();

                $sql = 'INSERT INTO log (user_id) VALUES (:user_id)';
                $stmt = $this->getDbh->prepare($sql);
                $stmt->bindValue(':user_id', $user_id);
                $stmt->execute();
                $this->getDbh()->commit();

                $retarr = [
                    'result' => 'in'
                ];
            }
            $this->getDbh()->commit();
            return $retarr;

        } catch(Exception $e) {
            $this->getDbh()->rollBack();

            $retarr =  [
                'result' => $e
            ];
            return $retarr;
        }
    }
    // ---------------------------------------------------------------

    // 任意の検索条件に当てはまるログデータをとってくる
    public function search_log(string $idm, string $name, string $from, string $to) {
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
            $stmt = $this->getDbh()->prepare($sql);
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

    // 現在利用している利用者のデータを取ってくる
    public function use_now() {
        try {
            $sql = 'SELECT user.name, log.enter_time '.
                    'FROM log '.
                    'INNER JOIN user ON log.user_id=user.id '.
                    'WHERE log.exit_time IS NULL '.
                    'AND log.enter_time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY) '.
                    'ORDER BY log.enter_time DESC';
            $stmt = $this->getDbh()->query($sql);
            $ret = $stmt->fetchAll();
            
            return $ret;
        }catch(Exception $e) {
            return $e;
        }
    }
}

// -------------------------------------------------------------------------------------------------------




// 送られてきたデータのエスケープ処理を行う
function h($str) {

    $str = htmlspecialchars($str , ENT_QUOTES, 'UTF-8');
    return $str;
}

// // データベースへ接続を行う
// function connectDB() {
//     $dbh = new PDO('mysql:dbname=monitoring;host=localhost;charset=utf8', 'root', 'Fumiya_0324');
//     $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
//     return $dbh;
// }

// // 現在利用している利用者のデータを取ってくる
// function use_now() {

//     try {
//         $dbh = connectDB();
//         $sql = 'SELECT user.name, log.enter_time '.
//                 'FROM log '.
//                 'INNER JOIN user ON log.user_id=user.id '.
//                 'WHERE log.exit_time IS NULL '.
//                 'AND log.enter_time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY) '.
//                 'ORDER BY log.enter_time DESC';
//         $stmt = $dbh->query($sql);
//         $ret = $stmt->fetchAll();
        
//         return $ret;
//     }catch(Exception $e) {
//         return $e;
//     }
// }

// // 任意のidmのユーザがuserテーブルにいるかどうか確認する
// function check_user($dbh, string $idm) {
   
//     $sql = 'SELECT * FROM user WHERE idm=?';
//     $stmt = $dbh->prepare($sql);
//     $data[] = $idm;
//     $stmt->execute($data);

//     $ret = $stmt->fetch();
//     if ($ret) {
//         return $ret['id'];
//     } else {
//         return false;
//     }
// }

// // userテーブルにユーザを登録する
// function add_user($dbh, string $idm, string $name) {

//     $sql = 'INSERT INTO user (idm, name) VALUES (?, ?)';
//     $stmt = $dbh ->prepare($sql);
//     $data[] = $idm;
//     $data[] = $name;
//     $stmt->execute($data);
    
//     return true;
    
// }

// // logテーブルのログを更新する
// function update_log($dbh, string $idm, string $name) {
        

//     if (check_user($dbh, $idm) === false) {
//         add_user($dbh, $idm, $name);
//     }

//     $sql = 'SELECT * '.
//             'FROM log INNER JOIN user '.
//             'ON log.user_id=user.id '.
//             'WHERE exit_time IS NULL '.
//             'AND enter_time >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 DAY) '.
//             'AND user.idm=? '.
//             'ORDER BY log.enter_time DESC LIMIT 1';

//     $stmt = $dbh->prepare($sql);
//     $data[] = $idm;
//     $stmt->execute($data);
//     unset($data);
//     $ret = $stmt->fetch();

//     if ($ret) {
//         // 退出する

//         $sql = 'UPDATE log '.
//                 'SET exit_time=CURRENT_TIMESTAMP '.
//                 'WHERE exit_time IS NULL '.
//                 'AND user_id=? '.
//                 'ORDER BY enter_time DESC LIMIT 1';
//         $stmt = $dbh->prepare($sql);
//         $data[] = $ret['user_id'];
//         $stmt->execute($data);
//         unset($data);

//         $retarr = [
//             'result' => 'out'
//         ];
//         return $retarr;
//     } else {
//         // 新しく入室

//         $user_id = check_user($dbh, $idm);

//         $sql = 'INSERT INTO log (user_id) VALUES (?)';
//         $stmt = $dbh->prepare($sql);
//         $data[] = $user_id;
//         $stmt->execute($data);

//         $retarr = [
//             'result' => 'in'
//         ];
//         return $retarr;
//     }
// }

// // 任意の検索条件に当てはまるログデータをとってくる
// function search_log(string $idm, string $name, string $from, string $to) {
//     $sql = 'SELECT * FROM log INNER JOIN user ON log.user_id=user.id WHERE ';

//     if ($idm !== '') {
//         $sql = $sql.'idm=? ';
//         $data[] = $idm;
//     } else if ($name !== '') {
//         $sql = $sql.'name=? ';
//         $data[] = $name;
//     }

//     if (($idm !== '' || $name !== '') && ($from !== '' && $to !== '')) {
//         $sql = $sql.'AND ';
//     }
//     if ($from !== '' && $to !== '') {
//         $sql = $sql.'(enter_time BETWEEN ? AND ? OR exit_time BETWEEN ? AND ?)';
//         $data[] = $from;
//         $data[] = $to;
//         $data[] = $from;
//         $data[] = $to;
//     }

//     try {
//         $dbh = connectDB('mysql:dbname=monitoring;host=localhost;charset=utf8', 'root', 'Fumiya_0324');
//         $stmt = $dbh->prepare($sql);
//         $stmt->execute($data);

//         $retarr = [
//             'result' => 'success',
//             'log' => $stmt->fetchAll(),
//             'from' => $from
//         ];
//         return $retarr;
//     } catch (Exception $e) {
//         $retarr = [
//             'error' => $e
//         ];
//         return $retarr;
//     }
// }

// 送られてくるデータによって処理を分岐する
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    if ($_GET['command'] === 'search') {

        $from = h($_GET['from']);
        $to = h($_GET['to']);
        $idm = h($_GET['idm']);
        $name = h($_GET['name']);
        $opeDB = new OpeDB('guest', '0000000000000000');

        echo json_encode($opeDB->search_log($idm, $name, $from, $to));

    } else if ($_GET['command'] === 'use_now') {

        $opeDB = new OpeDB('guest', '0000000000000000');
        echo json_encode($opeDB->use_now());
    }
}

?>