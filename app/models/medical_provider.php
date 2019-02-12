<?php

namespace Models;

use Classes\DB;
use Classes\City;
use Classes\Mail;
use Audit;
use Auth;

class Medical_provider extends \Classes\Model {
	
	const TBL = 'medical_provider';
	
	protected static $currentMPR;
		
	public function __construct() {  
		self::$tbl = self::TBL;
		parent::__construct();
	}

	public function setMPRINFO($user_id) {
		$db = DB::instance();
		$f3 = \Base::instance();
		$tbl = self::TBL;
		$row = $db->exec("SELECT * FROM $tbl WHERE user_id = ?", [$user_id]);
		$info = $row[0];
		$f3->set('SESSION.mpr', $info);
	}

	public function logout() {
		$f3 = \Base::instance();
		$f3->clear('SESSION.mpr');
		self::$currentMPR = false;
	}

	public function parseFields($fields) {
		$data = array(
					'first_name'	=> $fields['mpr_first_name'],
					'last_name'		=> $fields['mpr_last_name'],
					'middle_name'	=> $fields['mpr_middle_name'],
					'gender'		=> $fields['mpr_gender'],
					'dob'			=> $fields['mpr_dob'],
					'license'		=> $fields['mpr_license'],
					'license_state'	=> $fields['mpr_license_state'],
					'npi'			=> $fields['mpr_npi'],
					'board_spec'	=> $fields['mpr_board_specialty'],
					'board_name'	=> $fields['mpr_board_name'],
					'board_date'	=> $fields['mpr_board_date'],
					'board_num'		=> $fields['mpr_board_num'],
					'tax'			=> $fields['mpr_tax'],
					'dea_num'		=> $fields['mpr_dea_num'],
					'address1'		=> $fields['mpr_address1'],
					'city'			=> $fields['mpr_city'],
					'state'			=> $fields['mpr_state'],
					'country'		=> $fields['mpr_country'],
					'zipcode'		=> $fields['mpr_zipcode'],
					'email'			=> $fields['mpr_email'],
					'tel'			=> $fields['mpr_tel'],
					'cell'			=> $fields['mpr_cell'],
				);
		return $data;
	}

	public function getIdByUserId($uid) {
		$db = DB::instance();
		$tbl = self::TBL;
		$row = $db->exec("SELECT id FROM $tbl WHERE user_id = ?", array($uid));
		return $row[0]['id'];
	}
}