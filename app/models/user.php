<?php

namespace Models;

use Classes\DB;
use Classes\City;
use Classes\Mail;
use Audit;
use Auth;

class User extends \Classes\Model {
	
	const TBL = 'user';
	
	protected static $currentUser;
		
	public function __construct() {  
		self::$tbl = self::TBL;
		parent::__construct();
	}
	
	/**
	*	Return current user
	*	@return \Models\User
	**/  
	public static function current() {
		$f3 = \Base::instance();
		$user_id = $f3->get('SESSION.user.id');
		if (!$user_id) {
			return false;
		}
		if (!self::$currentUser) {
		   self::$currentUser = new self();
		   self::$currentUser->load(["id = :id", "id" => $user_id]);
		} 
		if (!self::$currentUser->active) {
			self::$currentUser->logout();
		}
		return self::$currentUser;
	}
	
	public function validateLogin($login) {
		$login = trim(strtolower($login));
		if (!preg_match('#^[a-zA-Z][a-zA-Z0-9_\.]{2,29}$#', $login)) {
			$this->errors[] = 'Not valid Login. Must consist of 3-30 letters, numbers, dots and underscores. The first character must be a letter';
			return false;
		}
		$db = DB::instance();
		$tbl = self::TBL;
		$uid = intval($this->id);

		if ($db->exec("SELECT id FROM {$db->prefix}$tbl WHERE login = LOWER(?) AND id <> ?", [$login, $uid])) {
			$this->errors[] = 'Login "'.$login.'" already exists';
			return false;
		}
		return true;
	}
	

	public function validateEmail($email) {
		$email = trim(strtolower($email));
		if (!self::checkEmail($email)) {
			$this->errors[] = 'Not valid E-mail';
			return false;
		}
		$db = DB::instance();
		$tbl = $db->prefix.self::$tbl;
		$uid = intval($this->id);
		if ($db->exec("SELECT id FROM {$db->prefix}$tbl WHERE email = LOWER(?) AND id <> ?", [$email, $uid])) {
			$this->errors[] = 'E-mail "'.$email.'" already exists';
			return false;
		}
		return true;
	}

	public static function validatePassword($password) {
		if ($password === false) return true;
		$result = preg_match('#^[^\s]{2,30}$#', $password);
		if ($result) {
			return true;
		} else {
			return "The password must consist of 3-30 non-whitespace characters";
		}
	}
	
	public static function checkEmail($email) {
		return preg_match('#[^@]+@[^@]+#', $email);
	}
	
	public function authorize($login, $password) {
		$f3 = \Base::instance();
		$user = $f3->get('SESSION.user');
		if ($user['login'] == $login || $user['email'] == $login) {
			$this->load(['id = ?', $user['id']]);
			$this->setLastVisit($user['id']);
			return true;
		}
		$this->logout();
		if (!$password) $passwordHash = '';
		else $passwordHash = self::crypt($password);
		$authField = '';
		if (self::checkEmail($login)) {
		   $authField = 'email';
		} else {
		   $authField = 'login';
		}
		if ($authField) {
			$auth = new Auth($this, ['id' => $authField, 'pw' => 'password']);
			$result = $auth->login($login, $passwordHash);
			if ($result && $this->active) {
				$this->load(["$authField = ?", $login]);
				$f3->set('SESSION.user', $this->cast());
				self::$currentUser = $this;
				$this->setLastVisit($this->id);
				return true;
			}
		}
		return false;
	}
	  
	public function setLastVisit($uid) {
		$db = DB::instance();
		$tbl = $db->prefix.self::$tbl;
		$tbl_live = $tbl.'_live';
		$uid = intval($uid);
		if ($uid) {
		$db->exec("INSERT INTO {$db->prefix}$tbl_live (id, last_visit) "
		. "VALUES ($uid, NOW()) ON DUPLICATE KEY UPDATE id=$uid, last_visit=NOW()");
		}
	}
	
	public function logout() {
		$f3 = \Base::instance();
		$f3->clear('SESSION.user');
		self::$currentUser = false;
	}
	
	public static function crypt($password) {
		$f3 = \Base::instance();
		if (!$password) {
			return '';
		}
		return hash("sha256", $password);
	}
	
	public function changePassword($newpass, $oldpass=false) {
		$result = true;
		if ($oldpass !== false) {
			$hash = self::crypt($oldpass);
			if ($this->password != $hash) {
				$result = 'Wrong password';
			}
		}
		if ($result === true) {
			$valid = self::validatePassword($newpass);
			if ($valid === true) {
				$this->password = self::crypt($newpass);
				$this->save();
			} else {
				$result = $valid;
			}
		}
		return $result;
	}

	public function setPassword($newpass) {
		$valid = self::validatePassword($newpass);
		if ($valid === true) {
			$this->password = self::crypt($newpass);
		}
		return $valid;
	}
	
	public function filterFields($fields) {
		$filterFields = parent::filterFields($fields);
		$city = City::instance();
		if (!$fields['email'] && !$fields['login']) {
			$this->errors[] = 'E-mail or login must be defined';
		}
		if ($fields['email'] && !$this->validateEmail($fields['email'])) {
			unset($fields['email']);
		}
		if ($fields['login'] && !$this->validateLogin($fields['login'])) {
			unset($fields['login']);
		}

		if ($fields['country'] && !$city->checkCountry($fields['country'])) {
			$this->errors[] = 'Not valid country';
			unset($fields['country']);
		}
		if ($fields['region'] && !$city->checkRegion($fields['region'], $fields['country'])) {
			$this->errors[] = 'Not valid region';
			unset($fields['region']);
		}
		// if ($fields['city'] && !$city->checkCity($fields['city'], $fields['region'], $fields['country'])) {
		//     $this->errors[] = 'Not valid city';
		//     unset($fields['city']);
		// }
		return $fields;
	}
	
	public static function getMany() {
		$db = DB::instance();
		$tbl = $db->prefix.self::TBL;
		$tbl_live = $tbl.'_live';
		$tbl_clinic = "medical_practice";
		$tbl_doctor = "medical_provider";
		return $db->exec("SELECT t1.id, t1.login, t1.email, t1.name, t1.last_name, "
		. "t1.role, t1.date_create, t1.date_change, t1.active, t2.last_visit, t3.name as clinic_name, t4.first_name as doc_first_name, t4.last_name as doc_last_name "
		. "FROM $tbl t1 "
		. "LEFT JOIN $tbl_live t2 ON t1.id = t2.id "
		. "LEFT JOIN $tbl_clinic t3 ON t1.id = t3.user_id "
		. "LEFT JOIN $tbl_doctor t4 ON t1.id = t4.user_id "
		. "ORDER BY t1.date_create DESC");
	}
	
	public static function delete($id) {
		$db = DB::instance();
		$tbl = $db->prefix.self::TBL;
		$tbl_live = $tbl.'_live';
		$db->begin();
		$res = $db->exec("DELETE FROM $tbl_live WHERE id = ?", $id);
		$res = $db->exec("DELETE FROM $tbl WHERE id = ?", $id);
		$res = $db->exec("DELETE FROM {$db->prefix}data WHERE record_id IN "
		. "(SELECT id FROM {$db->prefix}record WHERE uid = ?)", $id);
		$res = $db->exec("DELETE FROM {$db->prefix}record WHERE uid = ?", $id);
		$db->commit();
		return true;
	}

	public static function active($id, $state) {
		$db = DB::instance();
		$id = intval($id);
		$tbl = $db->prefix.self::TBL;
		if ($state == 'true') {
			$state = 1;
		} else if ($state == 'false') {
			$state = 0;
		} else {
			$state = (bool) $state;
		}
		$res = $db->exec("UPDATE $tbl SET active = ? WHERE id = ?", [$state, $id]);
		return true;
	}
	
	public static function sendRecoveryLetter($login)
	{
		$f3 = \Base::instance();
		$db = DB::instance();
		$tbl = $db->prefix.self::TBL;
		$tbl_live = $tbl.'_live';
		$authField = '';
		if (self::checkEmail($login)) {
		   $authField = 'email';
		} else {
		   $authField = 'login';
		}
		if ($authField) {
			$rows = $db->exec("SELECT id, email FROM $tbl WHERE $authField = ? AND active = 1", [$login]);
			$row = $rows[0];
			$uid = $row['id'];
			$email = $row['email'];
			if ($email) {
				$code = mt_rand(100000, 999999);
				$code_hash = self::crypt($code);
				$db->begin();
				$db->exec("INSERT IGNORE INTO $tbl_live (id) VALUES (?)", [$uid]);
				$db->exec("UPDATE $tbl_live SET recovery_hash = ?, recovery_time = NOW() "
				. "WHERE id = ?", [$code_hash, $uid]);
				$db->commit();

				$mail = new Mail();
				$mail->params['LINK'] = $f3->get('PROTO').'://'.$f3->get('SITE')
				. $f3->alias('passwordRecovery')."?email=$email&code=$code";
				$mail->subject = 'Password recovery';
				$mail->to = $email;
				$mail->tpl = 'recovery';
				return $mail->send();
			}
		}
		return false;
	}
	
	public static function checkRecoveryCode($email, $code)
	{
		$f3 = \Base::instance();
		$db = DB::instance();
		$tbl = $db->prefix.self::TBL;
		$tbl_live = $tbl.'_live';
		$code_hash = self::crypt($code);
		$rows = $db->exec("SELECT t1.id FROM $tbl t1 "
		. "LEFT JOIN $tbl_live t2 ON t1.id = t2.id "
		. "WHERE email = ? AND recovery_hash = ? "
		. "AND recovery_time > NOW() - INTERVAL 1 HOUR"
		, [$email, $code_hash]);
		$row = $rows[0];
		$id = $row['id'];
		if ($id) {
			$db->begin();
			$db->exec("UPDATE $tbl SET password = '' WHERE id = ?", [$id]);
			$db->exec("UPDATE $tbl_live SET recovery_hash = '', "
			. "recovery_time = '0000-00-00 00:00:00' "
			. "WHERE id = ?", [$id]);
			$db->commit();
			return true;
		}
		return false;
	}

	public function getInsuranceInfos() {
		$db = DB::instance();
		$row = $db->exec("SELECT * FROM {$db->prefix}insurance");
		return $row;
	}
	
}

