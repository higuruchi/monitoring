<?php
require_once('common.php');

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
    public function check_user() {
    
        $sql = 'SELECT * FROM user WHERE idm=:idm';
        $stmt = $this->GetDbh()->prepare($sql);
        $stmt->bindValue(':idm', $this->getIdm());
        $stmt->execute();

        $ret = $stmt->fetch();
        if ($ret) {
            return $ret;
        } else {
            return false;
        }
    }
    // ----------------------------------------------------------

    // userテーブルにユーザを追加する----------------------------
    public function add_user() {

        $sql = 'INSERT INTO user (idm, name) VALUES (:idm, :name)';
        $stmt = $this->getDbh()->prepare($sql);
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

            $stmt = $this->getDbh()->prepare($sql);
            $stmt->bindValue(':idm', $this->getIdm());
            $stmt->execute();
            $ret = $stmt->fetch();

            if ($ret) {
                // 退出する

                $sql = 'UPDATE log '.
                        'SET exit_time=CURRENT_TIMESTAMP '.
                        'WHERE exit_time IS NULL '.
                        'AND user_id=:idm '.
                        'ORDER BY enter_time DESC LIMIT 1';
                $stmt = $this->getDbh()->prepare($sql);
                $stmt->bindValue(':idm', $this->getIdm());
                $stmt->execute();

                $retarr = [
                    'result' => 'out'
                ];
            } else {
                // 新しく入室

                $user_id = $this->check_user();

                $sql = 'INSERT INTO log (user_id) VALUES (:user_id)';
                $stmt = $this->getDbh()->prepare($sql);
                $stmt->bindValue(':user_id', $user_id);
                $stmt->execute();

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
                'log' => $stmt->fetchAll()
            ];
            return $retarr;
        } catch (Exception $e) {
            $retarr = [
                'error' => $e
            ];
            return $retarr;
        }
    }

    // 現在利用している利用者のデータを取ってくる----------------------
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
    // -------------------------------------------------------------------

    // ユーザの参照--------------------------------------------------------
    public function show_user(int $id, string $idm, string $name) {
        try {

            if ($id === -1 && $idm === '' && $name === '') {
                $sql = 'SELECT id, idm, name '.
                        'FROM user ';
                $stmt = $this->getDbh()->query($sql);
            } else {
                $sql = 'SELECT id, idm, name '.
                        'FROM user '.
                        'WHERE ';

                if ($id !== -1) {
                    $sql .= 'id=:id ';
                }

                if ($idm !== '' && $id !== -1) {
                    $sql .= 'AND idm=:idm ';
                    $data[] = $idm;
                } else if ($idm !== '') {
                    $sql .= 'idm=:idm ';
                }

                if ($name !== '' && ($id !== -1 || $name !== '')) {
                    $sql .= 'AND name=:name ';
                    $data[] = $name;
                }else if ($name !== '') {
                    $sql .= 'name=:name ';
                }

            
                $stmt = $this->getDbh()->prepare($sql);
                if ($id !== -1) {
                    $stmt->bindValue(':id', $id);
                }
                if ($idm !== '') {
                    $stmt->bindValue(':idm', $idm);
                }
                if ($name !== '' ) {
                    $stmt->bindValue(':name', $name);
                }
                $stmt->execute();
            } 

            $retarr = [
                'result' => 'success',
                'user_data' => $stmt->fetchAll(),
            ];

            return $retarr;
        } catch (Exception $e) {
            $retarr = [
                'result' => 'fail',
                'error' => $e
            ];
            return $retarr;
        }
    }
    // --------------------------------------------------------------------------------------

    // public update_user($new_name) {
    //     try {
    //         $this->getDbh()->beginTransaction();
    //         if ($this->check_user() === false) {

    //         }
    //     }
    // }

    
}

?>