<?php

namespace Controllers;

use Classes\View;
use Classes\DB;
use Classes\Roster;
use Classes\Messages;
use Classes\City;
use Models\User;
use Models\Field;
use Models\Insurance;
use Models\Medical_practice;
use Models\Medical_provider;
use Models\Groups;
use Web;

class Main {
	
	protected $query;
	
	public function __construct() 
	{
		$this->parseQuery();
	}
	
	protected function parseQuery()
	{
		$f3 = \Base::instance();
		$query = [];
		if ($f3->get('GET.filter')) {
			$query = $f3->get('GET.filter');
		}
		$this->query = $query;
	}
	
	protected function getLink($key, $value) 
    {
        $f3 = \Base::instance();
        $query = $this->query;
        $result = [];
        $result['active'] = false;
        if ($query[$key] && 
            $query[$key] == $value) 
        {
            $result['active'] = true;
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
        $paramsStr = [];
        foreach ($query as $k => $v) {
            $paramsStr[] = "$k=$v";
        }
        $result['link'] = $f3->get('PATH');
        if ($paramsStr) {
            $result['link'] .= '?'.implode('&', $paramsStr);
        }
        return $result;
    }
	
	protected function createFieldLink($key, $value) {
		$f3 = \Base::instance();
        $query = $this->query;
        $result = [];
        $result['active'] = false;
        if ($query[$key] && $query[$key] == $value) 
        {
            $result['active'] = true;
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
        if (count($query) > 1) {
        	$query = [];
        	$query[$key] = $value;
        }
        $paramsStr = [];
        foreach ($query as $k => $v) {
            $paramsStr[] = "filter[$k]=$v";
        }
       	
        $result['link'] = $f3->get('PATH');
        $links = explode('&', $result['link']);
        $result['link'] = $links[0];
        if ($paramsStr) {
            $result['link'] .= '&'.implode('&', $paramsStr);
        }
        return $result;
	}

	protected function access($roles=[])
	{
		$f3 = \Base::instance();
		$user = $f3->get('SESSION.user');
		if (!$user) {
			$f3->reroute('@login');
		}
		if (!$roles) {
			return true;
		}
		if (!in_array($user['role'], $roles)) {
			$f3->error(404);
		}
	}
	
	protected function checkCSRF()
	{
		$f3 = \Base::instance();
		$post_token = $f3->get('POST.token');
		$session_token = $f3->get('SESSION.csrf');
		return ($post_token == $session_token);
	}
		
	public function actionUserLogout(\Base $f3, $params) {
		$this->access();
		$user = new User();
		$user->logout();
		$f3->reroute('@login');
	}
	
	public function actionUserLogin(\Base $f3, $params)
	{
		$view = View::instance();
		$user = $f3->get('SESSION.user');
		if ($user) {
			$f3->reroute('@roster');
		}
		$login = $f3->get('POST.login');
		if ($login && $this->checkCSRF()) {
			$user = new User();
			$password = $f3->get('POST.password');
			if ($user->authorize($login, $password)) {
				if (!$password) {
					$f3->reroute('@userPassword');
				} else {
					$user_info = $f3->get('SESSION.user');
					
					$mpt = new Medical_practice();
					$mpt->setMPTINFO($user_info['id']);

					$mpr = new Medical_provider();
					$mpr->setMPRINFO($user_info['id']);
					
					$f3->reroute('@roster');
				}
			} else {
				$view->setMessage('Error', 'Wrong login or password');
			}
		}
		
		$view->setvar('title', 'Precision quality');
		$view->setvar('h1_inner', 'Sign in');
		$view->setvar('page', 'login');
		//$view->setvar('h2_inner', 'Authorization');
		$view->show('login');
	}

	public function actionAdminFieldsOne(\Base $f3, $params)
	{
		$view = View::instance();
		$fid = intval($params['id']);
		$fields = Field::instance();
		if (!$fid || !$fields->existsID($fid)) {
			$f3->error(404);
		}
		$field = $fields->select($fid);
		if ($this->checkCSRF()) {
			$error = '';
			$common = '';
			
			$main = $f3->get('POST.main');
			if ($main) {
				$fields->setMainAlias($main);
			}
			$delete = $f3->get('POST.delete');
			if ($delete && $fields->deleteAlias($delete)) {                
				$view->setvar('deleted', true);
			}
			$newAliases = $fields->splitFieldsString($f3->get('POST.aliases'));
			foreach ($newAliases as $alias) {
				if ($fields->exists($alias)) {
					$error .= "Alias or alias \"$alias\" already exists<br>\n";
				} else {
					if ($fields->addAlias($fid, $alias)) {
						$common .= "Alias \"$alias\" added<br>\n";
					} else {
						$error .= "Alias \"$alias\" not added. Unknown error occurred.<br>\n";
					}
				}
			}
			$view->setSessionMessage('Common', $common);
			$view->setSessionMessage('Error', $error);
			if (!$f3->ajax()) {
				$f3->reroute($f3->get('URI'));
			}
		}
		$rows = $fields->getAliases($fid);
		$view->setvar('title', 'Aliases of field "'.$field['title'].'"');
		$view->setvar('h1', 'Aliases of field "'.$field['title'].'"');
		$view->setvar('rows', $rows);
		$view->setvar('main', $field['alias_id']);
		$view->show('admin_fields_one');
	}
	
	public function actionAdminFields(\Base $f3, $params)
	{
		$this->access(['admin']);
		$view = View::instance();
		$view->setvar('title', 'Fields');
		$view_name = 'admin_fields';
		$fields = Field::instance();

		if ($this->checkCSRF()) {
			$error = '';
			$common = '';
			$delete = $f3->get('POST.delete');
			$position = $f3->get('POST.position');
			$grouping = intval($f3->get('POST.grouping'));
			if ($delete) {
				$view->setvar('deleted', $fields->delete($delete));
			}
			if ($position) { 
				$id = intval($f3->get('POST.id'));
				$view->setvar('positionChanged', $fields->setPosition($id, $position));
			}
			if ($grouping) { 
				$state = intval($f3->get('POST.state'));
				$view->setvar('groupingChanged', $fields->setGrouping($grouping, $state));
			}
			if ($delete || $position || $grouping) {
				$view->show($view_name);
				return;
			}
			$newFields = $fields->splitFieldsString($f3->get('POST.fields'));
			foreach ($newFields as $field) {
				if ($fields->exists($field)) {
					$error .= "Field or alias \"$field\" already exists<br>\n";
				} else {
					if ($fields->add($field)) {
						$common .= "Field \"$field\" added<br>\n";
					} else {
						$error .= "Field \"$field\" not added. Unknown error occurred.<br>\n";
					}
				}
			}
			$view->setSessionMessage('Common', $common);
			$view->setSessionMessage('Error', $error);
			if (!$f3->ajax()) {
				$f3->reroute($f3->get('URI'));
			}
			if ($delete || $position || $grouping || $newFields) {
				$view->show($view_name);
				return;
			}
			$dontAskConfirm = $f3->get('POST.dontAskConfirm');
			if ($dontAskConfirm) {
				$f3->set('SESSION.dontAskConfirm', true);
			}
		}
		$rows = $fields->select();
		foreach ($rows as $k => $row) {
			$rows[$k]['link'] = $f3->alias('adminFieldsOne', ['id' => $row['id']]);
		}
		if (!$f3->get('POST.dontAskConfirm') && $f3->get('SESSION.dontAskConfirm')) {
			$view->setvar('dontAskConfirm', true);
			$f3->clear('SESSION.dontAskConfirm');
		}

		$view->setvar('rows', $rows);
		$view->show($view_name);
	}
	
	public function actionCaptcha(\Base $f3, $params)
	{
		$img = new \Image();
		$img->captcha('fonts/Athiti-Regular.ttf',16,4,'SESSION.captcha_code','', 0xFFFFFF, 0x446688);
		$img->render();
	}
	
	public function actionPasswordRecovery(\Base $f3, $params)
	{
		$view = View::instance();
		$user = $f3->get('SESSION.user');
		if ($user) {
			$f3->reroute('@roster');
		}
		if ($this->checkCSRF()) {
			if (strtoupper($f3->get('POST.code')) != strtoupper($f3->get('SESSION.captcha_code'))) {
				$view->setSessionMessage('Error', 'Wrong code');
				$f3->reroute($f3->get('URI'));
			}
			$login = $f3->get('POST.login');
			if ($login) {
				User::sendRecoveryLetter($login);
				$view->setSessionMessage('Common', 'Recovery letter has been sent to your E-mail');
				$f3->reroute('@login');
			}            
		}
		$email = $f3->get('GET.email');
		$code = $f3->get('GET.code');
		if ($email && $code) {
			$ok = User::checkRecoveryCode($email, $code);
			if ($ok) {
				$user = new User();
				if ($user->authorize($email, '')) {
					$f3->reroute('@userPassword');
				} else {
					$f3->reroute('@passwordRecovery');
				}
			}
		}

		$view->setvar('title', 'Password recovery - Patient roster management');
		$view->setvar('h1_inner', 'Password recovery');
		$view->setvar('page', 'password_recovery');
		$view->setvar('h2_inner', 'Password recovery');
		$view->show('recovery');
		
	}

	public function actionUserPassword(\Base $f3, $params)
	{
		$this->access();
		$view = View::instance();
		$user = User::current();
		$oldpass = $f3->get('POST.password_old');
		$pass = $f3->get('POST.password_new');
		$confirm = $f3->get('POST.password_confirm');
		$ok = ($pass == $confirm);
		if ($this->checkCSRF()) {
			if ($pass && $ok) {
				if (!$user->password) {
					$oldpass = false;
				}
				$result = $user->changePassword($pass, $oldpass); 
				if ($result === true) {
					$view->setMessage('Common', 'Password changed');
				} else {
					$view->setMessage('Error', $result);
				}
			} else {
				if ($pass) {
					$view->setMessage('Error', "Password confirmation is wrong.");
				}
			}
		}
		if (!$user->password) {
			$view->setMessage('Common', 'Your password is empty. Set your password, please.');
			$view->setvar('passwordEmpty', true);
		}
		$view->show('password');
	}

	public function actionAdminUsersEdit(\Base $f3, $params)                             
	{
		$this->access(['admin']);
		$view = View::instance();
		$city = City::instance();
		$view->setvar('title', 'Add user');
		$user = new User();
		$mpt = new Medical_practice();
		$mpr = new Medical_provider();

		$user->reset();
		$mpt->reset();
		$mpr->reset();

		$pass = $f3->get('POST.password_new');
		$confirm = $f3->get('POST.password_confirm');
		$ok = ($pass == $confirm);
		if ($params['id']) {
			$user->load(['id = ?', $params['id']]);
			$mpt->load(['user_id = ?', $params['id']]);
			$mpr->load(['user_id = ?', $params['id']]);
			if ($user->dry()) {
				$f3->error(404);
			}

			$mpt_empty = false;
			if ($mpt->dry())
				$mpt_empty = true;

			$mpr_empty = false;
			if ($mpr->dry())
				$mpr_empty = true;

			$name = $user->email ? $user->email : $user->login;
			$view->setvar('title', 'Edit user "'.$name.'"');
			$view->setvar('h3', 'Edit user "'.$name.'"');
			
			$values = $user->extractFields();
			foreach ($values as $k => $v) {
				$view->setvar("user_$k", $v);
			}

			if (!$mpt_empty) {
				$mpt_values = $mpt->extractFields();
				foreach ($mpt_values as $k => $v) {
					$view->setvar("mpt_$k", $v);
				}
			}

			if (!$mpr_empty) {
				$mpr_values = $mpr->extractFields();
				foreach ($mpr_values as $k => $v) {
					$view->setvar("mpr_$k", $v);
				}
			}
			$insurances = $user->getInsuranceInfos();
			$view->setvar('insurances', $insurances);
		}

		if ($this->checkCSRF()) {
			if ($pass && $ok) {
				$result = $user->setPassword($pass); 
				if ($result !== true) {
					$view->setSessionMessage('Error', $result);
				}
			}
			if ($f3->get('POST.getregions')) {
				$country = $f3->get('POST.country');
				$view->setvar('regions', $city->getRegions($country));
				$view->show();
				return true;
			}
			if ($f3->get('POST.getcities')) {
				$country = $f3->get('POST.country');
				$region = $f3->get('POST.region');
				$view->setvar('cities', $city->getCities($region, $country));
				$view->show();
				return true;
			}
			if ($f3->get('POST.email') || $f3->get('POST.login')) {
				$fields = $f3->get('POST');
				$user_info = array(
					'email'		=> $fields['email'],
					'login'		=> $fields['login'],
					'name'		=> $fields['name'],
					'last_name'	=> $fields['last_name'],
					'role'		=> $fields['role'],
					'gender'	=> $fields['gender'],
					'password_new'		=> $fields['password_new'],
					'password_confirm'	=> $fields['password_confirm'],
					'insurances'		=> serialize($fields['insurances'])
				);

				$mpt_info = $mpt->parseFields($fields);
				
				$mpr_info = $mpr->parseFields($fields);

				$user->copyfrom($user->filterFields($user_info));

				$curdate = date('Y-m-d h:i:s');
				if ($user->errors) {
					$text = implode("<br>\n", $user->errors);
					$view->setSessionMessage('Error', $text);
					$f3->reroute($f3->get('URI'));
				} else {
					if ($user->id) {
						$user->date_change = $curdate;
						$mpt_info['user_id'] = $user->id;
						$mpr_info['user_id'] = $user->id;
						$mpt->copyfrom($mpt_info);
						$mpr->copyfrom($mpr_info);

						if ($user->update()) {
							if ($mpt_empty) {
								$mpt->insert();
							} else {
								$mpt->update();
							}

							if ($mpr_empty) {
								$mpr->insert();
							} else
								$mpr->update();

							$view->setSessionMessage('Common', "Changes saved");
						} else {
							$view->setSessionMessage('Error', "Changes have not saved. Unknow error");                            
						}
						$f3->reroute($f3->get('URI'));

					} else {
						$user->date_create = $curdate;
						$user->date_change = $curdate;
						$user->active = 1;
						if ($user->insert()) {
							$mpt_info['user_id'] = $user->id;
							$mpr_info['user_id'] = $user->id;
							
							$mpt->copyfrom($mpt_info);
							$mpt->insert();
							
							$mpr->copyfrom($mpr_info);
							$mpr->insert();

							$name = $user->email ? $user->email : $user->login;
							$view->setSessionMessage('Common', "User \"$name\" successful added");
						} else {
							$view->setSessionMessage('Error', "User has not added. Unknow error");                            
						}
						$f3->reroute('@adminUsers');
					}
				}
			}
		}

		$default_country = 230;
		$default_region = 3646;
		// $default_city = 2609054;
		$default_city = 'New York';
		$view->setvar('regions', []);
		// $view->setvar('cities', []);
		if ($user->id) {
			$view->setvar('default_country', $user->country);
			$view->setvar('default_region', $user->region);
			$view->setvar('default_city', $user->city);
			if ($user->country) {
				$view->setvar('regions', $city->getRegions($user->country));
			}
			if ($user->region) {
			$view->setvar('cities', $city->getCities($user->region, $user->country));
			}
		} else {
			$view->setvar('default_country', $default_country);
			$view->setvar('default_region', $default_region);
			$view->setvar('default_city', $default_city);
			$view->setvar('regions', $city->getRegions($default_country));
			$view->setvar('cities', $city->getCities($default_region, $default_country));
		}
		$view->setvar('countries', $city->getCountries());
		$view->show('admin_users_edit');
	}
	
	public function actionAdminUsers(\Base $f3, $params) {
		$this->access(['admin']);
		$view = View::instance();
		$users = User::getMany();
		if ($this->checkCSRF()) {
			$delete = $f3->get('POST.delete');
			if ($delete && User::delete($delete)) {                
				$view->setvar('deleted', true);
			}            
			$active = $f3->get('POST.active');
			if ($active) {
				$state = $f3->get('POST.state');
			}
			if ($active && User::active($active, $state)) {                
				$view->setvar('active_changed', true);
			}            
		}
		foreach ($users as $k => $v) {
			$users[$k]['link'] = $f3->alias('adminUsersEdit', ['id' => $v['id']]);
		}
		$view->setvar('current_user', $f3->get('SESSION.user.id'));
		$view->setvar('users', $users);
		$view->setvar('title', 'Users');
		// $view->setvar('h1', 'Users');
		$view->show('admin_users');
	}
	
	public function actionAdminInsurances(\Base $f3, $params) {
		$this->access(['admin']);
		$view = View::instance();
		$rows = Insurance::getMany();
		if ($this->checkCSRF()) {
			$delete = $f3->get('POST.delete');
			if ($delete && Insurance::delete($delete)) {                
				$view->setvar('deleted', true);
			}
		}
		foreach ($rows as $k => $v) {
			$rows[$k]['link'] = $f3->alias('adminInsurancesEdit', ['id' => $v['id']]);
		}
		$view->setvar('rows', $rows);
		$view->setvar('title', 'Insurances');
		// $view->setvar('h1', 'Insurances');
		$view->show('admin_insurances');
	}
	
	public function actionAdminInsurancesEdit(\Base $f3, $params) {
		$this->access(['admin']);
		$view = View::instance();
		$insurance = new Insurance();
		$view->setvar('title', 'Add insurance');
		if ($params['id']) {
			$insurance->load(['id = ?', $params['id']]);
			if ($insurance->dry()) {
				$f3->error(404);
			}
			$name = $insurance->title;
			$view->setvar('title', 'Edit insurance "'.$name.'"');
			$view->setvar('h1', 'Edit insurance "'.$name.'"');
			$values = $insurance->extractFields();
			foreach ($values as $k => $v) {
				$view->setvar("item_$k", $v);
			}
		}
		if ($this->checkCSRF()) {
			if ($f3->get('POST.title')) {
				$fields = $f3->get('POST');
				$insurance->copyfrom($insurance->filterFields($fields));
				if ($insurance->errors) {
					$text = implode("<br>\n", $insurance->errors);
					$view->setSessionMessage('Error', $text);
					$f3->reroute($f3->get('URI'));
				} else {
					if ($insurance->id) {
						if ($insurance->update()) {
							$view->setSessionMessage('Common', 'Changes saved');
						} else {
							$view->setSessionMessage('Error', 'Unknow error');
						}
						$f3->reroute($f3->get('URI'));
					} else {
						if ($insurance->insert()) {
							$view->setSessionMessage('Common', 'Insurance successfull added');
						} else {
							$view->setSessionMessage('Error', 'Unknow error');
						}
						$f3->reroute('@adminInsurances');
					}
				}
			}    
		}
		$view->show('admin_insurances_edit');
	}
	
	public function actionMain(\Base $f3, $params) {
		$f3->reroute('@roster');
	}
	
	public function actionRosterType(\Base $f3, $params){
		$type = $params['type'];
		$year = date('Y');
		$month = date('m');
		$f3->reroute("@rosterTypeYearMonth(@type=$type,@year=$year,@month=$month)");
	}
	
	public function actionRosterTypeYear(\Base $f3, $params) {
		$type = $params['type'];
		$year = $params['year'];
		$month = date('m');
		$f3->reroute("@rosterTypeYearMonth(@type=$type,@year=$year,@month=$month)");
	}
	
	public function actionRoster(\Base $f3, $params) {
		$this->access(['admin', 'doctor']);
		$view = View::instance();
		$view->setvar('title', 'Roster');
		if ($params['type'] && $params['year'] && $params['month']) {
			$type = intval($params['type']);
			$year = $params['year'];
			$month = intval($params['month']);

			$date = intval($year).'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-01';
			$monthText = date('F', strtotime($date));
			$roster = new Roster($type, $date);
			$typeText = $roster->getTypeName();
			if ($this->checkCSRF()) {
				if ($f3->get('POST.clear')) {
					$roster->clear();
					$f3->reroute($f3->get('URI'));
				}
			}
			if (!$roster->exists() && $f3->get('FILES.roster.tmp_name')) {
				$roster->load($f3->get('FILES.roster.tmp_name'));                
				$f3->reroute($f3->get('URI'));
			}
			$diff = [];
			$diff['link'] = $f3->alias("rosterTypeYearMonthDiff", 
					['type' => $type, 'year' => $year, 'month' => $month]);

			$group = [];
			$fname = $year.'-'.date('F', strtotime($date)).'-'.$typeText;

			$groupping = [];
			$groupping['link'] = $f3->alias("rosterTypeYearMonthGroup", 
					['type' => $type, 'year' => $year, 'month' => $month]);

			$chart = [];
			$chart['link'] = $f3->alias("rosterTypeYearMonthChart", 
					['type' => $type, 'year' => $year, 'month' => $month]);

			if ($f3->get('ALIAS') == 'rosterTypeYearMonthDiff') {
				$roster->compare();
				$diff['active'] = true;
				$groupping['active'] = false;
				$chart['active'] = false;

			} else if ($f3->get('ALIAS') == 'rosterTypeYearMonthChart') {
				$chart_data = $roster->getChartData();
				$diff['active'] = false;
				$groupping['active'] = false;
				$chart['active'] = true;

			} else if ($f3->get('ALIAS') == 'rosterTypeYearMonthGroup') {
				$group_data = $roster->readByGroups();
				$diff['active'] = false;
				$groupping['active'] = true;
				$chart['active'] = false;

			} else {
				$diff['active'] = false;
				$groupping['active'] = false;
				$chart['active'] = false;
				$roster->read($f3->get('GET.filter'));
				$groups = $roster->getGroups(); 

				foreach ($groups as $kgroup => $group) {
					$id = $group['field_id'];
					$key = $id;
					$value = $group['value'];
					$link = $this->createFieldLink($key, $value);
					if ($link['active']) {
						$fname .= '-'.$value;
					}
					$groups[$kgroup] = array_merge($groups[$kgroup], $link);
				}
				$fieldsView = [];
				foreach ($groups as $group) {
					$fieldsView[$group['title']][] = $group;
				}
			}
			if ($f3->get('ALIAS') == 'rosterTypeYearMonthDiff') {
				$fname .= '-DIFF';
			}
			if ($f3->get('ALIAS') == 'rosterTypeYearMonthGroup') {
				$fname .= '-GROUP';
			}
			$fname = strtoupper($fname).'.xlsx';
			if ($f3->get('POST')) {
				$download = $f3->get('POST.download');
				if ($download){
					$roster->downloadExcel($fname);
				}
			}
			$view->setvar('rosterYear', $year);
			$view->setvar('rosterMonth', $monthText);
			$view->setvar('rosterType', $typeText);
			$view->setvar('diff', $diff);
			$view->setvar('group', $group);
			$view->setvar('chosen', true);
			$view->setvar('fieldsView', $fieldsView);
			$view->setvar('count', $roster->count());
			$view->setvar('fields', $roster->fields);
			$view->setvar('rows', $roster->rows);
			$view->setvar('base', $f3->get('base_url'));

			$view->setvar('groupping', $groupping);
			$view->setvar('group_headers', $roster->g_headers);
			$view->setvar('groups', $roster->groups);
			$view->setvar('group_data', $group_data);
			$view->setvar('del_grouptotal', $roster->deleted_grouptotal);
			$view->setvar('deleted_header', $roster->deleted_header);
			$view->setvar('deleted_link', $roster->deleted_link);

			$view->setvar('chart', $chart);
			$view->setvar('chart_data', $chart_data);
			$view->setvar('chart_month', $roster->month);
		} else {

			$date = date('Y-m-01');
			$roster = new Roster(0, $date);
			$ave_data = $roster->getAveData();
			$retention_data = $roster->getRetentionData();
			$view->setvar('retention_data', $retention_data);
			$view->setvar('ave_chart', $ave_data);
			$view->setvar('chart_month', $roster->month);
		}
		$mpt_info = $f3->get('SESSION.mpt');
		if (isset($mpt_info['name']))
			$view->setvar('mpt_session_name', $mpt_info['name']);
		$view->show('roster');
	}
	
	public function actionInstallDB(\Base $f3, $params) {
		$db = DB::instance();
		$db->install();
	}

	public function actionAdminGroups(\Base $f3) {
		$this->access(['admin']);
		$view = View::instance();
		$view->setvar('title', 'Groups');
		$view_name = 'admin_groups';
		$groups = new Groups();
		$insurances = $groups->getInsuranceName();
		$group_data = $groups->getGroupData();
		
		if ($this->checkCSRF()) {
			$delete_id = $f3->get('POST.delete');
			if ($groups->deleteById($delete_id)) {                
				$view->setvar('deleted', true);
			}
		}

		foreach ($group_data as $key => $value) {
			$group_data[$key]['link'] = $f3->alias('adminGroupsEdit', ['id' => $value['id']]);
		}

		$view->setvar('group_data', $group_data);
		$view->setvar('insurances', $insurances);
		$view->show($view_name);
	}

	public function actionAdminGroupEdit(\Base $f3, $params) {
		$this->access(['admin']);
		$view = View::instance();
		$groups = new Groups();
		$view->setvar('title', 'Add Group');
		$insurances_all = $groups->getInsuranceName();
		if ($params['id']) {
			$groups->load(['id = ?', $params['id']]);
			if ($groups->dry()) {
				$f3->error(404);
			}
			$name = $groups->group_name;
			$view->setvar('title', 'Edit insurance "'.$name.'"');
			$values = $groups->extractFields();
			$insurances = unserialize($values['insurance_id']);
			foreach ($insurances as $key => $value) {
				$insurances[$key] = $groups->getInsuranceDataById($value);
			}
			$values['insurances'] = $insurances;
			$values['series'] = unserialize($values['series']);
			$view->setvar('data', $values);
		}
		if ($this->checkCSRF()) {
			if ($f3->get('POST.group_name')) {
				$data = $f3->get('POST');
				$insurances = $data['insurance_ids'];
				$fields = $data['fields'];
				$f_count = $data['f_count'];
				$series = array();
				$i = 0;
				foreach ($data['insurance_ids'] as $key => $insurance_id) {
					if (!empty($insurance_id)) {
						$series[$insurance_id] = array_slice($fields, $i, $f_count[$key]);
						$i = $i + $f_count[$key];
					}
				}
				if (isset($data['id'])) {
					if ($groups->updateGroup($data['id'], $data['group_name'], $insurances, $series)) {
						$view->setSessionMessage('Common', 'Changes saved');
					} else {
						$view->setSessionMessage('Error', 'There is no to update');
					}
					$f3->reroute('@adminGroups');
				} else 
					$groups->setGroup($data['group_name'], $insurances, $series);
				$f3->reroute('@adminGroups');
			}
		}
		$view->setvar('insurances_all', $insurances_all);
		$view->show('admin_groups_edit');
	}

	public function actionProfile(\Base $f3, $params) {
		$this->access(['admin', 'doctor']);
		$view = View::instance();
		$city = City::instance();

		$user = new User();
		$mpt = new Medical_practice();
		$mpr = new Medical_provider();

		$user->reset();
		$mpt->reset();
		$mpr->reset();

		$user_id = $f3->get('SESSION.user.id');
		$mpt->load(['user_id = ?', $user_id]);
		$mpr->load(['user_id = ?', $user_id]);

		if ($this->checkCSRF()) {
			if ($pass && $ok) {
				$result = $user->setPassword($pass); 
				if ($result !== true) {
					$view->setSessionMessage('Error', $result);
				}
			}

			$fields = $f3->get('POST');

			$mpt_info = $mpt->parseFields($fields);
			$mpr_info = $mpr->parseFields($fields);

			$mpt_info['user_id'] = $user_id;
			$mpr_info['user_id'] = $user_id;

			$mpt->copyfrom($mpt_info);
			$mpr->copyfrom($mpr_info);

			$mpt->update();
			$mpr->update();
		}

		$mpt->load(['user_id = ?', $user_id]);
		$mpr->load(['user_id = ?', $user_id]);

		$mpt_empty = false;
		if ($mpt->dry())
			$mpt_empty = true;
		if (!$mpt_empty) {
			$mpt_values = $mpt->extractFields();
			foreach ($mpt_values as $k => $v) {
				$view->setvar("mpt_$k", $v);
			}
		}

		$mpr_empty = false;
		if ($mpr->dry())
			$mpr_empty = true;
		if (!$mpr_empty) {
			$mpr_values = $mpr->extractFields();
			foreach ($mpr_values as $k => $v) {
				$view->setvar("mpr_$k", $v);
			}
		}
		$view->setvar("full_name", $mpt_values['name']);
		$view->setvar("title", 'Edit Profile');
		$view->show('profile');
	}
}
