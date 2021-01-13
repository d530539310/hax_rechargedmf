<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_hax_rechargedmf_credit extends discuz_table
{
	public function __construct() {
		$this->_table = 'hax_rechargedmf_credit';
		$this->_pk = 'creditid';
		parent::__construct();
	}
	
	public function fetch_all() {
		$crd = DB::fetch_all("SELECT * FROM %t", array($this->_table));
		foreach($crd as $val){
			$return[$val['creditid']]=$val;
		}
		return $return;
	}
	
	
}

?>