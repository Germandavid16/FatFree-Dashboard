<?php

namespace Models;

use Classes\DB;
use Classes\City;
use Classes\Mail;
use Audit;
use Auth;

class Medical_practice extends \Classes\Model {
	
	const TBL = 'medical_practice';
	
	protected static $currentMPT;
		
	public function __construct() {  
		self::$tbl = self::TBL;
		parent::__construct();
	}

	public function setMPTINFO($user_id=0) {
		$db = DB::instance();
		$f3 = \Base::instance();
		$tbl = self::TBL;
		$row = $db->exec("SELECT * FROM $tbl WHERE user_id = ?", [$user_id]);
		$info = $row[0];
		$f3->set('SESSION.mpt', $info);
	}

	public function logout() {
		$f3 = \Base::instance();
		$f3->clear('SESSION.mpt');
		self::$currentMPT = false;
	}

	public function parseFields($fields) {
		$data = array(
					'name'			=> $fields['mpt_name'],
					'address1'		=> $fields['mpt_address1'],
					'address2'		=> $fields['mpt_address2'],
					'city'			=> $fields['mpt_city'],
					'state'			=> $fields['mpt_state'],
					'zipcode'		=> $fields['mpt_zipcode'],
					'country'		=> $fields['mpt_country'],
					'tel_number'	=> $fields['mpt_tel'],
					'fax'			=> $fields['mpt_fax'],
					'email'			=> $fields['mpt_email'],
					'npi'			=> $fields['mpt_npi'],
					'tin'			=> $fields['mpt_tin'],
					'manager'		=> $fields['mpt_manager'],
					'affiliations'	=> $fields['mpt_affilication'],
					'type'			=> $fields['mpt_type'],
					'contact_tel'	=> $fields['mpt_contact_tel'],
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