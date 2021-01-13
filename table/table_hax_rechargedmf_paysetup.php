<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_hax_rechargedmf_paysetup extends discuz_table
{
	public function __construct() {
		$this->_table = 'hax_rechargedmf_paysetup';
		$this->_pk = 'paymethodid';
		parent::__construct();
	}
	
	public function fetch_all() {
		$pay = DB::fetch_all("SELECT * FROM %t", array($this->_table));
		foreach($pay as $val){
			$return[$val['paymethodid']]=$val;
		}
		return $return;
	}
	
	
}

?>