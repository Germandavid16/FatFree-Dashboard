<?php

namespace Models;

use Classes\DB;

class Groups extends \Classes\Model {
	
	const TBL = 'groups';

	public $errors = [];
		
	public function __construct() {  
		self::$tbl = self::TBL;
		parent::__construct();
	}

	public function getInsuranceName() {
		$db = DB::instance();
		$tablename = 'insurance';
		$rows = $db->exec("SELECT * FROM {$db->db_prefix}$tablename");
		return $rows;
	}

	public function setGroup($group_name, $insurances, $field_series) {
		$db = DB::instance();
		$tbl = self::TBL;
		foreach ($insurances as $key => $value) {
			if (empty($value)) {
				unset($insurances[$key]);
			}
		}
		$insurance = serialize($insurances);
		$temp_series = array();
		foreach ($field_series as $key => $serie) {
			foreach ($serie as $value) {
				if ($value != '')
					$temp_series[$key][] = $value;
			}
		}

		$series = serialize($temp_series);

		$db->exec("INSERT INTO $tbl (group_name, insurance_id, series) VALUES ('$group_name', '$insurance', '$series')");
	}

	public function updateGroup($id, $group_name, $insurances, $field_series) {
		$db = DB::instance();
		$tbl = self::TBL;
		foreach ($insurances as $key => $value) {
			if (empty($value)) {
				unset($insurances[$key]);
			}
		}
		$insurance = serialize($insurances);
		$temp_series = array();
		foreach ($field_series as $key => $serie) {
			foreach ($serie as $value) {
				if ($value != '')
					$temp_series[$key][] = $value;
			}
		}
		$series = serialize($temp_series);
		$query = "UPDATE $tbl SET group_name='$group_name', insurance_id='$insurance', series='$series' WHERE id=$id";
		$result = $db->exec($query);
		return $result;
	}

	public function deleteById($id) {
		$db = DB::instance();
		$tbl = self::TBL;
		$query = "DELETE FROM $tbl WHERE id=$id";
		$result = $db->exec($query);
		return $result;
	}

	public function getGroupData() {
		$db = DB::instance();
		$tbl_name = self::TBL;

		$rows = $db->exec("SELECT * FROM $tbl_name");
		$data = array();
		foreach ($rows as $key => $row) {
			$insurance_ids = unserialize($row['insurance_id']);
			$insurnace_names = array();

			if (!empty($insurance_ids)) {
				foreach ($insurance_ids as $key1 => $insurance_id) {
					$insurnace_names[$key1] = $this->getInsuranceDataById($insurance_id);	
				}
				$data[$key] = array(
					'id'			=> $row['id'],
					'group_name'	=> $row['group_name'],
					'insurance_ids'	=> $insurnace_names,
					'fields'		=> unserialize($row['series'])
				);
			}
		}
		return $data;
	}

	public function getInsuranceDataById($id) {
		$db = DB::instance();
		$tbl_name = 'insurance';
		$row = $db->exec("SELECT * FROM {$db->db_prefix}$tbl_name WHERE id = $id");
		return $row[0];
	}
}