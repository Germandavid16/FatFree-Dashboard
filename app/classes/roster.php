<?php

namespace Classes;

use Classes\DB;

class Roster {
	
	protected $data;
	public $fields;
	public $rows;
	public $uid;
	public $groups;
	public $g_headers;
	public $group_data;
	public $deleted_grouptotal;
	public $deleted_header;
	public $deleted_link;
	public $month;
	protected $total_retention;
	protected $user_insurances;

	public $max_head_height = 3;
	
	protected $type;
	protected $date;
	
	protected $head_offset;
	protected $head_height;
	protected $aliases;
	protected $aliases_main;
	
	protected $member_field_id;
	protected $member_field_num;
	
	function __construct($type, $date, $uid=NULL) {
		$this->init($type, $date, $uid);
	}
	
	public function init($type, $date, $uid=NULL) {
		$f3 = \Base::instance();
		if ($uid === NULL) {
			$this->uid = $f3->get('SESSION.user.id');
		} else {
			$this->uid = intval($uid);
		}
		$this->type = intval($type);
		$this->date = $date;
		$this->getAliases();
		$this->getKeyFieldId();
		$this->head_height = 1;
		$this->head_offset = 0;        
		$this->fields = [];
		$this->data = [];
		$this->rows = [];
		$this->groups = array();
		$this->deleted_grouptotal = 0;
		$this->deleted_header = array();
		$this->g_headers = array(
			'STATUS', 'MEMBER_ID', 'MEMBER_NAME', 'FIRST NAME', 'LAST NAME', 'CIN', 'ADDRESS', 'CITY', 'STATE', 'ZIP', 'PHONE','GENDER', 'DOB', 'LOB', 'PROV_ADDR', 'PROV_NAME', 'DIS_DATE', 'DIS_RSN', 'RECERT DATE'
		);
		$this->month = [];
		$this->total_retention = [];
		$this->group_data = [];
		$this->user_insurances = $this->getUserInsurances();
	}
	
	protected function getAliases() {
		$db = DB::instance();
		$res = $db->exec("SELECT id, field_id, title, main FROM {$db->db_prefix}field_alias");
		$this->aliases = [];
		$this->aliases_main = [];
		foreach ($res as $r) {
			$this->aliases[$r['title']] = $r;
			if ($r['main']) {
				$this->aliases_main[$r['field_id']] = $r;
			}
		}
	}
	
	public static function processTitle($title) {
		$title = preg_replace('#[\s]+#', ' ', $title);
		$title = preg_replace('#[\s]*-[\s]*#', '-', $title);
		return strtoupper(trim($title));
	}

	public function getUserInsurances() {
		$u_id = $this->uid;
		$db = DB::instance();
		$row = $db->exec("SELECT insurances FROM {$db->db_prefix}user WHERE id=?", array($u_id));
		if ($row[0]['insurances']) {
			$insurances = unserialize($row[0]['insurances']);
			return (implode(', ',$insurances));
		} else 
			return '';
	}
	
	protected function nextHead() {
		$offset = $this->head_offset;
		for ($i = $offset; $i < count($this->data); $i++) {
			for ($j = 0; $j < $this->max_head_height; $j++) {
				$title = '';
				$val = trim($this->data[$i][0]);
				for ($n = 0; $n <= $j; $n++) {
					if (isset($this->data[$i+$n][0])) {
						$title .= ' '.$this->data[$i+$n][0];
					}
				}
				$title = self::processTitle($title);
				if ($title && $this->aliases[$title] && $val) {
					$this->head_offset = $i;
					$this->getHead();
					return true;
				}
			}
		}
		return false;
	}
	
	protected function getHead() {
		$offset = $this->head_offset;
		foreach ($this->data[$offset] as $i => $col) {
			for ($j = $this->max_head_height-1; $j >= 0; $j--) {
				$title = '';
				$val = trim($this->data[$offset][$i]);
				for ($n = 0; $n <= $j; $n++) {
					if (isset($this->data[$offset+$n][$i])) {
						$title .= ' '.$this->data[$offset+$n][$i];
					}
				}
				$title = self::processTitle($title);
				if ($title && $this->aliases[$title] && $val) {
					if ($this->aliases[$title]['field_id'] == $this->member_field_id) {
						$this->member_field_num = $i;
						$height = $j+1;
						if ($height > $this->head_height) {
							$this->head_height = $height;
						}
					}
					$fid = $this->aliases[$title]['field_id'];
					$this->fields[$i]['id'] = $fid;
					$this->fields[$i]['title'] = $this->aliases_main[$fid]['title'];
					break;
				}
			}
		}
	}
	
	protected function getKeyFieldId() {
		$db = DB::instance();
		$res = $db->exec("SELECT field_id FROM {$db->prefix}field_alias WHERE title = 'MEMBER_ID'");
		if ($res) {
			$this->member_field_id = $res[0]['field_id'];
			return $res[0]['field_id']; 
		}
		return false;        
	}
	
	protected function loadRowData($val, $rid, $fid) {
		$db = DB::instance();
		$val = trim($val);

		$params = ['rid' => $rid, 'fid' => $fid, 'val' => $val, 'valu' => $val];
		$res = $db->exec("INSERT INTO {$db->prefix}data "
		. "(record_id, field_id, value, date_create, date_change) "
		. "VALUES (:rid, :fid, :val, NOW(), NOW()) ON DUPLICATE KEY "
		. "UPDATE value=:valu, date_change = NOW()",
		$params);
	}
	
	protected function loadRow($row) {
		$db = DB::instance();
		$mid = trim($row[$this->member_field_num]);
		$params = ['uid' => $this->uid, 'mid' => $mid, 
			'type' => $this->type, 'date' => $this->date];
		$res = $db->exec("SELECT id FROM {$db->prefix}record "
		. "WHERE uid = :uid AND member_id = :mid AND type = :type AND roster_date = :date", $params);
		$rid = $res[0]['id'];
		if (!$rid) {
			$db->exec("INSERT INTO {$db->prefix}record "
			. "(uid, member_id, type, roster_date) "
			. "VALUES (:uid, :mid, :type, :date) ",
			$params);
			$pdo = $db->pdo();
			$rid = $pdo->lastInsertId();
		}
		foreach ($row as $n => $val) {
			$fid = intval($this->fields[$n]['id']);
			if (isset($this->fields[$n]) && $fid !== false) {
				$this->loadRowData($val, $rid, $fid);
			}
		}        
	}
	
	public function load($file)
	{
		$db = DB::instance();
		$xls = \PHPExcel_IOFactory::load($file);
		$data = $xls->getActiveSheet()->toArray();
		$this->data = $data;
		
		$db->begin();
		while ($this->nextHead()) {
			$start = $this->head_offset + $this->head_height;
			$irow = $start;
			while (true) {
				$this->head_offset = $irow;
				$row = $data[$irow];
				$mid = trim($row[$this->member_field_num]);
				if (!$mid) {
					break;
				}
				$this->loadRow($row);
				$irow++;
			}
		}
		$db->commit();
	}
	
	protected function filterToSql($filter, $key='id', $value='value') 
	{
		if (!is_array($filter)) {
			return ['sql' => [], 'values' => []];
		}
		$filterParams = [];
		$filterValues = [];
		foreach ($filter as $k => $item) {
			$fid = intval($k);
			$val = (string)$item;
			if ($val == 'NULL') {
				$val = '';
			}
			$filterParams[] = "($key = ? AND $value = ?)";
			$filterValues[] = $fid;
			$filterValues[] = $val;
		}
		return ['sql' => $filterParams, 'values' => $filterValues];
	}
	
	public function count() 
	{
		$db = DB::instance();
		$res = $db->exec("SELECT count(id) as count FROM {$db->prefix}record "
		. "WHERE uid = ? AND type = ? AND roster_date = ?",
		[$this->uid, $this->type, $this->date]);
		return (int)$res[0]['count'];
	}
		
	public function getGroups($date = '') 
	{
		$db = DB::instance();
		if ($date) {
			$values = [$this->uid, $this->type, $date];
		} else {
			$values = [$this->uid, $this->type, $this->date];
		}

		$fields = $db->exec("SELECT id FROM field WHERE grouping=1");
		$fids = [];
		foreach ($fields as $field) {
			$fids[] = $field['id'];
		}
		$fidsStr = implode(',', $fids);
		if (!$fidsStr) {
			return [];
		}
		$groups = $db->exec("SELECT t2.field_id, t4.title, t2.value, count(t2.value) as count "
		. "FROM {$db->prefix}record t1 "
		. "LEFT JOIN {$db->prefix}data t2 ON t1.id = t2.record_id "
		. "LEFT JOIN {$db->prefix}field t3 ON t2.field_id = t3.id "
		. "LEFT JOIN {$db->prefix}field_alias t4 ON t3.id = t4.field_id AND t4.main = 1 "
		. "WHERE t1.uid = ? AND t1.type = ? AND t1.roster_date = ? AND t2.field_id IN ($fidsStr) "
		. "GROUP BY t2.field_id, t2.value", $values);
		foreach(array_keys($groups) as $i) {
			if ($groups[$i]['value'] === '') {
				$groups[$i]['value'] = 'NULL';
				unset($groups[$i]);
			}
		}
		return $groups;
	}
	
	protected function readFields()
	{
		if ($this->fields) {
			return false;
		}
		$db = DB::instance();
		$fields = [];
		$fields_num = $db->exec("SELECT t1.id, t1.position, t2.title "
		. "FROM {$db->prefix}field t1 "
		. "LEFT JOIN {$db->prefix}field_alias t2 "
		. "ON t1.id = t2.field_id AND t2.main=1 "
		. "ORDER BY t1.position"
		);        
		foreach ($fields_num as $field) {
			$field['active'] = false;
			$field['_title'] = $field['title'];
			$field['title'] = htmlspecialchars($field['title']);
			$fields[$field['position']] = $field;
		}
		$this->fields = $fields;
		return true;
	}

	protected function parseCells($cells, $class=NULL)
	{
		$db = DB::instance();
		$rows = [];
		foreach ($cells as $cell) {
			$rows[$cell['row_id']]['fields'][$cell['position']]['_value'] = $cell['value'];
			$rows[$cell['row_id']]['fields'][$cell['position']]['value'] = htmlspecialchars($cell['value']);
			if (isset($cell['value_old'])) {
				$rows[$cell['row_id']]['fields'][$cell['position']]['_value_old'] = $cell['value_old'];
				$rows[$cell['row_id']]['fields'][$cell['position']]['value_old'] = htmlspecialchars($cell['value_old']);
			}
			$rows[$cell['row_id']]['fields'][$cell['position']]['class'] = '';
			if ($class != NULL) {
				if ($class == 'changed') {
					if ($cell['value'] != $cell['value_old']) {
						$rows[$cell['row_id']]['fields'][$cell['position']]['class'] = $class;
					}
				} else {
					$rows[$cell['row_id']]['fields'][$cell['position']]['class'] = $class;                    
				}
			}
			$this->fields[$cell['position']]['active'] = true;
		}
		return $rows;
	}
	
	public function read($filter = [])
	{
		$db = DB::instance();
		$this->readFields();
		$filterStr = '';
		$f = $this->filterToSql($filter, 'field_id', 'value');
		if ($f['sql']) {
			$cnt = (int)count($f['sql']);
			$where = implode(' OR ', $f['sql']);
			$filterStr = " AND t1.id IN (SELECT record_id FROM {$db->prefix}data "
			. "WHERE $where "
			. "GROUP BY record_id HAVING count(record_id) >= $cnt)";
		}
		$values = array_merge([$this->uid, $this->type, $this->date], $f['values']);
		$cells = $db->exec("SELECT t1.id as row_id, t3.position, t1.member_id, t2.field_id, t2.value, t4.title "
		. "FROM {$db->prefix}record t1 "
		. "LEFT JOIN {$db->prefix}data t2 ON t1.id = t2.record_id "
		. "LEFT JOIN {$db->prefix}field t3 ON t2.field_id = t3.id "
		. "LEFT JOIN {$db->prefix}field_alias t4 ON t3.id = t4.field_id AND t4.main = 1 "
		. "WHERE t1.uid = ? AND t1.type = ? AND t1.roster_date = ?$filterStr", 
		$values
		);
		$this->rows = $this->parseCells($cells);
		
		return true;
	}
	
	protected function compareChanges($uid, $type, $date1, $date2) 
	{
		$db = DB::instance();
		$cells = $db->exec("SELECT t1.id as row_id, t1old.id as row_id_old, t3.position, "
		. "t1.member_id, t2.field_id, t2.value, t2old.value as value_old, t4.title "
		. "FROM {$db->prefix}record t1 "
		. "LEFT JOIN {$db->prefix}record t1old "
			. "ON t1.uid = t1old.uid AND t1.type = t1old.type "
				. "AND t1old.roster_date = ? AND t1.member_id = t1old.member_id "
		. "LEFT JOIN {$db->prefix}data t2 ON t1.id = t2.record_id "
		. "LEFT JOIN {$db->prefix}data t2old "
		. "ON t1old.id = t2old.record_id AND t2.field_id = t2old.field_id "
		. "LEFT JOIN {$db->prefix}field t3 ON t2.field_id = t3.id "
		. "LEFT JOIN {$db->prefix}field_alias t4 ON t3.id = t4.field_id AND t4.main = 1 "
		. "WHERE t1.id IN ( "
			. "SELECT tt1.id FROM {$db->prefix}record tt1 "
			. "LEFT JOIN {$db->prefix}record tt1old "
			. "ON tt1.uid = tt1old.uid AND tt1.type = tt1old.type "
				. "AND tt1old.roster_date = ? AND tt1.member_id = tt1old.member_id "
			. "LEFT JOIN {$db->prefix}data tt2 ON tt1.id = tt2.record_id "
			. "LEFT JOIN {$db->prefix}data tt2old "
			. "ON tt1old.id = tt2old.record_id AND tt2.field_id = tt2old.field_id AND tt2.value <> tt2old.value "
			. "WHERE tt1.uid = ? AND tt1.type = ? AND tt1.roster_date = ? AND tt2old.value IS NOT NULL"
		. ") "
		. "ORDER BY t1.id",
		[$date2, $date2, $uid, $type, $date1]);
		return $this->parseCells($cells, 'changed');
	}

	protected function compareNew($uid, $type, $date1, $date2, $class='') 
	{
		$db = DB::instance();
		
		$cells = $db->exec("SELECT t1.id as row_id, t3.position, "
		. "t1.member_id, t2.field_id, t2.value, t4.title "
		. "FROM {$db->prefix}record t1 "
		. "LEFT JOIN {$db->prefix}data t2 ON t1.id = t2.record_id "
		. "LEFT JOIN {$db->prefix}field t3 ON t2.field_id = t3.id "
		. "LEFT JOIN {$db->prefix}field_alias t4 ON t3.id = t4.field_id AND t4.main = 1 "
		. "WHERE t1.uid = ? AND t1.type = ? AND t1.roster_date = ? AND t1.member_id NOT IN ( "
			. "SELECT member_id FROM {$db->prefix}record "
			. "WHERE uid = ? AND type = ? AND roster_date = ? "
		. ")",
		[$uid, $type, $date1, $uid, $type, $date2]);
		return $this->parseCells($cells, $class);        
	}
	
	public function compare()
	{
		$db = DB::instance();
		$date1 = $this->date;
		$time1 = strtotime($date1);
		$month2 = date('m', $time1) - 1;
		$time2 = strtotime(date('Y', $time1).'-'.$month2.'-'.date('d', $time1));
		$date2 = date('Y-m-d', $time2);
		$uid = $this->uid;
		$type = $this->type;
		
		$this->readFields();
		$this->rows = [];
		$this->rows = array_merge($this->rows, $this->compareNew($uid, $type, $date2, $date1, 'deleted'));
		$this->rows = array_merge($this->rows, $this->compareChanges($uid, $type, $date1, $date2, 'changes'));
		$this->rows = array_merge($this->rows, $this->compareNew($uid, $type, $date1, $date2, 'added'));
		return true;
	}

	public function clear() 
	{
		$db = DB::instance();
		$rows = $db->exec("SELECT id FROM {$db->prefix}record "
		. "WHERE uid = :uid AND type = :type AND roster_date = :date",
		['uid' => $this->uid, 'type' => $this->type, 'date' => $this->date]);
		$db->begin();
		foreach ($rows as $row) {
			self::deleteRecord($row['id']);            
		}
		$db->commit();
	}
	
	public function exists() 
	{
		$db = DB::instance();
		$res = $db->exec("SELECT count(id) as count FROM {$db->prefix}record "
		. "WHERE uid = :uid AND type = :type AND roster_date = :date "
		. "LIMIT 1",
		['uid' => $this->uid, 'type' => $this->type, 'date' => $this->date]);
		return (bool)$res[0]['count'];
	}
	
	protected static function deleteRecord($id) 
	{
		$db = DB::instance();
		$db->exec("DELETE FROM {$db->prefix}data WHERE record_id = :id", ['id' => $id]);
		$db->exec("DELETE FROM {$db->prefix}record WHERE id = :id", ['id' => $id]);
	}
	
	public static function menu($uid) 
	{
		$db = DB::instance();
		$u_insurances = $db->exec("SELECT insurances FROM {$db->prefix}user WHERE id=$uid");
		$insurances = $db->exec("SELECT id, title FROM {$db->prefix}insurance "
		. "ORDER BY title");

		$menu = [];
		if ($u_insurances[0]['insurances']) {
			$u_insurances = unserialize($u_insurances[0]['insurances']);
			$insurances = array();
			foreach ($u_insurances as $id) {
				$row = $db->exec("SELECT id, title FROM {$db->prefix}insurance  WHERE id = ?"
				. "ORDER BY title", array($id));
				$insurances[] = $row[0];
			}
			foreach ($insurances as $insurance) {
				$iid = $insurance['id'];
				$menu[$iid] = [
					'title' => $insurance['title'], 
					'submenu' => [
						'call' => __CLASS__."::submenu",
						'params' => [$uid, $iid],
					],
				];
			}
		}
		return $menu;
	}

	public static function submenu($uid, $type) 
	{
		$db = DB::instance();
		$dates = $db->exec("SELECT DISTINCT roster_date FROM {$db->prefix}record "
		. "WHERE uid = ? AND type = ? ORDER BY roster_date DESC", [$uid, $type]);
		$menu = [];
		$y = date('Y');
		$m = date('m');
		$d = date('d');
		$times = [
			'time_cur' => time(),
			'time_prev1' => strtotime($y.'-'.($m-1).'-'.$d),
		];
		for ($i=1; $i < $m; $i++) { 
			$times['time_prev'.$i] = strtotime($y.'-0'.($m-$i).'-'.$d);
		}

		foreach ($times as $time) {
			$menu[date('Y', $time)]['title'] = date('Y', $time);
			$menu[date('Y', $time)]['submenu'][date('m', $time)] = ['title' => date('F', $time)];
		}
		foreach ($dates as $k => $v) {
			$date = $v['roster_date'];
			$time = strtotime($date);
			$menu[date('Y', $time)]['title'] = date('Y', $time);
			$menu[date('Y', $time)]['submenu'][date('m', $time)] = ['title' => date('F', $time)];
		}
		return $menu;
	}
	
	public function getTypeName()
	{
		$db = DB::instance();
		$res = $db->exec("SELECT title FROM {$db->prefix}insurance WHERE id = ?",[$this->type]);
		return $res[0]['title'];
	}
	
	public function downloadExcel($fname) 
	{
		$xls = new \PHPExcel();
		$xls->setActiveSheetIndex(0);
		$sheet = $xls->getActiveSheet();

		$style = [
			'fill' =>  [
				'type' => \PHPExcel_Style_Fill::FILL_SOLID, 
			],
			'borders' => [
				'bottom' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],  
				'right' => ['style' => \PHPExcel_Style_Border::BORDER_THIN],  
			],
		];

		if ($this->fields) {
			$j = 0;
			foreach ($this->fields as $field) {
				if ($field['active']) {
					$sheet->getStyleByColumnAndRow($j, 1)->applyFromArray($style);
					$sheet->getColumnDimensionByColumn($j)->setAutoSize(true);
					$sheet->getStyleByColumnAndRow($j, 1)->getAlignment()
						->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet->setCellValueByColumnAndRow($j, 1, $field['title']);
					$j++;
				}
			}
			$i = 2;
			foreach ($this->rows as $row) { 
				$j = 0;
				foreach ($this->fields as $field) {
					if ($field['active']) {
						$cell = $row['fields'][$field['position']];
						$val = $cell['_value'];
						$sheet->getStyleByColumnAndRow($j, $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
						$sheet->getStyleByColumnAndRow($j, $i)->applyFromArray($style);
						if ($cell['class'] == 'added') {
							$sheet->getStyleByColumnAndRow($j, $i)->getFill()
								->getStartColor()->setRGB('F5DACF');
						}
						if ($cell['class'] == 'deleted') {
							$sheet->getStyleByColumnAndRow($j, $i)->getFill()
								->getStartColor()->setRGB('DAF5CF');
						}
						if ($cell['class'] == 'changed') {
							$sheet->getStyleByColumnAndRow($j, $i)->getFill()
								->getStartColor()->setRGB('F5F5AA');
							$sheet->getCommentByColumnAndRow($j, $i)->getText()->createTextRun($cell['_value_old']);
						}
						$sheet->setCellValueByColumnAndRow($j, $i, $val);
						$j++;
					}
				}
				$i++;
			}

		} else {
			$headers = [];
			$headers[] = array('id' => 0, 'title' => 'GROUP NAME');
			$headers[] = array('id'=>1, 'title' => 'INSURANCE NAME');
			$headers = array_merge($headers, $this->g_headers);

			$j = 0;
			foreach ($headers as $key => $header) {
				$sheet->getStyleByColumnAndRow($j, 1)->applyFromArray($style);
				$sheet->getColumnDimensionByColumn($j)->setAutoSize(true);
				$sheet->getStyleByColumnAndRow($j, 1)->getAlignment()
					->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$sheet->setCellValueByColumnAndRow($j, 1, $header['title']);
				$j++;
			}

			$i = 2;
			foreach ($this->group_data as $row) { 
				$j = 0;
				foreach ($headers as $id => $hader) {
					$val = $row[$id];
					$sheet->getStyleByColumnAndRow($j, $i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
					$sheet->getStyleByColumnAndRow($j, $i)->applyFromArray($style);
					$sheet->setCellValueByColumnAndRow($j, $i, $val);
					$j++;
				}
				$i++;
			}
		}
		header('Content-Type:xlsx:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition:attachment;filename="'.$fname.'"');
		$writer = new \PHPExcel_Writer_Excel2007($xls);
		$writer->save('php://output');
		//$writer->close();
		exit;
	}


	// ---------------------------------- Groupping ------------------------------ //
	public function readByGroups() {
		$db = DB::instance();
		$this->setGHeader();
		$groups = $this->getGroupArrays();
		$rows = array();

		$link_data = $this->parseGroupLink();

		if (!$link_data) {
			foreach ($groups as $key => $group) {
				$group_name = $group['group_name'];
				foreach ($group['fields'] as $ins_id => $insurance) {
					$insurance_name = $this->getInsuranceName($ins_id);
					foreach ($insurance as $record) {
						foreach ($record['record_ids'] as $info) {
							$row = [];
							$row[] = $group_name;
							$row[] = $insurance_name;

							$sub_row = $this->getPerFields($ins_id, $info['id']);
							$row = array_merge($row, $sub_row);
							$rows[] = $row;
						}
					}
				}
			}
			$sub_rows = $this->getDeletedGroup();
			$rows = array_merge($rows, $sub_rows);
		} else if ($link_data['type'] == 'group') {
			foreach ($groups as $key => $group) {
				$group_name = $group['group_name'];
				if ($link_data['id'] = $group['id']) {
					foreach ($group['fields'] as $ins_id => $insurance) {
						$insurance_name = $this->getInsuranceName($ins_id);
						foreach ($insurance as $record) {
							if ($link_data['title'] == $group_name || $link_data['title'] == $record['title']) {
								foreach ($record['record_ids'] as $info) {
									$row = [];
									$row[] = $group_name;
									$row[] = $insurance_name;
									$sub_row = $this->getPerFields($ins_id, $info['id']);
									$row = array_merge($row, $sub_row);
									$rows[] = $row;
								}
							}
						}
					}
				}
				$sub_rows = $this->getDeletedGroup();
			}
		} else if ($link_data['type'] == 'deleted') {
			$sub_rows = $this->getDeletedGroup($link_data['id']);
			$rows = array_merge($rows, $sub_rows);
		}
		$this->group_data = $rows;
		return $rows;
	}

	public function getGroupArrays() {
		$db = DB::instance();
		$group_all = $db->exec("SELECT * FROM {$db->prefix}groups");
		$groups = array();
		foreach ($group_all as $key => $group) {
			$insurance_ids = array();
			$insurance_ids = unserialize($group['insurance_id']);
			$fields = unserialize($group['series']);

			$fields_info = [];
			foreach ($fields as $id => $field) {
				$field_info = [];
				foreach ($field as $value) {
					$info = $this->getCountByGroups($value);
					$field_link = $this->getGroupFieldLink($group['id'], $value, 'group');
					$field_info[] = array('title'=>$value, 'count'=>$info['count'], 'link'=>$field_link, 'record_ids' => $info['ids']);
				}
				$fields_info[$id] = $field_info;
			}
			$groups[] = array(
				'id'			=> $group['id'],
				'group_name'	=> $group['group_name'],
				'link'			=> $this->getGroupFieldLink($group['id'], $group['group_name'], 'group'),
				'total'			=> $this->getCountTotalPTField($this->date, $this->uid, $group['group_name']),
				'fields'		=> $fields_info
			);
		}
		$this->deleted_grouptotal = $this->getCountDeletedRecIds($this->date);
		$this->deleted_link = $this->getDeletedLink('deleted');
		$this->groups = $groups;
		return $groups;
	}

	protected function parseGroupLink() {
		$f3 = \Base::instance();
		$link = $f3->get('QUERY');
		$link = str_replace('%20', ' ', $link);
		if ($link) {
			$data = explode('&', $link);
			return array('type'=>$data[0], 'id'=>$data[1], 'title'=>$data[2]);
		} else {
			return array();
		}
	}

	protected function getGroupFieldLink($key, $value, $type) {
		$f3 = \Base::instance();
		$link = $f3->get('PATH');
		$link_data = $this->parseGroupLink();
		$active = false;
		if ($value == $link_data['title']) {
			$active = true;
		}
		$link = $link."?$type&$key&$value";
		return array('active'=>$active, 'link'=>$link);
	}

	protected function getDeletedLink($type) {
		$f3 = \Base::instance();
		$link = $f3->get('PATH');
		$link_data = $this->parseGroupLink();
		$active = false;
		if ($link_data['id'] == 0) {
			$active = true;
		}
		$link = $link."?$type&0";
		return array('active'=>$active, 'link'=>$link);
	}

	protected function getPerFields($type, $rec_id) {
		$db = DB::instance();
		$result = [];
		foreach ($this->g_headers as $key => $field) {
			$value = [$rec_id, $field['id']];
			$row = $db->exec("SELECT value FROM {$db->prefix}data WHERE record_id = ? AND field_id = ?", $value);
			$result[] = $row[0]['value'];
		}
		return $result;
	}

	protected function getCountByGroups($name) {
		$db = DB::instance();
		$value = [$this->date, $name, $this->uid];
		$row = $db->exec("SELECT t1.record_id as id FROM {$db->prefix}data t1"
		." LEFT JOIN {$db->prefix}record as t2 on t2.id = t1.record_id WHERE t2.roster_date=? AND t1.value=? AND t2.uid=?", $value);
		$result = array('count'	=> count($row), 'ids' => $row);
		return $result;
	}

	protected function getInsuranceName($id) {
		$db = DB::instance();
		$value = [$id];
		$row = $db->exec("SELECT title FROM {$db->prefix}insurance WHERE id=?", $value);
		$title = '';
		if (!empty($row))
			$title = $row[0]['title'];
		return $title;
	}

	protected function getInsuranceInfos() {
		$db = DB::instance();
		if ($this->user_insurances)
			$row = $db->exec("SELECT * FROM {$db->prefix}insurance WHERE id IN (".$this->user_insurances.")");
		else 
			$row = [];
		return $row;
	}

	protected function setGHeader() {
		$db = DB::instance();
		$headers = $this->g_headers;
		$this->g_headers = [];
		foreach ($headers as $key => $header) {
			$value = [$header];
			$row = $db->exec("SELECT field_id as id, title FROM {$db->prefix}field_alias WHERE title=?", $value);
			if(!empty($row))
				$this->g_headers[] = $row[0];
		}
	}

	public function getDeletedGroup($id=0) {
		$db = DB::instance();
		$insurances = $this->getInsuranceInfos();
		$deleted_recordids = $this->getDeletedRecordId($insurances, $this->date);
		$rows = [];
		foreach ($deleted_recordids as $ins_id => $fields) {
			if (!$id || $id == $ins_id) {
				$insurance_name = $this->getInsuranceName($ins_id);
				foreach ($fields as $key => $value) {
					$row = [];
					$row[] = 'Deleted Patients';
					$row[] = $insurance_name;
					$sub_row = $this->getPerFields($ins_id, $value['id']);
					$row = array_merge($row, $sub_row);
					$rows[] = $row;
				}
			}
		}
		return $rows;
	}

	public function getDeletedRecordId($insurances, $date) {
		$db = DB::instance();
		$record_ids = [];

		$date1 = $date;
		$time1 = strtotime($date1);
		$month2 = date('m', $time1) - 1;
		$time2 = strtotime(date('Y', $time1).'-'.$month2.'-'.date('d', $time1));
		$date2 = date('Y-m-d', $time2);

		$rows = [];
		$deleted_header = [];
		foreach ($insurances as $key => $insurance) {
			$value = [$this->uid, $date2, $insurance['id'], $this->uid, $date1, $insurance['id']];
			$row = $db->exec("SELECT id FROM {$db->prefix}record WHERE uid=? AND roster_date = ? AND type = ? AND member_id NOT IN (".
				"SELECT member_id FROM {$db->prefix}record WHERE uid=? AND roster_date = ? AND type = ?".")", $value);
			$deleted_header[$key]['count'] = count($row);
			$deleted_header[$key]['title'] = $insurance['title'];
			$deleted_header[$key]['link'] = $this->getGroupFieldLink($insurance['id'], $insurance['title'], 'deleted');
			$rows[$insurance['id']] = $row;
		}
		$this->deleted_header = $deleted_header;
		return $rows;
	}
	// ---------------------------------- End Groupping ------------------------------ //


	protected function getLatestDate() {
		$db = DB::instance();
		$row = $db->exec("SELECT max(roster_date) as date FROM {$db->prefix}record WHERE uid=?", array($this->uid));
		if ($row)
			$date = $row[0]['date'];
		else 
			$date = $this->date;
		return $date;
	}

	protected function getMonthsByLatest() {
		$date = $this->getLatestDate();
		$time = strtotime($date);
		$month = date('m', $time);
		$year = date('Y', $time);
		$day = date('d', $time);

		$months = [];
		for ($i=0; $i < 12; $i++) { 
			$mon = $month - $i;
			if ($mon > 0) {
				$date = date('Y-m-d',strtotime($year.'-'.$mon.'-'.$day));
			} else {
				$mon = 12;
				$month = $month + 12;
				$year = $year - 1;
				$date = date('Y-m-d',strtotime($year.'-'.$mon.'-'.$day));
			}
			$months[] = $date;
		}
		return $months;
	}


	/* ------------------------------------- Per Insurance Chart  ------------------------------------- */
	public function getChartData() {
		$date = $this->date;
		$time = strtotime($date);
		$month = date('m', $time);
		$year = date('Y', $time);
		$day = date('d', $time);
		
		$data = [];
		$data['name'] = $this->getInsuranceName($this->type);

		$total = [];
		$total['name'] = 'Total';

		$temp_fields_data = [];
		$status_name = "LOB";
		$fields = $this->getFieldsByAlias($status_name);
		
		// Per Insurance Month Pie Chart
		$falias_month['data'] = $this->getFieldsDataByAlias($status_name);
		$falias_month['date'] = date('M, Y', strtotime($this->date));

		// Per Insurance Year line chart & total combine line chart
		$falias_year = [];
		$field_titles = [];
		$months = [];
		for ($i=0; $i < 12; $i++) { 
			$mon = $month - $i;
			if ($mon > 0) {
				$date = date('Y-m-d',strtotime($year.'-'.$mon.'-'.$day));
			} else {
				$mon = 12;
				$month = $month + 12;
				$year = $year - 1;
				$date = date('Y-m-d',strtotime($year.'-'.$mon.'-'.$day));
			}
			$data['data'][] = $this->getCountPatients($date, false);
			$total['data'][] = $this->getCountPatients($date, true);
			foreach ($fields as $key => $field) {
				$falias_year[$key]['data'][] = $this->getCountByFieldName($field['value'], $date);
				$falias_year[$key]['title'] = $field['value'];
			}
			$months[] = date('M, Y', strtotime($date));
		}
		foreach ($falias_year as $key => $falias) {
			$falias_year[$key]['data'] = array_reverse($falias['data']);
		}
		$this->month = array_reverse($months);
		$data['data'] = array_reverse($data['data']);
		$total['data'] = array_reverse($total['data']);
		return array('data' => $data, 'total' => $total, 'falias_month' => $falias_month, 'falias_year' => $falias_year, 'status_name' => $status_name);
	}

	protected function getFieldsByAlias($name) {
		$db = DB::instance();
		$values = array($this->uid, $this->type, $name);
		$groups = $db->exec("SELECT t2.field_id, t2.value "
		. "FROM {$db->prefix}record t1 "
		. "LEFT JOIN {$db->prefix}data t2 ON t1.id = t2.record_id "
		. "LEFT JOIN {$db->prefix}field t3 ON t2.field_id = t3.id "
		. "LEFT JOIN {$db->prefix}field_alias t4 ON t3.id = t4.field_id AND t4.main = 1 "
		. "WHERE t1.uid = ? AND t1.type = ? AND t4.title = ? "
		. "GROUP BY t2.field_id, t2.value", $values);
		foreach(array_keys($groups) as $i) {
			if ($groups[$i]['value'] === '') {
				$groups[$i]['value'] = 'NULL';
				unset($groups[$i]);
			}
		}
		return $groups;
	}

	protected function getCountByFieldName($name, $date) {
		$db = DB::instance();
		$values = array($name, $this->uid, $this->type, $date);
		$row = $db->exec("SELECT count(*) as cnt FROM {$db->prefix}data WHERE value = ? AND record_id IN (SELECT id FROM {$db->prefix}record WHERE uid = ? AND type = ? AND roster_date = ?)", $values);
		return $row[0]['cnt'];
	}

	protected function getCountPatients($date, $total_flag) {
		$db = DB::instance();
		if ($total_flag) {
			$value = [$this->uid, $date];
			$row = $db->exec("SELECT count(*) as cnt FROM {$db->prefix}record WHERE uid = ? AND roster_date = ? AND type in (".$this->user_insurances.")", $value);
		} else {
			$value = [$this->type, $this->uid, $date];
			$row = $db->exec("SELECT count(*) as cnt FROM {$db->prefix}record WHERE type = ? AND uid = ? AND roster_date = ?", $value);
		}
		return $row[0]['cnt'];
	}

	protected function getFieldsDataByAlias($name, $date='') {
		if (!$date) 
			$date = $this->date;
		$alias_groups = $this->getGroups($date);
		$fields = [];
		foreach ($alias_groups as $key => $value) {
			if ($value['title'] == $name) {
				$fields[] = array('title' => $value['value'], 'field_id' => $value['field_id'], 'value' => $value['count']);
			}
		}
		return $fields;
	}
	/*----------------------------------- End Per Insurances Chart -----------------------------*/

	/*  
	** Get Datas For First Chart On First Page
	*/
	public function getAveData() {
		$new = [];
		$new['name'] = 'NEW PATIENTS';
		$new['total'] = 0;

		$deleted = [];
		$deleted['name'] = 'DELETED PATIENTS';
		$deleted['total'] = 0;

		$disenrolled = [];
		$disenrolled['name'] = 'DISENROLLED';
		$disenrolled['total'] = 0;

		$months = $this->getMonthsByLatest();
		$str_months = [];
		foreach ($months as $key => $date) {
			$new_count = $this->getCountTotalPTField($date, $this->uid, $new['name']);
			$new['data'][] = $new_count;
			$new['total'] += $new_count;

			$dis_count = $this->getCountTotalPTField($date, $this->uid, $new['name']);
			$disenrolled['data'][] = $dis_count;
			$disenrolled['total'] += $dis_count;

			$del_count = $this->getCountDeletedRecIds($date);
			$deleted['data'][] = $del_count;
			$deleted['total'] +=$del_count;

			$time_temp = strtotime($date);
			$date = date('M, Y', $time_temp);
			$str_months[] = $date;
		}
		$this->month = array_reverse($str_months);
		$new['data'] = array_reverse($new['data']);
		$deleted['data'] = array_reverse($deleted['data']);
		$disenrolled['data'] = array_reverse($disenrolled['data']);
		return array('new' => $new, 'deleted' => $deleted, 'disenrolled' => $disenrolled);
	}

	protected function getCountTotalPTField($date, $uid, $group_name) {
		$db = DB::instance();
		$row = $db->exec("SELECT series FROM {$db->prefix}groups WHERE group_name = ?", array($group_name));
		$series = $row[0]['series'];
		$fields = unserialize($series);
		$t_count = 0;
		foreach ($fields as $key => $field) {
			foreach ($field as $value) {
				$t_count += $this->getCountPerField($date, $uid, $value);
			}
		}
		return $t_count;
	}

	protected function getCountPerField($date, $uid, $name) {
		$db = DB::instance();
		if ($this->user_insurances) {
			$row = $db->exec("SELECT count(*) as cnt FROM {$db->prefix}record WHERE roster_date = ? AND uid = ? AND type IN ($this->user_insurances) AND id IN (".
				"SELECT record_id as id FROM {$db->prefix}data WHERE value=?"
				.")", array($date, $uid, $name));
			if ($row)
				return $row[0]['cnt'];
			else 
				return 0;
		} else return 0;
	}

	protected function getCountDeletedRecIds($date) {

		$insurances = $this->getInsuranceInfos();
		$db = DB::instance();

		$date1 = $date;
		$time1 = strtotime($date1);
		$month2 = date('m', $time1) - 1;
		$time2 = strtotime(date('Y', $time1).'-'.$month2.'-'.date('d', $time1));
		$date2 = date('Y-m-d', $time2);

		$t_count = 0;
		foreach ($insurances as $key => $insurance) {
			$value = [$this->uid, $date2, $insurance['id'], $this->uid, $date1, $insurance['id']];
			$row = $db->exec("SELECT id FROM {$db->prefix}record WHERE uid=? AND roster_date = ? AND type = ? AND member_id NOT IN (".
				"SELECT member_id FROM {$db->prefix}record WHERE uid=? AND roster_date = ? AND type = ?".")", $value);

			$t_count += count($row);
		}
		return $t_count;
	}

	// ----------------------- GET TOTAL RETENTIONS -------------------- //
	public function getRetentionData() {
		$db = DB::instance();
		$month_data = [];
		$insurances = $this->getInsuranceInfos();
		$date = $this->getLatestDate();
		$row_month = $db->exec("SELECT type, count(type) as count FROM {$db->prefix}record WHERE uid = ? AND roster_date = ? GROUP BY type", array($this->uid, $date));

		$month_data = $year_data = [];
		foreach ($insurances as $key => $insurance) {
			$month_data[$key]['title'] = $year_data[$key]['title'] = $insurance['title'];
			$month_data[$key]['count'] = $year_data[$key]['count'] = 0;
			foreach ($row_month as $mon_value) {
				if ($mon_value['type'] == $insurance['id']) {
					$month_data[$key]['count'] = $mon_value['count'];
				}
			}
			$year_data[$key]['count'] = $this->getMonthlyTotal($insurance['id'], $date);
		}
		$year_data[$key+1]['title'] = "Total";
		$year_data[$key+1]['count'] = array_reverse($this->total_retention);
		$time = strtotime($date);
		$date = date('M, Y', $time);
		return array('month_data' => $month_data, 'date'=>$date, 'year_data' => $year_data, 'months' => $months);
	}

	protected function getMonthlyTotal($id, $date) {
		$db = DB::instance();
		$months = [];
		$counts = [];
		$time = strtotime($date);
		$month = date('m', $time);
		$year = date('Y', $time);
		$total = $this->total_retention;

		$months = $this->getMonthsByLatest();
		$str_months = [];
		foreach ($months as $key => $date) {
			$row = $db->exec("SELECT count(*) as cnt FROM {$db->prefix}record WHERE type=? AND roster_date = ? AND uid = ?", array($id, $date, $this->uid));
			$total[$key] += $row[0]['cnt'];
			$counts[] = $row[0]['cnt'];

			$time_temp = strtotime($date);
			$date = date('M, Y', $time_temp);
			$str_months[] = $date;
		}
		$counts = array_reverse($counts);
		$this->month = array_reverse($str_months);
		$this->total_retention = $total;
		return $counts;
	}
}