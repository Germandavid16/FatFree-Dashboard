<?php

namespace Models;

use Classes\DB;

class Insurance extends \Classes\Model {
    
    const TBL = 'insurance';
    
    public $errors = [];
    
    public function __construct() {        
        self::$tbl = self::TBL;
        parent::__construct();
        $this->adminWriteOnlyFields[] = 'title';
    }
    
    public static function getMany() {
        $db = DB::instance();
        $tbl = $db->prefix.self::TBL;
        return $db->exec("SELECT id, title "
        . "FROM {$db->prefix}$tbl "
        . "ORDER BY title");
    }

    public static function delete($id) {
        $db = DB::instance();
        $tbl = $db->prefix.self::TBL;
        $db->begin();
        $res = $db->exec("DELETE FROM {$db->prefix}$tbl WHERE id = ?", $id);
        $res = $db->exec("DELETE FROM {$db->prefix}data WHERE record_id IN "
        . "(SELECT id FROM {$db->prefix}record WHERE type = ?)", $id);
        $res = $db->exec("DELETE FROM {$db->prefix}record WHERE type = ?", $id);
        $db->commit();
        return true;
    }
    
    public static function processTitle($title) {
        return trim($title);
    }
    
    public function validateTitle($title) {
        $title = self::processTitle($title);
        $id = $this->id ? $this->id : 0;
        if (!$title || $this->count(['title = ? and id <> ?', $title, $id])) {
            $this->errors[] = 'Insurance already exists';
            return false;
        }
        return true;
    }
    
    public function filterFields($fields) {
        $fields['title'] = self::processTitle($fields['title']);
        parent::filterFields($fields);        
        if (!$this->validateTitle($fields['title'])) {
            unset($fields['title']);
        }
        return $fields;
    }
    
}

