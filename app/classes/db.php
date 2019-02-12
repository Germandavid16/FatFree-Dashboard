<?php

namespace Classes;

class DB extends \DB\SQL {
    
    protected static $_instance;
    
    public $prefix;
    
    public function __construct($dsn, $user = NULL, $pw = NULL, $options = NULL) {
        parent::__construct($dsn, $user, $pw, $options);
        $f3 = \Base::instance();
        $this->prefix = $f3->db_prefix;
    }
        
    /**
    *	Return database instance
    *	@return \Classes\DB
    **/
    public static function instance()
    {
        $f3 = \Base::instance();
        if (!self::$_instance) {
            self::$_instance = new self($f3->db_dsn, $f3->db_user, $f3->db_password);
        }       
        return self::$_instance;
    }
    
    public function install()
    {
        $db = self::instance();
        
        $table_name = $db->prefix.'user';
        $sql = 
        "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) unsigned NOT NULL AUTO_INCREMENT,
            login varchar(50) NOT NULL,
            email varchar(50) NOT NULL,
            password varchar(150) NOT NULL,
            name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            role enum('admin','doctor') NOT NULL,
            gender enum ('M','F','none') NOT NULL DEFAULT 'none',
            npi varchar(100) NOT NULL,
            address varchar(255) NOT NULL,
            license varchar(255) NOT NULL,
            specialty varchar(255) NOT NULL,
            country int(11) unsigned NOT NULL,
            region int(11) unsigned NOT NULL,
            city varchar(50) NOT NULL,
            phone varchar(50) NOT NULL,
            phone fax(50) NOT NULL,
            zip varchar(50) NOT NULL,
            active int(1) NOT NULL,
            date_create datetime NOT NULL,
            date_activation datetime NOT NULL,
            date_change datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY login (login),
            KEY email (email),
            KEY date_create (date_create)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->exec($sql);
        $db->exec("INSERT IGNORE INTO $table_name 
            (login, role, date_create, active)
            VALUES ('admin', 'admin', NOW(), 1)");

        $table_name = $db->prefix.'user_live';
        $sql = 
        "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) unsigned NOT NULL,
            last_visit datetime NOT NULL,
            recovery_hash varchar(150) NOT NULL,
            recovery_time datetime NOT NULL,
            PRIMARY KEY  (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        $db->exec($sql);

        $table_name = $db->prefix.'field';
        $sql = 
        "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) unsigned NOT NULL AUTO_INCREMENT,
            position int(2) unsigned NOT NULL,
            grouping int(1) unsigned NOT NULL,
            PRIMARY KEY  (id),
            KEY position (position),
            KEY grouping (grouping)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->exec($sql);

        $table_name = $db->prefix.'field_alias';
        $sql = 
        "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) unsigned NOT NULL AUTO_INCREMENT,
            field_id int(11) unsigned NOT NULL,
            title varchar(255) NOT NULL,
            main tinyint(1) NOT NULL,
            PRIMARY KEY  (id),
            KEY field_id_main (field_id_main),
            UNIQUE KEY title (title)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->exec($sql);
        
        $table_name = $db->prefix.'record';
        $sql = 
        "CREATE TABLE IF NOT EXISTS $table_name (
            id int(20) unsigned NOT NULL AUTO_INCREMENT,
            uid int(11) NOT NULL,
            member_id varchar(50) NOT NULL,
            type int(11) NOT NULL,
            roster_date date NOT NULL,
            PRIMARY KEY  (id),
            KEY type (type),
            UNIQUE KEY uid_type_date_member (uid,type,roster_date,member_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->exec($sql);

        $table_name = $db->prefix.'data';
        $sql = 
        "CREATE TABLE IF NOT EXISTS $table_name (
            record_id int(20) unsigned NOT NULL,
            field_id int(11) unsigned NOT NULL,
            value text NOT NULL,
            date_create datetime NOT NULL,
            date_change datetime NOT NULL,
            PRIMARY KEY  (record_id,field_id),
            KEY field (field_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->exec($sql);

        $table_name = $db->prefix.'insurance';
        $sql = 
        "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(50) NOT NULL,
            PRIMARY KEY  (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->exec($sql);
    }
}

