<?php

require_once('common.php');
require_once('opeDB.php');

class OpeUserTable extends OpeDB {
    private $userId = -1;
    private $studentId = '00X000';
    private $name = 'guest';
    private $idm = '0000000000000000';
    private $password = null;

    public function __construct($studentId) {
        parent::__construct();
        $this->setStudentId($studentId);
    }

    // userId変数セッター----------------
    public function setUserId($userId) {
        $this->userId = $userId;
    }
    public function getUserId() {
        return $this->userId;
    }
    // -------------------------------

    // name変数ゲッター、セッター---------------
    public function setName(string $name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }
    //-------------------------------------------- 

    // idm変数セッター、ゲッター--------------------
    public function setIdm(string $idm) {
        $this->idm = $idm;
    }

    public function getIdm() {
        return $this->idm;
    }
    // --------------------------------------------
    // idm変数セッター、ゲッター--------------------
    public function setPassword(string $password) {
        $this->password = $password;
    }

    public function getPassword() {
        return $this->password;
    }
    // --------------------------------------------

    // 学籍番号セッター、ゲッター-------------------
    public function setStudentId(string $studentId) {
        $this->studentId = $studentId;
    }
    public function getStudentId() {
        return $this->studentId;
    }
    // -------------------------------------------

    // 任意のユーザがいるかどうか調べる----------------
    public function check_user() {

        if ($this->getUserId() === -1 && $this->getName() === 'guest' && $this->getIdm() === '0000000000000000' && $this->getStudentId() === '00X000') {
            return false;
        } else {
            $data = array();
            $column = array();

            $sql = 'SELECT * FROM user WHERE ';

            if ($this->getUserId() !== -1) {
                $data[] = $this->getUserId();
                $column[] = 'id';
            }
            if ($this->getName() !== 'guest') {
                $data[] = $this->getName();
                $column[] = 'name';
            }
            if ($this->getIdm() !== '0000000000000000') {
                $data[] = $this->getIdm();
                $column[] = 'idm';
            }
            if ($this->getStudentId() !== '00X000') {
                $data[] = $this->getStudentId();
                $column[] = 'student_id';
            }

            $sql .= $column[0].'=:'.$column[0];
            for ($i = 1; $i < count($column); $i++) {
                $sql .= ' AND '.$column[0].'=:'.$column[0];
            }
            $stmt = $this->getDbh()->prepare($sql);
            for($i = 0; $i < count($data); $i++) {
                $stmt->bindValue(':'.$column[$i], $data[$i]);
            }
            $stmt->execute();
            $ret = $stmt->fetchAll();
            if ($ret) {
                return $ret;
            } else {
                return false;
            }
        }
    }
    // ---------------------------------------------------

    // userテーブルにユーザを追加する----------------------------
    public function add_user() {
    
        if ($this->getIdm() === '0000000000000000') {
            return false;
        } else if ($this->check_user() === false) {
            $sql = 'INSERT INTO user (idm, name, password) VALUES (:idm, :name, :password)';
            $stmt = $this->getDbh()->prepare($sql);
            $stmt->bindValue(':idm', $this->getIdm());
            $stmt->bindValue(':name', $this->getName());
            $password = md5($this->getPassword());
            $stmt->bindValue(':password', $password);
            $stmt->execute();
            return true;
        }
    }
    // ---------------------------------------------------------

    // ユーザの参照--------------------------------------------------------
    public function show_user() {
        try {
            $sql = 'SELECT id, idm, name, student_id FROM user ';
            $data = array();
            $column = array();
            if ($this->getName() !== 'guest') {
                $data[] = $this->getName();
                $column[] = 'name';
            }
            if ($this->getUserId() !== -1) {
                $data[] = $this->getUserId();
                $column[] = 'id';
            }
            if ($this->getIdm() !== '0000000000000000') {
                $data[] = $this->getIdm();
                $column[] = 'idm';
            }
            if ($this->getStudentId() !== '00X000') {
                $data[] = $this->getStudentId();
                $column[] = 'student_id';
            }

            if (count($data) > 0) {
                $sql .= 'WHERE ';
                $sql .= $column[0].'=:'.$column[0];
                for ($i = 1; $i < count($column); $i++) {
                    $sql .= 'AND '.$column[$i].'=:'.$column[$i];
                }

                $stmt = $this->getDbh()->prepare($sql);
                for ($i = 0; $i = count($data); $i++) {
                    $stmt->bindValue(':'.$column[$i], $data[$i]);
                }
            } else {
                $stmt = $this->getDbh()->prepare($sql);
            }
            $stmt->execute();
            $ret = $stmt->fetchAll();

            $retarr = [
                'result' => 'success',
                'detail' => $ret
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

    // ユーザ名の更新
    public function update_user($newName) {
        try {
            $this->getDbh()->beginTransaction();
            if ($this->check_user() === false || $newName === '') {
                $retarr = [
                    'result' => 'fail'
                ];
                echo json_encode($retarr);
                exit();
            }

            $sql = 'UPDATE user '.
                    'SET name=:name '.
                    'WHERE student_id=:student_id';
            $stmt = $this->getDbh()->prepare($sql);
            $stmt->bindValue(':name', $newName);
            $stmt->bindValue(':student_id', $this->getStudentId());
            $stmt->execute();
            $this->getDbh()->commit();

            $retarr = [
                'result' => 'success'
            ];
            return $retarr;

        } catch (Exception $e) {
            $this->getDbh()->rollBack();
            $retarr = [
                'result' => 'fail',
                'error' => $e
            ];
            
            return $retarr;
        }
    }

    public function update_password($newPassword) {
        if ($this->getStudentId() === '0X000' || $this->getPassword() === null || empty($newPassword)) {
            $retarr = [
                'result'=>'fail'
            ];
            return $retarr;
        }
        try {
            $this->getDbh()->beginTransaction();

            $sql = 'SELECT * FROM user WHERE student_id=:student_id AND password=:password';
            $stmt = $this->getDbh()->prepare($sql);
            $stmt->bindValue(':student_id', $this->getStudentId());
            $pass = md5($this->getPassword());
            $stmt->bindValue(':password', $pass);
            $stmt->execute();
            $ret = $stmt->fetch();

            if($ret) {
                $sql = 'UPDATE user SET password=:new_password WHERE student_id=:student_id';
                $stmt = $this->getDbh()->prepare($sql);
                $newPassword = md5($newPassword);
                $stmt->bindValue(':new_password', $newPassword);
                $stmt->bindValue(':student_id', $this->getStudentId());
                $stmt->execute();
                $this->getDbh()->commit();
                $retarr = [
                    'result'=>'success'
                ];
                return $retarr;
            } else {
                $this->getDbh()->rollBack();
                $retarr = [
                    'result'=>'fail',
                    'detail'=>'errorPassword'
                ];
                return $retarr;
            }
        } catch(Exception $e) {
            $this->getDbh()->rollBack();
            $retarr = [
                'result'=>'fail',
                'detail'=>$e
            ];
            return $retarr;
        }
    }
}

?>