<?php

require_once('common.php');
require_once('opeDB.php');

class OpeUserTable extends OpeDB {
    private $userId = -1;
    private $studentId = '00X000';
    private $name = 'guest';
    private $idm = '0000000000000000';
    private $password = null;
    private $mail;

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

    // メールアドレスのセッター、ゲッター------------
    public function setMail(string $mail) {
        $this->mail = $mail;
    }
    public function getMail() {
        return $this->mail;
    }
    // 

    // 任意のユーザがいるかどうか調べる----------------
    public function check_user() {
        // return ['idm'=> $this->getIdm(), 'studentId'=>$this->getStudentId(), 'name'=>$this->getName()];
        // exit();

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
            if ($this->getPassword() !== null) {
                $data[] = md5($this->getPassword());
                $column[] = 'password';
            }

            $sql .= $column[0].'=:'.$column[0];
            for ($i = 1; $i < count($column); $i++) {
                $sql .= ' AND '.$column[$i].'=:'.$column[$i];
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
            $sql = 'INSERT INTO user (idm, name, password, student_id) VALUES (:idm, :name, :password, :student_id)';
            $stmt = $this->getDbh()->prepare($sql);
            $stmt->bindValue(':idm', $this->getIdm());
            $stmt->bindValue(':name', $this->getName());
            $password = md5('password');
            $stmt->bindValue(':password', $password);
            $stmt->bindValue(':student_id', $this->getStudentId());
            $stmt->execute();
            return true;
        }
    }
    // ---------------------------------------------------------

    // ユーザの参照--------------------------------------------------------
    public function show_user() {

        try {
            $sql = 'SELECT idm, name, student_id, mail FROM user ';
            $data = array();
            $column = array();
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

            if (count($data) > 0) {
                $sql = $sql.'WHERE ';
                $sql = $sql.$column[0].'=:'.$column[0];
                for ($i = 1; $i < count($column); $i++) {
                    $sql = $sql.'AND '.$column[$i].'=:'.$column[$i];
                }

                $stmt = $this->getDbh()->prepare($sql);
                for ($i = 0; $i < count($data); $i++) {
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
                $this->getDbh()->rollBack();
                $retarr = [
                    'result' => 'fail'
                ];
                return $retarr;
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
                'result' => 'success',
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

    // パスワードの変更
    public function update_password($newPassword) {
        if ($this->getStudentId() === '0X000' || $this->getPassword() === null || empty($newPassword)) {
            $retarr = [
                'result'=>'fail'
            ];
            return $retarr;
        }
        try {
            $this->getDbh()->beginTransaction();

            $ret = $this->check_user();

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

    // メールアドレスの更新
    public function update_mail($newMail) {
        if ($this->getStudentId() === '0X000' || $this->getPassword() === null || !preg_match('/^[a-zA-Z0-9_.+-]+@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$/', $newMail)) {
            $retarr = [
                'result'=>'fail'
            ];
            return $retarr;
        }
        try {
            $this->getDbh()->beginTransaction();
            $ret = $this->check_user();
            if ($ret) {
                $sql = 'UPDATE user SET mail=:mail WHERE student_id=:student_id';
                $stmt = $this->getDbh()->prepare($sql);
                $stmt->bindValue(':mail', $newMail);
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
        }catch(Exception $e) {
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