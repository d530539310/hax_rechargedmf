<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_hax_rechargedmf_orderlog extends discuz_table {

	public function __construct() {
		$this->_table = 'hax_rechargedmf_orderlog';
		$this->_pk    = 'orderid';

		parent::__construct();
	}
	
	public function del_order() {
		return DB::query("delete FROM %t where state=0",array($this->_table));
	}
}

?>