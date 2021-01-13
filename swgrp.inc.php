<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

global $_G;
$hax_rechargedmf = $_G['cache']['plugin']['hax_rechargedmf'];
if(!$_G['uid']) {
	showmessage('not_loggedin', NULL, array(), array('login' => 1));
}
require_once("alipay/vip_function.func.php");
$groupid=intval($_GET['grpid']);
$ret=switchgroup($groupid,$_G['uid']);
echo json_encode(array('state' =>$ret['state'],'msg' =>$ret['msg']));
?>