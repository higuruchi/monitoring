<?php
require_once('common.php');
require_once('opeDB.php');
require_once('opeUserTable.php');

class signInSignOut extends OpeUserTable {

    public function login() {
        if ($this->getPassword() !== null && $this->getStudentId() !== -1) {
            $sql = 'SELECT id, idm, name, student_id, access_right '.
                    'FROM user '.
                    'WHERE student_id=:student_id AND password=:password';
            $stmt = $this->getDbh()->prepare($sql);
            $stmt->bindValue(':student_id', $this->getStudentId());
            $stmt->bindValue(':password', $this->getPassword());
            $stmt->execute();
            $ret = $stmt->fetch();
            if ($ret) {
                return $ret;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


}
?>