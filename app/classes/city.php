<?php
    
namespace Classes;

use Classes\DB;

class City extends \Prefab {
    
    public function getCountries() {
        $db = DB::instance();
        return $db->exec("SELECT id, name, code FROM countries WHERE name <> '' ORDER BY name");
    }
    
    public function getRegions($cid) {
        $db = DB::instance();
        return $db->exec("SELECT id, name, code FROM regions "
        . "WHERE country_id = ? AND name <> '' AND country_id > 0 ORDER BY country_id, name", $cid);
    }

    public function getCities($rid, $cid=false) {
        $db = DB::instance();
        if ($cid) {
        return $db->exec("SELECT id, name FROM cities WHERE country_id = ? "
        . "AND name <> '' AND country_id > 0 AND region_id > 0 "
        . "AND region_id = ? ORDER BY country_id, region_id, name", [$cid, $rid]);
        }
        return $db->exec("SELECT id, name FROM cities WHERE region_id = ? "
        . "AND name <> '' AND country_id > 0 AND region_id > 0 "
        . "ORDER BY country_id, region_id, name", $rid);
    }
    
    public function getCountryById($id) {
        $db = DB::instance();
        $rows = $db->exec("SELECT * FROM country WHERE id = ?", $id);
        if ($rows) {
            return $rows[0];
        }
        return false;
    }
    
    public function getRegionById($id) {
        $db = DB::instance();
        $rows = $db->exec("SELECT * FROM region WHERE id = ?", $id);
        if ($rows) {
            return $rows[0];
        }
        return false;
    }

    public function getCityById($id) {
        $db = DB::instance();
        $rows = $db->exec("SELECT * FROM cities WHERE id = ?", $id);
        if ($rows) {
            return $rows[0];
        }
        return false;
    }

    public function checkCountry($id) {
        $db = DB::instance();
        $rows = $db->exec("SELECT id FROM countries WHERE id = ? AND name <> ''", $id);
        return (bool) $rows;
    }

    public function checkRegion($id, $country_id) {
        $db = DB::instance();
        $rows = $db->exec("SELECT id FROM regions WHERE id = ? AND country_id = ? AND name <> ''", 
            [$id, $country_id]);
        return (bool) $rows;
    }

    public function checkCity($id, $region_id, $country_id) {
        $db = DB::instance();
        $rows = $db->exec("SELECT id FROM cities WHERE id = ? AND region_id = ? AND country_id = ? AND name <> ''", 
            [$id, $region_id, $country_id]);
        return (bool) $rows;
    }

}