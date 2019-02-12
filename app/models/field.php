<?php

namespace Models;

use Classes\DB;
use Classes\Roster;

class Field extends \Prefab {
                
    public function select($id=false) {
        $db = DB::instance();
        $where = '';
        $id = intval($id);
        if ($id) {
            $where = " WHERE t1.id = $id";
        }
        $rows = $db->exec("SELECT t1.id, t1.position, t1.grouping, t2.id as alias_id, t2.title "
        . "FROM {$db->prefix}field t1 "
        . "LEFT JOIN {$db->prefix}field_alias t2 "
        . "ON (t1.id = t2.field_id AND t2.main = 1)"
        . "$where ORDER BY t1.position");
        if ($id && $rows) {
            $rows = $rows[0];
        }
        return $rows;
    }
    
    public function getAliases($fid) {
        $db = DB::instance();
        $rows = $db->exec("SELECT id, title, main "
        . "FROM {$db->prefix}field_alias "
        . "WHERE field_id = :id "
        . "ORDER BY title", ['id' => $fid]);
        return $rows;
    }
    
    public function setPosition($id, $position) {
        $db = DB::instance();
        $id = intval($id);
        $position = intval($position);
        $db->begin();
        $pos = $db->exec("SELECT min(position) as min, max(position) as max FROM {$db->prefix}field");
        if ($pos) {
            $pos = $pos[0];
        }
        $curpos =  $db->exec("SELECT position FROM {$db->prefix}field WHERE id = $id");
        if ($curpos) {
            $curpos = $curpos[0]['position'];
        }
        if (!$pos || !$curpos || $position == $curpos 
            || $position < $pos['min'] || $position > $pos['max']) {
            return false;
        }

        if ($position > $curpos) {
            for ($i = $curpos+1; $i <= $position; $i++) {
                $db->exec("UPDATE {$db->prefix}field SET position = position - 1 WHERE position = $i");
            }
        } else
        if ($position < $curpos) {
            for ($i = $curpos-1; $i >= $position; $i--) {
                $db->exec("UPDATE {$db->prefix}field SET position = position + 1 WHERE position = $i");
            }
        } 
        $db->exec("UPDATE {$db->prefix}field SET position = $position WHERE id = $id");
        $db->commit();
        return true;
    }
    
    public function setGrouping($id, $active = 1) {
        $db = DB::instance();
        $id = intval($id);
        $active = intval($active);
        $db->exec("UPDATE {$db->prefix}field SET grouping = $active WHERE id = $id");
        return true;
    }
    
    public function delete($id) {
        $db = DB::instance();
        $db->begin();

        $position = $db->exec("SELECT position FROM {$db->prefix}field WHERE id = ?", $id);
        if ($position) {
            $position = $position[0]['position'];
        }
        $res = $db->exec("UPDATE {$db->prefix}field SET position = position-1 WHERE position > ?", $position);
        $res = $db->exec("DELETE FROM {$db->prefix}record WHERE id IN "
        . "(SELECT record_id FROM data WHERE field_id = ?)", $id);
        $res = $db->exec("DELETE FROM {$db->prefix}data WHERE field_id = ?", $id);
        $res = $db->exec("DELETE FROM {$db->prefix}field_alias WHERE field_id = ?", $id);
        $res = $db->exec("DELETE FROM {$db->prefix}field WHERE id = ?", $id);
                
        $db->commit();
        return (bool)$res;
    }
    
    public function deleteAlias($id) {
        $db = DB::instance();
        $res = $db->exec("DELETE FROM {$db->prefix}field_alias "
        . "WHERE id = :id AND main <> 1", ['id' => $id]);
        return (bool)$res;
    }

    public function add($title) {
        $db = DB::instance();
        $db->begin();
        $maxpos = $db->exec("SELECT (IFNULL(max(POSITION),0)) as max FROM {$db->prefix}field");
        if ($maxpos) {
            $maxpos = intval($maxpos[0]['max']);
        }
        $res = $db->exec("INSERT INTO {$db->prefix}field (position) VALUES (?)", $maxpos+1);
        $pdo = $db->pdo();
        $fid = $pdo->lastInsertId();
        $db->commit();
        if ($res && $fid) {
            $res = $this->addAlias($fid, $title, 1);
            if (!$res) {
                $this->delete($fid);
                return false;
            }
            return $fid;
        }
        return false;
    }
    
    public function addAlias($fid, $title, $main=0) {
        $db = DB::instance();
        $res = $db->exec("INSERT INTO {$db->prefix}field_alias "
        . "(field_id, title, main) VALUES (:fid, UPPER(:title), :main)",
            ['fid' => $fid, 'title' => $title, 'main' => $main]);
        $pdo = $db->pdo();
        $fid = $pdo->lastInsertId();
        if ($res) {
            return $fid;
        }
        return false;
    }
    
    public function updateAlias($id, $title) {
        $db = DB::instance();
        $res = $db->exec("UPDATE {$db->prefix}field_alias "
        . "SET title = :title WHERE id = :id");
        return (bool)$res;
    }

    public function existsID($id) {
        $db = DB::instance();
        $rows = $db->exec("SELECT id FROM {$db->prefix}field "
        . "WHERE id = :id", ['id' => $id]);
        return (bool)count($rows);
    }
    
    public function exists($title) {
        $db = DB::instance();
        $rows = $db->exec("SELECT id FROM {$db->prefix}field_alias "
        . "WHERE title = UPPER(:title)", ['title' => $title]);
        return (bool)count($rows);
    }
    
    public function setMainAlias($id) {
        $db = DB::instance();
        $rows = $db->exec("SELECT field_id FROM {$db->prefix}field_alias "
        . "WHERE id = :id", ['id' => $id]);
        $fid = $rows[0]['field_id'];
        if (!$fid) {
            return false;
        }
        $res = $db->exec([
            "UPDATE {$db->prefix}field_alias SET main=0 WHERE field_id = :fid",
            "UPDATE {$db->prefix}field_alias SET main=1 WHERE id = :id",
        ], [
            ['fid' => $fid],
            ['id' => $id],
        ]);
        return (bool)$res;
    }
    
    public function splitFieldsString($fields) {
        $fields = explode("\n", $fields);
        foreach ($fields as $k => $field) {  
            $fields[$k] = Roster::processTitle($field);
            if (!$fields[$k]) {
                unset($fields[$k]);
            }
        }
        $fields = array_values($fields);
        return $fields;
    }
}

