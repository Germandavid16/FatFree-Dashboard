<?php

namespace Classes;

use Classes\DB;

class Model extends \DB\SQL\Mapper {
    
    protected static $tbl;
    
    public $closedFields = ['id', 'password', 'date_create', 'date_activation'];
    public $adminWriteOnlyFields = ['login','role'];
    public $errors = [];
    public $roles = ['admin', 'doctor'];
    public $role;
    
    public function filterFields($fields) {
        $allfields = $this->fields();
        foreach ($fields as $k => $v) {
            if (!in_array($k, $allfields) || !$this->checkWritePerm($k)) {
               unset($fields[$k]); 
            }
        }
        return $fields;
    }
    
    public function __construct($role = NULL) {        
        $f3 = \Base::instance();
        $db = DB::instance();        
        if ($role === NULL) {
            $this->role = $f3->get('SESSION.user.role');
        } else {
            $this->role = $role;
        }
        parent::__construct($db, $db->prefix.self::$tbl, NULL, 0);
    }
    
    public function extractFields() {
        $fields = $this->fields();
        $res = [];
        foreach ($fields as $k => $v) {
            if ($this->checkReadPerm($v)) {
                $res[$v] = $this->get($v);
            }            
        }
        return $res;
    }
    
    public function checkReadPerm($field) {
        if (!$this->role) return true;
        $isadmin = ($this->role == 'admin');
        if ($isadmin) {
        } else {
            if (in_array($field, $this->closedFields)) {
                return false;
            }            
        }
        return true;
    }
    
    public function checkWritePerm($field) {        
        if (!$this->role) return true;
        $isadmin = ($this->role == 'admin');
        if ($isadmin) {
        } else {
            if (in_array($field, $this->closedFields)) {
                return false;
            }            
            if (in_array($field, $this->adminWriteOnlyFields)) {
                return false;
            }            
        }
        return true;
    }
    
}
