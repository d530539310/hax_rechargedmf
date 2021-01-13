<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_hax_rechargedmf_group extends discuz_table
{
	public function __construct() {
		$this->_table = 'hax_rechargedmf_group';
		$this->_pk = 'groupid';
		parent::__construct();
	}
	
	public function fetch_all() {
		$grp = DB::fetch_all("SELECT * FROM %t", array($this->_table));
		foreach($grp as $val){
			$return[$val['groupid']]=$val;
		}
		return $return;
	}
	
	
}

?>