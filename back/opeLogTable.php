<?php
require_once('common.php');
require_once('opeDB.php');
require_once('opeUserTable.php');

class OpeLogTable extends OpeUserTable {
    // logテーブルのログを更新する------------------
    public function update_log() {

        try {

            $this->getDbh()->beginTransaction();

            if ($this->check_user() === false) {
                if (!$this->add_user()) {
                    $this->getDbh()->rollBack();
                    return false;
                }
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
}

?>