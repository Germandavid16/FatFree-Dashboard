<?php

namespace Classes;

class Menu {
	
	protected $menu;
	protected $menuList;
	protected $path;
	protected $level;
	
	function __construct($menu) 
	{        
		$this->current = false;
		$this->menu = $menu;
		$this->menuList = [];
		$this->path = '/';
		$this->level = 0;
		$this->proccessMenu($this->menu);
	}
	
	function proccessMenu($menu) 
	{
		$f3 = \Base::instance();
		if ($menu['call']) {
			$params = [];
			if ($menu['params']) {
				$params = $menu['params'];
			}
			$menu = call_user_func_array($menu['call'], $params);
		}

		foreach ($menu as $k => $v) {
			if ($k{0} == '@') {
				$link = $f3->alias(str_replace('@', '', $k));
			} else if ($k{0} == '/') {
				$link = $k;
			} else {
				$link = $this->path.'/'.$k;
			}
			$menu[$k]['link'] = $link;
			$uri = $f3->get('URI');
			$menu[$k]['active'] = (bool)(strpos($uri, $link) === 0);
			$menu[$k]['current'] = (bool)($uri === $link);
			if ($menu[$k]['active'] && $menu[$k]['submenu']) {
				$path = $this->path;
				$this->path = $link;
				$this->level++;
				$this->proccessMenu($menu[$k]['submenu']);
				$this->level--;
				$this->path = $path;
			}
		}
		array_unshift($this->menuList, $menu);
	}
	
	public function getMenu() {
		return $this->menu;
	}
	
	public function getMenuList() {
		return $this->menuList;
	}
	
}
